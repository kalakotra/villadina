<?php

declare(strict_types=1);

namespace App\Elements;

use App\Elements\Traits\FormatsHeading;
use Bummzack\SortableFile\Forms\SortableUploadField;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\LinkField\Form\LinkField;
use SilverStripe\LinkField\Models\Link;
use SilverStripe\ORM\ManyManyList;

/**
 * Full-screen hero: image slideshow or background video, headline, CTAs, optional booking widget.
 *
 * @property string|null $Eyebrow
 * @property string|null $Lead
 * @property string $Size
 * @property bool $ShowBooking
 * @method ManyManyList<Image> Slides()
 * @method File Video()
 * @method Link PrimaryLink()
 * @method Link SecondaryLink()
 */
class HeroElement extends BaseElement
{
    use FormatsHeading;

    private static string $table_name = 'VD_HeroElement';
    private static string $singular_name = 'Hero';
    private static string $plural_name = 'Heroes';
    private static string $description = 'Full-screen intro with slideshow/video and booking widget';
    private static string $icon = 'font-icon-block-banner';
    private static bool $inline_editable = false;

    private static array $db = [
        'Eyebrow' => 'Varchar(120)',
        'Lead' => 'Text',
        'Size' => "Enum('full,medium','full')",
        'ShowBooking' => 'Boolean',
    ];

    private static array $has_one = [
        'Video' => File::class,
        'PrimaryLink' => Link::class,
        'SecondaryLink' => Link::class,
    ];

    private static array $many_many = [
        'Slides' => Image::class,
    ];

    private static array $many_many_extraFields = [
        'Slides' => ['SortOrder' => 'Int'],
    ];

    private static array $owns = [
        'Slides',
        'Video',
        'PrimaryLink',
        'SecondaryLink',
    ];

    private static array $cascade_deletes = [
        'PrimaryLink',
        'SecondaryLink',
    ];

    private static array $cascade_duplicates = [
        'PrimaryLink',
        'SecondaryLink',
    ];

    private static array $defaults = [
        'ShowTitle' => true,
        'ShowBooking' => true,
        'Size' => 'full',
    ];

    public function getCMSFields(): FieldList
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields): void {
            $fields->removeByName(['Slides', 'Video', 'VideoID', 'PrimaryLinkID', 'SecondaryLinkID']);

            $fields->dataFieldByName('Title')
                ?->setDescription('Wrap words in *asterisks* for italic accent, e.g. "Villa *Dina*".');

            $fields->addFieldsToTab('Root.Main', [
                TextField::create('Eyebrow', 'Eyebrow (small line above title)'),
                TextareaField::create('Lead', 'Lead text')->setRows(3),
                SortableUploadField::create('Slides', 'Slideshow images')
                    ->setSortColumn('SortOrder')
                    ->setFolderName('Hero')
                    ->setAllowedFileCategories('image/supported')
                    ->setDescription('Landscape, min. 2400px wide. First image is the poster for video.'),
                UploadField::create('Video', 'Background video (optional, MP4)')
                    ->setFolderName('Hero')
                    ->setAllowedExtensions(['mp4', 'webm'])
                    ->setDescription('Muted autoplay loop, max ~8 MB. Replaces the slideshow.'),
                LinkField::create('PrimaryLink', 'Primary button'),
                LinkField::create('SecondaryLink', 'Secondary button'),
                DropdownField::create('Size', 'Height', [
                    'full' => 'Full screen',
                    'medium' => 'Medium (≈ 80%)',
                ]),
                CheckboxField::create('ShowBooking', 'Show availability widget'),
            ]);
        });

        return parent::getCMSFields();
    }

    public function getType(): string
    {
        return 'Hero';
    }

    public function SortedSlides(): ManyManyList
    {
        return $this->Slides()->sort('SortOrder');
    }

    public function PosterImage(): ?Image
    {
        /** @var Image|null $img */
        $img = $this->SortedSlides()->first();

        return $img;
    }

    public function HasSlideshow(): bool
    {
        return $this->Slides()->count() > 1;
    }

    public function HasVideo(): bool
    {
        return $this->Video()->exists();
    }

    public function IsFlush(): bool
    {
        return true;
    }
}
