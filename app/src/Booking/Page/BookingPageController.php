<?php

declare(strict_types=1);

namespace App\Booking\Page;

use App\Booking\Model\Reservation;
use App\Booking\Service\BookingException;
use App\Booking\Service\PriceCalculator;
use App\Booking\Service\ProformaPdf;
use App\Booking\Service\ReservationService;
use App\Booking\Service\StripeGateway;
use DateTimeImmutable;
use PageController;
use Psr\Log\LoggerInterface;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\Validation\RequiredFieldsValidator;
use SilverStripe\i18n\i18n;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\Security\SecurityToken;
use SilverStripe\SiteConfig\SiteConfig;
use Throwable;

/**
 * @extends PageController<BookingPage>
 */
class BookingPageController extends PageController
{
    private const SESSION_REF = 'VDBooking.LastReference';

    private static array $allowed_actions = [
        'RequestForm',
        'received',
        'pay',
        'checkout',
        'proforma',
        'success',
    ];

    private ?Form $requestForm = null;

    // ---------------------------------------------------------------------
    // Step 1: request
    // ---------------------------------------------------------------------

    public function RequestForm(): Form
    {
        if ($this->requestForm) {
            return $this->requestForm;
        }

        $countries = i18n::getData()->getCountries();
        asort($countries);

        $terms = $this->data()->TermsPage();
        $termsLabel = $terms->exists()
            ? _t('VDBooking.AcceptTermsLink', 'I accept the <a href="{link}" target="_blank" rel="noopener">rental terms</a> and the payment schedule.', ['link' => $terms->Link()])
            : _t('VDBooking.AcceptTerms', 'I accept the rental terms and the payment schedule.');

        $hidden = static fn (string $name, string $model): HiddenField => HiddenField::create($name)->setAttribute('x-bind:value', $model);

        $fields = FieldList::create(
            $hidden('Arrival', 'arrival'),
            $hidden('Departure', 'departure'),
            $hidden('Adults', 'adults'),
            $hidden('Children', 'children'),
            $hidden('Infants', 'infants'),
            TextField::create('FirstName', _t('VDBooking.FirstName', 'First name'))->addExtraClass('half')->setAttribute('autocomplete', 'given-name'),
            TextField::create('LastName', _t('VDBooking.LastName', 'Last name'))->addExtraClass('half')->setAttribute('autocomplete', 'family-name'),
            EmailField::create('Email', _t('VDBooking.Email', 'E-mail'))->addExtraClass('half')->setAttribute('autocomplete', 'email'),
            TextField::create('Phone', _t('VDBooking.Phone', 'Phone'))->addExtraClass('half')->setAttribute('autocomplete', 'tel')->setAttribute('type', 'tel'),
            TextField::create('Street', _t('VDBooking.Street', 'Street & number'))->setAttribute('autocomplete', 'street-address'),
            TextField::create('PostalCode', _t('VDBooking.PostalCode', 'Postal code'))->addExtraClass('half')->setAttribute('autocomplete', 'postal-code'),
            TextField::create('City', _t('VDBooking.City', 'City'))->addExtraClass('half')->setAttribute('autocomplete', 'address-level2'),
            DropdownField::create('Country', _t('VDBooking.Country', 'Country'), $countries)
                ->setEmptyString(_t('VDBooking.ChooseCountry', '— please choose —'))
                ->setAttribute('autocomplete', 'country'),
            TextareaField::create('Message', _t('VDBooking.Message', 'Message (optional)'))->setRows(4),
            CheckboxField::create('AcceptTerms', DBField::create_field('HTMLFragment', $termsLabel)),
            TextField::create('Website', 'Website')->addExtraClass('vd-hp')->setAttribute('tabindex', '-1')->setAttribute('autocomplete', 'off'),
        );

        $actions = FieldList::create(
            FormAction::create('doRequest', _t('VDBooking.SendRequest', 'Send booking request'))
                ->setUseButtonTag(true)
                ->addExtraClass('btn btn-primary')
                ->setAttribute('x-bind:disabled', '!quote || !quote.valid || loading'),
        );

        $form = Form::create(
            $this,
            'RequestForm',
            $fields,
            $actions,
            RequiredFieldsValidator::create(['FirstName', 'LastName', 'Email', 'Phone', 'Country', 'AcceptTerms']),
        );
        $form->addExtraClass('vd-form vd-booking-form');

        return $this->requestForm = $form;
    }

    public function doRequest(array $data, Form $form): HTTPResponse
    {
        // Honeypot: pretend success
        if (!empty($data['Website'])) {
            return $this->redirect($this->Link('received'));
        }

        $quote = PriceCalculator::singleton()->quote(
            $this->parseDate($data['Arrival'] ?? null),
            $this->parseDate($data['Departure'] ?? null),
            (int) ($data['Adults'] ?? 0),
            (int) ($data['Children'] ?? 0),
            (int) ($data['Infants'] ?? 0),
        );

        if (!$quote->isValid()) {
            return $this->failForm($form, $data, implode(' ', $quote->errors));
        }

        try {
            $reservation = ReservationService::singleton()->createRequest($quote, $data, i18n::get_locale());
        } catch (BookingException $e) {
            return $this->failForm($form, $data, $e->getMessage());
        } catch (Throwable $e) {
            Injector::inst()->get(LoggerInterface::class)->error('Booking request failed: ' . $e->getMessage());

            return $this->failForm($form, $data, _t('VDBooking.ErrGeneric', 'Something went wrong. Please try again or contact us directly.'));
        }

        $this->getRequest()->getSession()->set(self::SESSION_REF, $reservation->Reference);

        return $this->redirect($this->Link('received'));
    }

