<?php
namespace KaarBooking\Component\KaarBooking\Api\Controller;
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
final class PackagesController extends BaseJsonController { public function display($cachable=false,$urlparams=[]): never { $db=Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);$q=$db->getQuery(true)->select(['id','title','alias','summary','duration_days','duration_nights','booking_mode','capacity','base_price','currency'])->from($db->quoteName('#__kaar_packages'))->where($db->quoteName('state').' = 1')->order('ordering,title');$rows=$db->setQuery($q)->loadAssocList()?:[];foreach($rows as &$row){$iq=$db->getQuery(true)->select(['item_type','title','description'])->from($db->quoteName('#__kaar_package_items'))->where($db->quoteName('package_id').'='.(int)$row['id'])->where($db->quoteName('state').' = 1')->order('ordering');$row['items']=$db->setQuery($iq)->loadAssocList()?:[];}unset($row);$this->respond(['data'=>$rows]); } }
