<?php
namespace KaarBooking\Component\KaarBooking\Administrator\View\Bookings;
defined('_JEXEC') or die;
use Joomla\CMS\Factory; use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
final class HtmlView extends BaseHtmlView
{
    public array $items=[];
    public function display($tpl=null):void {if(!Factory::getApplication()->getIdentity()->authorise('core.manage','com_kaarbooking'))throw new \RuntimeException('Not authorised',403);$db=Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);$q=$db->getQuery(true)->select('*')->from($db->quoteName('#__kaar_bookings'))->order($db->quoteName('created').' DESC');$this->items=$db->setQuery($q,0,200)->loadAssocList()?:[];parent::display($tpl);}
}
