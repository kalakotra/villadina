<?php

declare(strict_types=1);

namespace App\Task\Seed;

use App\Booking\Page\BookingPage;
use App\Elements\AmenitiesElement;
use App\Elements\BookingElement;
use App\Elements\CTAElement;
use App\Elements\FAQElement;
use App\Elements\GalleryElement;
use App\Elements\HeroElement;
use App\Elements\Items\Amenity;
use App\Elements\Items\Distance;
use App\Elements\Items\FAQItem;
use App\Elements\Items\KeyFact;
use App\Elements\Items\Review;
use App\Elements\KeyFactsElement;
use App\Elements\LocationElement;
use App\Elements\ReviewsElement;
use App\Elements\TextImageElement;

/**
 * Starter content in HR / DE / EN.
 *
 * Everything villa-specific (rooms, guests, prices, distances, coordinates, legal texts)
 * is EXAMPLE content — replace it with the real data in the CMS.
 *
 * Translatable values are ['hr' => …, 'de' => …, 'en' => …].
 */
final class SeedData
{
    /**
     * Image key => file in app/seed/images + alt/title
     *
     * @return array<string, array{file: string, title: string}>
     */
    public static function images(): array
    {
        return [
            'villa' => ['file' => 'villa-pool-sunset.jpg', 'title' => 'Villa Dina – Pool'],
            'terrace' => ['file' => 'terrace-sea-view.jpg', 'title' => 'Villa Dina – Terrace with sea view'],
            'labin' => ['file' => 'labin-old-town.jpg', 'title' => 'Labin old town'],
            'living' => ['file' => 'living-room.jpg', 'title' => 'Villa Dina – Living room'],
            'bedroom' => ['file' => 'bedroom.jpg', 'title' => 'Villa Dina – Bedroom'],
            'kitchen' => ['file' => 'kitchen-dining.jpg', 'title' => 'Villa Dina – Kitchen & dining'],
            'pool' => ['file' => 'pool.jpg', 'title' => 'Villa Dina – Pool deck'],
            'olives' => ['file' => 'olive-grove.jpg', 'title' => 'Olive grove'],
            'rabac' => ['file' => 'rabac-bay.jpg', 'title' => 'Rabac bay'],
            'evening' => ['file' => 'terrace-evening.jpg', 'title' => 'Villa Dina – Terrace in the evening'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function siteConfig(): array
    {
        return [
            'Title' => 'Villa Dina',
            'Tagline' => ['hr' => 'Kuća za odmor u Istri', 'de' => 'Ferienhaus in Istrien', 'en' => 'Holiday home in Istria'],
            'VillaSubtitle' => 'Šumber · Labin · Istra',
            'Email' => 'info@example.com',
            'Address' => "Šumber\nSveta Nedelja, Istra\nHrvatska",
            'MaxGuests' => 8,
            'PriceFrom' => ['hr' => 'od 140 € / noć', 'de' => 'ab 140 € / Nacht', 'en' => 'from €140 / night'],
            'DepositPercent' => 30,
            'BalanceDaysBefore' => 60,
            'DepositDays' => 3,
            'BalanceReminderDays' => 14,
            'MinLeadDays' => 2,
            'TouristTaxAdult' => 1.60,
            'TouristTaxChildPercent' => 50,
            'TouristTaxOnline' => true,
            'OwnerName' => '[Ime vlasnika / Name des Vermieters]',
            'OwnerAddress' => "[Adresa]\n[Grad]",
            'CancellationPolicy' => [
                'hr' => "Do 60 dana prije dolaska: povrat uplate umanjen za 30 % predujma.\nManje od 60 dana prije dolaska: 100 % iznosa.\n(Primjer – prilagodite vlastitim uvjetima.)",
                'de' => "Bis 60 Tage vor Anreise: Erstattung abzüglich der Anzahlung (30 %).\nWeniger als 60 Tage vor Anreise: 100 % des Betrags.\n(Beispiel – bitte an Ihre Bedingungen anpassen.)",
                'en' => "Up to 60 days before arrival: refund minus the 30 % deposit.\nLess than 60 days before arrival: 100 % of the amount.\n(Example – adapt to your own terms.)",
            ],
        ];
    }

    /**
     * Seasons for a given year. Weekly high season Sat–Sat.
     *
     * @return list<array<string, mixed>>
     */
    public static function seasons(int $year): array
    {
        $saturdayOnOrAfter = static function (string $date): \DateTimeImmutable {
            $d = new \DateTimeImmutable($date);
            $diff = (6 - (int) $d->format('N') + 7) % 7;

            return $d->modify("+{$diff} days");
        };

        $highStart = $saturdayOnOrAfter("{$year}-06-26");
        $highEnd = $saturdayOnOrAfter("{$year}-08-28")->modify('-1 day'); // last night (Friday)

        return [
            [
                'Title' => ['hr' => 'Predsezona', 'de' => 'Vorsaison', 'en' => 'Early season'],
                'StartDate' => "{$year}-04-01",
                'EndDate' => $highStart->modify('-1 day')->format('Y-m-d'),
                'Mode' => 'nightly',
                'NightlyPrice' => 140,
                'MinNights' => 3,
                'ArrivalDay' => 0,
            ],
            [
                'Title' => ['hr' => 'Glavna sezona', 'de' => 'Hauptsaison', 'en' => 'High season'],
                'StartDate' => $highStart->format('Y-m-d'),
                'EndDate' => $highEnd->format('Y-m-d'),
                'Mode' => 'weekly',
                'WeeklyPrice' => 1890,
                'MinNights' => 7,
                'ArrivalDay' => 6,
                'OverrideTouristTax' => true,
                'TouristTaxAdult' => 1.86,
            ],
            [
                'Title' => ['hr' => 'Posezona', 'de' => 'Nachsaison', 'en' => 'Late season'],
                'StartDate' => $highEnd->modify('+1 day')->format('Y-m-d'),
                'EndDate' => "{$year}-10-31",
                'Mode' => 'nightly',
                'NightlyPrice' => 150,
                'MinNights' => 3,
                'ArrivalDay' => 0,
            ],
        ];
    }

    /**
     * Page tree. 'type': modular | booking | blog | form | plain
     *
     * @return list<array<string, mixed>>
     */
    public static function pages(): array
    {
        return [
            [
                'key' => 'home',
                'type' => 'modular',
                'Title' => ['hr' => 'Početna', 'de' => 'Startseite', 'en' => 'Home'],
                'MenuTitle' => ['hr' => 'Početna', 'de' => 'Start', 'en' => 'Home'],
                'URLSegment' => 'home',
                'ShowInMenus' => false,
                'MetaDescription' => [
                    'hr' => 'Villa Dina – kamena kuća za odmor s bazenom u Šumberu kraj Labina, Istra. Mir, priroda i more na dohvat ruke.',
                    'de' => 'Villa Dina – Ferienhaus aus Naturstein mit Pool in Šumber bei Labin, Istrien. Ruhe, Natur und das Meer in der Nähe.',
                    'en' => 'Villa Dina – stone holiday home with private pool in Šumber near Labin, Istria. Peace, nature and the sea close by.',
                ],
                'elements' => 'home',
            ],
            [
                'key' => 'villa',
                'type' => 'modular',
                'Title' => ['hr' => 'Vila', 'de' => 'Die Villa', 'en' => 'The villa'],
                'URLSegment' => ['hr' => 'vila', 'de' => 'villa', 'en' => 'villa'],
                'header' => 'living',
                'elements' => 'villa',
            ],
            [
                'key' => 'gallery',
                'type' => 'modular',
                'Title' => ['hr' => 'Galerija', 'de' => 'Galerie', 'en' => 'Gallery'],
                'URLSegment' => ['hr' => 'galerija', 'de' => 'galerie', 'en' => 'gallery'],
                'elements' => 'gallery',
            ],
            [
                'key' => 'location',
                'type' => 'modular',
                'Title' => ['hr' => 'Lokacija', 'de' => 'Lage', 'en' => 'Location'],
                'URLSegment' => ['hr' => 'lokacija', 'de' => 'lage', 'en' => 'location'],
                'header' => 'labin',
                'elements' => 'location',
            ],
            [
                'key' => 'booking',
                'type' => 'booking',
                'Title' => ['hr' => 'Rezervacija', 'de' => 'Buchen', 'en' => 'Book'],
                'URLSegment' => ['hr' => 'rezervacija', 'de' => 'buchen', 'en' => 'book'],
                'Content' => [
                    'hr' => '<p>Odaberite termin i broj gostiju – cijenu vidite odmah. Nakon provjere dostupnosti šaljemo vam poveznicu za plaćanje.</p>',
                    'de' => '<p>Wählen Sie Reisedaten und Gäste – den Preis sehen Sie sofort. Nach Prüfung der Verfügbarkeit erhalten Sie einen Zahlungslink.</p>',
                    'en' => '<p>Choose your dates and guests – you see the price instantly. Once we have checked availability, we send you a payment link.</p>',
                ],
            ],
            [
                'key' => 'blog',
                'type' => 'blog',
                'Title' => ['hr' => 'Istra savjeti', 'de' => 'Istrien-Tipps', 'en' => 'Istria tips'],
                'URLSegment' => ['hr' => 'savjeti', 'de' => 'tipps', 'en' => 'tips'],
            ],
            [
                'key' => 'contact',
                'type' => 'form',
                'Title' => ['hr' => 'Kontakt', 'de' => 'Kontakt', 'en' => 'Contact'],
                'URLSegment' => ['hr' => 'kontakt', 'de' => 'kontakt', 'en' => 'contact'],
                'Content' => [
                    'hr' => '<p>Imate pitanje o vili, okolici ili dolasku? Pišite nam – odgovaramo u roku od 24 sata.</p>',
                    'de' => '<p>Fragen zur Villa, zur Umgebung oder zur Anreise? Schreiben Sie uns – wir antworten innerhalb von 24 Stunden.</p>',
                    'en' => '<p>Questions about the villa, the area or getting here? Write to us – we reply within 24 hours.</p>',
                ],
            ],
            [
                'key' => 'terms',
                'type' => 'plain',
                'Title' => ['hr' => 'Uvjeti najma', 'de' => 'Mietbedingungen', 'en' => 'Rental terms'],
                'URLSegment' => ['hr' => 'uvjeti-najma', 'de' => 'mietbedingungen', 'en' => 'rental-terms'],
                'ShowInMenus' => false,
                'Content' => [
                    'hr' => '<h2>Rezervacija i plaćanje</h2><p>Rezervacija je potvrđena primitkom predujma od 30 %. Ostatak dospijeva 60 dana prije dolaska. Za rezervacije unutar 60 dana do dolaska plaća se puni iznos odmah.</p><h2>Dolazak i odlazak</h2><p>Dolazak od 16:00, odlazak do 10:00. U glavnoj sezoni dolazak i odlazak subotom.</p><h2>Otkazivanje</h2><p>[Unesite vlastite uvjete otkazivanja.]</p><p><em>Primjer teksta – prilagodite i pravno provjerite.</em></p>',
                    'de' => '<h2>Buchung und Zahlung</h2><p>Die Buchung ist mit Eingang der Anzahlung von 30 % bestätigt. Der Restbetrag ist 60 Tage vor Anreise fällig. Bei Buchungen innerhalb von 60 Tagen vor Anreise ist der Gesamtbetrag sofort fällig.</p><h2>An- und Abreise</h2><p>Anreise ab 16:00 Uhr, Abreise bis 10:00 Uhr. In der Hauptsaison An- und Abreise samstags.</p><h2>Stornierung</h2><p>[Bitte eigene Stornobedingungen eintragen.]</p><p><em>Beispieltext – bitte anpassen und rechtlich prüfen lassen.</em></p>',
                    'en' => '<h2>Booking and payment</h2><p>Your booking is confirmed once the 30 % deposit has been received. The balance is due 60 days before arrival. Bookings made within 60 days of arrival are paid in full immediately.</p><h2>Arrival and departure</h2><p>Check-in from 4 pm, check-out by 10 am. In high season arrival and departure on Saturdays.</p><h2>Cancellation</h2><p>[Enter your own cancellation terms.]</p><p><em>Example text – please adapt and have it legally reviewed.</em></p>',
                ],
            ],
            [
                'key' => 'imprint',
                'type' => 'plain',
                'Title' => ['hr' => 'Impressum', 'de' => 'Impressum', 'en' => 'Imprint'],
                'URLSegment' => ['hr' => 'impressum', 'de' => 'impressum', 'en' => 'imprint'],
                'ShowInMenus' => false,
                'Content' => [
                    'hr' => '<p>[Naziv / ime vlasnika]<br>[Adresa]<br>OIB: [OIB]<br>E-pošta: [e-mail]<br>Telefon: [telefon]</p>',
                    'de' => '<p>Angaben gemäß § 5 DDG:<br>[Name des Vermieters]<br>[Anschrift]<br>OIB / USt-IdNr.: [Nummer]<br>E-Mail: [E-Mail]<br>Telefon: [Telefon]</p>',
                    'en' => '<p>[Owner / company name]<br>[Address]<br>Tax ID (OIB): [number]<br>E-mail: [e-mail]<br>Phone: [phone]</p>',
                ],
            ],
            [
                'key' => 'privacy',
                'type' => 'plain',
                'Title' => ['hr' => 'Zaštita podataka', 'de' => 'Datenschutz', 'en' => 'Privacy policy'],
                'URLSegment' => ['hr' => 'zastita-podataka', 'de' => 'datenschutz', 'en' => 'privacy'],
                'ShowInMenus' => false,
                'Content' => [
                    'hr' => '<p>[Unesite izjavu o zaštiti podataka: voditelj obrade, svrhe (upiti, rezervacije, plaćanja putem Stripea), rokovi čuvanja, prava ispitanika, kolačići, karta (OpenStreetMap se učitava tek nakon pristanka).]</p>',
                    'de' => '<p>[Datenschutzerklärung einfügen: Verantwortlicher, Zwecke (Anfragen, Buchungen, Zahlungen über Stripe), Speicherdauer, Betroffenenrechte, Cookies, Karte (OpenStreetMap wird erst nach Einwilligung geladen).]</p>',
                    'en' => '<p>[Insert privacy policy: controller, purposes (enquiries, bookings, payments via Stripe), retention periods, data subject rights, cookies, map (OpenStreetMap only loads after consent).]</p>',
                ],
            ],
        ];
    }

    /**
     * Element definitions per page.
     *
     * Keys: class, style, fields, hasOne (relation => image key), slides (relation => image keys),
     *       links (relation => [page, text]), items (relation => [class, rows])
     *
     * @return list<array<string, mixed>>
     */
    public static function elements(string $set): array
    {
        return match ($set) {
            'home' => [
                [
                    'class' => HeroElement::class,
                    'fields' => [
                        'Title' => ['hr' => 'Kamen, maslina *i more*', 'de' => 'Stein, Olive *und Meer*', 'en' => 'Stone, olives *and the sea*'],
                        'ShowTitle' => true,
                        'Eyebrow' => 'Šumber · Labin · Istra',
                        'Lead' => [
                            'hr' => 'Obnovljena istarska kamena kuća s privatnim bazenom, okružena maslinicima – petnaestak minuta do plaža Rapca.',
                            'de' => 'Ein restauriertes istrisches Steinhaus mit privatem Pool zwischen Olivenhainen – rund fünfzehn Minuten zu den Stränden von Rabac.',
                            'en' => 'A restored Istrian stone house with a private pool among olive groves – about fifteen minutes to the beaches of Rabac.',
                        ],
                        'Size' => 'full',
                        'ShowBooking' => true,
                    ],
                    'slides' => ['Slides' => ['villa', 'terrace', 'evening']],
                    'links' => [
                        'PrimaryLink' => ['villa', ['hr' => 'Upoznajte vilu', 'de' => 'Die Villa entdecken', 'en' => 'Discover the villa']],
                        'SecondaryLink' => ['gallery', ['hr' => 'Galerija', 'de' => 'Galerie', 'en' => 'Gallery']],
                    ],
                ],
                self::keyFacts(),
                [
                    'class' => TextImageElement::class,
                    'fields' => [
                        'Title' => ['hr' => 'Mjesto za *usporiti*', 'de' => 'Ein Ort zum *Ankommen*', 'en' => 'A place to *slow down*'],
                        'ShowTitle' => true,
                        'Eyebrow' => ['hr' => 'Dobrodošli', 'de' => 'Willkommen', 'en' => 'Welcome'],
                        'Content' => [
                            'hr' => '<p>Villa Dina stoji na rubu malog sela Šumber, iznad zelenih dolina istočne Istre. Debeli kameni zidovi ljeti čuvaju svježinu, a terasa uz bazen pogled na brežuljke i zalazak sunca.</p><p>Labin je udaljen nekoliko minuta vožnje, a do mora u Rapcu stižete za otprilike četvrt sata.</p>',
                            'de' => '<p>Die Villa Dina liegt am Rand des kleinen Dorfes Šumber, über den grünen Tälern Ost-Istriens. Dicke Steinmauern halten im Sommer die Kühle, die Poolterrasse den Blick auf die Hügel und den Sonnenuntergang.</p><p>Labin erreichen Sie in wenigen Autominuten, das Meer in Rabac in etwa einer Viertelstunde.</p>',
                            'en' => '<p>Villa Dina sits on the edge of the small village of Šumber, above the green valleys of eastern Istria. Thick stone walls keep the summer heat out, and the pool terrace frames the hills and the sunset.</p><p>Labin is a few minutes\' drive away, the sea at Rabac about a quarter of an hour.</p>',
                        ],
                        'ImagePosition' => 'right',
                        'ImageFormat' => 'portrait',
                    ],
                    'hasOne' => ['Image' => 'terrace', 'SecondaryImage' => 'olives'],
                    'links' => ['CTALink' => ['villa', ['hr' => 'Više o vili', 'de' => 'Mehr über die Villa', 'en' => 'More about the villa']]],
                ],
                self::amenities('vd-el--sand'),
                [
                    'class' => GalleryElement::class,
                    'fields' => [
                        'Title' => ['hr' => 'Pogled unutra', 'de' => 'Einblicke', 'en' => 'Take a look'],
                        'ShowTitle' => true,
                        'Eyebrow' => ['hr' => 'Galerija', 'de' => 'Galerie', 'en' => 'Gallery'],
                        'DisplayMode' => 'grid',
                    ],
                    'gallery' => ['villa', 'living', 'kitchen', 'bedroom', 'pool', 'terrace', 'evening', 'olives', 'labin'],
                ],
                self::location(),
                self::reviews(),
                self::faq(),
                [
                    'class' => CTAElement::class,
                    'fields' => [
                        'Title' => ['hr' => 'Vaše ljeto *u Istri*', 'de' => 'Ihr Sommer *in Istrien*', 'en' => 'Your summer *in Istria*'],
                        'ShowTitle' => true,
                        'Lead' => [
                            'hr' => 'Provjerite slobodne termine i cijenu u nekoliko klikova.',
                            'de' => 'Freie Termine und Preis in wenigen Klicks prüfen.',
                            'en' => 'Check free dates and the price in a few clicks.',
                        ],
                        'UseInquiryLink' => true,
                    ],
                    'hasOne' => ['BackgroundImage' => 'evening'],
                ],
            ],

            'villa' => [
                self::keyFacts(),
                [
                    'class' => TextImageElement::class,
                    'fields' => [
                        'Title' => ['hr' => 'Prostor za *cijelu obitelj*', 'de' => 'Platz für die *ganze Familie*', 'en' => 'Room for the *whole family*'],
                        'ShowTitle' => true,
                        'Eyebrow' => ['hr' => 'Prizemlje', 'de' => 'Erdgeschoss', 'en' => 'Ground floor'],
                        'Content' => [
                            'hr' => '<p>Otvoreni dnevni boravak s kaminom, potpuno opremljena kuhinja i blagovaonica za osam osoba. Velika staklena vrata vode izravno na terasu i bazen.</p>',
                            'de' => '<p>Offener Wohnbereich mit Kamin, voll ausgestattete Küche und Essbereich für acht Personen. Große Glastüren führen direkt auf Terrasse und Pool.</p>',
                            'en' => '<p>Open-plan living room with fireplace, fully equipped kitchen and dining for eight. Large glass doors open straight onto the terrace and pool.</p>',
                        ],
                        'ImagePosition' => 'left',
                        'ImageFormat' => 'landscape',
                    ],
                    'hasOne' => ['Image' => 'living', 'SecondaryImage' => 'kitchen'],
                ],
                [
                    'class' => TextImageElement::class,
                    'style' => 'vd-el--sand',
                    'fields' => [
                        'Title' => ['hr' => 'Mirne *noći*', 'de' => 'Ruhige *Nächte*', 'en' => 'Quiet *nights*'],
                        'ShowTitle' => true,
                        'Eyebrow' => ['hr' => 'Kat', 'de' => 'Obergeschoss', 'en' => 'Upstairs'],
                        'Content' => [
                            'hr' => '<p>Četiri klimatizirane spavaće sobe s kvalitetnim madracima i posteljinom od pamuka, tri kupaonice s tušem. Ručnici i posteljina uključeni su u cijenu.</p>',
                            'de' => '<p>Vier klimatisierte Schlafzimmer mit hochwertigen Matratzen und Baumwollbettwäsche, drei Duschbäder. Handtücher und Bettwäsche sind im Preis inbegriffen.</p>',
                            'en' => '<p>Four air-conditioned bedrooms with quality mattresses and cotton linen, three shower rooms. Towels and bed linen are included.</p>',
                        ],
                        'ImagePosition' => 'right',
                        'ImageFormat' => 'portrait',
                    ],
                    'hasOne' => ['Image' => 'bedroom'],
                ],
                self::amenities(),
                [
                    'class' => BookingElement::class,
                    'style' => 'vd-el--sand',
                    'fields' => [
                        'Title' => ['hr' => 'Slobodni *termini*', 'de' => 'Freie *Termine*', 'en' => 'Available *dates*'],
                        'ShowTitle' => true,
                        'Eyebrow' => ['hr' => 'Rezervacija', 'de' => 'Buchung', 'en' => 'Booking'],
                        'Note' => [
                            'hr' => 'U glavnoj sezoni dolazak i odlazak subotom, najmanje 7 noćenja.',
                            'de' => 'In der Hauptsaison An- und Abreise samstags, mindestens 7 Nächte.',
                            'en' => 'In high season arrival and departure on Saturdays, minimum 7 nights.',
                        ],
                        'ShowChannels' => true,
                    ],
                ],
            ],

            'gallery' => [
                [
                    'class' => GalleryElement::class,
                    'fields' => [
                        'Title' => ['hr' => 'Galerija', 'de' => 'Galerie', 'en' => 'Gallery'],
                        'ShowTitle' => false,
                        'DisplayMode' => 'grid',
                    ],
                    'gallery' => ['villa', 'terrace', 'living', 'kitchen', 'bedroom', 'pool', 'evening', 'olives', 'labin', 'rabac'],
                ],
                [
                    'class' => CTAElement::class,
                    'fields' => [
                        'Title' => ['hr' => 'Sviđa vam se?', 'de' => 'Gefällt es Ihnen?', 'en' => 'Like what you see?'],
                        'ShowTitle' => true,
                        'UseInquiryLink' => true,
                    ],
                ],
            ],

            'location' => [
                self::location(),
                [
                    'class' => TextImageElement::class,
                    'style' => 'vd-el--sand',
                    'fields' => [
                        'Title' => ['hr' => 'Labin i *Rabac*', 'de' => 'Labin und *Rabac*', 'en' => 'Labin and *Rabac*'],
                        'ShowTitle' => true,
                        'Eyebrow' => ['hr' => 'Okolica', 'de' => 'Umgebung', 'en' => 'Surroundings'],
                        'Content' => [
                            'hr' => '<p>Srednjovjekovni Labin smjestio se na brežuljku iznad mora – kamene uličice, galerije i vidikovci. Ispod njega je Rabac, nekadašnje ribarsko mjesto s uvalama i šljunčanim plažama.</p><p>Za izlete: Park prirode Učka, Opatija i otoci Kvarnera.</p>',
                            'de' => '<p>Das mittelalterliche Labin thront auf einem Hügel über dem Meer – Steingassen, Galerien und Aussichtspunkte. Darunter liegt Rabac, ein ehemaliges Fischerdorf mit Buchten und Kieselstränden.</p><p>Für Ausflüge: Naturpark Učka, Opatija und die Kvarner-Inseln.</p>',
                            'en' => '<p>Medieval Labin sits on a hill above the sea – stone lanes, galleries and viewpoints. Below it lies Rabac, a former fishing village with coves and pebble beaches.</p><p>For day trips: Učka Nature Park, Opatija and the Kvarner islands.</p>',
                        ],
                        'ImagePosition' => 'left',
                        'ImageFormat' => 'landscape',
                    ],
                    'hasOne' => ['Image' => 'rabac', 'SecondaryImage' => 'labin'],
                    'links' => ['CTALink' => ['blog', ['hr' => 'Naši savjeti', 'de' => 'Unsere Tipps', 'en' => 'Our tips']]],
                ],
            ],

            default => [],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private static function keyFacts(): array
    {
        return [
            'class' => KeyFactsElement::class,
            'style' => 'vd-el--tight',
            'fields' => ['Title' => ['hr' => 'Ukratko', 'de' => 'Auf einen Blick', 'en' => 'At a glance'], 'ShowTitle' => false],
            'items' => ['Facts' => [KeyFact::class, [
                ['Value' => '8', 'Label' => ['hr' => 'gostiju', 'de' => 'Gäste', 'en' => 'guests']],
                ['Value' => '4', 'Label' => ['hr' => 'spavaće sobe', 'de' => 'Schlafzimmer', 'en' => 'bedrooms']],
                ['Value' => '3', 'Label' => ['hr' => 'kupaonice', 'de' => 'Bäder', 'en' => 'bathrooms']],
                ['Value' => '~15 min', 'Label' => ['hr' => 'do mora', 'de' => 'zum Meer', 'en' => 'to the sea']],
            ]]],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function amenities(?string $style = null): array
    {
        $a = static fn (string $icon, array $title, array $text): array => ['IconKey' => $icon, 'Title' => $title, 'Description' => $text];

        return [
            'class' => AmenitiesElement::class,
            'style' => $style,
            'fields' => [
                'Title' => ['hr' => 'Sve što *trebate*', 'de' => 'Alles, was Sie *brauchen*', 'en' => 'Everything you *need*'],
                'ShowTitle' => true,
                'Eyebrow' => ['hr' => 'Sadržaji', 'de' => 'Ausstattung', 'en' => 'Amenities'],
            ],
            'items' => ['Amenities' => [Amenity::class, [
                $a('pool', ['hr' => 'Privatni bazen', 'de' => 'Privater Pool', 'en' => 'Private pool'], ['hr' => 'Ležaljke i suncobrani', 'de' => 'Liegen und Sonnenschirme', 'en' => 'Loungers and parasols']),
                $a('sun', ['hr' => 'Natkrivena terasa', 'de' => 'Überdachte Terrasse', 'en' => 'Covered terrace'], ['hr' => 'Blagovanje na otvorenom', 'de' => 'Essen im Freien', 'en' => 'Outdoor dining']),
                $a('bbq', ['hr' => 'Roštilj', 'de' => 'Grill', 'en' => 'Barbecue'], ['hr' => 'Zidani roštilj na drva', 'de' => 'Gemauerter Holzkohlegrill', 'en' => 'Built-in wood grill']),
                $a('wifi', ['hr' => 'Brzi Wi-Fi', 'de' => 'Schnelles WLAN', 'en' => 'Fast Wi-Fi'], ['hr' => 'U cijeloj kući', 'de' => 'Im ganzen Haus', 'en' => 'Throughout the house']),
                $a('ac', ['hr' => 'Klima uređaji', 'de' => 'Klimaanlage', 'en' => 'Air conditioning'], ['hr' => 'U svim sobama', 'de' => 'In allen Zimmern', 'en' => 'In every room']),
                $a('kitchen', ['hr' => 'Opremljena kuhinja', 'de' => 'Voll ausgestattete Küche', 'en' => 'Equipped kitchen'], ['hr' => 'Perilica posuđa, aparat za kavu', 'de' => 'Spülmaschine, Kaffeemaschine', 'en' => 'Dishwasher, coffee machine']),
                $a('parking', ['hr' => 'Parking', 'de' => 'Parkplatz', 'en' => 'Parking'], ['hr' => 'Za dva automobila', 'de' => 'Für zwei Autos', 'en' => 'For two cars']),
                $a('washer', ['hr' => 'Perilica rublja', 'de' => 'Waschmaschine', 'en' => 'Washing machine'], ['hr' => 'Glačalo i sušilo', 'de' => 'Bügeleisen und Wäscheständer', 'en' => 'Iron and drying rack']),
            ]]],
        ];
    }

    /**
     * Coordinates and distances are approximate – verify on site.
     *
     * @return array<string, mixed>
     */
    private static function location(): array
    {
        $d = static fn (string $icon, array $title, string $value): array => ['IconKey' => $icon, 'Title' => $title, 'Value' => $value];

        return [
            'class' => LocationElement::class,
            'fields' => [
                'Title' => ['hr' => 'Mirno, a *blizu svega*', 'de' => 'Ruhig – und doch *nah an allem*', 'en' => 'Quiet, yet *close to everything*'],
                'ShowTitle' => true,
                'Eyebrow' => ['hr' => 'Lokacija', 'de' => 'Lage', 'en' => 'Location'],
                'Content' => [
                    'hr' => '<p>Šumber je malo selo u općini Sveta Nedelja, na brežuljcima zapadno od Labina. Ovdje vas bude ptice, a ne promet.</p>',
                    'de' => '<p>Šumber ist ein kleines Dorf in der Gemeinde Sveta Nedelja, in den Hügeln westlich von Labin. Hier weckt Sie Vogelgesang statt Verkehr.</p>',
                    'en' => '<p>Šumber is a small village in the municipality of Sveta Nedelja, in the hills west of Labin. Here you wake up to birdsong, not traffic.</p>',
                ],
                'Latitude' => 45.159030,
                'Longitude' => 14.100790,
                'MapZoom' => 12,
                'MapProvider' => 'osm',
            ],
            'items' => ['Distances' => [Distance::class, [
                $d('home', ['hr' => 'Labin, stari grad', 'de' => 'Labin, Altstadt', 'en' => 'Labin old town'], '~8 km'),
                $d('sea', ['hr' => 'Plaže Rabac', 'de' => 'Strände Rabac', 'en' => 'Rabac beaches'], '~15 km'),
                $d('shop', ['hr' => 'Trgovina', 'de' => 'Supermarkt', 'en' => 'Supermarket'], '~5 km'),
                $d('car', ['hr' => 'Opatija', 'de' => 'Opatija', 'en' => 'Opatija'], '~45 min'),
                $d('plane', ['hr' => 'Zračna luka Pula', 'de' => 'Flughafen Pula', 'en' => 'Pula airport'], '~50 min'),
            ]]],
        ];
    }

    /**
     * Placeholders only – replace with real guest reviews.
     *
     * @return array<string, mixed>
     */
    private static function reviews(): array
    {
        $placeholder = [
            'hr' => 'Ovdje dolazi stvarna recenzija gosta – kopirajte je iz Booking.com ili Airbnb profila.',
            'de' => 'Hier steht eine echte Gästebewertung – bitte aus Booking.com oder Airbnb übernehmen.',
            'en' => 'A real guest review goes here – copy it from your Booking.com or Airbnb profile.',
        ];

        return [
            'class' => ReviewsElement::class,
            'style' => 'vd-el--sea',
            'fields' => [
                'Title' => ['hr' => 'Što kažu *gosti*', 'de' => 'Was unsere *Gäste* sagen', 'en' => 'What our *guests* say'],
                'ShowTitle' => true,
                'Eyebrow' => ['hr' => 'Recenzije', 'de' => 'Bewertungen', 'en' => 'Reviews'],
            ],
            'items' => ['Reviews' => [Review::class, [
                ['Author' => '[Gast 1]', 'Origin' => ['hr' => '[Grad, država]', 'de' => '[Stadt, Land]', 'en' => '[City, country]'], 'Quote' => $placeholder, 'Rating' => 5, 'Source' => 'booking'],
                ['Author' => '[Gast 2]', 'Origin' => ['hr' => '[Grad, država]', 'de' => '[Stadt, Land]', 'en' => '[City, country]'], 'Quote' => $placeholder, 'Rating' => 5, 'Source' => 'airbnb'],
                ['Author' => '[Gast 3]', 'Origin' => ['hr' => '[Grad, država]', 'de' => '[Stadt, Land]', 'en' => '[City, country]'], 'Quote' => $placeholder, 'Rating' => 5, 'Source' => 'direct'],
            ]]],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function faq(): array
    {
        $q = static fn (array $question, array $answer): array => ['Question' => $question, 'Answer' => $answer];

        return [
            'class' => FAQElement::class,
            'fields' => [
                'Title' => ['hr' => 'Česta *pitanja*', 'de' => 'Häufige *Fragen*', 'en' => 'Frequently asked *questions*'],
                'ShowTitle' => true,
                'Eyebrow' => 'FAQ',
                'OutputSchema' => true,
            ],
            'items' => ['Items' => [FAQItem::class, [
                $q(
                    ['hr' => 'Kada su dolazak i odlazak?', 'de' => 'Wann sind An- und Abreise?', 'en' => 'What are check-in and check-out times?'],
                    ['hr' => '<p>Dolazak od 16:00, odlazak do 10:00. U glavnoj sezoni dolazak i odlazak subotom.</p>', 'de' => '<p>Anreise ab 16:00 Uhr, Abreise bis 10:00 Uhr. In der Hauptsaison samstags.</p>', 'en' => '<p>Check-in from 4 pm, check-out by 10 am. In high season on Saturdays.</p>'],
                ),
                $q(
                    ['hr' => 'Kako funkcionira plaćanje?', 'de' => 'Wie funktioniert die Zahlung?', 'en' => 'How does payment work?'],
                    ['hr' => '<p>Nakon potvrde termina plaćate 30 % predujma karticom ili bankovnom uplatom. Ostatak dospijeva 60 dana prije dolaska.</p>', 'de' => '<p>Nach Bestätigung zahlen Sie 30 % Anzahlung per Karte oder Überweisung. Der Rest ist 60 Tage vor Anreise fällig.</p>', 'en' => '<p>Once your dates are confirmed you pay a 30 % deposit by card or bank transfer. The balance is due 60 days before arrival.</p>'],
                ),
                $q(
                    ['hr' => 'Je li boravišna pristojba uključena?', 'de' => 'Ist die Kurtaxe inklusive?', 'en' => 'Is the tourist tax included?'],
                    ['hr' => '<p>Da, boravišna pristojba prikazana je zasebno i uključena u ukupnu cijenu. Djeca do 12 godina su oslobođena.</p>', 'de' => '<p>Ja, die Kurtaxe wird separat ausgewiesen und ist im Gesamtpreis enthalten. Kinder unter 12 Jahren sind befreit.</p>', 'en' => '<p>Yes, the tourist tax is shown separately and included in the total. Children under 12 are exempt.</p>'],
                ),
                $q(
                    ['hr' => 'Jesu li kućni ljubimci dobrodošli?', 'de' => 'Sind Haustiere erlaubt?', 'en' => 'Are pets allowed?'],
                    ['hr' => '<p>[Unesite vlastito pravilo.]</p>', 'de' => '<p>[Bitte eigene Regel eintragen.]</p>', 'en' => '<p>[Enter your own policy.]</p>'],
                ),
                $q(
                    ['hr' => 'Treba li mi automobil?', 'de' => 'Brauche ich ein Auto?', 'en' => 'Do I need a car?'],
                    ['hr' => '<p>Preporučujemo – do plaža, trgovina i Labina najbrže se stiže automobilom. Parking je uz kuću.</p>', 'de' => '<p>Wir empfehlen es – Strände, Einkaufen und Labin erreichen Sie am besten mit dem Auto. Parkplatz direkt am Haus.</p>', 'en' => '<p>We recommend it – beaches, shops and Labin are best reached by car. Parking is right by the house.</p>'],
                ),
            ]]],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function blogPosts(): array
    {
        return [
            [
                'Title' => ['hr' => 'Pet uvala oko Rapca', 'de' => 'Fünf Buchten rund um Rabac', 'en' => 'Five coves around Rabac'],
                'URLSegment' => ['hr' => 'pet-uvala-oko-rapca', 'de' => 'fuenf-buchten-rabac', 'en' => 'five-coves-rabac'],
                'image' => 'rabac',
                'category' => ['hr' => 'More', 'de' => 'Meer', 'en' => 'Sea'],
                'Summary' => [
                    'hr' => '<p>Od gradske plaže do skrivenih šljunčanih uvala – naši favoriti za kupanje.</p>',
                    'de' => '<p>Vom Stadtstrand bis zu versteckten Kieselbuchten – unsere Lieblingsplätze zum Baden.</p>',
                    'en' => '<p>From the town beach to hidden pebble coves – our favourite swimming spots.</p>',
                ],
                'Content' => [
                    'hr' => '<p>[Primjer članka – zamijenite vlastitim savjetima.] Rabac je okružen uvalama s čistim morem i šljunčanim plažama. Rano ujutro ili predvečer plaže su najmirnije.</p><h2>Savjet</h2><p>Ponesite cipele za vodu i suncobran – mnoge su uvale prirodne, bez sadržaja.</p>',
                    'de' => '<p>[Beispielartikel – bitte durch eigene Tipps ersetzen.] Rabac ist von Buchten mit klarem Wasser und Kieselstränden umgeben. Früh morgens oder am späten Nachmittag ist es am ruhigsten.</p><h2>Tipp</h2><p>Badeschuhe und Sonnenschirm mitnehmen – viele Buchten sind naturbelassen.</p>',
                    'en' => '<p>[Example article – replace with your own tips.] Rabac is surrounded by coves with clear water and pebble beaches. Early morning or late afternoon is the quietest time.</p><h2>Tip</h2><p>Bring water shoes and a parasol – many coves are natural, without facilities.</p>',
                ],
            ],
            [
                'Title' => ['hr' => 'Šetnja starim Labinom', 'de' => 'Ein Spaziergang durch Labins Altstadt', 'en' => 'A walk through old Labin'],
                'URLSegment' => ['hr' => 'setnja-starim-labinom', 'de' => 'spaziergang-labin', 'en' => 'walk-old-labin'],
                'image' => 'labin',
                'category' => ['hr' => 'Izleti', 'de' => 'Ausflüge', 'en' => 'Day trips'],
                'Summary' => [
                    'hr' => '<p>Kamene uličice, galerije i pogled na Kvarner – Labin za jedno popodne.</p>',
                    'de' => '<p>Steingassen, Galerien und Blick auf den Kvarner – Labin für einen Nachmittag.</p>',
                    'en' => '<p>Stone lanes, galleries and views over the Kvarner – Labin in an afternoon.</p>',
                ],
                'Content' => [
                    'hr' => '<p>[Primjer članka – zamijenite vlastitim savjetima.] Stari Labin smjestio se na brežuljku iznad Rapca. Parkirajte ispod staroga grada i prošećite do vidikovca.</p>',
                    'de' => '<p>[Beispielartikel – bitte durch eigene Tipps ersetzen.] Die Altstadt von Labin liegt auf einem Hügel über Rabac. Parken Sie unterhalb und spazieren Sie hinauf zum Aussichtspunkt.</p>',
                    'en' => '<p>[Example article – replace with your own tips.] Old Labin sits on a hill above Rabac. Park below the old town and stroll up to the viewpoint.</p>',
                ],
            ],
        ];
    }

    /**
     * Contact form fields (userforms).
     *
     * @return list<array<string, mixed>>
     */
    public static function contactFields(): array
    {
        return [
            ['class' => 'SilverStripe\\UserForms\\Model\\EditableFormField\\EditableTextField', 'Name' => 'Name', 'Title' => ['hr' => 'Ime i prezime', 'de' => 'Name', 'en' => 'Name'], 'Required' => true, 'ExtraClass' => 'half'],
            ['class' => 'SilverStripe\\UserForms\\Model\\EditableFormField\\EditableEmailField', 'Name' => 'Email', 'Title' => ['hr' => 'E-pošta', 'de' => 'E-Mail', 'en' => 'E-mail'], 'Required' => true, 'ExtraClass' => 'half'],
            ['class' => 'SilverStripe\\UserForms\\Model\\EditableFormField\\EditableTextField', 'Name' => 'Phone', 'Title' => ['hr' => 'Telefon', 'de' => 'Telefon', 'en' => 'Phone'], 'ExtraClass' => 'half'],
            ['class' => 'SilverStripe\\UserForms\\Model\\EditableFormField\\EditableTextField', 'Name' => 'Dates', 'Title' => ['hr' => 'Željeni termin', 'de' => 'Wunschtermin', 'en' => 'Preferred dates'], 'ExtraClass' => 'half'],
            ['class' => 'SilverStripe\\UserForms\\Model\\EditableFormField\\EditableTextField', 'Name' => 'Message', 'Title' => ['hr' => 'Poruka', 'de' => 'Nachricht', 'en' => 'Message'], 'Required' => true, 'Rows' => 6],
            ['class' => 'SilverStripe\\UserForms\\Model\\EditableFormField\\EditableCheckbox', 'Name' => 'Privacy', 'Title' => ['hr' => 'Pristajem na obradu podataka radi odgovora na upit.', 'de' => 'Ich stimme der Verarbeitung meiner Daten zur Beantwortung der Anfrage zu.', 'en' => 'I agree to my data being processed to answer this enquiry.'], 'Required' => true],
        ];
    }
}
