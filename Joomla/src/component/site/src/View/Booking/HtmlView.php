<?php
namespace KaarBooking\Component\KaarBooking\Site\View\Booking;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

final class HtmlView extends BaseHtmlView
{
    public array $vehicleTypes = [];

    public function display($tpl = null): void
    {
        $db = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);
        $query = $db->getQuery(true)->select(['id', 'title', 'passenger_capacity', 'with_driver', 'self_drive'])
            ->from($db->quoteName('#__kaar_vehicle_types'))->where($db->quoteName('state') . ' = 1')->order('ordering, title');
        $this->vehicleTypes = $db->setQuery($query)->loadAssocList() ?: [];
        $wa = $this->getDocument()->getWebAssetManager();
        $wa->useStyle('com_kaarbooking.site')->useScript('com_kaarbooking.booking');
        parent::display($tpl);
    }
}
