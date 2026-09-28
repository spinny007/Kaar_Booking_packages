<?php
namespace KaarBooking\Component\KaarBooking\Api\Controller;
defined('_JEXEC') or die;
use Joomla\CMS\Component\ComponentHelper;
use KaarBooking\Component\KaarBooking\Administrator\Domain\Realtime\LocationSyncPolicy;
final class ConfigController extends BaseJsonController { public function display($cachable=false,$urlparams=[]): never { $p=ComponentHelper::getParams('com_kaarbooking'); $this->respond(['apiVersion'=>'v1','manualConfirmation'=>true,'currency'=>$p->get('currency','INR'),'advanceDays'=>(int)$p->get('advance_days',365),'driverLocationIntervalSeconds'=>LocationSyncPolicy::interval((int)$p->get('driver_location_interval_seconds',120)),'features'=>['bookings'=>(bool)$p->get('api_booking_enabled',1),'realtime'=>(bool)$p->get('api_realtime_enabled',0),'kyc'=>(bool)$p->get('api_kyc_enabled',1)],'bookingModes'=>array_values(array_filter([$p->get('with_driver',1)?'with_driver':null,$p->get('self_drive',0)?'self_drive':null]))]); } }
