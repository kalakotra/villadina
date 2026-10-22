<?php

declare(strict_types=1);

namespace App\View;

use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\View\TemplateGlobalProvider;

/**
 * Inline SVG line icons (24×24, stroke = currentColor).
 *
 * Template:  $SvgIcon('wifi')   $SvgIcon('arrow-right', 'my-class')
 */
final class Icons implements TemplateGlobalProvider
{
    /** @var array<string, string> inner SVG markup */
    private const PATHS = [
        // UI
        'arrow-right' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'arrow-left' => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'close' => '<path d="M6 6l12 12M18 6 6 18"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h10"/>',
        'calendar' => '<rect x="3.5" y="5" width="17" height="15.5" rx="1.5"/><path d="M3.5 10h17M8 3v4M16 3v4"/>',
        'users' => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c.6-3.6 3.2-5.5 6.5-5.5s5.9 1.9 6.5 5.5"/><path d="M16 4.6a3.5 3.5 0 0 1 0 6.8M18 14.8c2 .6 3.2 2.3 3.5 5.2"/>',
        'pin' => '<path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
        'phone' => '<path d="M5 3.5h3.5l1.8 4.5-2.3 1.4a11 11 0 0 0 6.6 6.6l1.4-2.3 4.5 1.8V19a1.5 1.5 0 0 1-1.6 1.5C10.4 20 4 13.6 3.5 5.1A1.5 1.5 0 0 1 5 3.5z"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="1.5"/><path d="m3.5 6 8.5 7 8.5-7"/>',
        'whatsapp' => '<path d="M4 20l1.3-4A8.5 8.5 0 1 1 8.4 19z"/><path d="M9 8.5c0 3.6 2.9 6.5 6.5 6.5l1-1.6-2-1-1 .9a4.6 4.6 0 0 1-2.8-2.8l.9-1-1-2z"/>',
        'instagram' => '<rect x="3.5" y="3.5" width="17" height="17" rx="4.5"/><circle cx="12" cy="12" r="4"/><circle cx="17.3" cy="6.7" r=".6" fill="currentColor"/>',
        'facebook' => '<path d="M14 21v-7.5h2.6l.4-3H14V8.6c0-.9.3-1.6 1.6-1.6H17V4.3c-.3 0-1.2-.1-2.3-.1-2.3 0-3.7 1.4-3.7 3.9v2.4H8.5v3H11V21"/>',
        'star' => '<path fill="currentColor" stroke="none" d="m12 2.8 2.8 5.8 6.4.9-4.6 4.5 1.1 6.3L12 17.3l-5.7 3 1.1-6.3L2.8 9.5l6.4-.9z"/>',
        'check' => '<path d="m5 12.5 4.5 4.5L19 7.5"/>',
        // Amenities
        'wifi' => '<path d="M2.5 9a14 14 0 0 1 19 0M5.5 12.3a9.5 9.5 0 0 1 13 0M8.6 15.5a5 5 0 0 1 6.8 0"/><circle cx="12" cy="19" r="1" fill="currentColor"/>',
        'pool' => '<path d="M2.5 17c1.6 0 1.6 1.2 3.2 1.2S7.3 17 8.9 17s1.6 1.2 3.1 1.2S13.6 17 15.2 17s1.6 1.2 3.1 1.2 1.6-1.2 3.2-1.2"/><path d="M8 14V5.5a2 2 0 0 1 4 0M16 14V5.5a2 2 0 0 0-2-2M8 8.5h8M8 12h8"/>',
        'parking' => '<rect x="3.5" y="3.5" width="17" height="17" rx="2"/><path d="M9.5 17V7h3.3a3 3 0 0 1 0 6H9.5"/>',
        'ac' => '<path d="M12 2.5v19M4 7l16 10M20 7 4 17"/><path d="m9.5 4 2.5 2 2.5-2M9.5 20l2.5-2 2.5 2"/>',
        'bbq' => '<path d="M12 21c-3.6 0-6-2.4-6-5.6C6 12 9 10.5 9.5 7c1.8 1.2 2.6 2.8 2.6 4.2.8-.7 1.3-1.9 1.3-3.2C16.3 9.6 18 12.4 18 15.4 18 18.6 15.6 21 12 21z"/>',
        'sea' => '<path d="M2.5 9c1.6 0 1.6-1.2 3.2-1.2S7.3 9 8.9 9s1.6-1.2 3.1-1.2S13.6 9 15.2 9s1.6-1.2 3.1-1.2S19.9 9 21.5 9M2.5 14c1.6 0 1.6-1.2 3.2-1.2S7.3 14 8.9 14s1.6-1.2 3.1-1.2 1.6 1.2 3.2 1.2 1.6-1.2 3.1-1.2 1.6 1.2 3.2 1.2M2.5 19c1.6 0 1.6-1.2 3.2-1.2S7.3 19 8.9 19s1.6-1.2 3.1-1.2 1.6 1.2 3.2 1.2 1.6-1.2 3.1-1.2 1.6 1.2 3.2 1.2"/>',
        'kitchen' => '<path d="M7 3v7.5a2 2 0 0 0 2 2V21M5 3v5M9 3v5M17 21V3c-2.2 0-3.5 2.6-3.5 6.5V13H17"/>',
        'washer' => '<rect x="4" y="2.5" width="16" height="19" rx="2"/><circle cx="12" cy="13" r="4.5"/><path d="M7.5 6h.01M10.5 6h.01"/>',
        'pets' => '<circle cx="5.5" cy="10" r="1.8"/><circle cx="9.5" cy="5.8" r="1.8"/><circle cx="14.5" cy="5.8" r="1.8"/><circle cx="18.5" cy="10" r="1.8"/><path d="M12 11.5c-2.6 0-5.5 4-5.5 6.3 0 1.6 1.3 2.2 2.8 2.2 1.1 0 1.8-.6 2.7-.6s1.6.6 2.7.6c1.5 0 2.8-.6 2.8-2.2 0-2.3-2.9-6.3-5.5-6.3z"/>',
        'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2.5v2M12 19.5v2M4.6 4.6 6 6M18 18l1.4 1.4M2.5 12h2M19.5 12h2M4.6 19.4 6 18M18 6l1.4-1.4"/>',
        'bike' => '<circle cx="5.5" cy="16.5" r="3.5"/><circle cx="18.5" cy="16.5" r="3.5"/><path d="M5.5 16.5 9 9h6l3.5 7.5M12 16.5 9 9M14 5.5h2.5L18.5 16.5"/>',
        'tv' => '<rect x="2.5" y="5" width="19" height="13" rx="1.5"/><path d="M8 21h8M12 18v3"/>',
        'bed' => '<path d="M2.5 19v-9M2.5 15h19v4M21.5 15v-3a3 3 0 0 0-3-3h-7v6"/><circle cx="7" cy="11.5" r="2"/>',
        'bath' => '<path d="M3 12h18v2.5a5 5 0 0 1-5 5H8a5 5 0 0 1-5-5zM6 12V5.5a2 2 0 0 1 3.7-1.1M6.5 19.5 5.5 21M17.5 19.5l1 1.5"/>',
        'home' => '<path d="M3.5 10.5 12 3.5l8.5 7V20a.5.5 0 0 1-.5.5h-5v-6h-6v6H4a.5.5 0 0 1-.5-.5z"/>',
        'leaf' => '<path d="M5 19c0-8.5 5-13.5 15-14 .5 10-4.5 15-13 15M5 19c2.5-4 5.5-6.5 9-8"/>',
        'car' => '<path d="M5 16.5h14M3.5 16.5V12l2-5h13l2 5v4.5H18M6 16.5V18.5M18 16.5V18.5M3.5 12h17"/><circle cx="7.5" cy="14" r=".8" fill="currentColor"/><circle cx="16.5" cy="14" r=".8" fill="currentColor"/>',
        'plane' => '<path d="m2.5 13.5 7-1.5 4-7.5h2l-1.5 7 5-1 1.5-2.5h1.5l-1 4 1 4h-1.5L19 14.5l-5-1 1.5 7h-2l-4-7.5-7-1.5z"/>',
        'shop' => '<path d="M3.5 9 5 3.5h14L20.5 9M3.5 9h17M3.5 9c0 1.7 1.3 3 3 3s2.8-1.3 2.8-3c0 1.7 1.2 3 2.7 3s2.7-1.3 2.7-3c0 1.7 1.3 3 2.8 3s3-1.3 3-3M5 12v8.5h14V12M10 20.5v-5h4v5"/>',
        'fork' => '<path d="M7 3v18M4.5 3v5a2.5 2.5 0 0 0 5 0V3M16.5 21V3c2 1 3 3.5 3 6.5V13h-3"/>',
    ];

