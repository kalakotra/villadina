<?php

declare(strict_types=1);

namespace App\Geo;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\NumericField;
use SilverStripe\i18n\i18n;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;

/**
 * Coordinates + driving distance from the villa (OSRM, straight-line fallback).
 *
 * Distances are recalculated on save when the coordinates change, and in bulk with
 *   vendor/bin/sake tasks:villa-distances
 *
 * @property float $Latitude
 * @property float $Longitude
 * @property float $DriveKm
 * @property int $DriveMinutes
 * @property float $StraightKm
 * @property string|null $RouteUpdated
 * @extends Extension<DataObject>
 */
class RouteFromVillaExtension extends Extension
{
    /** Set to false for bulk imports (the seed task calculates in one batch at the end) */
    public static bool $autoRoute = true;

    private static array $db = [
        'Latitude' => 'Decimal(9,6)',
        'Longitude' => 'Decimal(9,6)',
        'DriveKm' => 'Decimal(6,1)',
        'DriveMinutes' => 'Int',
        'StraightKm' => 'Decimal(6,1)',
        'RouteUpdated' => 'Datetime',
    ];

    public function updateCMSFields(FieldList $fields): void
    {
        $fields->removeByName(['Latitude', 'Longitude', 'DriveKm', 'DriveMinutes', 'StraightKm', 'RouteUpdated']);

        $info = $this->HasGeo()
            ? ($this->DriveLabel() ?: 'not calculated yet')
            : 'enter coordinates';

        $fields->addFieldsToTab('Root.Main', [
            HeaderField::create('GeoHeader', 'Location', 3),
            NumericField::create('Latitude', 'Latitude')->setScale(6)
                ->setDescription('Decimal degrees, e.g. 45.080000 (Google Maps: right-click the spot → copy coordinates)'),
            NumericField::create('Longitude', 'Longitude')->setScale(6),
            LiteralField::create(
                'GeoInfo',
                '<p class="form__field-description">Distance from the villa by car: <strong>'
                . htmlspecialchars($info, ENT_QUOTES) . '</strong>'
                . ($this->getOwner()->RouteUpdated ? ' (calculated ' . htmlspecialchars((string) $this->getOwner()->RouteUpdated, ENT_QUOTES) . ')' : '')
                . '. Recalculated automatically when the coordinates change.</p>',
            ),
        ]);
    }

    public function onBeforeWrite(): void
    {
        $owner = $this->getOwner();

        if (!self::$autoRoute || !$this->HasGeo()) {
            return;
        }

        $moved = $owner->isChanged('Latitude', DataObject::CHANGE_VALUE)
            || $owner->isChanged('Longitude', DataObject::CHANGE_VALUE);

        if ($moved || !$owner->RouteUpdated) {
            $this->calculateRoute(RouteService::singleton());
        }
    }

    /**
     * Updates the fields (does not write). Returns true when a driving route was found.
     */
    public function calculateRoute(RouteService $service, ?array $route = null, bool $lookup = true): bool
    {
        $owner = $this->getOwner();
        $origin = RouteService::origin();

        if (!$origin || !$this->HasGeo()) {
            return false;
        }

        $lat = (float) $owner->Latitude;
        $lng = (float) $owner->Longitude;
        $owner->StraightKm = round(RouteService::haversineKm($origin['lat'], $origin['lng'], $lat, $lng), 1);

        if ($route === null && $lookup) {
            $route = $service->route($origin['lat'], $origin['lng'], $lat, $lng);
        }

        if ($route === null) {
            return false;
        }

        $owner->DriveKm = $route['km'];
        $owner->DriveMinutes = $route['minutes'];
        $owner->RouteUpdated = DBDatetime::now()->getValue();

        return true;
    }

    public function HasGeo(): bool
    {
        $owner = $this->getOwner();

        return (float) $owner->Latitude !== 0.0 && (float) $owner->Longitude !== 0.0;
    }

    /**
     * "17 km · 22 min" (driving) or "≈ 11 km" (straight line) – identical in HR/DE/EN.
     */
    public function DriveLabel(): string
    {
        $owner = $this->getOwner();
        $km = (float) $owner->DriveKm;

        if ($km > 0 && (int) $owner->DriveMinutes > 0) {
            return sprintf('%s km · %s', self::km($km), self::minutes((int) $owner->DriveMinutes));
        }

        $straight = (float) $owner->StraightKm;

        return $straight > 0 ? '≈ ' . self::km($straight) . ' km' : '';
    }

    public function DriveKmLabel(): string
    {
        $km = (float) $this->getOwner()->DriveKm;

        return $km > 0 ? self::km($km) . ' km' : '';
    }

    public function DriveTimeLabel(): string
    {
        $min = (int) $this->getOwner()->DriveMinutes;

        return $min > 0 ? self::minutes($min) : '';
    }

    public function DirectionsFromVillaURL(): string
    {
        $owner = $this->getOwner();
        if (!$this->HasGeo()) {
            return '';
        }

        $params = [
            'api' => '1',
            'destination' => sprintf('%F,%F', (float) $owner->Latitude, (float) $owner->Longitude),
            'travelmode' => 'driving',
        ];
        if ($origin = RouteService::origin()) {
            $params['origin'] = sprintf('%F,%F', $origin['lat'], $origin['lng']);
        }

        return 'https://www.google.com/maps/dir/?' . http_build_query($params);
    }

    public function MapURL(): string
    {
        $owner = $this->getOwner();

        return $this->HasGeo()
            ? sprintf('https://www.google.com/maps/search/?api=1&query=%F,%F', (float) $owner->Latitude, (float) $owner->Longitude)
            : '';
    }

    /**
     * schema.org GeoCoordinates
     *
     * @return array<string, mixed>|null
     */
    public function GeoSchema(): ?array
    {
        $owner = $this->getOwner();

        return $this->HasGeo()
            ? ['@type' => 'GeoCoordinates', 'latitude' => (float) $owner->Latitude, 'longitude' => (float) $owner->Longitude]
            : null;
    }

    private static function km(float $km): string
    {
        if ($km >= 10) {
            return (string) (int) round($km);
        }

        $sep = str_starts_with(i18n::get_locale(), 'en') ? '.' : ',';

        return str_replace('.', $sep, (string) round($km, 1));
    }

    private static function minutes(int $minutes): string
    {
        if ($minutes < 60) {
            return $minutes . ' min';
        }

        return sprintf('%d h %02d min', intdiv($minutes, 60), $minutes % 60);
    }
}
