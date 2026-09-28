<?php
namespace KaarBooking\Component\KaarBooking\Administrator\View\Vehicles;
defined('_JEXEC') or die;
use Joomla\CMS\Factory; use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
final class HtmlView extends BaseHtmlView { public array $items=[]; public function display($tpl=null):void {if(!Factory::getApplication()->getIdentity()->authorise('core.manage','com_kaarbooking'))throw new \RuntimeException('Not authorised',403);$db=Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);$q=$db->getQuery(true)->select(['v.*','t.title AS type_title'])->from($db->quoteName('#__kaar_vehicles','v'))->join('LEFT',$db->quoteName('#__kaar_vehicle_types','t').' ON '.$db->quoteName('t.id').'='.$db->quoteName('v.vehicle_type_id'))->order('v.registration_number');$this->items=$db->setQuery($q)->loadAssocList()?:[];parent::display($tpl);} }
