<?php
declare(strict_types=1);
namespace KaarBooking\Tests\Unit;
use KaarBooking\Component\KaarBooking\Administrator\Domain\Booking\BookingReference;
use PHPUnit\Framework\TestCase;
require_once dirname(__DIR__, 2) . '/src/component/administrator/src/Domain/Booking/BookingReference.php';
final class BookingReferenceTest extends TestCase
{
    public function testReferenceHasExpectedNonSequentialShape(): void
    {
        $now=new \DateTimeImmutable('2026-09-28T12:00:00Z');$one=BookingReference::generate($now);$two=BookingReference::generate($now);
        self::assertMatchesRegularExpression('/^KB-260928-[A-F0-9]{6}$/',$one);self::assertNotSame($one,$two);
    }
}
