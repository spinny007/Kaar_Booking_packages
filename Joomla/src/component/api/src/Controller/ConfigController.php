<?php
namespace KaarBooking\Component\KaarBooking\Api\Controller;
defined('_JEXEC') or die;
use Joomla\CMS\Component\ComponentHelper;
final class ConfigController extends BaseJsonController { public function display($cachable=false,$urlparams=[]): never { $p=ComponentHelper::getParams('com_kaarbooking'); $this->respond(['apiVersion'=>'v1','manualConfirmation'=>true,'currency'=>$p->get('currency','INR'),'advanceDays'=>(int)$p->get('advance_days',365),'bookingModes'=>array_values(array_filter([$p->get('with_driver',1)?'with_driver':null,$p->get('self_drive',0)?'self_drive':null]))]); } }
