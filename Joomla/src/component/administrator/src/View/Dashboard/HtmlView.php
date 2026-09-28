<?php
namespace KaarBooking\Component\KaarBooking\Administrator\View\Dashboard;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

final class HtmlView extends BaseHtmlView
{
    public array $metrics = [];
    public array $queue = [];

    public function display($tpl = null): void
    {
        $user = Factory::getApplication()->getIdentity();
        if (!$user->authorise('core.manage', 'com_kaarbooking')) {
            throw new \RuntimeException('Not authorised', 403);
        }

        $db = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);
        $this->metrics = $this->loadMetrics($db);
        $query = $db->getQuery(true)
            ->select(['id', 'reference', 'product_type', 'customer_name', 'departure_at', 'status', 'payment_status', 'total_amount'])
            ->from($db->quoteName('#__kaar_bookings'))
            ->where($db->quoteName('status') . ' IN (' . implode(',', array_map([$db, 'quote'], ['pending_admin_review', 'paid_pending_confirmation'])) . ')')
            ->order($db->quoteName('created') . ' ASC');
        $this->queue = $db->setQuery($query, 0, 20)->loadAssocList() ?: [];
        parent::display($tpl);
    }

    private function loadMetrics($db): array
    {
        $metrics = [];
        foreach (['pending_admin_review', 'paid_pending_confirmation', 'confirmed', 'completed', 'cancelled'] as $status) {
            $query = $db->getQuery(true)->select('COUNT(*)')->from($db->quoteName('#__kaar_bookings'))
                ->where($db->quoteName('status') . ' = ' . $db->quote($status));
            $metrics[$status] = (int) $db->setQuery($query)->loadResult();
        }
        $query = $db->getQuery(true)->select('COALESCE(SUM(' . $db->quoteName('total_amount') . '), 0)')
            ->from($db->quoteName('#__kaar_bookings'))->where($db->quoteName('payment_status') . ' = ' . $db->quote('paid'));
        $metrics['captured_value'] = (float) $db->setQuery($query)->loadResult();
        return $metrics;
    }
}