    /**
     * Keys selectable for amenities in the CMS.
     *
     * @return array<string, string>
     */
    public static function amenityOptions(): array
    {
        return [
            'wifi' => 'Wi-Fi',
            'pool' => 'Pool',
            'parking' => 'Parking',
            'ac' => 'Air conditioning',
            'bbq' => 'BBQ / Grill',
            'sea' => 'Sea / view',
            'kitchen' => 'Kitchen',
            'washer' => 'Washing machine',
            'pets' => 'Pets',
            'sun' => 'Terrace / sun',
            'bike' => 'Bikes',
            'tv' => 'TV',
            'bed' => 'Bedroom',
            'bath' => 'Bathroom',
            'home' => 'House',
            'leaf' => 'Garden / nature',
            'car' => 'Car',
            'plane' => 'Airport',
            'shop' => 'Shop',
            'fork' => 'Restaurant',
            'users' => 'Guests',
        ];
    }

    public static function svg(string $name = '', string $class = ''): DBHTMLText
    {
        $inner = self::PATHS[$name] ?? '';
        $html = $inner === ''
            ? ''
            : sprintf(
                '<svg class="vd-icon%s" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%s</svg>',
                $class !== '' ? ' ' . htmlspecialchars($class, ENT_QUOTES) : '',
                $inner,
            );

        return DBField::create_field('HTMLFragment', $html);
    }

    public static function get_template_global_variables(): array
    {
        return [
            // Not "Icon": core CMS templates use $Icon for record/menu icons
            'SvgIcon' => 'svg',
        ];
    }
}
