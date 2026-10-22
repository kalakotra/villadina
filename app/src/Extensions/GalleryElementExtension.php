<?php

declare(strict_types=1);

namespace App\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Model\List\SS_List;

/**
 * Theme helpers for the existing App\Elements\GalleryElement
 * (many_many Images → Image, extraField SortOrder).
 *
 * @property string|null $Eyebrow
 * @property string $DisplayMode
 */
class GalleryElementExtension extends Extension
{
    public const GRID_LIMIT = 9;

    private static array $db = [
        'Eyebrow' => 'Varchar(120)',
        'DisplayMode' => "Enum('grid,slider','grid')",
    ];

    public function updateCMSFields(FieldList $fields): void
    {
        $fields->addFieldsToTab('Root.Main', [
            TextField::create('Eyebrow', 'Eyebrow'),
            DropdownField::create('DisplayMode', 'Display', [
                'grid' => 'Mosaic grid (max. 9 visible, rest in lightbox)',
                'slider' => 'Slider',
            ]),
        ], 'Images');
    }

    public function SortedImages(): SS_List
    {
        return $this->owner->Images()->sort('SortOrder');
    }

    public function GridImages(): SS_List
    {
        return $this->SortedImages()->limit(self::GRID_LIMIT);
    }

    public function HiddenImages(): SS_List
    {
        return $this->SortedImages()->limit(PHP_INT_MAX, self::GRID_LIMIT);
    }

    public function RemainingCount(): int
    {
        return max(0, $this->owner->Images()->count() - self::GRID_LIMIT);
    }

    /**
     * Mosaic tile modifier for 1-based position: 9 tiles fill 4 × 4 cells
     * (2 big + 1 wide + 6 single).
     */
    public function TileClass(int $pos): string
    {
        return match ($pos % 9) {
            1, 6 => 'vd-gallery__item--big',
            0 => 'vd-gallery__item--wide',
            default => '',
        };
    }
}
