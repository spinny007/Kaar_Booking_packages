<?php
declare(strict_types=1);
namespace KaarBooking\Tests\Unit;
use KaarBooking\Component\KaarBooking\Administrator\Domain\Booking\BookingStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
require_once dirname(__DIR__, 2) . '/src/component/administrator/src/Domain/Booking/BookingStatus.php';
final class BookingStatusTest extends TestCase
{
    public static function scheduledTransitions(): iterable
    {
        yield 'request reaches review' => [BookingStatus::Draft, BookingStatus::PendingAdminReview, true];
        yield 'review can quote' => [BookingStatus::PendingAdminReview, BookingStatus::Quoted, true];
        yield 'paid needs confirmation' => [BookingStatus::PaidPendingConfirmation, BookingStatus::Confirmed, true];
        yield 'draft cannot auto-confirm scheduled' => [BookingStatus::Draft, BookingStatus::Confirmed, false];
        yield 'completed is terminal' => [BookingStatus::Completed, BookingStatus::Confirmed, false];
        yield 'cannot skip allocation' => [BookingStatus::Confirmed, BookingStatus::InProgress, false];
    }
    #[DataProvider('scheduledTransitions')]
    public function testScheduledTransitions(BookingStatus $from, BookingStatus $to, bool $expected): void
    {
        self::assertSame($expected, $from->canTransitionTo($to));
    }
    public function testRealtimeMayAutoConfirmDraft(): void
    {
        self::assertTrue(BookingStatus::Draft->canTransitionTo(BookingStatus::Confirmed, true));
    }
}
