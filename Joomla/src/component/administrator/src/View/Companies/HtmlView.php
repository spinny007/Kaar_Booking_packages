<?php
namespace KaarBooking\Component\KaarBooking\Administrator\View\Companies;
defined('_JEXEC') or die;
use Joomla\CMS\Factory;use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
final class HtmlView extends BaseHtmlView {public array $items=[];public function display($tpl=null):void{if(!Factory::getApplication()->getIdentity()->authorise('core.manage','com_kaarbooking'))throw new \RuntimeException('Not authorised',403);$db=Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);$this->items=$db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName('#__kaar_companies'))->order('name'))->loadAssocList()?:[];parent::display($tpl);}}
