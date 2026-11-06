<?php

declare(strict_types=1);

namespace App\Task\Seed;

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
use App\Elements\Items\Place;
use App\Elements\Items\Review;
use App\Elements\KeyFactsElement;
use App\Elements\LocationElement;
use App\Elements\PlacesElement;
use App\Elements\ReviewsElement;
use App\Elements\TextImageElement;

/**
 * Starter content in HR / DE / EN.
 *
 * Villa-specific details (rooms, guests, prices, legal texts, reviews) are PLACEHOLDERS –
 * replace them with the real data in the CMS. Istria content (guides, beaches, destinations)
 * is researched; driving distances are calculated from the coordinates (OSRM).
 *
 * Translatable values are ['hr' => …, 'de' => …, 'en' => …].
 */
final class SeedData
{
    /** Villa position (Google Maps place "Kuća za odmor VILLA DINA") – verify in Settings → Villa */
    public const VILLA_LAT = 45.158524;
    public const VILLA_LNG = 14.076953;

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
            'Latitude' => self::VILLA_LAT,
            'Longitude' => self::VILLA_LNG,
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
     * Page tree. 'type': modular | booking | blog | form | plain; 'parent': key of the parent page.
     *
     * SEO: every page has MetaTitle (≤ 60 chars, site name is appended) and MetaDescription (≤ 160).
     *
     * @return list<array<string, mixed>>
     */
    public static function pages(): array
    {
        return [
            [
                'key' => 'home',
                'type' => 'modular',
                'Title' => self::t('Početna', 'Startseite', 'Home'),
                'MenuTitle' => self::t('Početna', 'Start', 'Home'),
                'URLSegment' => 'home',
                'ShowInMenus' => false,
                'MetaTitle' => self::t(
                    'Villa Dina – kuća za odmor s bazenom kraj Labina, Istra',
                    'Villa Dina – Ferienhaus mit Pool bei Labin, Istrien',
                    'Villa Dina – Holiday Home with Pool near Labin, Istria',
                ),
                'MetaDescription' => self::t(
                    'Kamena kuća za odmor s privatnim bazenom u Šumberu kraj Labina, za do 8 osoba. Plaže Rapca ~20 min, Pula i Rovinj oko 1 h. Provjerite slobodne termine.',
                    'Natursteinhaus mit privatem Pool in Šumber bei Labin für bis zu 8 Gäste. Strände von Rabac ca. 20 Min., Pula und Rovinj ca. 1 Std. Jetzt Termine prüfen.',
                    'Stone holiday home with a private pool in Šumber near Labin, Istria, for up to 8 guests. Rabac beaches ~20 min, Pula and Rovinj ~1 h. Check availability.',
                ),
                'elements' => 'home',
            ],
            [
                'key' => 'villa',
                'type' => 'modular',
                'Title' => self::t('Vila', 'Die Villa', 'The villa'),
                'URLSegment' => self::t('vila', 'villa', 'villa'),
                'header' => 'living',
                'MetaTitle' => self::t(
                    'Vila s bazenom za 8 osoba – sobe i sadržaji',
                    'Villa mit Pool für 8 Personen – Zimmer & Ausstattung',
                    'Villa with Pool for 8 – Rooms and Amenities',
                ),
                'MetaDescription' => self::t(
                    'Villa Dina: 4 spavaće sobe, 3 kupaonice, privatni bazen, natkrivena terasa, roštilj, klima i Wi-Fi. Obnovljena istarska kamena kuća u Šumberu kraj Labina.',
                    'Villa Dina: 4 Schlafzimmer, 3 Bäder, privater Pool, überdachte Terrasse, Grill, Klimaanlage und WLAN. Renoviertes istrisches Steinhaus in Šumber bei Labin.',
                    'Villa Dina: 4 bedrooms, 3 bathrooms, private pool, covered terrace, barbecue, air conditioning and Wi-Fi. A restored Istrian stone house near Labin.',
                ),
                'elements' => 'villa',
            ],
            [
                'key' => 'gallery',
                'type' => 'modular',
                'Title' => self::t('Galerija', 'Galerie', 'Gallery'),
                'URLSegment' => self::t('galerija', 'galerie', 'gallery'),
                'MetaTitle' => self::t(
                    'Galerija – fotografije vile, bazena i okolice',
                    'Galerie – Fotos von Villa, Pool und Umgebung',
                    'Gallery – Photos of the Villa, Pool and Area',
                ),
                'MetaDescription' => self::t(
                    'Fotografije Ville Dina: kamena kuća, privatni bazen, terasa s pogledom, interijer, maslinik te Labin i Rabac u blizini.',
                    'Fotos der Villa Dina: Steinhaus, privater Pool, Terrasse mit Aussicht, Innenräume, Olivenhain sowie Labin und Rabac in der Nähe.',
                    'Photos of Villa Dina: the stone house, private pool, terrace with a view, interiors, the olive grove and nearby Labin and Rabac.',
                ),
                'elements' => 'gallery',
            ],
            [
                'key' => 'location',
                'type' => 'modular',
                'Title' => self::t('Lokacija', 'Lage', 'Location'),
                'URLSegment' => self::t('lokacija', 'lage', 'location'),
                'header' => 'labin',
                'MetaTitle' => self::t(
                    'Lokacija i dolazak – Šumber kraj Labina, Istra',
                    'Lage & Anreise – Šumber bei Labin, Istrien',
                    'Location & Getting Here – Šumber near Labin, Istria',
                ),
                'MetaDescription' => self::t(
                    'Gdje je Villa Dina: Šumber, istočna Istra, sjeverno od Labina. Udaljenosti do plaža, gradova i zračnih luka Pula, Rijeka i Trst te upute za dolazak.',
                    'Wo liegt die Villa Dina: Šumber in Ost-Istrien, nördlich von Labin. Entfernungen zu Stränden, Städten und den Flughäfen Pula, Rijeka, Triest. Anreise.',
                    'Where Villa Dina is: Šumber in eastern Istria, north of Labin. Distances to beaches, towns and Pula, Rijeka and Trieste airports, plus directions.',
                ),
                'elements' => 'location',
            ],
            [
                'key' => 'istria',
                'type' => 'modular',
                'Title' => self::t('Istra – vodič za izlete', 'Istrien – Ausflugsführer', 'Istria – day-trip guide'),
                'MenuTitle' => self::t('Istra', 'Istrien', 'Istria'),
                'URLSegment' => self::t('istra', 'istrien', 'istria'),
                'header' => 'rabac',
                'MetaTitle' => self::t(
                    'Izleti po Istri – Pula, Rovinj, Brijuni i više',
                    'Ausflüge in Istrien – Pula, Rovinj, Brijuni & mehr',
                    'Istria Day Trips – Pula, Rovinj, Brijuni and More',
                ),
                'MetaDescription' => self::t(
                    'Najbolji izleti iz Ville Dina: Labin, Rabac, Pula, Brijuni, Rovinj, Poreč, Motovun, Hum, Učka i Kamenjak – s točnom udaljenošću i vremenom vožnje.',
                    'Die schönsten Ausflüge ab Villa Dina: Labin, Rabac, Pula, Brijuni, Rovinj, Poreč, Motovun, Hum, Učka und Kamenjak – mit Entfernung und Fahrzeit.',
                    'The best day trips from Villa Dina: Labin, Rabac, Pula, Brijuni, Rovinj, Poreč, Motovun, Hum, Učka and Kamenjak – with driving distance and time.',
                ),
                'elements' => 'istria',
            ],
            [
                'key' => 'beaches',
                'type' => 'modular',
                'parent' => 'istria',
                'Title' => self::t('Plaže u blizini', 'Strände in der Nähe', 'Beaches nearby'),
                'MenuTitle' => self::t('Plaže', 'Strände', 'Beaches'),
                'URLSegment' => self::t('plaze', 'straende', 'beaches'),
                'header' => 'rabac',
                'MetaTitle' => self::t(
                    'Najbolje plaže kraj Labina i Rapca – s udaljenostima',
                    'Strände bei Labin und Rabac – mit Entfernungen',
                    'Best Beaches near Labin and Rabac – with Distances',
                ),
                'MetaDescription' => self::t(
                    'Plaže u blizini Ville Dina: Maslinica i Girandella s Plavom zastavom, mirne uvale Sveta Marina, Tunarica i Ravni, Kvarner i Kamenjak – km i minute vožnje.',
                    'Strände nahe der Villa Dina: Maslinica und Girandella mit Blauer Flagge, ruhige Buchten Sveta Marina, Tunarica, Ravni, Kvarner und Kamenjak – mit Fahrzeit.',
                    'Beaches near Villa Dina: Blue Flag Maslinica and Girandella, quiet coves at Sveta Marina, Tunarica and Ravni, Kvarner and Kamenjak – with driving times.',
                ),
                'elements' => 'beaches',
            ],
            [
                'key' => 'booking',
                'type' => 'booking',
                'Title' => self::t('Rezervacija', 'Buchen', 'Book'),
                'URLSegment' => self::t('rezervacija', 'buchen', 'book'),
                'MetaTitle' => self::t(
                    'Rezervacija – slobodni termini i cijene',
                    'Buchen – freie Termine und Preise',
                    'Book – Availability and Prices',
                ),
                'MetaDescription' => self::t(
                    'Provjerite slobodne termine Ville Dina i odmah vidite cijenu s boravišnom pristojbom. Rezervacija izravno kod vlasnika, 30 % predujma, ostatak 60 dana prije.',
                    'Freie Termine der Villa Dina prüfen und sofort den Preis inkl. Kurtaxe sehen. Direkt beim Eigentümer buchen: 30 % Anzahlung, Rest 60 Tage vor Anreise.',
                    'Check Villa Dina availability and see the price including tourist tax instantly. Book direct with the owner: 30 % deposit, balance 60 days before arrival.',
                ),
                'Content' => self::t(
                    '<p>Odaberite termin i broj gostiju – cijenu vidite odmah. Nakon provjere dostupnosti šaljemo vam poveznicu za plaćanje.</p>',
                    '<p>Wählen Sie Reisedaten und Gäste – den Preis sehen Sie sofort. Nach Prüfung der Verfügbarkeit erhalten Sie einen Zahlungslink.</p>',
                    '<p>Choose your dates and guests – you see the price instantly. Once we have checked availability, we send you a payment link.</p>',
                ),
            ],
            [
                'key' => 'blog',
                'type' => 'blog',
                'Title' => self::t('Istra savjeti', 'Istrien-Tipps', 'Istria tips'),
                'URLSegment' => self::t('savjeti', 'tipps', 'tips'),
                'MetaTitle' => self::t(
                    'Istra savjeti – gradovi, priroda i plaže',
                    'Istrien-Tipps – Städte, Natur und Strände',
                    'Istria Travel Tips – Towns, Nature and Beaches',
                ),
                'MetaDescription' => self::t(
                    'Vodiči za Istru iz prve ruke: Labin i Rabac, Pula, Brijuni, Rovinj, Poreč, Motovun, Učka i Kamenjak – što vidjeti, kada ići i praktični savjeti.',
                    'Istrien-Reiseführer aus erster Hand: Labin und Rabac, Pula, Brijuni, Rovinj, Poreč, Motovun, Učka und Kamenjak – Sehenswertes, Reisezeit, Tipps.',
                    'First-hand Istria guides: Labin and Rabac, Pula, Brijuni, Rovinj, Poreč, Motovun, Učka and Kamenjak – what to see, when to go and practical tips.',
                ),
            ],
            [
                'key' => 'contact',
                'type' => 'form',
                'Title' => self::t('Kontakt', 'Kontakt', 'Contact'),
                'URLSegment' => self::t('kontakt', 'kontakt', 'contact'),
                'MetaTitle' => self::t('Kontakt – pitanja o vili i boravku', 'Kontakt – Fragen zur Villa und zum Aufenthalt', 'Contact – Questions about the Villa'),
                'MetaDescription' => self::t(
                    'Imate pitanje o Villi Dina, okolici ili dolasku? Pišite nam – odgovaramo u roku od 24 sata.',
                    'Fragen zur Villa Dina, zur Umgebung oder zur Anreise? Schreiben Sie uns – wir antworten innerhalb von 24 Stunden.',
                    'Questions about Villa Dina, the area or getting here? Write to us – we reply within 24 hours.',
                ),
                'Content' => self::t(
                    '<p>Imate pitanje o vili, okolici ili dolasku? Pišite nam – odgovaramo u roku od 24 sata.</p>',
                    '<p>Fragen zur Villa, zur Umgebung oder zur Anreise? Schreiben Sie uns – wir antworten innerhalb von 24 Stunden.</p>',
                    '<p>Questions about the villa, the area or getting here? Write to us – we reply within 24 hours.</p>',
                ),
            ],
            [
                'key' => 'terms',
                'type' => 'plain',
                'Title' => self::t('Uvjeti najma', 'Mietbedingungen', 'Rental terms'),
                'URLSegment' => self::t('uvjeti-najma', 'mietbedingungen', 'rental-terms'),
                'ShowInMenus' => false,
                'Content' => self::t(
                    '<h2>Rezervacija i plaćanje</h2><p>Rezervacija je potvrđena primitkom predujma od 30 %. Ostatak dospijeva 60 dana prije dolaska. Za rezervacije unutar 60 dana do dolaska plaća se puni iznos odmah.</p><h2>Dolazak i odlazak</h2><p>Dolazak od 16:00, odlazak do 10:00. U glavnoj sezoni dolazak i odlazak subotom.</p><h2>Otkazivanje</h2><p>[Unesite vlastite uvjete otkazivanja.]</p><p><em>Primjer teksta – prilagodite i pravno provjerite.</em></p>',
                    '<h2>Buchung und Zahlung</h2><p>Die Buchung ist mit Eingang der Anzahlung von 30 % bestätigt. Der Restbetrag ist 60 Tage vor Anreise fällig. Bei Buchungen innerhalb von 60 Tagen vor Anreise ist der Gesamtbetrag sofort fällig.</p><h2>An- und Abreise</h2><p>Anreise ab 16:00 Uhr, Abreise bis 10:00 Uhr. In der Hauptsaison An- und Abreise samstags.</p><h2>Stornierung</h2><p>[Bitte eigene Stornobedingungen eintragen.]</p><p><em>Beispieltext – bitte anpassen und rechtlich prüfen lassen.</em></p>',
                    '<h2>Booking and payment</h2><p>Your booking is confirmed once the 30 % deposit has been received. The balance is due 60 days before arrival. Bookings made within 60 days of arrival are paid in full immediately.</p><h2>Arrival and departure</h2><p>Check-in from 4 pm, check-out by 10 am. In high season arrival and departure on Saturdays.</p><h2>Cancellation</h2><p>[Enter your own cancellation terms.]</p><p><em>Example text – please adapt and have it legally reviewed.</em></p>',
                ),
            ],
            [
                'key' => 'imprint',
                'type' => 'plain',
                'Title' => self::t('Impressum', 'Impressum', 'Imprint'),
                'URLSegment' => self::t('impressum', 'impressum', 'imprint'),
                'ShowInMenus' => false,
                'Content' => self::t(
                    '<p>[Naziv / ime vlasnika]<br>[Adresa]<br>OIB: [OIB]<br>E-pošta: [e-mail]<br>Telefon: [telefon]</p>',
                    '<p>Angaben gemäß § 5 DDG:<br>[Name des Vermieters]<br>[Anschrift]<br>OIB / USt-IdNr.: [Nummer]<br>E-Mail: [E-Mail]<br>Telefon: [Telefon]</p>',
                    '<p>[Owner / company name]<br>[Address]<br>Tax ID (OIB): [number]<br>E-mail: [e-mail]<br>Phone: [phone]</p>',
                ),
            ],
            [
                'key' => 'privacy',
                'type' => 'plain',
                'Title' => self::t('Zaštita podataka', 'Datenschutz', 'Privacy policy'),
                'URLSegment' => self::t('zastita-podataka', 'datenschutz', 'privacy'),
                'ShowInMenus' => false,
                'Content' => self::t(
                    '<p>[Unesite izjavu o zaštiti podataka: voditelj obrade, svrhe (upiti, rezervacije, plaćanja putem Stripea), rokovi čuvanja, prava ispitanika, kolačići, karta (OpenStreetMap se učitava tek nakon pristanka), izračun ruta (OSRM – šalju se samo koordinate, bez osobnih podataka).]</p>',
                    '<p>[Datenschutzerklärung einfügen: Verantwortlicher, Zwecke (Anfragen, Buchungen, Zahlungen über Stripe), Speicherdauer, Betroffenenrechte, Cookies, Karte (OpenStreetMap wird erst nach Einwilligung geladen), Routenberechnung (OSRM – nur Koordinaten, keine personenbezogenen Daten).]</p>',
                    '<p>[Insert privacy policy: controller, purposes (enquiries, bookings, payments via Stripe), retention periods, data subject rights, cookies, map (OpenStreetMap only loads after consent), route calculation (OSRM – coordinates only, no personal data).]</p>',
                ),
            ],
        ];
    }

