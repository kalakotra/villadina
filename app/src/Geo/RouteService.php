<?php

declare(strict_types=1);

namespace App\Geo;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\SiteConfig\SiteConfig;
use Throwable;

/**
 * Driving distance/time from the villa via OSRM (OpenStreetMap routing).
 *
 * Default endpoint is the public OSRM demo server (fair use: ~1 request/s, no heavy use).
 * For production volume point `osrm_url` to your own OSRM or a commercial instance.
 *
 *   App\Geo\RouteService:
 *     osrm_url: 'https://router.project-osrm.org'
 */
class RouteService
{
    use Configurable;
    use Injectable;

    private static string $osrm_url = 'https://router.project-osrm.org';
    private static float $timeout = 8.0;
    private static string $user_agent = 'VillaDina-Website/1.0 (+distance calculation)';

    /** Max. coordinates per table request (public server limit is ~100) */
    private static int $table_chunk = 80;

    private ?Client $client = null;

    /**
     * Villa coordinates from Settings → Villa.
     *
     * @return array{lat: float, lng: float}|null
     */
    public static function origin(): ?array
    {
        $config = SiteConfig::current_site_config();
        $lat = (float) $config->Latitude;
        $lng = (float) $config->Longitude;

        return $lat !== 0.0 && $lng !== 0.0 ? ['lat' => $lat, 'lng' => $lng] : null;
    }

    /**
     * Great-circle distance in km.
     */
    public static function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371.0088;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 2 * $r * asin(min(1.0, sqrt($a)));
    }

    /**
     * One route origin → destination.
     *
     * @return array{km: float, minutes: int}|null
     */
    public function route(float $fromLat, float $fromLng, float $toLat, float $toLng): ?array
    {
        $path = sprintf('/route/v1/driving/%F,%F;%F,%F', $fromLng, $fromLat, $toLng, $toLat);
        $data = $this->get($path, ['overview' => 'false', 'alternatives' => 'false', 'steps' => 'false']);
        $route = $data['routes'][0] ?? null;

        if (!is_array($route) || !isset($route['distance'], $route['duration'])) {
            return null;
        }

        return self::result((float) $route['distance'], (float) $route['duration']);
    }

    /**
     * Many destinations from one origin (OSRM table service, one request per chunk).
     *
     * @param array{lat: float, lng: float} $origin
     * @param array<array-key, array{lat: float, lng: float}> $destinations
     * @return array<array-key, array{km: float, minutes: int}|null> same keys as $destinations
     */
    public function table(array $origin, array $destinations): array
    {
        $results = array_fill_keys(array_keys($destinations), null);
        $chunkSize = max(1, (int) static::config()->get('table_chunk'));

        foreach (array_chunk($destinations, $chunkSize, true) as $chunk) {
            $coords = [sprintf('%F,%F', $origin['lng'], $origin['lat'])];
            foreach ($chunk as $point) {
                $coords[] = sprintf('%F,%F', $point['lng'], $point['lat']);
            }

            $data = $this->get('/table/v1/driving/' . implode(';', $coords), [
                'sources' => '0',
                'annotations' => 'distance,duration',
            ]);

            $distances = $data['distances'][0] ?? null;
            $durations = $data['durations'][0] ?? null;
            if (!is_array($distances) || !is_array($durations)) {
                continue;
            }

            $i = 1;
            foreach (array_keys($chunk) as $key) {
                $m = $distances[$i] ?? null;
                $s = $durations[$i] ?? null;
                $results[$key] = is_numeric($m) && is_numeric($s) ? self::result((float) $m, (float) $s) : null;
                $i++;
            }
        }

        return $results;
    }

    /**
     * @return array{km: float, minutes: int}
     */
    private static function result(float $meters, float $seconds): array
    {
        return [
            'km' => round($meters / 1000, 1),
            'minutes' => max(1, (int) round($seconds / 60)),
        ];
    }

    /**
     * @param array<string, string> $query
     * @return array<string, mixed>|null
     */
    private function get(string $path, array $query): ?array
    {
        try {
            $response = $this->client()->get(rtrim((string) static::config()->get('osrm_url'), '/') . $path, [
                'query' => $query,
            ]);
            $data = json_decode((string) $response->getBody(), true);

            if (!is_array($data) || ($data['code'] ?? '') !== 'Ok') {
                $this->log('OSRM response not OK: ' . ($data['code'] ?? 'invalid JSON') . ' ' . ($data['message'] ?? ''));

                return null;
            }

            return $data;
        } catch (Throwable $e) {
            $this->log('OSRM request failed: ' . $e->getMessage());

            return null;
        }
    }

    private function client(): Client
    {
        return $this->client ??= new Client([
            'timeout' => (float) static::config()->get('timeout'),
            'connect_timeout' => 4.0,
            'http_errors' => true,
            'headers' => [
                'User-Agent' => (string) static::config()->get('user_agent'),
                'Accept' => 'application/json',
            ],
        ]);
    }

    private function log(string $message): void
    {
        try {
            Injector::inst()->get(LoggerInterface::class)->warning('[RouteService] ' . $message);
        } catch (Throwable) {
            // logging must never break a save
        }
    }
}
