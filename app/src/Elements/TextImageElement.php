<?php

declare(strict_types=1);

namespace App\Elements;

use App\Elements\Traits\FormatsHeading;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\TextField;
use SilverStripe\LinkField\Form\LinkField;
use SilverStripe\LinkField\Models\Link;

/**
 * Story block: text next to one (or two overlapping) images.
 *
 * @property string|null $Eyebrow
 * @property string|null $Content
 * @property string $ImagePosition
 * @property string $ImageFormat
 * @method Image Image()
 * @method Image SecondaryImage()
 * @method Link CTALink()
 */
class TextImageElement extends BaseElement
{
    use FormatsHeading;

    private static string $table_name = 'VD_TextImageElement';
    private static string $singular_name = 'Text + image';
    private static string $plural_name = 'Text + image';
    private static string $description = 'Text beside an image (optionally a second overlapping image)';
    private static string $icon = 'font-icon-block-promo-3';

    private static array $db = [
        'Eyebrow' => 'Varchar(120)',
        'Content' => 'HTMLText',
        'ImagePosition' => "Enum('right,left','right')",
        'ImageFormat' => "Enum('portrait,landscape','portrait')",
    ];

    private static array $has_one = [
        'Image' => Image::class,
        'SecondaryImage' => Image::class,
        'CTALink' => Link::class,
    ];

    private static array $owns = ['Image', 'SecondaryImage', 'CTALink'];
    private static array $cascade_deletes = ['CTALink'];
    private static array $cascade_duplicates = ['CTALink'];

    private static array $defaults = [
        'ShowTitle' => true,
    ];

    public function getCMSFields(): FieldList
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields): void {
            $fields->removeByName(['ImageID', 'SecondaryImageID', 'CTALinkID']);
            $fields->dataFieldByName('Title')?->setDescription('Use *asterisks* for italic accent.');

            $fields->addFieldsToTab('Root.Main', [
                TextField::create('Eyebrow', 'Eyebrow'),
                HTMLEditorField::create('Content', 'Text')->setRows(10),
                UploadField::create('Image', 'Image')
                    ->setFolderName('Content')
                    ->setAllowedFileCategories('image/supported'),
                UploadField::create('SecondaryImage', 'Second image (overlapping, optional)')
                    ->setFolderName('Content')
                    ->setAllowedFileCategories('image/supported'),
                DropdownField::create('ImagePosition', 'Image position', [
                    'right' => 'Right',
                    'left' => 'Left',
                ]),
                DropdownField::create('ImageFormat', 'Image format', [
                    'portrait' => 'Portrait (4:5)',
                    'landscape' => 'Landscape (4:3)',
                ]),
                LinkField::create('CTALink', 'Link'),
            ]);
        });

        return parent::getCMSFields();
    }

    public function getType(): string
    {
        return 'Text + image';
    }

    public function IsFlush(): bool
    {
        return false;
    }

    public function ImageLeft(): bool
    {
        return $this->ImagePosition === 'left';
    }
}