    /**
     * Element definitions per page.
     *
     * Keys: class, style, fields, hasOne (relation => image key), slides (relation => image keys),
     *       links (relation => [page, text]), items (relation => [class, rows]);
     *       item rows may contain 'article' (post key) and 'image' (image key).
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
                        'Title' => self::t('Kamen, maslina *i more*', 'Stein, Olive *und Meer*', 'Stone, olives *and the sea*'),
                        'ShowTitle' => true,
                        'Eyebrow' => 'Šumber · Labin · Istra',
                        'Lead' => self::t(
                            'Obnovljena istarska kamena kuća s privatnim bazenom za do 8 osoba, okružena maslinicima – dvadesetak minuta do plaža Rapca.',
                            'Ein restauriertes istrisches Steinhaus mit privatem Pool für bis zu 8 Gäste zwischen Olivenhainen – rund zwanzig Minuten zu den Stränden von Rabac.',
                            'A restored Istrian stone house with a private pool for up to 8 guests among olive groves – about twenty minutes to the beaches of Rabac.',
                        ),
                        'Size' => 'full',
                        'ShowBooking' => true,
                    ],
                    'slides' => ['Slides' => ['villa', 'terrace', 'evening']],
                    'links' => [
                        'PrimaryLink' => ['villa', self::t('Upoznajte vilu', 'Die Villa entdecken', 'Discover the villa')],
                        'SecondaryLink' => ['gallery', self::t('Galerija', 'Galerie', 'Gallery')],
                    ],
                ],
                self::keyFacts(),
                [
                    'class' => TextImageElement::class,
                    'fields' => [
                        'Title' => self::t('Mjesto za *usporiti*', 'Ein Ort zum *Ankommen*', 'A place to *slow down*'),
                        'ShowTitle' => true,
                        'Eyebrow' => self::t('Dobrodošli', 'Willkommen', 'Welcome'),
                        'Content' => self::t(
                            '<p><strong>Villa Dina je kamena kuća za odmor s privatnim bazenom u selu Šumber, na brežuljcima istočne Istre sjeverno od Labina.</strong> Ima četiri spavaće sobe i tri kupaonice za do osam gostiju – idealna za obitelji i prijatelje koji traže mir, a ne žele odustati od mora i izleta.</p><p>Debeli kameni zidovi ljeti čuvaju svježinu, a terasa uz bazen gleda na zelene doline i zalazak sunca. Stari Labin udaljen je oko 15 minuta vožnje, plaže Rapca dvadesetak minuta, a Pula, Rovinj i Učka unutar otprilike sat vremena.</p>',
                            '<p><strong>Die Villa Dina ist ein Ferienhaus aus Naturstein mit privatem Pool im Dorf Šumber, in den Hügeln Ost-Istriens nördlich von Labin.</strong> Vier Schlafzimmer und drei Bäder bieten Platz für bis zu acht Gäste – ideal für Familien und Freunde, die Ruhe suchen, ohne auf Meer und Ausflüge zu verzichten.</p><p>Dicke Steinmauern halten im Sommer die Kühle, die Poolterrasse blickt auf grüne Täler und den Sonnenuntergang. Die Altstadt von Labin erreichen Sie in etwa 15 Minuten, die Strände von Rabac in rund zwanzig, Pula, Rovinj und die Učka in etwa einer Stunde.</p>',
                            '<p><strong>Villa Dina is a stone holiday home with a private pool in the village of Šumber, in the hills of eastern Istria north of Labin.</strong> Four bedrooms and three bathrooms sleep up to eight guests – ideal for families and friends who want peace and quiet without giving up the sea and day trips.</p><p>Thick stone walls keep the summer heat out, and the pool terrace looks over green valleys and the sunset. Labin old town is about 15 minutes away by car, the beaches of Rabac around twenty, and Pula, Rovinj and Učka within roughly an hour.</p>',
                        ),
                        'ImagePosition' => 'right',
                        'ImageFormat' => 'portrait',
                    ],
                    'hasOne' => ['Image' => 'terrace', 'SecondaryImage' => 'olives'],
                    'links' => ['CTALink' => ['villa', self::t('Više o vili', 'Mehr über die Villa', 'More about the villa')]],
                ],
                self::amenities('vd-el--sand'),
                [
                    'class' => GalleryElement::class,
                    'fields' => [
                        'Title' => self::t('Pogled unutra', 'Einblicke', 'Take a look'),
                        'ShowTitle' => true,
                        'Eyebrow' => self::t('Galerija', 'Galerie', 'Gallery'),
                        'DisplayMode' => 'grid',
                    ],
                    'gallery' => ['villa', 'living', 'kitchen', 'bedroom', 'pool', 'terrace', 'evening', 'olives', 'labin'],
                ],
                [
                    'class' => TextImageElement::class,
                    'style' => 'vd-el--sand',
                    'fields' => [
                        'Title' => self::t('Istra *na dohvat ruke*', 'Istrien *vor der Haustür*', 'Istria *on your doorstep*'),
                        'ShowTitle' => true,
                        'Eyebrow' => self::t('Izleti i plaže', 'Ausflüge & Strände', 'Day trips & beaches'),
                        'Content' => self::t(
                            '<p>Iz Ville Dina za jedan dan stižete do rimske Arene u Puli, safarija i rimskih ruševina na Brijunima, starog Rovinja, UNESCO-ove bazilike u Poreču i gradića na brežuljcima poput Motovuna i Huma. Za vruće dane tu su vrh Učke i desetci šljunčanih plaža.</p><p>Za svako odredište izračunali smo udaljenost i vrijeme vožnje od vile.</p>',
                            '<p>Von der Villa Dina erreichen Sie an einem Tag die römische Arena in Pula, Safaripark und Ruinen auf Brijuni, die Altstadt von Rovinj, die UNESCO-Basilika in Poreč und Hügelstädtchen wie Motovun und Hum. Für heiße Tage gibt es den Gipfel der Učka und Dutzende Kiesstrände.</p><p>Für jedes Ziel haben wir Entfernung und Fahrzeit ab der Villa berechnet.</p>',
                            '<p>From Villa Dina you can reach the Roman Arena in Pula, the safari park and ruins of Brijuni, Rovinj old town, the UNESCO basilica in Poreč and hilltop towns such as Motovun and Hum in a day. For hot days there is the summit of Učka and dozens of pebble beaches.</p><p>For every destination we have calculated the distance and driving time from the villa.</p>',
                        ),
                        'ImagePosition' => 'left',
                        'ImageFormat' => 'landscape',
                    ],
                    'hasOne' => ['Image' => 'rabac', 'SecondaryImage' => 'labin'],
                    'links' => ['CTALink' => ['istria', self::t('Vodič za izlete', 'Zum Ausflugsführer', 'Day-trip guide')]],
                ],
                self::location(),
                self::reviews(),
                self::faq(),
                [
                    'class' => CTAElement::class,
                    'fields' => [
                        'Title' => self::t('Vaše ljeto *u Istri*', 'Ihr Sommer *in Istrien*', 'Your summer *in Istria*'),
                        'ShowTitle' => true,
                        'Lead' => self::t(
                            'Provjerite slobodne termine i cijenu u nekoliko klikova.',
                            'Freie Termine und Preis in wenigen Klicks prüfen.',
                            'Check free dates and the price in a few clicks.',
                        ),
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
                        'Title' => self::t('Prostor za *cijelu obitelj*', 'Platz für die *ganze Familie*', 'Room for the *whole family*'),
                        'ShowTitle' => true,
                        'Eyebrow' => self::t('Prizemlje', 'Erdgeschoss', 'Ground floor'),
                        'Content' => self::t(
                            '<p>Otvoreni dnevni boravak s kaminom, potpuno opremljena kuhinja i blagovaonica za osam osoba. Velika staklena vrata vode izravno na terasu i bazen.</p>',
                            '<p>Offener Wohnbereich mit Kamin, voll ausgestattete Küche und Essbereich für acht Personen. Große Glastüren führen direkt auf Terrasse und Pool.</p>',
                            '<p>Open-plan living room with fireplace, fully equipped kitchen and dining for eight. Large glass doors open straight onto the terrace and pool.</p>',
                        ),
                        'ImagePosition' => 'left',
                        'ImageFormat' => 'landscape',
                    ],
                    'hasOne' => ['Image' => 'living', 'SecondaryImage' => 'kitchen'],
                ],
                [
                    'class' => TextImageElement::class,
                    'style' => 'vd-el--sand',
                    'fields' => [
                        'Title' => self::t('Mirne *noći*', 'Ruhige *Nächte*', 'Quiet *nights*'),
                        'ShowTitle' => true,
                        'Eyebrow' => self::t('Kat', 'Obergeschoss', 'Upstairs'),
                        'Content' => self::t(
                            '<p>Četiri klimatizirane spavaće sobe s kvalitetnim madracima i posteljinom od pamuka, tri kupaonice s tušem. Ručnici i posteljina uključeni su u cijenu.</p>',
                            '<p>Vier klimatisierte Schlafzimmer mit hochwertigen Matratzen und Baumwollbettwäsche, drei Duschbäder. Handtücher und Bettwäsche sind im Preis inbegriffen.</p>',
                            '<p>Four air-conditioned bedrooms with quality mattresses and cotton linen, three shower rooms. Towels and bed linen are included.</p>',
                        ),
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
                        'Title' => self::t('Slobodni *termini*', 'Freie *Termine*', 'Available *dates*'),
                        'ShowTitle' => true,
                        'Eyebrow' => self::t('Rezervacija', 'Buchung', 'Booking'),
                        'Note' => self::t(
                            'U glavnoj sezoni dolazak i odlazak subotom, najmanje 7 noćenja.',
                            'In der Hauptsaison An- und Abreise samstags, mindestens 7 Nächte.',
                            'In high season arrival and departure on Saturdays, minimum 7 nights.',
                        ),
                        'ShowChannels' => true,
                    ],
                ],
            ],

            'gallery' => [
                [
                    'class' => GalleryElement::class,
                    'fields' => [
                        'Title' => self::t('Galerija', 'Galerie', 'Gallery'),
                        'ShowTitle' => false,
                        'DisplayMode' => 'grid',
                    ],
                    'gallery' => ['villa', 'terrace', 'living', 'kitchen', 'bedroom', 'pool', 'evening', 'olives', 'labin', 'rabac'],
                ],
                [
                    'class' => CTAElement::class,
                    'fields' => [
                        'Title' => self::t('Sviđa vam se?', 'Gefällt es Ihnen?', 'Like what you see?'),
                        'ShowTitle' => true,
                        'UseInquiryLink' => true,
                    ],
                ],
            ],

            'location' => [
                self::location(true),
                [
                    'class' => TextImageElement::class,
                    'fields' => [
                        'Title' => self::t('Kako *doći*', 'So kommen Sie *an*', 'Getting *here*'),
                        'ShowTitle' => true,
                        'Eyebrow' => self::t('Dolazak', 'Anreise', 'Arrival'),
                        'Content' => self::t(
                            '<p><strong>Automobilom:</strong> iz Slovenije i Trsta autocestom Istarski ipsilon prema Puli, pa preko Pazina prema Labinu; iz Rijeke kroz tunel Učka. Posljednji kilometri vode lokalnom cestom do Šumbera – točnu lokaciju šaljemo uz potvrdu rezervacije.</p><p><strong>Zrakoplovom:</strong> najbliže su zračne luke Pula i Rijeka (otok Krk), a za dulje putovanje i Trst. Automobil je preporučljiv – plaže, trgovine i izleti najlakše su dostupni autom.</p>',
                            '<p><strong>Mit dem Auto:</strong> aus Slowenien und Triest über die Autobahn Istarski ipsilon Richtung Pula, dann über Pazin nach Labin; aus Rijeka durch den Učka-Tunnel. Die letzten Kilometer führen über eine Landstraße nach Šumber – die genaue Lage erhalten Sie mit der Buchungsbestätigung.</p><p><strong>Mit dem Flugzeug:</strong> die nächsten Flughäfen sind Pula und Rijeka (Insel Krk), außerdem Triest. Ein Mietwagen ist empfehlenswert – Strände, Einkauf und Ausflüge erreichen Sie am besten mit dem Auto.</p>',
                            '<p><strong>By car:</strong> from Slovenia and Trieste take the Istrian Y motorway towards Pula, then via Pazin to Labin; from Rijeka through the Učka tunnel. The last kilometres follow a local road to Šumber – we send the exact location with your booking confirmation.</p><p><strong>By air:</strong> the nearest airports are Pula and Rijeka (on the island of Krk), plus Trieste. A car is recommended – beaches, shops and day trips are easiest to reach by car.</p>',
                        ),
                        'ImagePosition' => 'right',
                        'ImageFormat' => 'landscape',
                    ],
                    'hasOne' => ['Image' => 'olives'],
                ],
                [
                    'class' => TextImageElement::class,
                    'style' => 'vd-el--sand',
                    'fields' => [
                        'Title' => self::t('Labin i *Rabac*', 'Labin und *Rabac*', 'Labin and *Rabac*'),
                        'ShowTitle' => true,
                        'Eyebrow' => self::t('Okolica', 'Umgebung', 'Surroundings'),
                        'Content' => self::t(
                            '<p>Srednjovjekovni Labin smjestio se na brežuljku iznad mora – kamene uličice, galerije i vidikovac s pogledom na Kvarner. Ispod njega je Rabac, nekadašnje ribarsko mjesto s uvalama i šljunčanim plažama s Plavom zastavom.</p><p>Dalje: Park prirode Učka, Pula, Brijuni, Rovinj i gradići unutrašnjosti – sve u našem vodiču za izlete.</p>',
                            '<p>Das mittelalterliche Labin thront auf einem Hügel über dem Meer – Steingassen, Galerien und ein Aussichtspunkt über der Kvarner-Bucht. Darunter liegt Rabac, ein ehemaliges Fischerdorf mit Buchten und Kiesstränden mit Blauer Flagge.</p><p>Weiter: Naturpark Učka, Pula, Brijuni, Rovinj und die Städtchen im Landesinneren – alles in unserem Ausflugsführer.</p>',
                            '<p>Medieval Labin sits on a hill above the sea – stone lanes, galleries and a viewpoint over the Kvarner Gulf. Below it lies Rabac, a former fishing village with coves and Blue Flag pebble beaches.</p><p>Further afield: Učka Nature Park, Pula, Brijuni, Rovinj and the hill towns inland – all in our day-trip guide.</p>',
                        ),
                        'ImagePosition' => 'left',
                        'ImageFormat' => 'landscape',
                    ],
                    'hasOne' => ['Image' => 'rabac', 'SecondaryImage' => 'labin'],
                    'links' => ['CTALink' => ['istria', self::t('Vodič za izlete', 'Ausflugsführer', 'Day-trip guide')]],
                ],
            ],

            'istria' => [
                [
                    'class' => TextImageElement::class,
                    'fields' => [
                        'Title' => self::t('Cijela Istra *u jednom tjednu*', 'Ganz Istrien *in einer Woche*', 'All of Istria *in a week*'),
                        'ShowTitle' => true,
                        'Eyebrow' => self::t('Vodič za izlete', 'Ausflugsführer', 'Day-trip guide'),
                        'Content' => self::t(
                            '<p><strong>Villa Dina nalazi se u istočnoj Istri, kraj Labina – dovoljno središnje da najpoznatija odredišta poluotoka obiđete u jednodnevnim izletima.</strong> Najbliži su Labin i Rabac (15–20 minuta) te Park prirode Učka (oko 45 minuta). Za otprilike sat vremena stižete do Pule, Fažane (brod za Brijune), Rovinja i Motovuna, a nešto dalje su Poreč i Rt Kamenjak.</p><p>U popisu ispod za svako odredište navodimo što vas čeka, udaljenost i vrijeme vožnje automobilom od vile te poveznicu na detaljan vodič.</p>',
                            '<p><strong>Die Villa Dina liegt in Ost-Istrien bei Labin – zentral genug, um die bekanntesten Ziele der Halbinsel als Tagesausflüge zu erleben.</strong> Am nächsten sind Labin und Rabac (15–20 Minuten) sowie der Naturpark Učka (etwa 45 Minuten). In rund einer Stunde erreichen Sie Pula, Fažana (Boot nach Brijuni), Rovinj und Motovun, etwas weiter liegen Poreč und Kap Kamenjak.</p><p>In der Liste unten finden Sie zu jedem Ziel, was Sie erwartet, Entfernung und Fahrzeit mit dem Auto ab der Villa sowie einen Link zum ausführlichen Reiseführer.</p>',
                            '<p><strong>Villa Dina is in eastern Istria near Labin – central enough to see the peninsula\'s best-known places on day trips.</strong> Closest are Labin and Rabac (15–20 minutes) and Učka Nature Park (about 45 minutes). Within roughly an hour you reach Pula, Fažana (boat to Brijuni), Rovinj and Motovun; Poreč and Cape Kamenjak are a little further.</p><p>The list below shows what to expect at each destination, the driving distance and time from the villa, and a link to our detailed guide.</p>',
                        ),
                        'ImagePosition' => 'right',
                        'ImageFormat' => 'landscape',
                    ],
                    'hasOne' => ['Image' => 'labin', 'SecondaryImage' => 'olives'],
                    'links' => ['CTALink' => ['beaches', self::t('Plaže u blizini', 'Strände in der Nähe', 'Beaches nearby')]],
                ],
                [
                    'class' => PlacesElement::class,
                    'style' => 'vd-el--sand',
                    'fields' => [
                        'Title' => self::t('Izleti *od vile*', 'Ausflüge *ab der Villa*', 'Day trips *from the villa*'),
                        'ShowTitle' => true,
                        'Eyebrow' => self::t('Odredišta', 'Ziele', 'Destinations'),
                        'Content' => self::t(
                            '<p>Udaljenosti su izračunate za vožnju automobilom od Ville Dina (OpenStreetMap). Ljeti računajte s dodatnim vremenom za gužve i parkiranje.</p>',
                            '<p>Entfernungen berechnet für die Fahrt mit dem Auto ab der Villa Dina (OpenStreetMap). Im Sommer etwas Zeit für Verkehr und Parken einplanen.</p>',
                            '<p>Distances are calculated for driving from Villa Dina (OpenStreetMap). In summer allow extra time for traffic and parking.</p>',
                        ),
                        'SortBy' => 'distance',
                        'OutputSchema' => true,
                    ],
                    'items' => ['Places' => [Place::class, self::destinations()]],
                ],
                self::istriaFaq(),
                [
                    'class' => CTAElement::class,
                    'fields' => [
                        'Title' => self::t('Vaša baza *za Istru*', 'Ihr Zuhause *für Istrien*', 'Your base *for Istria*'),
                        'ShowTitle' => true,
                        'Lead' => self::t(
                            'Bazen nakon izleta, mir navečer – provjerite slobodne termine.',
                            'Pool nach dem Ausflug, Ruhe am Abend – freie Termine prüfen.',
                            'A pool after the day out, peace in the evening – check availability.',
                        ),
                        'UseInquiryLink' => true,
                    ],
                    'hasOne' => ['BackgroundImage' => 'pool'],
                ],
            ],

            'beaches' => [
                [
                    'class' => TextImageElement::class,
                    'fields' => [
                        'Title' => self::t('Gdje se *kupati*', 'Wo man *baden* geht', 'Where to *swim*'),
                        'ShowTitle' => true,
                        'Eyebrow' => self::t('Plaže', 'Strände', 'Beaches'),
                        'Content' => self::t(
                            '<p><strong>Najbliže plaže Villi Dina nalaze se u Rapcu, dvadesetak minuta vožnje: Maslinica i Girandella nose Plavu zastavu, imaju blag ulaz u more, ležaljke i sadržaje za djecu.</strong> Mirnije uvale su Sveta Marina, Tunarica i Ravni južno od Rapca, a na kvarnerskoj strani Učke šljunčane plaže Mošćeničke Drage i Medveje.</p><p>Istarska obala je pretežno šljunčana i stjenovita, a more vrlo čisto – ponesite cipele za vodu. Pješčanih plaža gotovo nema.</p>',
                            '<p><strong>Die nächsten Strände zur Villa Dina liegen in Rabac, rund zwanzig Minuten mit dem Auto: Maslinica und Girandella tragen die Blaue Flagge, haben einen flachen Einstieg, Liegen und Angebote für Kinder.</strong> Ruhigere Buchten sind Sveta Marina, Tunarica und Ravni südlich von Rabac, auf der Kvarner-Seite der Učka die Kiesstrände von Mošćenička Draga und Medveja.</p><p>Die istrische Küste besteht überwiegend aus Kies und Fels, das Wasser ist sehr klar – Badeschuhe mitnehmen. Sandstrände gibt es kaum.</p>',
                            '<p><strong>The beaches closest to Villa Dina are in Rabac, about twenty minutes by car: Maslinica and Girandella fly the Blue Flag and have a gentle entry into the sea, loungers and facilities for children.</strong> Quieter coves are Sveta Marina, Tunarica and Ravni south of Rabac, and on the Kvarner side of Učka the pebble beaches of Mošćenička Draga and Medveja.</p><p>The Istrian coast is mostly pebble and rock with very clear water – bring water shoes. Sandy beaches are rare.</p>',
                        ),
                        'ImagePosition' => 'right',
                        'ImageFormat' => 'landscape',
                    ],
                    'hasOne' => ['Image' => 'rabac'],
                ],
                [
                    'class' => PlacesElement::class,
                    'style' => 'vd-el--sand',
                    'fields' => [
                        'Title' => self::t('Plaže *od najbliže*', 'Strände *nach Entfernung*', 'Beaches *nearest first*'),
                        'ShowTitle' => true,
                        'Eyebrow' => self::t('Udaljenost od vile', 'Entfernung ab Villa', 'Distance from the villa'),
                        'Content' => self::t(
                            '<p>Poredano po vremenu vožnje automobilom od Ville Dina. Plava zastava dodjeljuje se svake godine – aktualni popis potražite na stranicama programa Plava zastava.</p>',
                            '<p>Sortiert nach Fahrzeit mit dem Auto ab der Villa Dina. Die Blaue Flagge wird jährlich vergeben – die aktuelle Liste finden Sie beim Programm Blue Flag.</p>',
                            '<p>Sorted by driving time from Villa Dina. The Blue Flag is awarded annually – check the Blue Flag programme for the current list.</p>',
                        ),
                        'SortBy' => 'distance',
                        'OutputSchema' => true,
                    ],
                    'items' => ['Places' => [Place::class, self::beaches()]],
                ],
                self::beachFaq(),
                [
                    'class' => CTAElement::class,
                    'fields' => [
                        'Title' => self::t('More danju, *bazen navečer*', 'Tagsüber Meer, *abends Pool*', 'Sea by day, *pool by night*'),
                        'ShowTitle' => true,
                        'Lead' => self::t(
                            'Provjerite slobodne termine Ville Dina.',
                            'Freie Termine der Villa Dina prüfen.',
                            'Check Villa Dina availability.',
                        ),
                        'UseInquiryLink' => true,
                    ],
                    'hasOne' => ['BackgroundImage' => 'evening'],
                ],
            ],

            default => [],
        };
    }

    /**
     * @return array<string, string>
     */
    private static function t(string $hr, string $de, string $en): array
    {
        return ['hr' => $hr, 'de' => $de, 'en' => $en];
    }

