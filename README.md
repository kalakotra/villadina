# Villa Dina – tema za Silverstripe 6

Moderni mediteranski minimal: krečnjačka bijela, kvarnersko plavo, maslinasti akcent.
Fontovi: *Instrument Serif* (naslovi) + *Manrope* (tekst), self-hosted (bez Google Fonts → GDPR).

## Stack
- Vite 6 + Sass, Bootstrap 5.3 (selektivni import), Swiper 11, GLightbox, flatpickr
- Alpine.js (booking widget, FAQ) + vanilla ES moduli (header, slideri, lightbox, mapa, reveal)
- GLightbox i flatpickr se učitavaju **on-demand** (samo stranice koje ih koriste)

## Instalacija

```bash
composer update      # sve zavisnosti su u composer.json
composer build-assets   # npm ci + vite build → app/client/dist (manifest.json)
npm run dev          # dev server, uz .env: VITE_DEV_SERVER="http://localhost:5173"
vendor/bin/sake db:build --flush
```

`composer.json` već sadrži `extra.expose` (`app/client/dist`, `app/client/admin`, `themes/villadina/images`).

## Bitno
- `App\Extensions\ViteExtension` **zamjenjuje** postojeću (iste metode `IsViteDev`/`ViteDevUrl`/`ViteAsset` + nova `ViteTags`). Ispravno skuplja CSS statičkih importa i dodaje `modulepreload`.
- `app/_config/elemental.yml`: ključ `ModularPage` prilagoditi FQCN-u ako je klasa u namespaceu.
- `GalleryElement` ostaje vaš — `GalleryElementExtension` mu dodaje `Eyebrow`, `DisplayMode` (grid/slider) i helpere za template.
- Settings → **Villa**: logo, kontakt, WhatsApp, cijena „od“, min. noćenja, max. gostiju, stranica za upit, Booking.com/Airbnb, Impressum/Datenschutz.

## Elementi
| Element | Namjena |
|---|---|
| Hero | full-screen slideshow ili video, CTA, booking widget |
| Key facts | brojke (gosti, sobe, bazen m², km do mora) |
| Text + image | priča uz 1–2 slike, lijevo/desno |
| Amenities | ikonice sadržaja (21 ugrađena SVG ikona ili custom) |
| Gallery | mozaik (9 + lightbox) ili slider |
| Location | udaljenosti + mapa (OSM/Google, učitava se tek na klik – GDPR) |
| Reviews | slider recenzija + ukupna ocjena |
| Booking | widget dostupnosti → booking stranica, + Booking.com/Airbnb |
| FAQ | accordion + FAQPage JSON-LD |
| CTA | završni poziv, sa ili bez pozadinske slike |

Svaki element ima stil pozadine (bijela / pijesak / more) i opciju manjeg razmaka.
U naslovima `*riječ*` → italic akcent u plavoj (npr. `Villa *Dina*`).

## Rezervacijski sistem

### Instalacija
Stripe, mPDF i Guzzle su u `composer.json`.
`.env`:
```
STRIPE_SECRET_KEY="sk_live_…"
STRIPE_WEBHOOK_SECRET="whsec_…"
```
Stripe webhook → `https://<domena>/booking/stripe-webhook`, događaji:
`checkout.session.completed`, `checkout.session.async_payment_succeeded`, `checkout.session.async_payment_failed`, `checkout.session.expired`.
Načini plaćanja (kartica, SEPA Debit, Apple/Google Pay…) uključuju se u Stripe dashboardu.

Cron:
```
15 6 * * *   cd /var/www/villadina && vendor/bin/sake tasks:booking-maintenance
7,37 * * * * cd /var/www/villadina && vendor/bin/sake tasks:booking-ical-sync
```

### Postavke
- **Settings → Booking**: predujam % (30), ostatak X dana prije dolaska (60), rok za uplatu nakon odobrenja (3 dana), podsjetnik (14 dana prije roka), min. dana do dolaska, boravišna pristojba (po odrasloj osobi/noći, djeca 12–17 %, djeca < 12 oslobođena, naplata online ili na licu mjesta), podaci vlasnika i IBAN za predračun, uvjeti otkazivanja.
- **Bookings → Seasons & prices**: sezone (od – zadnja noć).
  - *Weekly*: tjedna cijena, fiksni dan dolaska/odlaska (npr. subota); boravak samo u cijelim tjednima. Sezona mora početi na taj dan.
  - *Nightly*: cijena po noći, min. noćenja.
  - Boravak može prelaziti iz sezone u sezonu; svaki dio poštuje pravila svoje sezone. Period bez sezone = nema online cijene.
  - Boravišna pristojba po sezoni (opcionalno, npr. viša u glavnoj sezoni).
