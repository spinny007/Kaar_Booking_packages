<?php
declare(strict_types=1);
namespace KaarBooking\Tests\Unit;
use KaarBooking\Component\KaarBooking\Administrator\Domain\Kyc\OtpService;
use PHPUnit\Framework\TestCase;
require_once dirname(__DIR__, 2) . '/src/component/administrator/src/Domain/Kyc/OtpService.php';
final class OtpServiceTest extends TestCase
{
    public function testOtpIsSixDigitsAndOnlyHashIsPersistable(): void
    {
        $issued=(new OtpService())->issue();
        self::assertMatchesRegularExpression('/^\d{6}$/',$issued['plain']);
        self::assertNotSame($issued['plain'],$issued['hash']);
        self::assertTrue(password_verify($issued['plain'],$issued['hash']));
    }
    public function testWrongAndMalformedCodesFail(): void
    {
        $service=new OtpService();$issued=$service->issue();
        self::assertFalse($service->verify('abcdef',$issued['hash']));
        self::assertFalse($service->verify('000000',$issued['hash']));
    }
}
