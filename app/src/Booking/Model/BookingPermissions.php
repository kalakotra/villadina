<?php

declare(strict_types=1);

namespace App\Booking\Model;

use SilverStripe\Security\Permission;

/**
 * Admin-only records (reservations, payments, blocks, feeds).
 */
trait BookingPermissions
{
    public function canView($member = null): bool
    {
        return Permission::check(Season::PERMISSION, 'any', $member);
    }

    public function canEdit($member = null): bool
    {
        return Permission::check(Season::PERMISSION, 'any', $member);
    }

    public function canDelete($member = null): bool
    {
        return Permission::check(Season::PERMISSION, 'any', $member);
    }

    public function canCreate($member = null, $context = []): bool
    {
        return Permission::check(Season::PERMISSION, 'any', $member);
    }
}