    /**
     * @return array<string, mixed>
     */
    private static function keyFacts(): array
    {
        return [
            'class' => KeyFactsElement::class,
            'style' => 'vd-el--tight',
            'fields' => ['Title' => self::t('Ukratko', 'Auf einen Blick', 'At a glance'), 'ShowTitle' => false],
            'items' => ['Facts' => [KeyFact::class, [
                ['Value' => '8', 'Label' => self::t('gostiju', 'Gäste', 'guests')],
                ['Value' => '4', 'Label' => self::t('spavaće sobe', 'Schlafzimmer', 'bedrooms')],
                ['Value' => '3', 'Label' => self::t('kupaonice', 'Bäder', 'bathrooms')],
                ['Value' => '~20 min', 'Label' => self::t('do mora', 'zum Meer', 'to the sea')],
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
                'Title' => self::t('Sve što *trebate*', 'Alles, was Sie *brauchen*', 'Everything you *need*'),
                'ShowTitle' => true,
                'Eyebrow' => self::t('Sadržaji', 'Ausstattung', 'Amenities'),
            ],
            'items' => ['Amenities' => [Amenity::class, [
                $a('pool', self::t('Privatni bazen', 'Privater Pool', 'Private pool'), self::t('Ležaljke i suncobrani', 'Liegen und Sonnenschirme', 'Loungers and parasols')),
                $a('sun', self::t('Natkrivena terasa', 'Überdachte Terrasse', 'Covered terrace'), self::t('Blagovanje na otvorenom', 'Essen im Freien', 'Outdoor dining')),
                $a('bbq', self::t('Roštilj', 'Grill', 'Barbecue'), self::t('Zidani roštilj na drva', 'Gemauerter Holzkohlegrill', 'Built-in wood grill')),
                $a('wifi', self::t('Brzi Wi-Fi', 'Schnelles WLAN', 'Fast Wi-Fi'), self::t('U cijeloj kući', 'Im ganzen Haus', 'Throughout the house')),
                $a('ac', self::t('Klima uređaji', 'Klimaanlage', 'Air conditioning'), self::t('U svim sobama', 'In allen Zimmern', 'In every room')),
                $a('kitchen', self::t('Opremljena kuhinja', 'Voll ausgestattete Küche', 'Equipped kitchen'), self::t('Perilica posuđa, aparat za kavu', 'Spülmaschine, Kaffeemaschine', 'Dishwasher, coffee machine')),
                $a('parking', self::t('Parking', 'Parkplatz', 'Parking'), self::t('Za dva automobila', 'Für zwei Autos', 'For two cars')),
                $a('washer', self::t('Perilica rublja', 'Waschmaschine', 'Washing machine'), self::t('Glačalo i sušilo', 'Bügeleisen und Wäscheständer', 'Iron and drying rack')),
            ]]],
        ];
    }

    /**
     * Distance rows with coordinates are calculated by car from the villa (OSRM);
     * the manual Value is the fallback when routing is not available.
     *
     * @return array<string, mixed>
     */
    private static function location(bool $full = false): array
    {
        $d = static fn (string $icon, array $title, string $value, ?float $lat = null, ?float $lng = null): array => array_filter(
            ['IconKey' => $icon, 'Title' => $title, 'Value' => $value, 'Latitude' => $lat, 'Longitude' => $lng],
            static fn ($v) => $v !== null,
        );

        $rows = [
            $d('home', self::t('Labin, stari grad', 'Labin, Altstadt', 'Labin old town'), '~15 min', 45.0953, 14.1222),
            $d('sea', self::t('Plaža Maslinica, Rabac', 'Strand Maslinica, Rabac', 'Maslinica beach, Rabac'), '~20 min', 45.07996, 14.15262),
            $d('shop', self::t('Trgovina', 'Supermarkt', 'Supermarket'), '[~5 km]'),
            $d('car', self::t('Opatija', 'Opatija', 'Opatija'), '~45 min', 45.3377, 14.3059),
            $d('plane', self::t('Zračna luka Pula', 'Flughafen Pula', 'Pula airport'), '~55 min', 44.8935, 13.9222),
        ];
        if ($full) {
            $rows[] = $d('plane', self::t('Zračna luka Rijeka (Krk)', 'Flughafen Rijeka (Krk)', 'Rijeka airport (Krk)'), '~1 h 15 min', 45.2169, 14.5703);
            $rows[] = $d('plane', self::t('Zračna luka Trst', 'Flughafen Triest', 'Trieste airport'), '~1 h 50 min', 45.8275, 13.4722);
        }

        return [
            'class' => LocationElement::class,
            'fields' => [
                'Title' => self::t('Mirno, a *blizu svega*', 'Ruhig – und doch *nah an allem*', 'Quiet, yet *close to everything*'),
                'ShowTitle' => true,
                'Eyebrow' => self::t('Lokacija', 'Lage', 'Location'),
                'Content' => self::t(
                    '<p><strong>Villa Dina nalazi se u Šumberu, malom selu u općini Sveta Nedelja, na brežuljcima istočne Istre sjeverno od Labina.</strong> Ovdje vas bude ptice, a ne promet – a more, trgovine i restorani su na kratkoj vožnji.</p>',
                    '<p><strong>Die Villa Dina liegt in Šumber, einem kleinen Dorf der Gemeinde Sveta Nedelja in den Hügeln Ost-Istriens nördlich von Labin.</strong> Hier weckt Sie Vogelgesang statt Verkehr – Meer, Einkauf und Restaurants sind dennoch nur eine kurze Fahrt entfernt.</p>',
                    '<p><strong>Villa Dina is in Šumber, a small village in the municipality of Sveta Nedelja, in the hills of eastern Istria north of Labin.</strong> Here you wake up to birdsong, not traffic – yet the sea, shops and restaurants are a short drive away.</p>',
                ),
                'Latitude' => self::VILLA_LAT,
                'Longitude' => self::VILLA_LNG,
                'MapZoom' => 12,
                'MapProvider' => 'osm',
            ],
            'items' => ['Distances' => [Distance::class, $rows]],
        ];
    }

    /**
     * Day-trip destinations. Coordinates: town centre / main sight / departure point.
     *
     * @return list<array<string, mixed>>
     */
    private static function destinations(): array
    {
        $p = static fn (string $kind, string|array $title, array $summary, float $lat, float $lng, ?string $article = null, ?array $features = null, ?string $image = null): array => array_filter([
            'Kind' => $kind,
            'Title' => $title,
            'Summary' => $summary,
            'Latitude' => $lat,
            'Longitude' => $lng,
            'Features' => $features,
            'article' => $article,
            'image' => $image,
        ], static fn ($v) => $v !== null);

        return [
            $p('town', 'Labin', self::t(
                'Srednjovjekovni stari grad na brežuljku: kamene uličice, galerije, rudarska baština i vidikovac Fortica s pogledom na Kvarner.',
                'Mittelalterliche Altstadt auf dem Hügel: Steingassen, Galerien, Bergbaugeschichte und der Aussichtspunkt Fortica über der Kvarner-Bucht.',
                'Medieval hilltop old town: stone lanes, galleries, mining heritage and the Fortica viewpoint over the Kvarner Gulf.',
            ), 45.0953, 14.1222, 'labin', self::t('Stari grad, Vidikovac, Galerije', 'Altstadt, Aussicht, Galerien', 'Old town, Viewpoint, Galleries'), 'labin'),
            $p('town', 'Rabac', self::t(
                'Nekadašnje ribarsko mjesto podno Labina sa šetnicom uz more i šljunčanim plažama s Plavom zastavom.',
                'Ehemaliges Fischerdorf unterhalb von Labin mit Uferpromenade und Kiesstränden mit Blauer Flagge.',
                'Former fishing village below Labin with a seafront promenade and Blue Flag pebble beaches.',
            ), 45.0789, 14.1586, 'labin', self::t('Plaže, Šetnica, Restorani', 'Strände, Promenade, Restaurants', 'Beaches, Promenade, Restaurants'), 'rabac'),
            $p('nature', 'Učka – Vojak (1401 m)', self::t(
                'Najviši vrh Istre s kamenom vidikovačkom kulom; pogled na Kvarner i otoke, kanjon Vela draga i planinske staze.',
                'Höchster Gipfel Istriens mit steinernem Aussichtsturm; Blick über Kvarner und Inseln, Canyon Vela draga und Bergwege.',
                'Istria\'s highest peak with a stone lookout tower; views over Kvarner and the islands, Vela Draga canyon and mountain trails.',
            ), 45.2859, 14.2034, 'ucka', self::t('Vidikovac, Planinarenje, Svježina', 'Aussicht, Wandern, Bergfrische', 'Views, Hiking, Cool air')),
            $p('town', 'Opatija', self::t(
                'Elegantno ljetovalište iz austrougarskog doba na Kvarneru, s vilama, parkovima i obalnom šetnicom Lungomare.',
                'Elegantes Seebad aus der k.u.k.-Zeit an der Kvarner-Bucht mit Villen, Parks und der Uferpromenade Lungomare.',
                'Elegant Austro-Hungarian-era resort on the Kvarner Gulf with villas, parks and the Lungomare seafront path.',
            ), 45.3377, 14.3059, null, self::t('Šetnica, Parkovi, Kava', 'Promenade, Parks, Kaffeehäuser', 'Promenade, Parks, Cafés')),
            $p('town', 'Hum', self::t(
                '„Najmanji grad na svijetu“ – nekoliko ulica unutar zidina, Aleja glagoljaša od Roča i tradicionalna biska.',
                '„Kleinste Stadt der Welt“ – wenige Gassen innerhalb der Mauern, die Allee der Glagoliter ab Roč und traditioneller Biska.',
                'The "smallest town in the world" – a few lanes inside its walls, the Glagolitic Alley from Roč and traditional biska brandy.',
            ), 45.3493, 14.0496, 'hilltowns', self::t('Srednjovjekovni grad, Glagoljica', 'Mittelalter, Glagoliza', 'Medieval town, Glagolitic heritage')),
            $p('sight', self::t('Pazin – kaštel i jama', 'Pazin – Burg und Schlucht', 'Pazin – castle and chasm'), self::t(
                'Srednjovjekovni kaštel na rubu Pazinske jame u srcu Istre; u kaštelu su Etnografski muzej Istre i Gradski muzej.',
                'Mittelalterliche Burg am Rand der Pazinska jama im Herzen Istriens; in der Burg das Ethnografische Museum Istriens und das Stadtmuseum.',
                'Medieval castle on the edge of the Pazin chasm in the heart of Istria, home to the Ethnographic Museum of Istria and the Town Museum.',
            ), 45.2403, 13.9364, null, self::t('Kaštel, Muzej, Zipline', 'Burg, Museum, Zipline', 'Castle, Museum, Zipline')),
            $p('town', 'Motovun', self::t(
                'Gradić na brežuljku iznad doline Mirne s očuvanim zidinama; Motovunska šuma poznata je po tartufima.',
                'Hügelstädtchen über dem Mirna-Tal mit begehbarer Stadtmauer; der Wald von Motovun ist für Trüffel bekannt.',
                'Hilltop town above the Mirna valley with walkable walls; Motovun forest is famous for truffles.',
            ), 45.3367, 13.8285, 'hilltowns', self::t('Zidine, Tartufi, Vino', 'Stadtmauer, Trüffel, Wein', 'Town walls, Truffles, Wine'), 'olives'),
            $p('town', 'Grožnjan', self::t(
                'Grad umjetnika: galerije i ateljei u kamenim uličicama, ljeti koncerti i glazbene radionice.',
                'Stadt der Künstler: Galerien und Ateliers in Steingassen, im Sommer Konzerte und Musikkurse.',
                'Town of artists: galleries and studios in stone lanes, with concerts and music workshops in summer.',
            ), 45.3797, 13.7244, 'hilltowns', self::t('Galerije, Zalazak sunca', 'Galerien, Sonnenuntergang', 'Galleries, Sunset')),
            $p('town', 'Pula', self::t(
                'Najveći grad Istre: rimska Arena iz 1. stoljeća, Augustov hram, Slavoluk Sergijevaca i Kaštel.',
                'Größte Stadt Istriens: römische Arena aus dem 1. Jahrhundert, Augustustempel, Sergierbogen und Kastell.',
                'Istria\'s largest city: the 1st-century Roman Arena, Temple of Augustus, Arch of the Sergii and the Kaštel.',
            ), 44.8732, 13.8502, 'pula', self::t('Rimska baština, Muzeji, Koncerti', 'Römerzeit, Museen, Konzerte', 'Roman heritage, Museums, Concerts')),
            $p('nature', self::t('NP Brijuni (brod iz Fažane)', 'NP Brijuni (Boot ab Fažana)', 'Brijuni NP (boat from Fažana)'), self::t(
                'Nacionalni park od 14 otoka: brodom iz Fažane na Veliki Brijun – safari park, rimska vila, tragovi dinosaura.',
                'Nationalpark aus 14 Inseln: per Boot ab Fažana nach Veliki Brijun – Safaripark, römische Villa, Dinosaurierspuren.',
                'National park of 14 islands: boat from Fažana to Veliki Brijun – safari park, Roman villa, dinosaur footprints.',
            ), 44.9271, 13.8030, 'brijuni', self::t('Nacionalni park, Izlet brodom, Za djecu', 'Nationalpark, Bootsfahrt, Für Kinder', 'National park, Boat trip, Kids')),
            $p('town', 'Rovinj', self::t(
                'Stari grad na poluotoku s crkvom sv. Eufemije, umjetničkom ulicom Grisia i park-šumom Zlatni rt.',
                'Altstadt auf der Halbinsel mit der Kirche St. Euphemia, der Künstlergasse Grisia und dem Waldpark Goldenes Kap.',
                'Old town on a peninsula with St Euphemia\'s Church, the artists\' street Grisia and the Golden Cape forest park.',
            ), 45.0839, 13.6309, 'rovinj', self::t('Stari grad, Zalazak sunca, Plaže', 'Altstadt, Sonnenuntergang, Strände', 'Old town, Sunset, Beaches')),
            $p('sight', self::t('Poreč – Eufrazijeva bazilika', 'Poreč – Euphrasius-Basilika', 'Poreč – Euphrasian Basilica'), self::t(
                'Eufrazijeva bazilika iz 6. stoljeća s bizantskim mozaicima (UNESCO) i stari grad s rimskim rasporedom ulica.',
                'Euphrasius-Basilika aus dem 6. Jahrhundert mit byzantinischen Mosaiken (UNESCO) und Altstadt mit römischem Straßennetz.',
                'The 6th-century Euphrasian Basilica with Byzantine mosaics (UNESCO) and an old town on a Roman street plan.',
            ), 45.2285, 13.5934, 'porec', self::t('UNESCO, Mozaici, Šetnica', 'UNESCO, Mosaiken, Promenade', 'UNESCO, Mosaics, Promenade')),
            $p('nature', self::t('Rt Kamenjak', 'Kap Kamenjak', 'Cape Kamenjak'), self::t(
                'Najjužniji rt Istre: zaštićeni krajolik s tridesetak km razvedene obale, uvalama i skokovima u more.',
                'Südspitze Istriens: Schutzgebiet mit rund 30 km zerklüfteter Küste, Buchten und Klippenspringen.',
                'Istria\'s southern tip: a protected landscape with around 30 km of rugged coast, coves and cliff jumping.',
            ), 44.7940, 13.9130, 'kamenjak', self::t('Divlja priroda, Snorkeling, Bicikl', 'Wilde Natur, Schnorcheln, Rad', 'Wild nature, Snorkelling, Cycling')),
        ];
    }

    /**
     * Beaches. Coordinates from tourist boards / beach atlases; entries marked "approx."
     * are estimated from the map – verify in the CMS (Places block → place → Location).
     *
     * @return list<array<string, mixed>>
     */
    private static function beaches(): array
    {
        $b = static fn (string $title, array $summary, array $surface, array $features, float $lat, float $lng, bool $blueFlag = false, ?string $article = null): array => array_filter([
            'Kind' => 'beach',
            'Title' => $title,
            'Summary' => $summary,
            'Surface' => $surface,
            'Features' => $features,
            'BlueFlag' => $blueFlag,
            'Latitude' => $lat,
            'Longitude' => $lng,
            'article' => $article,
        ], static fn ($v) => $v !== null);

        $pebble = self::t('šljunak', 'Kies', 'pebble');
        $rocks = self::t('stijene i kamene ploče', 'Fels und Steinplatten', 'rocks and flat stones');
        $mixed = self::t('šljunak i stijene', 'Kies und Fels', 'pebble and rocks');

        return [
            $b('Maslinica, Rabac', self::t(
                'Oko 500 m duga šljunčana plaža u zaljevu zapadno od središta Rapca, uz borovu šumu; blag ulaz u more, restorani, sportovi na vodi i djelomično prilagođen pristup za osobe s invaliditetom.',
                'Rund 500 m langer Kiesstrand in der Bucht westlich des Zentrums von Rabac, am Pinienwald; flacher Einstieg, Restaurants, Wassersport und teilweise barrierefreier Zugang.',
                'About 500 m of pebble beach in the bay west of Rabac centre, backed by pine woods; gentle entry, restaurants, water sports and partly accessible for wheelchair users.',
            ), $pebble, self::t('Obitelji, Ležaljke, Sportovi na vodi, Pristupačno', 'Familien, Liegen, Wassersport, Barrierearm', 'Families, Loungers, Water sports, Accessible'), 45.07996, 14.15262, true, 'labin'),
            $b('Girandella, Rabac', self::t(
                'Najopremljenija plaža Rapca: obiteljski dio s pijeskom i šljunkom, plutajući vodeni park, beach barovi i ronilački centar; dalje uz obalu mirnije stjenovite uvale.',
                'Am besten ausgestatteter Strand von Rabac: Familienbereich mit Sand und Kies, schwimmender Wasserpark, Beachbars und Tauchbasis; weiter entlang der Küste ruhigere Felsbuchten.',
                'Rabac\'s best-equipped beach: a family section with sand and pebbles, floating water park, beach bars and a diving centre; quieter rocky coves further along.',
            ), self::t('šljunak, pijesak i kamene ploče', 'Kies, Sand und Steinplatten', 'pebble, sand and flat stones'), self::t('Obitelji, Vodeni park, Ronjenje, Plaža za pse u blizini', 'Familien, Wasserpark, Tauchen, Hundestrand in der Nähe', 'Families, Water park, Diving, Dog beach nearby'), 45.077333, 14.170972, true, 'labin'),
            $b('Lanterna, Rabac', self::t(
                'Stjenovita plaža uz šetnicu od središta Rapca prema Girandelli, s prirodnim hladom borova; ležaljke, tuševi i beach bar, a do nje se lako dođe pješice iz centra.',
                'Felsstrand an der Promenade vom Zentrum Rabac Richtung Girandella mit natürlichem Pinienschatten; Liegen, Duschen und Beachbar, vom Zentrum zu Fuß erreichbar.',
                'Rocky beach on the promenade from Rabac centre towards Girandella, with natural pine shade; loungers, showers and a beach bar, an easy walk from the centre.',
            ), $rocks, self::t('Hlad, Tuševi, Beach bar', 'Schatten, Duschen, Beachbar', 'Shade, Showers, Beach bar'), 45.0786, 14.1640, true), // approx.
            $b('Sveti Andrija, Rabac', self::t(
                'Manja i mirnija plaža istočno od Girandelle, šljunak i stijene; dobar izbor za jutarnje kupanje dalje od gužve.',
                'Kleinerer, ruhigerer Strand östlich von Girandella, Kies und Fels; gut für ein Morgenbad abseits des Trubels.',
                'Smaller, quieter beach east of Girandella, pebble and rock; a good choice for a morning swim away from the crowds.',
            ), $mixed, self::t('Mirno, Snorkeling', 'Ruhig, Schnorcheln', 'Quiet, Snorkelling'), 45.0748, 14.1765), // approx.
            $b('Prtlog (Duga Luka)', self::t(
                'Uvala nekadašnje labinske luke južno od Rapca; prirodna, mirna obala bez velikih sadržaja – za one koji traže tišinu.',
                'Bucht des ehemaligen Hafens von Labin südlich von Rabac; naturbelassene, ruhige Küste ohne große Infrastruktur – für Ruhesuchende.',
                'Cove of Labin\'s former port south of Rabac; a natural, quiet shoreline without big facilities – for those who want peace.',
            ), $mixed, self::t('Mirno, Priroda', 'Ruhig, Natur', 'Quiet, Nature'), 45.0476, 14.1590), // approx.
            $b('Sveta Marina', self::t(
                'Šljunčana plaža u malom mjestu Sveta Marina, s parkiralištem, barom i ronilačkim centrom; mirnija obiteljska alternativa Rapcu.',
                'Kiesstrand im kleinen Ort Sveta Marina mit Parkplatz, Bar und Tauchbasis; ruhigere Familienalternative zu Rabac.',
                'Pebble beach in the small village of Sveta Marina with parking, a bar and a diving centre; a quieter family alternative to Rabac.',
            ), $pebble, self::t('Obitelji, Parking, Bar, Ronjenje', 'Familien, Parkplatz, Bar, Tauchen', 'Families, Parking, Bar, Diving'), 45.0316, 14.1599),
            $b('Ravni', self::t(
                'Mala plaža u istoimenom selu koja rijetko bude puna: popločeni dio i šljunak, duboko i čisto more za ronjenje te vjetar koji privlači surfere.',
                'Kleiner Strand im gleichnamigen Dorf, selten voll: befestigter Bereich und Kies, tiefes klares Wasser zum Tauchen und Wind für Surfer.',
                'Small beach in the village of the same name that is rarely busy: a paved area and pebbles, deep clear water for diving and wind for surfers.',
            ), self::t('kamene ploče i šljunak', 'Steinplatten und Kies', 'paved and pebble'), self::t('Mirno, Ronjenje, Surfanje', 'Ruhig, Tauchen, Surfen', 'Quiet, Diving, Surfing'), 44.9803, 14.1655),
            $b('Tunarica', self::t(
                'Uvala uz istoimeni kamp na jugu Labinštine; obiteljska i prilagođena psima, s parkiralištem.',
                'Bucht am gleichnamigen Campingplatz im Süden der Region Labin; familien- und hundefreundlich, mit Parkplatz.',
                'Cove next to the campsite of the same name in the south of the Labin area; family- and dog-friendly, with parking.',
            ), $mixed, self::t('Obitelji, Psi dopušteni, Parking, Kamp', 'Familien, Hunde erlaubt, Parkplatz, Camping', 'Families, Dogs allowed, Parking, Campsite'), 44.969563, 14.096144),
            $b('Sipar, Mošćenička Draga', self::t(
                'Duga plaža sitnog šljunka na Kvarneru, podno Učke; obiteljska, sa sportskim i ronilačkim sadržajima te restoranima uz obalu.',
                'Langer Feinkiesstrand an der Kvarner-Bucht am Fuß der Učka; familienfreundlich, mit Sport- und Tauchangeboten und Restaurants an der Promenade.',
                'Long fine-pebble beach on the Kvarner Gulf at the foot of Učka; family-friendly, with sports and diving facilities and seafront restaurants.',
            ), self::t('sitni šljunak', 'Feinkies', 'fine pebble'), self::t('Obitelji, Sportovi na vodi, Restorani', 'Familien, Wassersport, Restaurants', 'Families, Water sports, Restaurants'), 45.235794, 14.254632, false, 'ucka'),
            $b('Medveja', self::t(
                'Šljunčana plaža u uvali između Lovrana i Mošćeničke Drage, podno Učke; idealna za kupanje nakon izleta na Vojak.',
                'Kiesstrand in der Bucht zwischen Lovran und Mošćenička Draga am Fuß der Učka; ideal zum Baden nach dem Ausflug auf den Vojak.',
                'Pebble beach in the bay between Lovran and Mošćenička Draga below Učka; ideal for a swim after a trip up Vojak.',
            ), $pebble, self::t('Obitelji, Parking, Restorani', 'Familien, Parkplatz, Restaurants', 'Families, Parking, Restaurants'), 45.2697, 14.2700, false, 'ucka'), // approx.
            $b('Lone, Zlatni rt (Rovinj)', self::t(
                'Plaže u park-šumi Zlatni rt južno od Rovinja: šljunak i stijene, hlad borova i šetnica uz more.',
                'Strände im Waldpark Goldenes Kap südlich von Rovinj: Kies und Fels, Pinienschatten und Uferweg.',
                'Beaches in the Golden Cape forest park south of Rovinj: pebble and rock, pine shade and a seafront path.',
            ), $mixed, self::t('Hlad, Obitelji, Beach bar', 'Schatten, Familien, Beachbar', 'Shade, Families, Beach bar'), 45.0717, 13.6400, false, 'rovinj'), // approx.
            $b('Ambrela, Pula', self::t(
                'Uređena šljunčana plaža na poluotoku Verudela u Puli, s blagim ulazom u more i sadržajima za obitelji – idealna nakon razgledavanja Arene.',
                'Gepflegter Kiesstrand auf der Halbinsel Verudela in Pula mit flachem Einstieg und Familienangeboten – ideal nach dem Besuch der Arena.',
                'Well-kept pebble beach on the Verudela peninsula in Pula with a gentle entry and family facilities – ideal after visiting the Arena.',
            ), $pebble, self::t('Obitelji, Ležaljke, Beach bar', 'Familien, Liegen, Beachbar', 'Families, Loungers, Beach bar'), 44.8605, 13.8186, false, 'pula'), // approx.
            $b('Mala Kolombarica, Kamenjak', self::t(
                'Divlja stjenovita obala na Rtu Kamenjak s morskim špiljama i dubokim morem; popularna za skokove u more i snorkeling – bez hlada i sadržaja.',
                'Wilde Felsküste am Kap Kamenjak mit Meereshöhlen und tiefem Wasser; beliebt zum Klippenspringen und Schnorcheln – ohne Schatten und Infrastruktur.',
                'Wild rocky shore at Cape Kamenjak with sea caves and deep water; popular for cliff jumping and snorkelling – no shade or facilities.',
            ), $rocks, self::t('Skokovi u more, Snorkeling, Bez sadržaja', 'Klippenspringen, Schnorcheln, Naturbelassen', 'Cliff jumping, Snorkelling, No facilities'), 44.7690533, 13.9114965, false, 'kamenjak'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function faqElement(array $title, array $items, ?string $style = null): array
    {
        return [
            'class' => FAQElement::class,
            'style' => $style,
            'fields' => [
                'Title' => $title,
                'ShowTitle' => true,
                'Eyebrow' => 'FAQ',
                'OutputSchema' => true,
            ],
            'items' => ['Items' => [FAQItem::class, array_map(
                static fn (array $qa): array => ['Question' => $qa[0], 'Answer' => $qa[1]],
                $items,
            )]],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function faq(): array
    {
        return self::faqElement(self::t('Česta *pitanja*', 'Häufige *Fragen*', 'Frequently asked *questions*'), [
            [
                self::t('Gdje se nalazi Villa Dina?', 'Wo liegt die Villa Dina?', 'Where is Villa Dina?'),
                self::t(
                    '<p>U selu Šumber (općina Sveta Nedelja) na brežuljcima istočne Istre, sjeverno od Labina. Do starog Labina vozite oko 15 minuta, do plaža Rapca dvadesetak minuta.</p>',
                    '<p>Im Dorf Šumber (Gemeinde Sveta Nedelja) in den Hügeln Ost-Istriens, nördlich von Labin. Zur Altstadt von Labin fahren Sie etwa 15 Minuten, zu den Stränden von Rabac rund zwanzig.</p>',
                    '<p>In the village of Šumber (Sveta Nedelja municipality) in the hills of eastern Istria, north of Labin. Labin old town is about 15 minutes by car, the beaches of Rabac around twenty.</p>',
                ),
            ],
            [
                self::t('Koliko gostiju prima vila?', 'Für wie viele Gäste ist die Villa?', 'How many guests does the villa sleep?'),
                self::t(
                    '<p>Do 8 osoba u četiri spavaće sobe s tri kupaonice. Kuća se iznajmljuje u cijelosti, s privatnim bazenom.</p>',
                    '<p>Bis zu 8 Personen in vier Schlafzimmern mit drei Bädern. Das Haus wird komplett vermietet, mit privatem Pool.</p>',
                    '<p>Up to 8 people in four bedrooms with three bathrooms. The whole house is rented exclusively, with a private pool.</p>',
                ),
            ],
            [
                self::t('Kada su dolazak i odlazak?', 'Wann sind An- und Abreise?', 'What are check-in and check-out times?'),
                self::t('<p>Dolazak od 16:00, odlazak do 10:00. U glavnoj sezoni dolazak i odlazak subotom.</p>', '<p>Anreise ab 16:00 Uhr, Abreise bis 10:00 Uhr. In der Hauptsaison samstags.</p>', '<p>Check-in from 4 pm, check-out by 10 am. In high season on Saturdays.</p>'),
            ],
            [
                self::t('Kako funkcionira plaćanje?', 'Wie funktioniert die Zahlung?', 'How does payment work?'),
                self::t('<p>Nakon potvrde termina plaćate 30 % predujma karticom ili bankovnom uplatom. Ostatak dospijeva 60 dana prije dolaska.</p>', '<p>Nach Bestätigung zahlen Sie 30 % Anzahlung per Karte oder Überweisung. Der Rest ist 60 Tage vor Anreise fällig.</p>', '<p>Once your dates are confirmed you pay a 30 % deposit by card or bank transfer. The balance is due 60 days before arrival.</p>'),
            ],
            [
                self::t('Je li boravišna pristojba uključena?', 'Ist die Kurtaxe inklusive?', 'Is the tourist tax included?'),
                self::t('<p>Da, boravišna pristojba prikazana je zasebno i uključena u ukupnu cijenu. Djeca do 12 godina su oslobođena.</p>', '<p>Ja, die Kurtaxe wird separat ausgewiesen und ist im Gesamtpreis enthalten. Kinder unter 12 Jahren sind befreit.</p>', '<p>Yes, the tourist tax is shown separately and included in the total. Children under 12 are exempt.</p>'),
            ],
            [
                self::t('Jesu li kućni ljubimci dobrodošli?', 'Sind Haustiere erlaubt?', 'Are pets allowed?'),
                self::t('<p>[Unesite vlastito pravilo.]</p>', '<p>[Bitte eigene Regel eintragen.]</p>', '<p>[Enter your own policy.]</p>'),
            ],
            [
                self::t('Treba li mi automobil?', 'Brauche ich ein Auto?', 'Do I need a car?'),
                self::t('<p>Preporučujemo – do plaža, trgovina i izleta najbrže se stiže automobilom. Parking je uz kuću.</p>', '<p>Wir empfehlen es – Strände, Einkaufen und Ausflüge erreichen Sie am besten mit dem Auto. Parkplatz direkt am Haus.</p>', '<p>We recommend it – beaches, shops and day trips are best reached by car. Parking is right by the house.</p>'),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private static function istriaFaq(): array
    {
        return self::faqElement(self::t('Izleti po Istri – *pitanja*', 'Ausflüge in Istrien – *Fragen*', 'Istria day trips – *FAQ*'), [
            [
                self::t('Koji su najbolji izleti iz Ville Dina?', 'Welche Ausflüge lohnen sich ab der Villa Dina?', 'What are the best day trips from Villa Dina?'),
                self::t(
                    '<p>Najbliži su Labin i Rabac (15–20 min) te Učka (oko 45 min). Unutar otprilike sat vremena stižete do Pule, Fažane (brod za Brijune), Rovinja, Motovuna i Huma; Poreč i Rt Kamenjak su nešto dalje.</p>',
                    '<p>Am nächsten sind Labin und Rabac (15–20 Min.) sowie die Učka (ca. 45 Min.). In rund einer Stunde erreichen Sie Pula, Fažana (Boot nach Brijuni), Rovinj, Motovun und Hum; Poreč und Kap Kamenjak liegen etwas weiter.</p>',
                    '<p>Closest are Labin and Rabac (15–20 min) and Učka (about 45 min). Within roughly an hour you reach Pula, Fažana (boat to Brijuni), Rovinj, Motovun and Hum; Poreč and Cape Kamenjak are a little further.</p>',
                ),
            ],
            [
                self::t('Kako doći na Brijune?', 'Wie kommt man nach Brijuni?', 'How do I get to Brijuni?'),
                self::t(
                    '<p>Brodom Nacionalnog parka iz Fažane kraj Pule, vožnja traje oko 15 minuta. Ulaznice kupite online u službenoj trgovini NP Brijuni ili u uredu parka u Fažani – ljeti unaprijed.</p>',
                    '<p>Mit dem Boot des Nationalparks ab Fažana bei Pula, die Überfahrt dauert etwa 15 Minuten. Tickets online im offiziellen Shop des NP Brijuni oder im Büro in Fažana – im Sommer vorab.</p>',
                    '<p>By National Park boat from Fažana near Pula; the crossing takes about 15 minutes. Buy tickets online in the official Brijuni NP shop or at the park office in Fažana – in advance in summer.</p>',
                ),
            ],
            [
                self::t('Koliko dana treba za Istru?', 'Wie viele Tage braucht man für Istrien?', 'How many days do I need for Istria?'),
                self::t(
                    '<p>Za Pulu, Brijune, Rovinj, Poreč i gradiće unutrašnjosti planirajte 4–5 izletničkih dana. Tjedan boravka ostavlja dovoljno vremena i za plažu i bazen.</p>',
                    '<p>Für Pula, Brijuni, Rovinj, Poreč und die Hügelstädtchen planen Sie 4–5 Ausflugstage. Eine Woche lässt genug Zeit für Strand und Pool.</p>',
                    '<p>Plan 4–5 days of trips for Pula, Brijuni, Rovinj, Poreč and the hill towns. A week\'s stay leaves plenty of time for the beach and pool.</p>',
                ),
            ],
            [
                self::t('Kada je najbolje vrijeme za izlete?', 'Wann ist die beste Zeit für Ausflüge?', 'When is the best time for sightseeing?'),
                self::t(
                    '<p>Proljeće i rana jesen (svibanj–lipanj, rujan–listopad): ugodne temperature i manje gužve. Ljeti krenite rano ujutro. Jesen je i sezona bijelih tartufa oko Motovuna.</p>',
                    '<p>Frühling und Frühherbst (Mai–Juni, September–Oktober): angenehme Temperaturen und weniger Andrang. Im Sommer früh am Morgen losfahren. Im Herbst ist zudem Saison der weißen Trüffel rund um Motovun.</p>',
                    '<p>Spring and early autumn (May–June, September–October): pleasant temperatures and fewer crowds. In summer set off early. Autumn is also white truffle season around Motovun.</p>',
                ),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private static function beachFaq(): array
    {
        return self::faqElement(self::t('Plaže – *pitanja*', 'Strände – *Fragen*', 'Beaches – *FAQ*'), [
            [
                self::t('Koja je plaža najbliža Villi Dina?', 'Welcher Strand ist der Villa Dina am nächsten?', 'Which beach is closest to Villa Dina?'),
                self::t(
                    '<p>Plaže u Rapcu – Maslinica, Lanterna i Girandella – udaljene su dvadesetak minuta vožnje. Točne kilometre i minute za svaku plažu vidite u popisu iznad.</p>',
                    '<p>Die Strände von Rabac – Maslinica, Lanterna und Girandella – sind rund zwanzig Minuten entfernt. Genaue Kilometer und Minuten je Strand stehen in der Liste oben.</p>',
                    '<p>The Rabac beaches – Maslinica, Lanterna and Girandella – are about twenty minutes away. Exact kilometres and minutes for each beach are in the list above.</p>',
                ),
            ],
            [
                self::t('Ima li u blizini pješčanih plaža?', 'Gibt es Sandstrände in der Nähe?', 'Are there sandy beaches nearby?'),
                self::t(
                    '<p>Malo – istarska obala je pretežno šljunčana i stjenovita. Obiteljski dio Girandelle ima mješavinu pijeska i šljunka; za sve ostale plaže preporučujemo cipele za vodu.</p>',
                    '<p>Kaum – die istrische Küste ist überwiegend Kies und Fels. Der Familienbereich von Girandella hat eine Mischung aus Sand und Kies; für alle anderen Strände empfehlen wir Badeschuhe.</p>',
                    '<p>Few – the Istrian coast is mostly pebble and rock. Girandella\'s family section has a mix of sand and pebbles; for the others we recommend water shoes.</p>',
                ),
            ],
            [
                self::t('Koja je plaža najbolja za djecu?', 'Welcher Strand ist am besten für Kinder?', 'Which beach is best for children?'),
                self::t(
                    '<p>Maslinica i Girandella u Rapcu: blag ulaz u more, Plava zastava, tuševi, ležaljke i sportovi na vodi. Mirnija alternativa je Sveta Marina.</p>',
                    '<p>Maslinica und Girandella in Rabac: flacher Einstieg, Blaue Flagge, Duschen, Liegen und Wassersport. Ruhigere Alternative: Sveta Marina.</p>',
                    '<p>Maslinica and Girandella in Rabac: gentle entry, Blue Flag, showers, loungers and water sports. A quieter alternative is Sveta Marina.</p>',
                ),
            ],
            [
                self::t('Gdje se mogu kupati sa psom?', 'Wo darf ich mit Hund baden?', 'Where can I swim with my dog?'),
                self::t(
                    '<p>U Rapcu postoji posebna plaža za pse u blizini Girandelle, a uvala Tunarica uz istoimeni kamp prilagođena je psima. Na ostalim plažama provjerite lokalna pravila.</p>',
                    '<p>In Rabac gibt es einen Hundestrand in der Nähe von Girandella, und die Bucht Tunarica am gleichnamigen Campingplatz ist hundefreundlich. An anderen Stränden lokale Regeln beachten.</p>',
                    '<p>Rabac has a dedicated dog beach near Girandella, and Tunarica cove by the campsite of the same name is dog-friendly. Check local rules at other beaches.</p>',
                ),
            ],
            [
                self::t('Gdje je najbolji snorkeling?', 'Wo kann man am besten schnorcheln?', 'Where is the best snorkelling?'),
                self::t(
                    '<p>Na stjenovitim dijelovima obale s dubljim morem: Ravni, Sveti Andrija i uvale Rta Kamenjak (npr. Kolombarica).</p>',
                    '<p>An felsigen Abschnitten mit tieferem Wasser: Ravni, Sveti Andrija und die Buchten am Kap Kamenjak (z. B. Kolumbarica).</p>',
                    '<p>On rocky stretches with deeper water: Ravni, Sveti Andrija and the coves of Cape Kamenjak (e.g. Kolumbarica).</p>',
                ),
            ],
        ]);
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
     * Istria guides (see SeedArticles).
     *
     * @return list<array<string, mixed>>
     */
    public static function blogPosts(): array
    {
        return SeedArticles::all();
    }

    /**
     * URL segments (default locale) of the earlier example posts – archived by --rebuild=blog.
     *
     * @return list<string>
     */
    public static function legacyPostSegments(): array
    {
        return ['pet-uvala-oko-rapca', 'setnja-starim-labinom', 'fuenf-buchten-rabac', 'spaziergang-labin', 'five-coves-rabac', 'walk-old-labin'];
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
