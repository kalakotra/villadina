<?php

declare(strict_types=1);

namespace App\Elements\Traits;

use SilverStripe\Security\Permission;

/**
 * Child records of an element: visible to everyone, editable by CMS users.
 * Expects a has_one "Parent" to a BaseElement.
 */
trait ElementItemPermissions
{
    public function canView($member = null): bool
    {
        return true;
    }

    public function canEdit($member = null): bool
    {
        return Permission::check('CMS_ACCESS_CMSMain', 'any', $member);
    }

    public function canDelete($member = null): bool
    {
        return Permission::check('CMS_ACCESS_CMSMain', 'any', $member);
    }

    public function canCreate($member = null, $context = []): bool
    {
        return Permission::check('CMS_ACCESS_CMSMain', 'any', $member);
    }
}
