<?php
namespace KaarBooking\Component\KaarBooking\Api\Controller;
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
final class VehicletypesController extends BaseJsonController { public function display($cachable=false,$urlparams=[]): never { $db=Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);$q=$db->getQuery(true)->select(['id','title','alias','passenger_capacity','luggage_capacity','with_driver','self_drive'])->from($db->quoteName('#__kaar_vehicle_types'))->where($db->quoteName('state').' = 1')->order('ordering,title');$this->respond(['data'=>$db->setQuery($q)->loadAssocList()?:[]]); } }
