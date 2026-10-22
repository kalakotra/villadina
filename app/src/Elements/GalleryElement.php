<?php

declare(strict_types=1);

namespace App\Elements;

use Bummzack\SortableFile\Forms\SortableUploadField;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\Model\List\SS_List;

/**
 * Image gallery: mosaic grid (9 visible, rest in the lightbox) or slider.
 *
 * @property string|null $Eyebrow
 * @property string $DisplayMode grid|slider
 * @method ManyManyList<Image> Images()
 */
class GalleryElement extends BaseElement
{
    public const GRID_LIMIT = 9;

    private static string $table_name = 'VD_GalleryElement';
    private static string $singular_name = 'Gallery';
    private static string $plural_name = 'Galleries';
    private static string $description = 'Image mosaic or slider with lightbox';
    private static string $icon = 'font-icon-block-carousel';
    private static bool $inline_editable = false;

    private static array $db = [
        'Eyebrow' => 'Varchar(120)',
        'DisplayMode' => "Enum('grid,slider','grid')",
    ];

    private static array $many_many = [
        'Images' => Image::class,
    ];

    private static array $many_many_extraFields = [
        'Images' => ['SortOrder' => 'Int'],
    ];

    private static array $owns = [
        'Images',
    ];

    private static array $defaults = [
        'ShowTitle' => true,
        'DisplayMode' => 'grid',
    ];

    public function getCMSFields(): FieldList
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields): void {
            $fields->removeByName('Images');

            $fields->addFieldsToTab('Root.Main', [
                TextField::create('Eyebrow', 'Eyebrow'),
                DropdownField::create('DisplayMode', 'Display', [
                    'grid' => 'Mosaic grid (max. 9 visible, rest in lightbox)',
                    'slider' => 'Slider',
                ]),
                SortableUploadField::create('Images', 'Images')
                    ->setSortColumn('SortOrder')
                    ->setFolderName('Gallery')
                    ->setAllowedFileCategories('image/supported')
                    ->setDescription('Drag to sort. Landscape, min. 2000 px wide.'),
            ]);
        });

        return parent::getCMSFields();
    }

    public function getType(): string
    {
        return 'Gallery';
    }

    public function IsFlush(): bool
    {
        return false;
    }

    public function SortedImages(): SS_List
    {
        return $this->Images()->sort('SortOrder');
    }

    public function GridImages(): SS_List
    {
        return $this->SortedImages()->limit(self::GRID_LIMIT);
    }

    /**
     * Images beyond the grid – rendered as hidden lightbox links.
     */
    public function HiddenImages(): SS_List
    {
        return $this->SortedImages()->limit(PHP_INT_MAX, self::GRID_LIMIT);
    }

    public function RemainingCount(): int
    {
        return max(0, $this->Images()->count() - self::GRID_LIMIT);
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
