<?php

declare(strict_types=1);

namespace App\Elements\Traits;

use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

trait HasItemsGrid
{
    protected function itemsGrid(string $relation, string $title): GridField
    {
        $config = GridFieldConfig_RecordEditor::create(50);
        $config->addComponent(GridFieldOrderableRows::create('Sort'));

        return GridField::create($relation, $title, $this->$relation(), $config);
    }
}
