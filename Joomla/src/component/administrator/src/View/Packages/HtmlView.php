<?php
namespace KaarBooking\Component\KaarBooking\Administrator\View\Packages;
defined('_JEXEC') or die;
use Joomla\CMS\Factory; use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
final class HtmlView extends BaseHtmlView { public array $items=[]; public function display($tpl=null):void {if(!Factory::getApplication()->getIdentity()->authorise('core.manage','com_kaarbooking'))throw new \RuntimeException('Not authorised',403);$db=Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);$q=$db->getQuery(true)->select('*')->from($db->quoteName('#__kaar_packages'))->order('ordering,title');$this->items=$db->setQuery($q)->loadAssocList()?:[];parent::display($tpl);} }
