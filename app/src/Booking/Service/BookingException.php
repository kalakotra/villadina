<?php

declare(strict_types=1);

namespace App\Booking\Service;

use RuntimeException;

/**
 * Expected domain failure with a message safe to show to the user.
 */
final class BookingException extends RuntimeException
{
}