    /**
     * Initial widget state: query string (from the hero widget) or the re-displayed form.
     *
     * @return array<string, string|int>
     */
    public function initialState(): array
    {
        $req = $this->getRequest();
        $fields = $this->RequestForm()->Fields();
        $pick = static fn (string $key, string $field) => $req->getVar($key) ?? $fields->dataFieldByName($field)?->getValue();

        return [
            'arrival' => (string) ($this->parseDate($pick('arrival', 'Arrival'))?->format('Y-m-d') ?? ''),
            'departure' => (string) ($this->parseDate($pick('departure', 'Departure'))?->format('Y-m-d') ?? ''),
            'adults' => max(1, (int) ($req->getVar('adults') ?? $req->getVar('guests') ?? $fields->dataFieldByName('Adults')?->getValue() ?: 2)),
            'children' => max(0, (int) $pick('children', 'Children')),
            'infants' => max(0, (int) $pick('infants', 'Infants')),
        ];
    }

    public function InitialStateJSON(): string
    {
        return htmlspecialchars((string) json_encode($this->initialState()), ENT_QUOTES);
    }

    public function received(HTTPRequest $request): array
    {
        return [
            'Reference' => $request->getSession()->get(self::SESSION_REF),
        ];
    }

    // ---------------------------------------------------------------------
    // Step 2: payment
    // ---------------------------------------------------------------------

    public function pay(HTTPRequest $request): array
    {
        $reservation = $this->reservationFromToken($request->param('ID'));
        $config = SiteConfig::current_site_config();

        return [
            'Reservation' => $reservation,
            'StripeEnabled' => Injector::inst()->get(StripeGateway::class)->isEnabled(),
            'BankEnabled' => $config->BankTransferEnabled(),
            'PaymentReference' => (new ProformaPdf())->paymentReference($reservation),
            'Cancelled' => (bool) $request->getVar('cancelled'),
            'SecurityTokenName' => SecurityToken::inst()->getName(),
            'SecurityTokenValue' => SecurityToken::inst()->getValue(),
        ];
    }

    public function checkout(HTTPRequest $request): HTTPResponse
    {
        if (!$request->isPOST() || !SecurityToken::inst()->checkRequest($request)) {
            return $this->httpError(400);
        }

        $reservation = $this->reservationFromToken($request->postVar('token'));
        $token = $reservation->AccessToken;

        if (!$reservation->dueNow()) {
            return $this->redirect($this->Link('pay/' . $token));
        }

        try {
            $url = Injector::inst()->get(StripeGateway::class)->createCheckout(
                $reservation,
                $this->AbsoluteLink('success/' . $token),
                $this->AbsoluteLink('pay/' . $token) . '?cancelled=1',
            );
        } catch (Throwable $e) {
            Injector::inst()->get(LoggerInterface::class)->error('Stripe checkout failed: ' . $e->getMessage(), ['reservation' => $reservation->ID]);

            return $this->redirect($this->Link('pay/' . $token) . '?cancelled=1');
        }

        return $this->redirect($url);
    }

    public function proforma(HTTPRequest $request): HTTPResponse
    {
        $reservation = $this->reservationFromToken($request->param('ID'));
        if (!$reservation->dueNow()) {
            return $this->httpError(404);
        }

        $pdf = new ProformaPdf();
        $response = HTTPResponse::create($pdf->render($reservation));
        $response->addHeader('Content-Type', 'application/pdf');
        $response->addHeader('Content-Disposition', 'inline; filename="' . $pdf->filename($reservation) . '"');
        $response->addHeader('Cache-Control', 'private, no-store');
        $response->addHeader('X-Robots-Tag', 'noindex');

        return $response;
    }

    public function success(HTTPRequest $request): array
    {
        return [
            'Reservation' => $this->reservationFromToken($request->param('ID')),
        ];
    }

    // ---------------------------------------------------------------------

    private function reservationFromToken(mixed $token): Reservation
    {
        $token = is_string($token) ? $token : '';
        if (!preg_match('/^[a-f0-9]{48}$/', $token)) {
            $this->httpError(404);
        }

        /** @var Reservation|null $reservation */
        $reservation = Reservation::get()->filter('AccessToken', $token)->first();
        if (!$reservation) {
            $this->httpError(404);
        }

        // Private pages: never cache or index
        $this->getResponse()->addHeader('Cache-Control', 'private, no-store');
        $this->getResponse()->addHeader('X-Robots-Tag', 'noindex, nofollow');

        return $reservation;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function failForm(Form $form, array $data, string $message): HTTPResponse
    {
        $form->sessionError($message);
        $form->setSessionData($data);

        return $this->redirectBack();
    }

    private function parseDate(mixed $value): ?DateTimeImmutable
    {
        if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        return DateTimeImmutable::createFromFormat('!Y-m-d', $value) ?: null;
    }
}
