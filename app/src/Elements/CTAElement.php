<?php

declare(strict_types=1);

namespace App\Elements;

use App\Elements\Traits\FormatsHeading;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\LinkField\Form\LinkField;
use SilverStripe\LinkField\Models\Link;

/**
 * Closing call-to-action, full-bleed image or plain.
 *
 * @property string|null $Eyebrow
 * @property string|null $Lead
 * @property bool $UseInquiryLink
 * @method Image BackgroundImage()
 * @method Link CTALink()
 */
class CTAElement extends BaseElement
{
    use FormatsHeading;

    private static string $table_name = 'VD_CTAElement';
    private static string $singular_name = 'Call to action';
    private static string $plural_name = 'Calls to action';
    private static string $description = 'Big closing CTA, optionally over an image';
    private static string $icon = 'font-icon-link';

    private static array $db = [
        'Eyebrow' => 'Varchar(120)',
        'Lead' => 'Text',
        'UseInquiryLink' => 'Boolean',
    ];

    private static array $has_one = [
        'BackgroundImage' => Image::class,
        'CTALink' => Link::class,
    ];

    private static array $owns = ['BackgroundImage', 'CTALink'];
    private static array $cascade_deletes = ['CTALink'];
    private static array $cascade_duplicates = ['CTALink'];

    private static array $defaults = [
        'ShowTitle' => true,
        'UseInquiryLink' => true,
    ];

    public function getCMSFields(): FieldList
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields): void {
            $fields->removeByName(['BackgroundImageID', 'CTALinkID']);
            $fields->dataFieldByName('Title')?->setDescription('Use *asterisks* for italic accent.');
            $fields->addFieldsToTab('Root.Main', [
                TextField::create('Eyebrow', 'Eyebrow'),
                TextareaField::create('Lead', 'Text')->setRows(3),
                UploadField::create('BackgroundImage', 'Background image (optional)')
                    ->setFolderName('Content')
                    ->setAllowedFileCategories('image/supported'),
                CheckboxField::create('UseInquiryLink', 'Show "Check availability" button (inquiry page from Settings → Villa)'),
                LinkField::create('CTALink', 'Additional link'),
            ]);
        });

        return parent::getCMSFields();
    }

    public function getType(): string
    {
        return 'Call to action';
    }

    public function IsFlush(): bool
    {
        return $this->BackgroundImage()->exists();
    }
}
