<?php

declare(strict_types=1);

namespace App\Pages;

use DNADesign\Elemental\Extensions\ElementalPageExtension;
use DNADesign\Elemental\Models\ElementalArea;
use Page;
use SilverStripe\Forms\FieldList;

/**
 * Base page for everything built from Elemental blocks.
 * Allowed blocks: app/_config/elemental.yml
 *
 * @method ElementalArea ElementalArea()
 * @mixin ElementalPageExtension
 */
class ModularPage extends Page
{
    private static string $table_name = 'VD_ModularPage';
    private static string $singular_name = 'Modular page';
    private static string $plural_name = 'Modular pages';
    private static string $description = 'Page built from content blocks';
    private static string $icon_class = 'font-icon-p-alt';

    private static array $extensions = [
        ElementalPageExtension::class,
    ];

    public function getCMSFields(): FieldList
    {
        $fields = parent::getCMSFields();

        // Blocks replace the WYSIWYG; header image only makes sense without a Hero block
        $fields->removeByName('Content');

        return $fields;
    }
}