- **Bookings → Calendar sync**: po kanalu (Booking.com, Airbnb) *Import URL* (njihov export) + naš *Export URL* (zalijepiti kod njih). Export sadrži naše rezervacije, ručne blokade i blokade s **drugih** kanala → nema petlji.
- **Bookings → Blocked periods**: vlastito korištenje, radovi.
- Kreirati stranicu tipa **Booking page** (npr. `/buchen`); svi gumbi „Provjerite dostupnost" automatski vode na nju.

### Tok
1. Gost bira termin (kalendar prikazuje zauzete noći, dan odlaska može biti tuđi dan dolaska), broj odraslih / djece 12–17 / djece 0–11 → **cijena uživo** (sezone, pristojba, plan plaćanja).
2. Šalje **upit** → status *Request*, e-mail gostu i vlasniku. Termin još nije blokiran.
3. Vlasnik u CMS-u: **Approve & send payment link** (ponovna provjera dostupnosti u transakciji) ili **Decline**.
   - Odobreno → termin se drži, plan: 30 % do *danas + 3 dana*, ostatak 60 dana prije dolaska. Ako je dolazak bliže od 60 dana → 100 % odmah.
   - Gost dobiva e-mail s linkom za plaćanje + PDF predračun (ako je IBAN unesen).
4. Gost plaća **Stripeom** (webhook automatski potvrđuje) ili **uplatom na račun** (vlasnik klikne *Bank transfer received*).
   - Predujam plaćen → *Confirmed*, e-mail potvrde. Puni iznos → *Fully paid*.
5. Cron: neplaćena odobrenja nakon roka → *Expired* (termin se oslobađa); podsjetnik za ostatak 14 dana prije roka; obavijest vlasniku kad ostatak kasni (bez automatskog otkazivanja); prošli boravci → *Completed*.
6. Kasna uplata na isteklu rezervaciju: vraća se u *Confirmed* samo ako je termin još slobodan, inače napomena „refund required".

Ručne rezervacije (telefon): u CMS-u nova rezervacija → cijena se računa iz sezona (pravila min. noćenja / dana dolaska se preskaču), opcionalna korekcija (popust) → *Approve* → *Bank transfer / Cash received*.

### Napomene
- Predračun (PDF) **nije fiskalni račun** — račun izdaje vlasnik (eVisitor / fiskalizacija) nakon boravka.
- Povrati novca (storno) rade se ručno u Stripeu / bankom; status *Cancelled* samo oslobađa termin i obavještava gosta.
- Iznosi se spremaju u centima (int), valuta EUR.
- Čuvanje podataka gostiju (GDPR): po potrebi dodati task za anonimizaciju starih rezervacija.

## Početni sadržaj (seed)

```bash
vendor/bin/sake db:build --flush
vendor/bin/sake tasks:villa-seed                    # svjež DB
vendor/bin/sake tasks:villa-seed --remove-defaults  # + arhivira installer "About Us"/"Contact Us"
vendor/bin/sake tasks:villa-seed --force            # ponovo; dodaje samo ono što nedostaje
```

Kreira:
- Fluent lokale `hr_HR` (default), `de_DE`, `en_GB` ako ne postoje;
- stranice: Početna (installer `home` se pretvara u `ModularPage`), Vila, Galerija, Lokacija, Rezervacija (Booking page), Istra savjeti (blog s 2 članka), Kontakt (UserForms + primatelj), Uvjeti najma, Impressum, Zaštita podataka;
- sve blokove s HR/DE/EN tekstovima, slike iz `app/seed/images` (folder *Villa* u Files);
- postavke (Settings → Villa / Booking), sezone za tekuću i sljedeću godinu, Booking.com/Airbnb feedove (bez import URL-a).

`ModularPage` klasa: `App\Task\SeedContentTask.page_class` (yml) ako je u namespaceu.

**Sve je primjer:** cijene, broj soba/gostiju, udaljenosti i koordinate (približne – provjeriti), recenzije (`[Gast 1]` placeholderi), pravni tekstovi (`[…]`) i slike (ilustracije označene „Placeholder“) zamijeniti stvarnim sadržajem prije objave.

## Preview
`preview/` sadrži statičke HTML stranice istog markupa kao templati (za brzi pregled bez SS-a) i `screenshots/`.

## Prijevodi
`app/lang/{en,de,hr}.yml` — namespace `VillaDina`. Fluent: lokalizirani su itemi elemenata i SiteConfig polja (`app/_config/fluent.yml`).
