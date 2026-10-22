<?php

declare(strict_types=1);

namespace App\Extensions;

use SilverStripe\Control\Director;
use SilverStripe\Core\Environment;
use SilverStripe\Core\Extension;
use SilverStripe\Core\Manifest\ModuleResourceLoader;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;

/**
 * Vite integration for templates.
 *
 * .env (dev only):  VITE_DEV_SERVER="http://localhost:5173"
 * Template:         $ViteTags('js/main.js')
 *
 * Production reads app/client/dist/manifest.json and emits:
 *  - <link rel="stylesheet"> for the entry CSS (incl. CSS of static imports)
 *  - <link rel="modulepreload"> for static JS imports
 *  - <script type="module"> for the entry
 */
class ViteExtension extends Extension
{
    private const DIST_DIR = 'app/client/dist';
    private const MANIFEST = self::DIST_DIR . '/manifest.json';

    /** @var array<string, array<string, mixed>>|null */
    private static ?array $manifestCache = null;

    public function IsViteDev(): bool
    {
        return Director::isDev() && (string) Environment::getEnv('VITE_DEV_SERVER') !== '';
    }

    public function ViteDevUrl(): string
    {
        return rtrim((string) Environment::getEnv('VITE_DEV_SERVER'), '/');
    }

    /**
     * URL of a single built file (e.g. an image imported by the bundle).
     */
    public function ViteAsset(string $entry): string
    {
        if ($this->IsViteDev()) {
            return $this->ViteDevUrl() . '/' . ltrim($entry, '/');
        }

        $chunk = $this->manifest()[$entry] ?? null;

        return $chunk ? $this->distUrl((string) $chunk['file']) : '';
    }

    public function ViteTags(string $entry = 'js/main.js'): DBHTMLText
    {
        $html = $this->IsViteDev() ? $this->devTags($entry) : $this->buildTags($entry);

        return DBField::create_field('HTMLFragment', $html);
    }

    private function devTags(string $entry): string
    {
        $base = $this->ViteDevUrl();

        return sprintf(
            '<script type="module" src="%1$s/@vite/client"></script>' . "\n"
            . '<script type="module" src="%1$s/%2$s"></script>',
            htmlspecialchars($base, ENT_QUOTES),
            htmlspecialchars(ltrim($entry, '/'), ENT_QUOTES),
        );
    }

    private function buildTags(string $entry): string
    {
        $manifest = $this->manifest();
        if (!isset($manifest[$entry])) {
            return Director::isDev()
                ? sprintf('<!-- Vite: entry "%s" missing in manifest, run npm run build -->', htmlspecialchars($entry))
                : '';
        }

        $css = [];
        $preload = [];
        $this->collect($entry, $manifest, $css, $preload, true);

        $out = [];
        foreach (array_unique($css) as $file) {
            $out[] = sprintf('<link rel="stylesheet" href="%s">', $this->distUrl($file));
        }
        foreach (array_unique($preload) as $file) {
            $out[] = sprintf('<link rel="modulepreload" href="%s">', $this->distUrl($file));
        }
        $out[] = sprintf('<script type="module" src="%s"></script>', $this->distUrl((string) $manifest[$entry]['file']));

        return implode("\n", $out);
    }

    /**
     * Walk static imports only — dynamic imports are loaded by Vite at runtime.
     *
     * @param array<string, array<string, mixed>> $manifest
     * @param list<string> $css
     * @param list<string> $preload
     */
    private function collect(string $key, array $manifest, array &$css, array &$preload, bool $isEntry = false): void
    {
        $chunk = $manifest[$key] ?? null;
        if ($chunk === null) {
            return;
        }

        foreach ($chunk['css'] ?? [] as $file) {
            $css[] = (string) $file;
        }

        if (!$isEntry) {
            $preload[] = (string) $chunk['file'];
        }

        foreach ($chunk['imports'] ?? [] as $import) {
            $this->collect((string) $import, $manifest, $css, $preload);
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function manifest(): array
    {
        if (self::$manifestCache !== null) {
            return self::$manifestCache;
        }

        $path = Director::baseFolder() . '/' . self::MANIFEST;
        if (!is_file($path)) {
            return self::$manifestCache = [];
        }

        try {
            $data = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $data = [];
        }

        return self::$manifestCache = is_array($data) ? $data : [];
    }

    private function distUrl(string $file): string
    {
        return htmlspecialchars(
            ModuleResourceLoader::resourceURL(self::DIST_DIR . '/' . ltrim($file, '/')),
            ENT_QUOTES,
        );
    }
}
