<?php
namespace KaarBooking\Component\KaarBooking\Site\View\Packages;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

final class HtmlView extends BaseHtmlView
{
    public array $packages = [];

    public function display($tpl = null): void
    {
        $db = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);
        $query = $db->getQuery(true)->select('*')->from($db->quoteName('#__kaar_packages'))
            ->where($db->quoteName('state') . ' = 1')->order('ordering, title');
        $this->packages = $db->setQuery($query)->loadAssocList() ?: [];
        foreach ($this->packages as &$package) {
            $items = $db->getQuery(true)->select(['item_type', 'title', 'description'])->from($db->quoteName('#__kaar_package_items'))
                ->where($db->quoteName('package_id') . ' = ' . (int) $package['id'])->where($db->quoteName('state') . ' = 1')->order('ordering');
            $package['items'] = $db->setQuery($items)->loadAssocList() ?: [];
        }
        unset($package);
        $this->getDocument()->getWebAssetManager()->useStyle('com_kaarbooking.site');
        parent::display($tpl);
    }
}
