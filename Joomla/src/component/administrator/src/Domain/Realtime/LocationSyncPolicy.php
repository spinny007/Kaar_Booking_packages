<?php
namespace KaarBooking\Component\KaarBooking\Administrator\Domain\Realtime;
defined('_JEXEC') or die;
final class LocationSyncPolicy
{
    public static function interval(int $configured):int{return max(15,min(600,$configured));}
    public static function minimumGap(int $configured):int{return max(10,(int)floor(self::interval($configured)/2));}
}
