<?php

declare(strict_types=1);

namespace App\Booking\Admin;

use App\Booking\Model\Reservation;
use App\Booking\Service\BookingException;
use App\Booking\Service\ReservationService;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;
use SilverStripe\ORM\FieldType\DBHTMLText;
use Throwable;

/**
 * Workflow buttons on the reservation edit form.
 *
 * @extends Extension<GridFieldDetailForm_ItemRequest>
 */
class ReservationItemRequestExtension extends Extension
{
    private static array $allowed_actions = [
        'doVdApprove',
        'doVdDecline',
        'doVdCancel',
        'doVdResend',
        'doVdBankPaid',
        'doVdCashPaid',
    ];

    public function updateFormActions(FieldList $actions): void
    {
        $record = $this->owner->getRecord();
        if (!$record instanceof Reservation || !$record->isInDB() || !$record->canEdit()) {
            return;
        }

        $buttons = [];
        switch ($record->Status) {
            case 'requested':
                $buttons[] = $this->button('doVdApprove', 'Approve & send payment link', 'btn-success font-icon-check-mark');
                $buttons[] = $this->button('doVdDecline', 'Decline', 'btn-outline-danger font-icon-cancel', 'Decline this request and notify the guest? (Reason from the Internal tab)');
                break;
            case 'approved':
            case 'confirmed':
                $buttons[] = $this->button('doVdBankPaid', 'Bank transfer received (' . $record->DueNowFormatted() . ')', 'btn-success font-icon-bank');
                $buttons[] = $this->button('doVdCashPaid', 'Cash received', 'btn-outline-secondary');
                $buttons[] = $this->button('doVdResend', 'Resend payment link', 'btn-outline-secondary font-icon-email');
                $buttons[] = $this->button('doVdCancel', 'Cancel booking', 'btn-outline-danger font-icon-cancel', 'Cancel this booking and notify the guest? Refunds are handled manually.');
                break;
            case 'paid':
                $buttons[] = $this->button('doVdCancel', 'Cancel booking', 'btn-outline-danger font-icon-cancel', 'Cancel this booking and notify the guest? Refunds are handled manually.');
                break;
        }

        foreach ($buttons as $button) {
            $actions->push($button);
        }
    }

    public function doVdApprove(array $data, Form $form): HTTPResponse|DBHTMLText
    {
        return $this->run($form, fn (Reservation $r) => $this->service()->approve($r), 'Approved – payment link sent to the guest.');
    }

    public function doVdDecline(array $data, Form $form): HTTPResponse|DBHTMLText
    {
        return $this->run($form, fn (Reservation $r) => $this->service()->decline($r, (string) ($data['DeclineReason'] ?? '')), 'Request declined.');
    }

    public function doVdCancel(array $data, Form $form): HTTPResponse|DBHTMLText
    {
        return $this->run($form, fn (Reservation $r) => $this->service()->cancel($r, (string) ($data['DeclineReason'] ?? '')), 'Booking cancelled.');
    }

    public function doVdResend(array $data, Form $form): HTTPResponse|DBHTMLText
    {
        return $this->run($form, fn (Reservation $r) => $this->service()->resendPaymentLink($r), 'Payment link sent again.');
    }

    public function doVdBankPaid(array $data, Form $form): HTTPResponse|DBHTMLText
    {
        return $this->run($form, fn (Reservation $r) => $this->service()->recordManualPayment($r, 'bank'), 'Payment recorded.');
    }

    public function doVdCashPaid(array $data, Form $form): HTTPResponse|DBHTMLText
    {
        return $this->run($form, fn (Reservation $r) => $this->service()->recordManualPayment($r, 'cash'), 'Payment recorded.');
    }

    /**
     * @param callable(Reservation): mixed $action
     */
    private function run(Form $form, callable $action, string $success): HTTPResponse|DBHTMLText
    {
        $record = $this->owner->getRecord();
        if (!$record instanceof Reservation || !$record->canEdit()) {
            return $this->owner->httpError(403);
        }

        try {
            $action($record);
            $message = $success;
        } catch (BookingException $e) {
            $message = $e->getMessage();
        } catch (Throwable $e) {
            $message = 'Error: ' . $e->getMessage();
        }

        // Services may have written a fresh copy – refresh the in-memory record so
        // the re-rendered form shows the new status and buttons.
        if ($fresh = Reservation::get()->byID($record->ID)) {
            foreach ($fresh->toMap() as $field => $value) {
                $record->setField($field, $value);
            }
        }

        // The CMS can't "redirect" to the same URL via ajax – return the edit view (see redirectAfterSave()).
        $controller = Controller::curr();
        $controller->getResponse()->addHeader('X-Status', rawurlencode($message));

        return $this->owner->edit($controller->getRequest());
    }

    private function button(string $action, string $title, string $classes, ?string $confirm = null): FormAction
    {
        $button = FormAction::create($action, $title)
            ->setUseButtonTag(true)
            ->addExtraClass('btn ' . $classes);

        if ($confirm) {
            $button->setAttribute('data-vd-confirm', $confirm);
        }

        return $button;
    }

    private function service(): ReservationService
    {
        return ReservationService::singleton();
    }
}
