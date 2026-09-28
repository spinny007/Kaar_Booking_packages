<?php
declare(strict_types=1);
namespace KaarBooking\Tests\Unit;
use PHPUnit\Framework\TestCase;
use KaarBooking\Component\KaarBooking\Administrator\Domain\Realtime\LocationSyncPolicy;
require_once dirname(__DIR__,2).'/src/component/administrator/src/Domain/Realtime/LocationSyncPolicy.php';
final class LocationSyncPolicyTest extends TestCase
{
    public function testIntervalIsClampedToSafeBounds():void{$this->assertSame(15,LocationSyncPolicy::interval(1));$this->assertSame(120,LocationSyncPolicy::interval(120));$this->assertSame(600,LocationSyncPolicy::interval(9999));}
    public function testMinimumGapAllowsNetworkJitter():void{$this->assertSame(60,LocationSyncPolicy::minimumGap(120));$this->assertSame(10,LocationSyncPolicy::minimumGap(15));}
}
