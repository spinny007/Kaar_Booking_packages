<?php
namespace KaarBooking\Component\KaarBooking\Administrator\Domain\Booking;

defined('_JEXEC') or die;

final class BookingReference
{
    public static function generate(\DateTimeImmutable $now): string
    {
        return 'KB-' . $now->format('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    }
}
