<?php

declare(strict_types=1);

namespace App\Task\Seed;

/**
 * Istria travel guides (blog posts) in HR / DE / EN.
 *
 * Written answer-first for search engines and AI answers (SEO / AIO / GEO):
 * the first paragraph answers "what is it and why go", followed by sights,
 * practical tips and a short FAQ. Exact driving distances from the villa are
 * shown automatically in the "Quick facts" box (linked Place, OSRM).
 *
 * {link:key} is replaced with a link to the seeded page (e.g. {link:beaches}).
 * Facts checked against official/tourist-board sources in 2026 – review before publishing.
 */
final class SeedArticles
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            self::labin(),
            self::pula(),
            self::brijuni(),
            self::rovinj(),
            self::porec(),
            self::hilltowns(),
            self::ucka(),
            self::kamenjak(),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function t(string $hr, string $de, string $en): array
    {
        return ['hr' => $hr, 'de' => $de, 'en' => $en];
    }

    private static function labin(): array
    {
        return [
            'key' => 'labin',
            'image' => 'labin',
            'category' => self::t('Gradovi', 'Städte', 'Towns'),
            'Title' => self::t(
                'Labin i Rabac: stari grad na brežuljku i more ispod njega',
                'Labin und Rabac: Altstadt auf dem Hügel, Meer zu Füßen',
                'Labin and Rabac: hilltop old town with the sea below',
            ),
            'URLSegment' => self::t('labin-i-rabac', 'labin-und-rabac', 'labin-and-rabac'),
            'MetaTitle' => self::t(
                'Labin i Rabac – što vidjeti, plaže i savjeti',
                'Labin & Rabac – Sehenswürdigkeiten, Strände, Tipps',
                'Labin & Rabac – Things to Do, Beaches and Tips',
            ),
            'MetaDescription' => self::t(
                'Labin i Rabac kraj Ville Dina: srednjovjekovni stari grad, rudarska baština, šetnica Lungomare i šljunčane plaže s Plavom zastavom. Savjeti za posjet.',
                'Labin und Rabac bei der Villa Dina: mittelalterliche Altstadt, Bergbaugeschichte, Lungomare und Kiesstrände mit Blauer Flagge. Tipps für Ihren Besuch.',
                'Labin and Rabac near Villa Dina: medieval old town, mining heritage, the Lungomare promenade and Blue Flag pebble beaches. Tips for your visit.',
            ),
            'Summary' => self::t(
                '<p>Najbliži izlet od vile: kamene uličice i vidikovac Labina, a ispod njih plaže Rapca.</p>',
                '<p>Der nächste Ausflug ab der Villa: Steingassen und Aussicht in Labin, darunter die Strände von Rabac.</p>',
                '<p>The closest outing from the villa: Labin\'s stone lanes and viewpoint, with the beaches of Rabac below.</p>',
            ),
            'Content' => self::t(
                '<p><strong>Labin je srednjovjekovni gradić na brežuljku iznad istočne obale Istre, a Rabac nekadašnje ribarsko mjesto podno njega, danas poznato po šljunčanim plažama s Plavom zastavom.</strong> Od Ville Dina do starog Labina vozite otprilike četvrt sata, do mora u Rapcu oko 20 minuta – idealno za poludnevni izlet ili večernju šetnju.</p>'
                . '<h2>Što vidjeti u starom Labinu</h2>'
                . '<ul>'
                . '<li><strong>Stari grad</strong> – kamene uličice, renesansne i barokne palače te gradska vrata. S vidikovca Fortica pruža se pogled na Kvarner i otok Cres.</li>'
                . '<li><strong>Matija Vlačić Ilirik</strong> – Labin je rodni grad ovog reformatora iz 16. stoljeća; posvećena mu je spomen-zbirka u starom gradu.</li>'
                . '<li><strong>Narodni muzej Labin</strong> – među ostalim prikazuje rudarsku prošlost kraja, koji je stoljećima živio od ugljena.</li>'
                . '<li><strong>Galerije i ateljei</strong> – u uličicama starog grada radi niz umjetnika; ljeti grad oživi koncertima i festivalima.</li>'
                . '</ul>'
                . '<h2>Rabac: šetnica Lungomare i plaže</h2>'
                . '<p>Rabac je od Labina udaljen nekoliko kilometara vijugave ceste. Obalna šetnica povezuje središte mjesta s plažama Maslinica, Lanterna i Girandella. Maslinica i Girandella imaju blag ulaz u more, ležaljke, tuševe i sportove na vodi, pa su prvi izbor za obitelji s djecom. Popis svih plaža s točnim udaljenostima od vile pronaći ćete na stranici <a href="{link:beaches}">Plaže u blizini</a>.</p>'
                . '<h2>Praktični savjeti</h2>'
                . '<ul>'
                . '<li>U stari grad Labina ulazi se uglavnom pješice – parkirajte na parkiralištima ispod starog grada.</li>'
                . '<li>Najljepše je kasno poslijepodne: zalazak sunca s vidikovca, zatim večera uz more u Rapcu.</li>'
                . '<li>Ljeti na plaže Rapca krenite prije 10 sati – parkirališta se brzo pune.</li>'
                . '</ul>'
                . '<h2>Česta pitanja</h2>'
                . '<h3>Koliko je od Ville Dina do Labina i Rapca?</h3><p>Do starog grada Labina oko 15 minuta, do plaža Rapca oko 20 minuta vožnje automobilom.</p>'
                . '<h3>Je li Rabac dobar za obitelji s djecom?</h3><p>Da. Maslinica i Girandella imaju blag ulaz u more i sadržaje za djecu, a Girandella i plutajući vodeni park.</p>',
                '<p><strong>Labin ist ein mittelalterliches Städtchen auf einem Hügel über der Ostküste Istriens; Rabac, das ehemalige Fischerdorf darunter, ist heute für seine Kiesstrände mit Blauer Flagge bekannt.</strong> Von der Villa Dina erreichen Sie die Altstadt von Labin in etwa einer Viertelstunde, das Meer in Rabac in rund 20 Minuten – ideal für einen halben Tag oder einen Abendbummel.</p>'
                . '<h2>Sehenswertes in der Altstadt von Labin</h2>'
                . '<ul>'
                . '<li><strong>Altstadt</strong> – Steingassen, Renaissance- und Barockpaläste und Stadttore. Vom Aussichtspunkt Fortica blicken Sie über die Kvarner-Bucht bis zur Insel Cres.</li>'
                . '<li><strong>Matthias Flacius Illyricus</strong> – der Reformator des 16. Jahrhunderts wurde in Labin geboren; eine Gedenksammlung in der Altstadt ist ihm gewidmet.</li>'
                . '<li><strong>Stadtmuseum Labin</strong> – zeigt unter anderem die Bergbaugeschichte der Region, die jahrhundertelang von der Kohle lebte.</li>'
                . '<li><strong>Galerien und Ateliers</strong> – in den Gassen arbeiten zahlreiche Künstler; im Sommer beleben Konzerte und Festivals die Stadt.</li>'
                . '</ul>'
                . '<h2>Rabac: Lungomare und Strände</h2>'
                . '<p>Rabac liegt einige Kilometer kurvige Straße unterhalb von Labin. Die Uferpromenade verbindet den Ortskern mit den Stränden Maslinica, Lanterna und Girandella. Maslinica und Girandella bieten einen flachen Einstieg ins Wasser, Liegen, Duschen und Wassersport – erste Wahl für Familien. Alle Strände mit genauen Entfernungen ab der Villa finden Sie unter <a href="{link:beaches}">Strände in der Nähe</a>.</p>'
                . '<h2>Praktische Tipps</h2>'
                . '<ul>'
                . '<li>Die Altstadt von Labin erkunden Sie zu Fuß – parken Sie auf den Parkplätzen unterhalb.</li>'
                . '<li>Am schönsten ist der späte Nachmittag: Sonnenuntergang am Aussichtspunkt, danach Abendessen am Meer in Rabac.</li>'
                . '<li>Im Sommer vor 10 Uhr an die Strände von Rabac fahren – die Parkplätze füllen sich schnell.</li>'
                . '</ul>'
                . '<h2>Häufige Fragen</h2>'
                . '<h3>Wie weit ist es von der Villa Dina nach Labin und Rabac?</h3><p>Bis zur Altstadt von Labin etwa 15 Minuten, bis zu den Stränden von Rabac etwa 20 Minuten mit dem Auto.</p>'
                . '<h3>Ist Rabac für Familien mit Kindern geeignet?</h3><p>Ja. Maslinica und Girandella haben einen flachen Einstieg und Angebote für Kinder, Girandella zudem einen schwimmenden Wasserpark.</p>',
                '<p><strong>Labin is a medieval hilltop town above the east coast of Istria; Rabac, the former fishing village below it, is now known for its Blue Flag pebble beaches.</strong> From Villa Dina you reach Labin old town in about a quarter of an hour and the sea at Rabac in around 20 minutes – ideal for half a day out or an evening stroll.</p>'
                . '<h2>What to see in Labin old town</h2>'
                . '<ul>'
                . '<li><strong>The old town</strong> – stone lanes, Renaissance and Baroque palaces and town gates. The Fortica viewpoint looks out over the Kvarner Gulf to the island of Cres.</li>'
                . '<li><strong>Matthias Flacius Illyricus</strong> – the 16th-century reformer was born in Labin; a memorial collection in the old town is dedicated to him.</li>'
                . '<li><strong>Labin City Museum</strong> – among other things it tells the story of the area\'s coal-mining past.</li>'
                . '<li><strong>Galleries and studios</strong> – many artists work in the old lanes; in summer concerts and festivals bring the town to life.</li>'
                . '</ul>'
                . '<h2>Rabac: the Lungomare and beaches</h2>'
                . '<p>Rabac lies a few kilometres of winding road below Labin. The seafront promenade links the village centre with the beaches of Maslinica, Lanterna and Girandella. Maslinica and Girandella have a gentle entry into the water, loungers, showers and water sports – the first choice for families. All beaches with exact distances from the villa are listed under <a href="{link:beaches}">Beaches nearby</a>.</p>'
                . '<h2>Practical tips</h2>'
                . '<ul>'
                . '<li>Labin old town is explored on foot – park in the car parks below it.</li>'
                . '<li>Late afternoon is best: sunset from the viewpoint, then dinner by the sea in Rabac.</li>'
                . '<li>In summer head to the Rabac beaches before 10 am – the car parks fill up quickly.</li>'
                . '</ul>'
                . '<h2>FAQ</h2>'
                . '<h3>How far is Labin and Rabac from Villa Dina?</h3><p>About 15 minutes by car to Labin old town and about 20 minutes to the beaches of Rabac.</p>'
                . '<h3>Is Rabac good for families with children?</h3><p>Yes. Maslinica and Girandella have a gentle entry into the sea and facilities for children; Girandella also has a floating water park.</p>',
            ),
        ];
    }

    private static function pula(): array
    {
        return [
            'key' => 'pula',
            'category' => self::t('Gradovi', 'Städte', 'Towns'),
            'Title' => self::t(
                'Pula u jednom danu: Arena, Forum i more',
                'Pula an einem Tag: Arena, Forum und Meer',
                'Pula in a day: the Arena, the Forum and the sea',
            ),
            'URLSegment' => self::t('pula-u-jednom-danu', 'pula-an-einem-tag', 'pula-in-a-day'),
            'MetaTitle' => self::t(
                'Pula u jednom danu – Arena, Forum i plaže',
                'Pula an einem Tag – Arena, Forum und Strände',
                'Pula in a Day – Arena, Roman Forum and Beaches',
            ),
            'MetaDescription' => self::t(
                'Što vidjeti u Puli: rimska Arena iz 1. stoljeća, Augustov hram, Slavoluk Sergijevaca i Kaštel. Plan za jedan dan i savjeti za izlet iz Ville Dina.',
                'Pula an einem Tag: römische Arena aus dem 1. Jahrhundert, Augustustempel, Sergierbogen und Kastell. Tagesplan und Tipps für den Ausflug ab Villa Dina.',
                'What to see in Pula: the 1st-century Roman Arena, Temple of Augustus, Arch of the Sergii and the Kaštel. A one-day plan and tips for a trip from Villa Dina.',
            ),
            'Summary' => self::t(
                '<p>Rimski amfiteatar, stari grad i kupanje na Verudeli – najveći grad Istre u jednom danu.</p>',
                '<p>Römisches Amphitheater, Altstadt und Baden auf Verudela – Istriens größte Stadt an einem Tag.</p>',
                '<p>Roman amphitheatre, old town and a swim at Verudela – Istria\'s largest city in a day.</p>',
            ),
            'Content' => self::t(
                '<p><strong>Pula je najveći grad Istre i dom Arene – rimskog amfiteatra iz 1. stoljeća, jednog od šest najvećih sačuvanih na svijetu.</strong> Stari grad s Forumom i rimskim spomenicima obiđe se pješice za pola dana, a ostatak dana možete provesti na plažama Verudele ili na rtu Kamenjak. Od Ville Dina do Pule vozite otprilike 50 minuta.</p>'
                . '<h2>Što vidjeti u Puli</h2>'
                . '<ul>'
                . '<li><strong>Arena</strong> – monumentalni amfiteatar iz vremena cara Vespazijana; ljeti pozornica koncerata i Pulskog filmskog festivala.</li>'
                . '<li><strong>Forum i Augustov hram</strong> – glavni trg još od rimskog doba, s hramom posvećenim caru Augustu.</li>'
                . '<li><strong>Slavoluk Sergijevaca</strong> – rimski slavoluk na ulazu u stari grad, odakle počinje glavna pješačka ulica.</li>'
                . '<li><strong>Kaštel</strong> – mletačka utvrda na brežuljku iznad grada s pogledom na luku; u njoj je Povijesni i pomorski muzej Istre.</li>'
                . '<li><strong>Gradska tržnica</strong> – secesijska zgrada tržnice, idealna za kavu, ribu i lokalne proizvode.</li>'
                . '<li><strong>Svjetleći divovi</strong> – navečer osvijetljene dizalice brodogradilišta Uljanik.</li>'
                . '</ul>'
                . '<h2>Plan za jedan dan</h2>'
                . '<p>Ujutro Arena (prije vrućine i gužve), zatim šetnja do Slavoluka Sergijevaca i Foruma, ručak kod tržnice. Poslijepodne kupanje na poluotoku Verudela (npr. plaža Ambrela) ili u zaštićenom krajoliku Kamenjak, a navečer povratak uz osvijetljene dizalice.</p>'
                . '<h2>Praktični savjeti</h2>'
                . '<ul>'
                . '<li>Parkirajte na javnim parkiralištima uz rub starog grada – centar je pješačka zona.</li>'
                . '<li>Ulaznice za koncerte u Areni ljeti se brzo rasprodaju – rezervirajte na vrijeme.</li>'
                . '<li>Pula ima zračnu luku – najbližu Villi Dina za letove iz Njemačke, Austrije i Velike Britanije.</li>'
                . '</ul>'
                . '<h2>Česta pitanja</h2>'
                . '<h3>Koliko vremena treba za Pulu?</h3><p>Za Arenu i stari grad pola dana; s kupanjem i Kamenjakom cijeli dan.</p>'
                . '<h3>Koliko je Pula udaljena od Ville Dina?</h3><p>Otprilike 50 minuta vožnje automobilom; točnu udaljenost vidite u okviru „Ukratko“.</p>',
                '<p><strong>Pula ist die größte Stadt Istriens und Heimat der Arena – eines römischen Amphitheaters aus dem 1. Jahrhundert und eines der sechs größten erhaltenen der Welt.</strong> Die Altstadt mit Forum und römischen Denkmälern erkunden Sie zu Fuß in einem halben Tag; den Rest des Tages verbringen Sie an den Stränden von Verudela oder am Kap Kamenjak. Von der Villa Dina fahren Sie etwa 50 Minuten nach Pula.</p>'
                . '<h2>Sehenswürdigkeiten in Pula</h2>'
                . '<ul>'
                . '<li><strong>Arena</strong> – monumentales Amphitheater aus der Zeit Kaiser Vespasians; im Sommer Bühne für Konzerte und das Filmfestival Pula.</li>'
                . '<li><strong>Forum und Augustustempel</strong> – seit der Römerzeit der Hauptplatz der Stadt, mit dem Kaiser Augustus geweihten Tempel.</li>'
                . '<li><strong>Sergierbogen</strong> – römischer Triumphbogen am Eingang zur Altstadt, hier beginnt die Fußgängerzone.</li>'
                . '<li><strong>Kastell (Kaštel)</strong> – venezianische Festung auf dem Hügel über der Stadt mit Blick auf den Hafen; darin das Historische und Maritime Museum Istriens.</li>'
                . '<li><strong>Markthalle</strong> – Jugendstil-Markthalle, ideal für Kaffee, Fisch und regionale Produkte.</li>'
                . '<li><strong>Leuchtende Giganten</strong> – abends beleuchtete Kräne der Werft Uljanik.</li>'
                . '</ul>'
                . '<h2>Tagesplan</h2>'
                . '<p>Morgens die Arena (vor Hitze und Andrang), dann zu Fuß zum Sergierbogen und Forum, Mittagessen an der Markthalle. Nachmittags Baden auf der Halbinsel Verudela (z. B. Strand Ambrela) oder im Landschaftsschutzgebiet Kamenjak, abends vorbei an den beleuchteten Kränen zurück.</p>'
                . '<h2>Praktische Tipps</h2>'
                . '<ul>'
                . '<li>Parken Sie auf öffentlichen Parkplätzen am Rand der Altstadt – das Zentrum ist Fußgängerzone.</li>'
                . '<li>Konzertkarten für die Arena sind im Sommer schnell ausverkauft – rechtzeitig buchen.</li>'
                . '<li>Pula hat einen Flughafen – für Flüge aus Deutschland, Österreich und Großbritannien der nächstgelegene zur Villa Dina.</li>'
                . '</ul>'
                . '<h2>Häufige Fragen</h2>'
                . '<h3>Wie viel Zeit braucht man für Pula?</h3><p>Für Arena und Altstadt einen halben Tag; mit Baden und Kamenjak einen ganzen Tag.</p>'
                . '<h3>Wie weit ist Pula von der Villa Dina entfernt?</h3><p>Etwa 50 Minuten mit dem Auto; die genaue Entfernung steht im Kasten „Auf einen Blick“.</p>',
                '<p><strong>Pula is Istria\'s largest city and home to the Arena – a 1st-century Roman amphitheatre and one of the six largest surviving in the world.</strong> The old town with its Forum and Roman monuments takes half a day on foot; spend the rest of the day on the beaches of Verudela or at Cape Kamenjak. From Villa Dina the drive to Pula takes about 50 minutes.</p>'
                . '<h2>What to see in Pula</h2>'
                . '<ul>'
                . '<li><strong>The Arena</strong> – a monumental amphitheatre from the reign of Emperor Vespasian; in summer a stage for concerts and the Pula Film Festival.</li>'
                . '<li><strong>The Forum and Temple of Augustus</strong> – the main square since Roman times, with a temple dedicated to Emperor Augustus.</li>'
                . '<li><strong>Arch of the Sergii</strong> – a Roman triumphal arch at the entrance to the old town, where the main pedestrian street begins.</li>'
                . '<li><strong>The Kaštel</strong> – a Venetian fortress on the hill above town overlooking the harbour; it houses the Historical and Maritime Museum of Istria.</li>'
                . '<li><strong>The market hall</strong> – an Art Nouveau market building, perfect for coffee, fish and local produce.</li>'
                . '<li><strong>Lighting Giants</strong> – the Uljanik shipyard cranes, illuminated at night.</li>'
                . '</ul>'
                . '<h2>A one-day plan</h2>'
                . '<p>Arena in the morning (before the heat and crowds), then walk to the Arch of the Sergii and the Forum and have lunch by the market. In the afternoon swim on the Verudela peninsula (e.g. Ambrela beach) or in the Kamenjak protected landscape, and drive back past the illuminated cranes.</p>'
                . '<h2>Practical tips</h2>'
                . '<ul>'
                . '<li>Park in the public car parks at the edge of the old town – the centre is pedestrianised.</li>'
                . '<li>Concert tickets for the Arena sell out quickly in summer – book early.</li>'
                . '<li>Pula has an airport – the closest to Villa Dina for flights from Germany, Austria and the UK.</li>'
                . '</ul>'
                . '<h2>FAQ</h2>'
                . '<h3>How much time do I need for Pula?</h3><p>Half a day for the Arena and the old town; a full day with swimming and Kamenjak.</p>'
                . '<h3>How far is Pula from Villa Dina?</h3><p>About 50 minutes by car; the exact distance is shown in the "Quick facts" box.</p>',
            ),
        ];
    }

    private static function brijuni(): array
    {
        return [
            'key' => 'brijuni',
            'category' => self::t('Priroda', 'Natur', 'Nature'),
            'Title' => self::t(
                'Nacionalni park Brijuni: izlet brodom iz Fažane',
                'Nationalpark Brijuni: Bootsausflug ab Fažana',
                'Brijuni National Park: a boat trip from Fažana',
            ),
            'URLSegment' => self::t('nacionalni-park-brijuni', 'nationalpark-brijuni', 'brijuni-national-park'),
            'MetaTitle' => self::t(
                'NP Brijuni – izlet iz Fažane, ulaznice i savjeti',
                'Nationalpark Brijuni – Ausflug ab Fažana, Tickets',
                'Brijuni National Park – Trip from Fažana, Tickets',
            ),
            'MetaDescription' => self::t(
                'Kako posjetiti NP Brijuni: brod iz Fažane, safari park, rimska vila u Verigama, tragovi dinosaura i stara maslina. Ulaznice, trajanje i savjeti.',
                'So besuchen Sie den Nationalpark Brijuni: Boot ab Fažana, Safaripark, römische Villa in Verige, Dinosaurierspuren und uralter Olivenbaum. Tickets & Tipps.',
                'How to visit Brijuni National Park: boat from Fažana, safari park, Roman villa at Verige, dinosaur footprints and an ancient olive tree. Tickets and tips.',
            ),
            'Summary' => self::t(
                '<p>Otočje od 14 otoka nasuprot Fažane: safari park, rimske ruševine i tragovi dinosaura.</p>',
                '<p>14 Inseln gegenüber von Fažana: Safaripark, römische Ruinen und Dinosaurierspuren.</p>',
                '<p>An archipelago of 14 islands off Fažana: safari park, Roman ruins and dinosaur footprints.</p>',
            ),
            'Content' => self::t(
                '<p><strong>Nacionalni park Brijuni otočje je od 14 otoka uz zapadnu obalu Istre, nasuprot mjesta Fažana kraj Pule.</strong> Brodom Nacionalnog parka iz Fažane za oko 15 minuta stižete na Veliki Brijun, najveći otok, gdje se razgledavanje nastavlja turističkim vlakićem uz vodiča. Od Ville Dina do Fažane vozite otprilike 55 minuta.</p>'
                . '<h2>Što vidjeti na Velikom Brijunu</h2>'
                . '<ul>'
                . '<li><strong>Safari park</strong> – egzotične životinje, dijelom darovi svjetskih državnika iz vremena kada je otok bio Titova ljetna rezidencija.</li>'
                . '<li><strong>Rimska vila u uvali Verige</strong> – ostaci raskošnog ljetnikovca iz 1. stoljeća.</li>'
                . '<li><strong>Bizantski castrum</strong> – ostaci utvrđenog naselja iz kasne antike.</li>'
                . '<li><strong>Tragovi dinosaura</strong> – otisci sačuvani u stijenama uz obalu.</li>'
                . '<li><strong>Stara maslina</strong> – stablo staro oko 1600 godina koje i danas rađa.</li>'
                . '<li><strong>Izložba „Tito na Brijunima“</strong> – fotografije i priče o državnim posjetima.</li>'
                . '</ul>'
                . '<h2>Ulaznice i organizacija</h2>'
                . '<ul>'
                . '<li>Ulaznice se kupuju online u službenoj trgovini NP Brijuni (shop.np-brijuni.hr) ili u uredu Nacionalnog parka u Fažani.</li>'
                . '<li>Standardni posjet uključuje brodski prijevoz i vođeni obilazak Velikog Brijuna; izlet traje otprilike pola dana.</li>'
                . '<li>U srpnju i kolovozu rezervirajte unaprijed – broj mjesta na brodovima je ograničen.</li>'
                . '<li>Ponesite vodu, šešir i udobnu obuću; dio obilaska možete prijeći i biciklom ili električnim vozilom (provjerite aktualnu ponudu).</li>'
                . '</ul>'
                . '<h2>Česta pitanja</h2>'
                . '<h3>Može li se na Brijune vlastitim brodom?</h3><p>Posjet je reguliran pravilima Nacionalnog parka; najjednostavnije je doći brodom NP-a iz Fažane. Za privatna plovila provjerite uvjete na službenim stranicama parka.</p>'
                . '<h3>Isplati li se Brijuni s djecom?</h3><p>Da – vožnja brodom, vlakić, safari park i tragovi dinosaura djeci su obično vrhunac izleta.</p>',
                '<p><strong>Der Nationalpark Brijuni ist eine Inselgruppe aus 14 Inseln vor der Westküste Istriens, gegenüber dem Ort Fažana bei Pula.</strong> Mit dem Boot des Nationalparks erreichen Sie von Fažana in etwa 15 Minuten Veliki Brijun, die größte Insel; dort geht es mit der Touristenbahn und Führung weiter. Von der Villa Dina nach Fažana fahren Sie etwa 55 Minuten.</p>'
                . '<h2>Sehenswertes auf Veliki Brijun</h2>'
                . '<ul>'
                . '<li><strong>Safaripark</strong> – exotische Tiere, teils Geschenke von Staatsgästen aus der Zeit, als die Insel Titos Sommerresidenz war.</li>'
                . '<li><strong>Römische Villa in der Bucht Verige</strong> – Reste eines prächtigen Landsitzes aus dem 1. Jahrhundert.</li>'
                . '<li><strong>Byzantinisches Castrum</strong> – Reste einer befestigten Siedlung aus der Spätantike.</li>'
                . '<li><strong>Dinosaurierspuren</strong> – Abdrücke im Uferfels.</li>'
                . '<li><strong>Uralter Olivenbaum</strong> – rund 1600 Jahre alt und trägt noch heute Früchte.</li>'
                . '<li><strong>Ausstellung „Tito auf Brijuni“</strong> – Fotos und Geschichten von Staatsbesuchen.</li>'
                . '</ul>'
                . '<h2>Tickets und Organisation</h2>'
                . '<ul>'
                . '<li>Tickets gibt es online im offiziellen Shop des Nationalparks (shop.np-brijuni.hr) oder im Büro des Nationalparks in Fažana.</li>'
                . '<li>Der Standardbesuch umfasst Bootsfahrt und geführte Rundfahrt auf Veliki Brijun; rechnen Sie mit einem halben Tag.</li>'
                . '<li>Im Juli und August vorab reservieren – die Plätze auf den Booten sind begrenzt.</li>'
                . '<li>Wasser, Hut und bequeme Schuhe mitnehmen; Teile der Insel lassen sich auch per Fahrrad oder Elektrofahrzeug erkunden (aktuelles Angebot prüfen).</li>'
                . '</ul>'
                . '<h2>Häufige Fragen</h2>'
                . '<h3>Kann man mit dem eigenen Boot nach Brijuni?</h3><p>Der Besuch ist durch die Regeln des Nationalparks geregelt; am einfachsten ist das Nationalpark-Boot ab Fažana. Bedingungen für private Boote finden Sie auf der offiziellen Website des Parks.</p>'
                . '<h3>Lohnt sich Brijuni mit Kindern?</h3><p>Ja – Bootsfahrt, Bähnchen, Safaripark und Dinosaurierspuren sind für Kinder meist der Höhepunkt.</p>',
                '<p><strong>Brijuni National Park is an archipelago of 14 islands off the west coast of Istria, opposite the village of Fažana near Pula.</strong> The National Park boat from Fažana takes about 15 minutes to Veliki Brijun, the largest island, where the visit continues by tourist train with a guide. From Villa Dina the drive to Fažana takes about 55 minutes.</p>'
                . '<h2>What to see on Veliki Brijun</h2>'
                . '<ul>'
                . '<li><strong>Safari park</strong> – exotic animals, some of them gifts from world leaders from the time when the island was Tito\'s summer residence.</li>'
                . '<li><strong>Roman villa at Verige Bay</strong> – remains of a lavish 1st-century country estate.</li>'
                . '<li><strong>Byzantine castrum</strong> – remains of a fortified late-antique settlement.</li>'
                . '<li><strong>Dinosaur footprints</strong> – preserved in the rocks along the shore.</li>'
                . '<li><strong>Ancient olive tree</strong> – around 1,600 years old and still bearing fruit.</li>'
                . '<li><strong>"Tito on Brijuni" exhibition</strong> – photos and stories of state visits.</li>'
                . '</ul>'
                . '<h2>Tickets and planning</h2>'
                . '<ul>'
                . '<li>Buy tickets online in the official Brijuni National Park shop (shop.np-brijuni.hr) or at the National Park office in Fažana.</li>'
                . '<li>The standard visit includes the boat transfer and a guided tour of Veliki Brijun; allow about half a day.</li>'
                . '<li>Book ahead in July and August – boat capacity is limited.</li>'
                . '<li>Bring water, a hat and comfortable shoes; parts of the island can also be explored by bike or electric vehicle (check the current offer).</li>'
                . '</ul>'
                . '<h2>FAQ</h2>'
                . '<h3>Can I visit Brijuni on my own boat?</h3><p>Visits are regulated by the National Park; the easiest way is the park boat from Fažana. Check the conditions for private boats on the park\'s official website.</p>'
                . '<h3>Is Brijuni worth it with children?</h3><p>Yes – the boat ride, little train, safari park and dinosaur footprints are usually the highlight of the trip for kids.</p>',
            ),
        ];
    }

    private static function rovinj(): array
    {
        return [
            'key' => 'rovinj',
            'category' => self::t('Gradovi', 'Städte', 'Towns'),
            'Title' => self::t(
                'Rovinj: stari grad, sv. Eufemija i Zlatni rt',
                'Rovinj: Altstadt, St. Euphemia und Goldenes Kap',
                'Rovinj: old town, St Euphemia and the Golden Cape',
            ),
            'URLSegment' => self::t('rovinj', 'rovinj', 'rovinj'),
            'MetaTitle' => self::t(
                'Rovinj – što vidjeti, plaže i savjeti za izlet',
                'Rovinj – Sehenswürdigkeiten, Strände und Tipps',
                'Rovinj – Things to See, Beaches and Day-Trip Tips',
            ),
            'MetaDescription' => self::t(
                'Rovinj za jedan dan: stari grad na poluotoku, crkva sv. Eufemije, ulica Grisia i plaže u park-šumi Zlatni rt. Savjeti za parking i najbolje vrijeme.',
                'Rovinj an einem Tag: Altstadt auf der Halbinsel, Kirche St. Euphemia, Grisia-Gasse und Strände im Waldpark Goldenes Kap. Tipps zu Parken und Zeit.',
                'Rovinj in a day: the old town on its peninsula, St Euphemia\'s Church, Grisia street and the beaches of Golden Cape forest park. Parking and timing tips.',
            ),
            'Summary' => self::t(
                '<p>Najslikovitiji grad zapadne obale: uličice starog grada, zvonik sv. Eufemije i kupanje na Zlatnom rtu.</p>',
                '<p>Die malerischste Stadt der Westküste: Altstadtgassen, Glockenturm von St. Euphemia und Baden am Goldenen Kap.</p>',
                '<p>The west coast\'s most picturesque town: old-town lanes, St Euphemia\'s bell tower and a swim at the Golden Cape.</p>',
            ),
            'Content' => self::t(
                '<p><strong>Rovinj je gradić na zapadnoj obali Istre čiji se stari grad na poluotoku penje do barokne crkve sv. Eufemije, sa zvonikom visokim oko 60 metara.</strong> Južno od grada prostire se park-šuma Zlatni rt s borovima, šetnicama i šljunčanim plažama. Od Ville Dina do Rovinja vozite otprilike sat vremena.</p>'
                . '<h2>Što vidjeti u Rovinju</h2>'
                . '<ul>'
                . '<li><strong>Crkva sv. Eufemije</strong> – dominira gradom; na vrhu zvonika kip svetice okreće se prema vjetru. Uz zvonik se pruža pogled na otoke.</li>'
                . '<li><strong>Ulica Grisia</strong> – strma kamena ulica galerija i ateljea koja vodi od rive do crkve.</li>'
                . '<li><strong>Balbijev luk</strong> – barokna gradska vrata na ulazu u stari grad.</li>'
                . '<li><strong>Riva i ribarska luka</strong> – najljepša u predvečerje, kada sunce zalazi iza otoka.</li>'
                . '<li><strong>Park-šuma Zlatni rt</strong> – zaštićeni park s uvalama, biciklističkim i pješačkim stazama.</li>'
                . '</ul>'
                . '<h2>Praktični savjeti</h2>'
                . '<ul>'
                . '<li>Stari grad je zatvoren za promet – parkirajte na velikim parkiralištima uz rub centra i prošećite.</li>'
                . '<li>Ljeti dođite ujutro ili kasno poslijepodne; podne je najtoplije i najgužvovitije.</li>'
                . '<li>Kupanje: plaže Zlatnog rta (npr. uvala Lone) su šljunčane i stjenovite, s hladom borova.</li>'
                . '<li>Kombinacija: Rovinj se lako spaja s Brijunima (Fažana) ili s Porečom i unutrašnjošću.</li>'
                . '</ul>'
                . '<h2>Česta pitanja</h2>'
                . '<h3>Ima li Rovinj plaže?</h3><p>Da – najpoznatije su u park-šumi Zlatni rt južno od grada, uglavnom šljunak i stijene.</p>'
                . '<h3>Koliko vremena treba za Rovinj?</h3><p>Za stari grad 2–3 sata; s kupanjem i večerom na rivi cijeli dan.</p>',
                '<p><strong>Rovinj ist ein Städtchen an der Westküste Istriens, dessen Altstadt auf einer Halbinsel zur barocken Kirche St. Euphemia hinaufsteigt – mit einem rund 60 Meter hohen Glockenturm.</strong> Südlich der Stadt liegt der Waldpark Goldenes Kap (Zlatni rt) mit Pinien, Spazierwegen und Kiesstränden. Von der Villa Dina fahren Sie etwa eine Stunde nach Rovinj.</p>'
                . '<h2>Sehenswertes in Rovinj</h2>'
                . '<ul>'
                . '<li><strong>Kirche St. Euphemia</strong> – prägt das Stadtbild; die Statue der Heiligen auf der Turmspitze dreht sich mit dem Wind. Vom Kirchplatz blicken Sie auf die Inseln.</li>'
                . '<li><strong>Grisia</strong> – steile Steingasse mit Galerien und Ateliers zwischen Hafen und Kirche.</li>'
                . '<li><strong>Balbi-Bogen</strong> – barockes Stadttor am Eingang zur Altstadt.</li>'
                . '<li><strong>Uferpromenade und Fischerhafen</strong> – am schönsten am Abend, wenn die Sonne hinter den Inseln versinkt.</li>'
                . '<li><strong>Waldpark Goldenes Kap</strong> – geschützter Park mit Buchten, Rad- und Wanderwegen.</li>'
                . '</ul>'
                . '<h2>Praktische Tipps</h2>'
                . '<ul>'
                . '<li>Die Altstadt ist autofrei – parken Sie auf den großen Parkplätzen am Rand und gehen Sie zu Fuß.</li>'
                . '<li>Im Sommer morgens oder am späten Nachmittag kommen; mittags ist es am heißesten und vollsten.</li>'
                . '<li>Baden: Die Strände am Goldenen Kap (z. B. Bucht Lone) sind Kies- und Felsstrände mit Pinienschatten.</li>'
                . '<li>Kombination: Rovinj lässt sich gut mit Brijuni (Fažana) oder mit Poreč und dem Landesinneren verbinden.</li>'
                . '</ul>'
                . '<h2>Häufige Fragen</h2>'
                . '<h3>Hat Rovinj Strände?</h3><p>Ja – die bekanntesten liegen im Waldpark Goldenes Kap südlich der Stadt, meist Kies und Fels.</p>'
                . '<h3>Wie viel Zeit braucht man für Rovinj?</h3><p>Für die Altstadt 2–3 Stunden; mit Baden und Abendessen am Hafen einen ganzen Tag.</p>',
                '<p><strong>Rovinj is a town on Istria\'s west coast whose old town climbs a peninsula to the Baroque Church of St Euphemia, with a bell tower about 60 metres high.</strong> South of town lies the Golden Cape (Zlatni rt) forest park with pine woods, footpaths and pebble beaches. From Villa Dina the drive to Rovinj takes about an hour.</p>'
                . '<h2>What to see in Rovinj</h2>'
                . '<ul>'
                . '<li><strong>St Euphemia\'s Church</strong> – dominates the skyline; the saint\'s statue on top of the tower turns with the wind. The churchyard looks out over the islands.</li>'
                . '<li><strong>Grisia street</strong> – a steep stone lane of galleries and studios between the harbour and the church.</li>'
                . '<li><strong>Balbi Arch</strong> – a Baroque town gate at the entrance to the old town.</li>'
                . '<li><strong>Waterfront and fishing harbour</strong> – loveliest in the evening as the sun sets behind the islands.</li>'
                . '<li><strong>Golden Cape forest park</strong> – a protected park with coves, cycling and walking trails.</li>'
                . '</ul>'
                . '<h2>Practical tips</h2>'
                . '<ul>'
                . '<li>The old town is car-free – use the large car parks at the edge of the centre and walk in.</li>'
                . '<li>In summer come in the morning or late afternoon; midday is the hottest and busiest.</li>'
                . '<li>Swimming: the Golden Cape beaches (e.g. Lone Bay) are pebble and rock, shaded by pines.</li>'
                . '<li>Combine Rovinj with Brijuni (Fažana) or with Poreč and the hill towns.</li>'
                . '</ul>'
                . '<h2>FAQ</h2>'
                . '<h3>Does Rovinj have beaches?</h3><p>Yes – the best known are in the Golden Cape forest park south of town, mostly pebble and rock.</p>'
                . '<h3>How much time do I need for Rovinj?</h3><p>2–3 hours for the old town; a full day with swimming and dinner on the waterfront.</p>',
            ),
        ];
    }

    private static function porec(): array
    {
        return [
            'key' => 'porec',
            'category' => self::t('Gradovi', 'Städte', 'Towns'),
            'Title' => self::t(
                'Poreč i Eufrazijeva bazilika (UNESCO)',
                'Poreč und die Euphrasius-Basilika (UNESCO)',
                'Poreč and the Euphrasian Basilica (UNESCO)',
            ),
            'URLSegment' => self::t('porec-eufrazijeva-bazilika', 'porec-euphrasius-basilika', 'porec-euphrasian-basilica'),
            'MetaTitle' => self::t(
                'Poreč – Eufrazijeva bazilika i stari grad',
                'Poreč – Euphrasius-Basilika und Altstadt',
                'Poreč – Euphrasian Basilica and Old Town Guide',
            ),
            'MetaDescription' => self::t(
                'Poreč: Eufrazijeva bazilika iz 6. stoljeća s bizantskim mozaicima (UNESCO od 1997.), Dekumanus, trg Marafor i šetnica. Savjeti za izlet iz Ville Dina.',
                'Poreč: Euphrasius-Basilika aus dem 6. Jahrhundert mit byzantinischen Mosaiken (UNESCO seit 1997), Decumanus, Marafor-Platz. Ausflugstipps ab Villa Dina.',
                'Poreč: the 6th-century Euphrasian Basilica with Byzantine mosaics (UNESCO since 1997), the Decumanus and Marafor square. Day-trip tips from Villa Dina.',
            ),
            'Summary' => self::t(
                '<p>Bizantski mozaici pod zaštitom UNESCO-a i rimski raspored ulica na zapadnoj obali.</p>',
                '<p>Byzantinische Mosaiken unter UNESCO-Schutz und römisches Straßennetz an der Westküste.</p>',
                '<p>UNESCO-listed Byzantine mosaics and a Roman street plan on the west coast.</p>',
            ),
            'Content' => self::t(
                '<p><strong>Poreč je obalni grad na zapadu Istre, poznat po Eufrazijevoj bazilici – ranokršćanskom kompleksu iz 6. stoljeća s bizantskim mozaicima, upisanom na UNESCO-ov popis svjetske baštine 1997. godine.</strong> Stari grad na poluotoku zadržao je rimski raspored ulica. Od Ville Dina vozite otprilike 1 sat i 10 minuta, pa je Poreč dobro spojiti s Motovunom ili Grožnjanom.</p>'
                . '<h2>Što vidjeti u Poreču</h2>'
                . '<ul>'
                . '<li><strong>Eufrazijeva bazilika</strong> – zlatni mozaici u apsidi, atrij, krstionica i biskupska palača; sa zvonika se vidi cijeli grad.</li>'
                . '<li><strong>Dekumanus</strong> – glavna ulica još od rimskog doba, danas puna trgovina i kafića.</li>'
                . '<li><strong>Trg Marafor</strong> – ostaci rimskih hramova na nekadašnjem forumu.</li>'
                . '<li><strong>Gradske kule i šetnica</strong> – srednjovjekovne kule i obalna šetnica oko poluotoka.</li>'
                . '</ul>'
                . '<h2>Praktični savjeti</h2>'
                . '<ul>'
                . '<li>Baziliku posjetite ujutro – mozaici su najljepši na jutarnjem svjetlu, a gužve manje.</li>'
                . '<li>Za posjet crkvi prikladna je odjeća koja pokriva ramena.</li>'
                . '<li>Parkiralište ostavite izvan starog grada; centar je pješačka zona.</li>'
                . '<li>Na povratku stanite u Motovunu ili Grožnjanu na zalazak sunca.</li>'
                . '</ul>'
                . '<h2>Česta pitanja</h2>'
                . '<h3>Zašto je Eufrazijeva bazilika pod zaštitom UNESCO-a?</h3><p>Zbog iznimno očuvanog ranokršćanskog kompleksa i bizantskih mozaika iz 6. stoljeća – jednog od najvažnijih primjera tog razdoblja na Mediteranu.</p>'
                . '<h3>Koliko je Poreč udaljen od Ville Dina?</h3><p>Otprilike 1 sat i 10 minuta vožnje automobilom.</p>',
                '<p><strong>Poreč ist eine Küstenstadt im Westen Istriens, berühmt für die Euphrasius-Basilika – einen frühchristlichen Komplex aus dem 6. Jahrhundert mit byzantinischen Mosaiken, seit 1997 UNESCO-Weltkulturerbe.</strong> Die Altstadt auf der Halbinsel hat ihr römisches Straßennetz bewahrt. Von der Villa Dina fahren Sie etwa 1 Stunde 10 Minuten; Poreč lässt sich gut mit Motovun oder Grožnjan verbinden.</p>'
                . '<h2>Sehenswertes in Poreč</h2>'
                . '<ul>'
                . '<li><strong>Euphrasius-Basilika</strong> – goldene Mosaiken in der Apsis, Atrium, Baptisterium und Bischofspalast; vom Glockenturm überblicken Sie die ganze Stadt.</li>'
                . '<li><strong>Decumanus</strong> – Hauptstraße seit römischer Zeit, heute voller Geschäfte und Cafés.</li>'
                . '<li><strong>Marafor-Platz</strong> – Reste römischer Tempel auf dem einstigen Forum.</li>'
                . '<li><strong>Stadttürme und Promenade</strong> – mittelalterliche Türme und Uferweg rund um die Halbinsel.</li>'
                . '</ul>'
                . '<h2>Praktische Tipps</h2>'
                . '<ul>'
                . '<li>Die Basilika am Morgen besuchen – die Mosaiken leuchten im Morgenlicht, und es ist ruhiger.</li>'
                . '<li>Für den Kirchenbesuch schulterbedeckende Kleidung tragen.</li>'
                . '<li>Außerhalb der Altstadt parken; das Zentrum ist Fußgängerzone.</li>'
                . '<li>Auf dem Rückweg zum Sonnenuntergang in Motovun oder Grožnjan halten.</li>'
                . '</ul>'
                . '<h2>Häufige Fragen</h2>'
                . '<h3>Warum steht die Euphrasius-Basilika unter UNESCO-Schutz?</h3><p>Wegen des außergewöhnlich gut erhaltenen frühchristlichen Komplexes und der byzantinischen Mosaiken aus dem 6. Jahrhundert – eines der bedeutendsten Zeugnisse dieser Epoche im Mittelmeerraum.</p>'
                . '<h3>Wie weit ist Poreč von der Villa Dina entfernt?</h3><p>Etwa 1 Stunde 10 Minuten mit dem Auto.</p>',
                '<p><strong>Poreč is a coastal town in western Istria, famous for the Euphrasian Basilica – a 6th-century early Christian complex with Byzantine mosaics, a UNESCO World Heritage Site since 1997.</strong> The old town on its peninsula keeps the Roman street plan. From Villa Dina it is about 1 hour 10 minutes by car, so Poreč combines well with Motovun or Grožnjan.</p>'
                . '<h2>What to see in Poreč</h2>'
                . '<ul>'
                . '<li><strong>Euphrasian Basilica</strong> – golden apse mosaics, atrium, baptistery and bishop\'s palace; the bell tower gives a view over the whole town.</li>'
                . '<li><strong>The Decumanus</strong> – the main street since Roman times, now lined with shops and cafés.</li>'
                . '<li><strong>Marafor square</strong> – remains of Roman temples on the former forum.</li>'
                . '<li><strong>Town towers and promenade</strong> – medieval towers and a seafront path around the peninsula.</li>'
                . '</ul>'
                . '<h2>Practical tips</h2>'
                . '<ul>'
                . '<li>Visit the basilica in the morning – the mosaics glow in the morning light and it is quieter.</li>'
                . '<li>Wear clothing that covers the shoulders inside the church.</li>'
                . '<li>Park outside the old town; the centre is pedestrianised.</li>'
                . '<li>On the way back stop in Motovun or Grožnjan for sunset.</li>'
                . '</ul>'
                . '<h2>FAQ</h2>'
                . '<h3>Why is the Euphrasian Basilica a UNESCO site?</h3><p>For its exceptionally well-preserved early Christian complex and 6th-century Byzantine mosaics – one of the most important examples of the period in the Mediterranean.</p>'
                . '<h3>How far is Poreč from Villa Dina?</h3><p>About 1 hour 10 minutes by car.</p>',
            ),
        ];
    }

    private static function hilltowns(): array
    {
        return [
            'key' => 'hilltowns',
            'image' => 'olives',
            'category' => self::t('Gradovi', 'Städte', 'Towns'),
            'Title' => self::t(
                'Motovun, Grožnjan i Hum: istarski gradići na brežuljcima',
                'Motovun, Grožnjan und Hum: Istriens Hügelstädtchen',
                'Motovun, Grožnjan and Hum: Istria\'s hilltop towns',
            ),
            'URLSegment' => self::t('motovun-groznjan-hum', 'motovun-groznjan-hum', 'motovun-groznjan-hum'),
            'MetaTitle' => self::t(
                'Motovun, Grožnjan i Hum – izlet u unutrašnjost Istre',
                'Motovun, Grožnjan, Hum – Ausflug ins Landesinnere',
                'Motovun, Grožnjan & Hum – Istria Hilltop Towns Trip',
            ),
            'MetaDescription' => self::t(
                'Dnevni izlet u unutrašnjost Istre: zidine Motovuna i tartufi doline Mirne, Grožnjan grad umjetnika i Hum, najmanji grad na svijetu. Ruta i savjeti.',
                'Tagesausflug ins Innere Istriens: Stadtmauer von Motovun und Trüffel im Mirna-Tal, Künstlerstadt Grožnjan und Hum, die kleinste Stadt der Welt.',
                'A day in inland Istria: Motovun\'s walls and Mirna valley truffles, Grožnjan the artists\' town and Hum, the "smallest town in the world". Route and tips.',
            ),
            'Summary' => self::t(
                '<p>Srednjovjekovni gradići, vidikovci, tartufi i vino – dnevni krug kroz zelenu Istru.</p>',
                '<p>Mittelalterliche Städtchen, Aussichten, Trüffel und Wein – eine Tagesrunde durch das grüne Istrien.</p>',
                '<p>Medieval towns, viewpoints, truffles and wine – a day loop through green Istria.</p>',
            ),
            'Content' => self::t(
                '<p><strong>Unutrašnjost Istre posuta je srednjovjekovnim gradićima na vrhovima brežuljaka. Najpoznatiji su Motovun iznad doline rijeke Mirne, Grožnjan – grad umjetnika – i Hum, koji se predstavlja kao najmanji grad na svijetu.</strong> Sva tri obiđete u jednom danu; od Ville Dina do Huma vozite oko 50 minuta, do Motovuna otprilike sat vremena.</p>'
                . '<h2>Motovun</h2>'
                . '<p>Gradić na brežuljku iznad doline Mirne s očuvanim zidinama po kojima se može prošetati. Ispod njega prostire se Motovunska šuma, jedno od najpoznatijih nalazišta tartufa u Hrvatskoj. Ljeti se ovdje održava Motovun Film Festival.</p>'
                . '<h2>Grožnjan</h2>'
                . '<p>Kamene uličice pune galerija i ateljea; ljeti gradić ispunjavaju glazbene radionice i koncerti mladih glazbenika. Idealno mjesto za kavu i zalazak sunca.</p>'
                . '<h2>Hum</h2>'
                . '<p>Tek nekoliko ulica unutar zidina – zato „najmanji grad na svijetu“. Do Huma vodi Aleja glagoljaša, spomen-staza od Roča s kamenim spomenicima posvećenima glagoljici. Probajte biska, tradicionalnu rakiju od imele.</p>'
                . '<h2>Prijedlog rute</h2>'
                . '<ol><li>Hum i Aleja glagoljaša (jutro)</li><li>Motovun – zidine, ručak s tartufima</li><li>Grožnjan – galerije i zalazak sunca</li></ol>'
                . '<h2>Praktični savjeti</h2>'
                . '<ul>'
                . '<li>U Motovun se ulazi pješice – parkirajte ispod gradića i prošećite uzbrdo.</li>'
                . '<li>Jesen je sezona bijelih tartufa; degustacije i izleti u potragu za tartufima nude se tijekom cijele godine.</li>'
                . '<li>Usput posjetite neku od vinarija – istarska malvazija i teran ovdje su kod kuće.</li>'
                . '</ul>'
                . '<h2>Česta pitanja</h2>'
                . '<h3>Koji je gradić najljepši za zalazak sunca?</h3><p>Grožnjan i Motovun – oba imaju vidikovce prema zapadu i dolini Mirne.</p>'
                . '<h3>Je li Hum zaista najmanji grad na svijetu?</h3><p>Hum se tako predstavlja: ima status grada, a unutar zidina živi tek nekolicina stanovnika.</p>',
                '<p><strong>Das Landesinnere Istriens ist übersät mit mittelalterlichen Städtchen auf Hügelkuppen. Die bekanntesten sind Motovun über dem Tal der Mirna, Grožnjan – die Stadt der Künstler – und Hum, das sich als kleinste Stadt der Welt bezeichnet.</strong> Alle drei schaffen Sie an einem Tag; von der Villa Dina nach Hum sind es rund 50 Minuten, nach Motovun etwa eine Stunde.</p>'
                . '<h2>Motovun</h2>'
                . '<p>Städtchen auf einem Hügel über dem Mirna-Tal mit erhaltener Stadtmauer, auf der man spazieren kann. Darunter liegt der Wald von Motovun, eines der bekanntesten Trüffelgebiete Kroatiens. Im Sommer findet hier das Motovun Film Festival statt.</p>'
                . '<h2>Grožnjan</h2>'
                . '<p>Steingassen voller Galerien und Ateliers; im Sommer füllen Musikkurse und Konzerte junger Musiker das Städtchen. Perfekt für einen Kaffee und den Sonnenuntergang.</p>'
                . '<h2>Hum</h2>'
                . '<p>Nur wenige Gassen innerhalb der Mauern – daher „die kleinste Stadt der Welt“. Nach Hum führt die Allee der Glagoliter, ein Gedenkweg ab Roč mit Steindenkmälern zur glagolitischen Schrift. Probieren Sie Biska, den traditionellen Mistelschnaps.</p>'
                . '<h2>Routenvorschlag</h2>'
                . '<ol><li>Hum und Allee der Glagoliter (Vormittag)</li><li>Motovun – Stadtmauer, Mittagessen mit Trüffeln</li><li>Grožnjan – Galerien und Sonnenuntergang</li></ol>'
                . '<h2>Praktische Tipps</h2>'
                . '<ul>'
                . '<li>Motovun erkunden Sie zu Fuß – unterhalb parken und hinaufspazieren.</li>'
                . '<li>Der Herbst ist Saison der weißen Trüffel; Verkostungen und Trüffelsuchen werden das ganze Jahr angeboten.</li>'
                . '<li>Unterwegs ein Weingut besuchen – istrische Malvazija und Teran sind hier zu Hause.</li>'
                . '</ul>'
                . '<h2>Häufige Fragen</h2>'
                . '<h3>Welches Städtchen ist am schönsten zum Sonnenuntergang?</h3><p>Grožnjan und Motovun – beide mit Aussicht nach Westen und ins Mirna-Tal.</p>'
                . '<h3>Ist Hum wirklich die kleinste Stadt der Welt?</h3><p>So präsentiert sich Hum: Es hat Stadtstatus, innerhalb der Mauern leben aber nur eine Handvoll Menschen.</p>',
                '<p><strong>Inland Istria is dotted with medieval towns perched on hilltops. The best known are Motovun above the Mirna valley, Grožnjan – the town of artists – and Hum, which calls itself the smallest town in the world.</strong> You can see all three in a day; from Villa Dina it is about 50 minutes to Hum and around an hour to Motovun.</p>'
                . '<h2>Motovun</h2>'
                . '<p>A hilltop town above the Mirna valley with intact walls you can walk along. Below it lies Motovun forest, one of Croatia\'s best-known truffle areas. In summer the town hosts the Motovun Film Festival.</p>'
                . '<h2>Grožnjan</h2>'
                . '<p>Stone lanes full of galleries and studios; in summer music workshops and concerts by young musicians fill the town. Perfect for a coffee and the sunset.</p>'
                . '<h2>Hum</h2>'
                . '<p>Just a few lanes inside its walls – hence "the smallest town in the world". The Glagolitic Alley, a memorial path from Roč with stone monuments to the Glagolitic script, leads to Hum. Try biska, the traditional mistletoe brandy.</p>'
                . '<h2>Suggested route</h2>'
                . '<ol><li>Hum and the Glagolitic Alley (morning)</li><li>Motovun – the walls and a truffle lunch</li><li>Grožnjan – galleries and sunset</li></ol>'
                . '<h2>Practical tips</h2>'
                . '<ul>'
                . '<li>Motovun is explored on foot – park below the town and walk up.</li>'
                . '<li>Autumn is white truffle season; tastings and truffle hunts are offered all year.</li>'
                . '<li>Stop at a winery on the way – Istrian Malvazija and Teran are at home here.</li>'
                . '</ul>'
                . '<h2>FAQ</h2>'
                . '<h3>Which town is best for sunset?</h3><p>Grožnjan and Motovun – both have viewpoints facing west over the Mirna valley.</p>'
                . '<h3>Is Hum really the smallest town in the world?</h3><p>That is how Hum presents itself: it has town status, yet only a handful of people live inside its walls.</p>',
            ),
        ];
    }

    private static function ucka(): array
    {
        return [
            'key' => 'ucka',
            'category' => self::t('Priroda', 'Natur', 'Nature'),
            'Title' => self::t(
                'Park prirode Učka: Vojak, Vela draga i pogled na Kvarner',
                'Naturpark Učka: Vojak, Vela draga und Kvarner-Blick',
                'Učka Nature Park: Vojak, Vela Draga and Kvarner views',
            ),
            'URLSegment' => self::t('park-prirode-ucka', 'naturpark-ucka', 'ucka-nature-park'),
            'MetaTitle' => self::t(
                'Učka – vrh Vojak, Vela draga i izleti',
                'Učka – Gipfel Vojak, Vela draga und Wanderungen',
                'Učka Nature Park – Vojak Peak and Vela Draga',
            ),
            'MetaDescription' => self::t(
                'Park prirode Učka: vrh Vojak (1401 m) s kamenom kulom i pogledom na Kvarner, kanjon Vela draga i šetnje. Kako doći iz Ville Dina i što ponijeti.',
                'Naturpark Učka: Gipfel Vojak (1401 m) mit Steinturm und Blick über die Kvarner-Bucht, Canyon Vela draga, Wanderungen. Anfahrt ab Villa Dina und Tipps.',
                'Učka Nature Park: Vojak peak (1,401 m) with a stone tower and views over the Kvarner Gulf, Vela Draga canyon and walks. Getting there from Villa Dina.',
            ),
            'Summary' => self::t(
                '<p>Najviši vrh Istre na dohvat ruke: kula na Vojaku, kanjon Vela draga i svježina planine.</p>',
                '<p>Istriens höchster Gipfel ganz nah: Turm auf dem Vojak, Canyon Vela draga und Bergfrische.</p>',
                '<p>Istria\'s highest peak close by: the tower on Vojak, Vela Draga canyon and cool mountain air.</p>',
            ),
            'Content' => self::t(
                '<p><strong>Park prirode Učka obuhvaća planinski masiv koji odvaja Istru od Kvarnera. Najviši vrh, Vojak (1401 m), ima kamenu vidikovačku kulu s koje se pruža pogled na Kvarnerski zaljev i otoke, a za vedrih dana i na Alpe.</strong> Od Ville Dina do Učke vozite otprilike 45 minuta – savršen izlet za vruće ljetne dane.</p>'
                . '<h2>Što vidjeti</h2>'
                . '<ul>'
                . '<li><strong>Vojak</strong> – cesta vodi gotovo do vrha; posljednji dio prijeđite pješice do kamene kule.</li>'
                . '<li><strong>Poklon</strong> – planinski prijevoj s centrom za posjetitelje i polazištem za šetnje.</li>'
                . '<li><strong>Vela draga</strong> – kanjon s vapnenačkim stupovima, zaštićen kao spomenik prirode; poučna staza s vidikovcem.</li>'
                . '<li><strong>Planinske šetnje</strong> – označene staze kroz bukove šume i livade.</li>'
                . '</ul>'
                . '<h2>Praktični savjeti</h2>'
                . '<ul>'
                . '<li>Ponesite jaknu – na vrhu je osjetno hladnije i često vjetrovito.</li>'
                . '<li>Najbolja vidljivost je ujutro i za bure, kada je zrak najčišći.</li>'
                . '<li>Kombinacija: nakon Učke spustite se na kupanje u Mošćeničku Dragu ili Medveju na kvarnerskoj strani.</li>'
                . '</ul>'
                . '<h2>Česta pitanja</h2>'
                . '<h3>Može li se automobilom na Vojak?</h3><p>Da, uska cesta vodi gotovo do vrha; zadnjih nekoliko minuta prijeđe se pješice.</p>'
                . '<h3>Koliko je Učka udaljena od Ville Dina?</h3><p>Otprilike 45 minuta vožnje; točnu udaljenost vidite u okviru „Ukratko“.</p>',
                '<p><strong>Der Naturpark Učka umfasst das Bergmassiv, das Istrien von der Kvarner-Bucht trennt. Auf dem höchsten Gipfel, dem Vojak (1401 m), steht ein steinerner Aussichtsturm mit Blick über die Kvarner-Bucht und die Inseln, bei klarer Sicht bis zu den Alpen.</strong> Von der Villa Dina fahren Sie etwa 45 Minuten – ideal an heißen Sommertagen.</p>'
                . '<h2>Sehenswertes</h2>'
                . '<ul>'
                . '<li><strong>Vojak</strong> – die Straße führt fast bis zum Gipfel; das letzte Stück gehen Sie zu Fuß zum Steinturm.</li>'
                . '<li><strong>Poklon</strong> – Bergpass mit Besucherzentrum und Ausgangspunkt für Wanderungen.</li>'
                . '<li><strong>Vela draga</strong> – Canyon mit Kalksteinsäulen, als Naturdenkmal geschützt; Lehrpfad mit Aussichtspunkt.</li>'
                . '<li><strong>Bergwanderungen</strong> – markierte Wege durch Buchenwälder und Wiesen.</li>'
                . '</ul>'
                . '<h2>Praktische Tipps</h2>'
                . '<ul>'
                . '<li>Jacke mitnehmen – oben ist es deutlich kühler und oft windig.</li>'
                . '<li>Die beste Fernsicht gibt es morgens und bei Bora, wenn die Luft am klarsten ist.</li>'
                . '<li>Kombination: nach der Učka zum Baden nach Mošćenička Draga oder Medveja auf der Kvarner-Seite.</li>'
                . '</ul>'
                . '<h2>Häufige Fragen</h2>'
                . '<h3>Kann man mit dem Auto auf den Vojak fahren?</h3><p>Ja, eine schmale Straße führt fast bis zum Gipfel; die letzten Minuten gehen Sie zu Fuß.</p>'
                . '<h3>Wie weit ist die Učka von der Villa Dina entfernt?</h3><p>Etwa 45 Minuten Fahrt; die genaue Entfernung steht im Kasten „Auf einen Blick“.</p>',
                '<p><strong>Učka Nature Park covers the mountain range that separates Istria from the Kvarner Gulf. Its highest peak, Vojak (1,401 m), has a stone lookout tower with views over the Kvarner Gulf and its islands – and on clear days as far as the Alps.</strong> From Villa Dina the drive takes about 45 minutes – perfect for hot summer days.</p>'
                . '<h2>What to see</h2>'
                . '<ul>'
                . '<li><strong>Vojak</strong> – the road leads almost to the top; walk the last stretch to the stone tower.</li>'
                . '<li><strong>Poklon</strong> – a mountain pass with a visitor centre and trailheads.</li>'
                . '<li><strong>Vela Draga</strong> – a canyon with limestone pillars, protected as a natural monument; educational trail with viewpoint.</li>'
                . '<li><strong>Mountain walks</strong> – marked trails through beech forest and meadows.</li>'
                . '</ul>'
                . '<h2>Practical tips</h2>'
                . '<ul>'
                . '<li>Bring a jacket – it is noticeably cooler and often windy at the top.</li>'
                . '<li>Visibility is best in the morning and during the bora wind, when the air is clearest.</li>'
                . '<li>Combine it with a swim at Mošćenička Draga or Medveja on the Kvarner side.</li>'
                . '</ul>'
                . '<h2>FAQ</h2>'
                . '<h3>Can you drive up to Vojak?</h3><p>Yes, a narrow road leads almost to the summit; the last few minutes are on foot.</p>'
                . '<h3>How far is Učka from Villa Dina?</h3><p>About 45 minutes by car; the exact distance is shown in the "Quick facts" box.</p>',
            ),
        ];
    }

    private static function kamenjak(): array
    {
        return [
            'key' => 'kamenjak',
            'category' => self::t('Priroda', 'Natur', 'Nature'),
            'Title' => self::t(
                'Rt Kamenjak: divlja obala na jugu Istre',
                'Kap Kamenjak: wilde Küste im Süden Istriens',
                'Cape Kamenjak: the wild coast of southern Istria',
            ),
            'URLSegment' => self::t('rt-kamenjak', 'kap-kamenjak', 'cape-kamenjak'),
            'MetaTitle' => self::t(
                'Rt Kamenjak – uvale, Kolombarica i savjeti',
                'Kap Kamenjak – Buchten, Kolumbarica und Tipps',
                'Cape Kamenjak – Coves, Kolumbarica and Tips',
            ),
            'MetaDescription' => self::t(
                'Rt Kamenjak kod Premanture: zaštićeni krajolik s tridesetak km razvedene obale, uvalama, skokovima u more na Kolombarici i tragovima dinosaura. Savjeti.',
                'Kap Kamenjak bei Premantura: Schutzgebiet mit rund 30 km Küste, Buchten, Klippenspringen an der Kolumbarica und Dinosaurierspuren. Tipps für den Besuch.',
                'Cape Kamenjak near Premantura: a protected landscape with around 30 km of coastline, coves, cliff jumping at Kolumbarica and dinosaur tracks. Visitor tips.',
            ),
            'Summary' => self::t(
                '<p>Najjužniji rt Istre: kamene uvale, kristalno more i bez hotela – za ljubitelje prirode.</p>',
                '<p>Istriens Südspitze: Felsbuchten, kristallklares Wasser und keine Hotels – für Naturliebhaber.</p>',
                '<p>Istria\'s southern tip: rocky coves, crystal-clear water and no hotels – for nature lovers.</p>',
            ),
            'Content' => self::t(
                '<p><strong>Rt Kamenjak najjužniji je dio istarskog poluotoka, zaštićeni krajolik kod Premanture s tridesetak kilometara razvedene obale, uvalama, stijenama i kristalno čistim morem – bez hotela i velikih plaža.</strong> Od Ville Dina vozite otprilike 1 sat i 10 minuta; dan na Kamenjaku lako se spaja s Pulom.</p>'
                . '<h2>Što vidjeti i raditi</h2>'
                . '<ul>'
                . '<li><strong>Mala i Velika Kolombarica</strong> – stijene, morske špilje i duboko more; poznato mjesto za skokove u more (samo za iskusne).</li>'
                . '<li><strong>Skrivene uvale</strong> – desetci malih uvala sa šljunkom i kamenim pločama; svatko nađe svoj kutak.</li>'
                . '<li><strong>Tragovi dinosaura</strong> – otisci sačuvani u stijenama uz obalu.</li>'
                . '<li><strong>Bicikl i snorkeling</strong> – makadamske staze kroz makiju i bistro more puno života.</li>'
                . '</ul>'
                . '<h2>Praktični savjeti</h2>'
                . '<ul>'
                . '<li>U sezoni se za motorna vozila naplaćuje ulaz; pješaci i biciklisti ulaze besplatno ili uz nižu naknadu – provjerite aktualne cijene.</li>'
                . '<li>Ceste su makadamske – vozite polako, parkirajte samo na označenim mjestima.</li>'
                . '<li>Hlada gotovo nema: ponesite suncobran, dovoljno vode i cipele za vodu.</li>'
                . '<li>Dođite rano ujutro – u srpnju i kolovozu parkirališta uz popularne uvale brzo se pune.</li>'
                . '</ul>'
                . '<h2>Česta pitanja</h2>'
                . '<h3>Je li Kamenjak pogodan za djecu?</h3><p>Djelomično – uvale s plitkim kamenim pločama su u redu, ali stijene Kolombarice i duboko more nisu za male plivače.</p>'
                . '<h3>Ima li na Kamenjaku hrane i pića?</h3><p>Ponuda je skromna – nekoliko sezonskih barova; ponesite vlastitu hranu i vodu.</p>',
                '<p><strong>Kap Kamenjak ist die Südspitze der istrischen Halbinsel – ein Landschaftsschutzgebiet bei Premantura mit rund 30 Kilometern zerklüfteter Küste, Buchten, Felsen und kristallklarem Wasser, ganz ohne Hotels und große Strände.</strong> Von der Villa Dina fahren Sie etwa 1 Stunde 10 Minuten; ein Tag auf Kamenjak lässt sich gut mit Pula verbinden.</p>'
                . '<h2>Sehenswertes und Aktivitäten</h2>'
                . '<ul>'
                . '<li><strong>Mala und Velika Kolumbarica</strong> – Felsen, Meereshöhlen und tiefes Wasser; bekannt für Klippensprünge (nur für Geübte).</li>'
                . '<li><strong>Versteckte Buchten</strong> – Dutzende kleiner Buchten mit Kies und Felsplatten; jeder findet sein Plätzchen.</li>'
                . '<li><strong>Dinosaurierspuren</strong> – Abdrücke im Uferfels.</li>'
                . '<li><strong>Radfahren und Schnorcheln</strong> – Schotterwege durch die Macchia und klares, lebendiges Wasser.</li>'
                . '</ul>'
                . '<h2>Praktische Tipps</h2>'
                . '<ul>'
                . '<li>In der Saison wird für Kraftfahrzeuge Eintritt erhoben; Fußgänger und Radfahrer zahlen nichts oder weniger – aktuelle Preise prüfen.</li>'
                . '<li>Die Wege sind Schotterpisten – langsam fahren und nur auf markierten Flächen parken.</li>'
                . '<li>Schatten gibt es kaum: Sonnenschirm, genug Wasser und Badeschuhe mitnehmen.</li>'
                . '<li>Früh kommen – im Juli und August füllen sich die Parkplätze an beliebten Buchten schnell.</li>'
                . '</ul>'
                . '<h2>Häufige Fragen</h2>'
                . '<h3>Ist Kamenjak für Kinder geeignet?</h3><p>Teilweise – Buchten mit flachen Felsplatten sind gut, die Felsen der Kolumbarica und das tiefe Wasser aber nicht für kleine Schwimmer.</p>'
                . '<h3>Gibt es auf Kamenjak Essen und Getränke?</h3><p>Das Angebot ist bescheiden – einige Saisonbars; eigenes Essen und Wasser mitbringen.</p>',
                '<p><strong>Cape Kamenjak is the southern tip of the Istrian peninsula – a protected landscape near Premantura with around 30 kilometres of indented coastline, coves, rocks and crystal-clear water, with no hotels or big beaches.</strong> From Villa Dina it is about 1 hour 10 minutes by car; a day at Kamenjak combines easily with Pula.</p>'
                . '<h2>What to see and do</h2>'
                . '<ul>'
                . '<li><strong>Mala and Velika Kolumbarica</strong> – rocks, sea caves and deep water; a well-known cliff-jumping spot (experienced swimmers only).</li>'
                . '<li><strong>Hidden coves</strong> – dozens of small coves with pebbles and flat rocks; everyone finds a spot.</li>'
                . '<li><strong>Dinosaur tracks</strong> – footprints preserved in the coastal rocks.</li>'
                . '<li><strong>Cycling and snorkelling</strong> – gravel tracks through the maquis and clear water full of life.</li>'
                . '</ul>'
                . '<h2>Practical tips</h2>'
                . '<ul>'
                . '<li>In season there is an entry fee for motor vehicles; walkers and cyclists enter free or for less – check current prices.</li>'
                . '<li>Roads are gravel – drive slowly and park only in marked areas.</li>'
                . '<li>There is almost no shade: bring a parasol, plenty of water and water shoes.</li>'
                . '<li>Arrive early – in July and August the car parks by popular coves fill quickly.</li>'
                . '</ul>'
                . '<h2>FAQ</h2>'
                . '<h3>Is Kamenjak suitable for children?</h3><p>Partly – coves with shallow flat rocks are fine, but the Kolumbarica cliffs and deep water are not for young swimmers.</p>'
                . '<h3>Is there food and drink at Kamenjak?</h3><p>Only a few seasonal bars – bring your own food and water.</p>',
            ),
        ];
    }
}
