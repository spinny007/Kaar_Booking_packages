<?php
namespace KaarBooking\Module\BookingForm\Site\Dispatcher;
defined('_JEXEC') or die;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Factory;
final class Dispatcher extends AbstractModuleDispatcher
{
    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();
        $db = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);
        try {
            $query = $db->getQuery(true)->select(['id','title','passenger_capacity'])->from($db->quoteName('#__kaar_vehicle_types'))->where($db->quoteName('state').' = 1')->order('ordering,title');
            $data['vehicleTypes'] = $db->setQuery($query)->loadAssocList() ?: [];
        } catch (\Throwable $error) { $data['vehicleTypes'] = []; }
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->registerAndUseStyle('mod_kaarbooking_booking_form', 'media/com_kaarbooking/css/site.css');
        $wa->registerAndUseScript('mod_kaarbooking_booking_form', 'media/com_kaarbooking/js/booking.js', [], ['type'=>'module']);
        return $data;
    }
}
