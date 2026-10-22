<?php

declare(strict_types=1);

namespace App\View;

use SilverStripe\Control\Director;
use SilverStripe\i18n\i18n;
use SilverStripe\View\TemplateGlobalProvider;

/**
 * Globals usable in any template scope — incl. Elemental blocks,
 * which are rendered with the element (not the page) as $Top.
 *
 *   $LocaleCode         "de" | "hr" | "en"
 *   $BookingEndpoint    "/booking/availability"
 */
final class ThemeGlobals implements TemplateGlobalProvider
{
    public static function localeCode(): string
    {
        return strtolower(substr(i18n::get_locale(), 0, 2));
    }

    public static function bookingEndpoint(): string
    {
        return Director::baseURL() . 'booking/availability';
    }

    public static function get_template_global_variables(): array
    {
        return [
            'LocaleCode' => 'localeCode',
            'BookingEndpoint' => 'bookingEndpoint',
        ];
    }
}
