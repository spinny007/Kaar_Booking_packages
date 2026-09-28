<?php
namespace KaarBooking\Component\KaarBooking\Administrator\Domain\Booking;

defined('_JEXEC') or die;

enum BookingStatus: string
{
    case Draft = 'draft';
    case PendingAdminReview = 'pending_admin_review';
    case Quoted = 'quoted';
    case PendingPayment = 'pending_payment';
    case PaidPendingConfirmation = 'paid_pending_confirmation';
    case Confirmed = 'confirmed';
    case Allocated = 'allocated';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';

    public function canTransitionTo(self $next, bool $realtime = false): bool
    {
        $allowed = match ($this) {
            self::Draft => [self::PendingAdminReview, self::Cancelled],
            self::PendingAdminReview => [self::Quoted, self::PendingPayment, self::Rejected, self::Cancelled],
            self::Quoted => [self::PendingPayment, self::Cancelled],
            self::PendingPayment => [self::PaidPendingConfirmation, self::Cancelled],
            self::PaidPendingConfirmation => [self::Confirmed, self::Rejected, self::Cancelled],
            self::Confirmed => [self::Allocated, self::Cancelled],
            self::Allocated => [self::InProgress, self::Cancelled, self::NoShow],
            self::InProgress => [self::Completed, self::Cancelled],
            default => [],
        };

        if ($realtime && $this === self::Draft) {
            $allowed[] = self::Confirmed;
        }

        return in_array($next, $allowed, true);
    }
}
