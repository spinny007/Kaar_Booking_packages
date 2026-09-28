<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;

/** Repairs databases from the 0.1.0 package, whose registered component could lack its tables. */
final class Com_KaarbookingInstallerScript
{
    public function postflight(string $type, InstallerAdapter $parent): bool
    {
        if (!in_array($type, ['install', 'update'], true)) {
            return true;
        }

        $db = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);
        $tables = array_map('strtolower', $db->getTableList());
        $required = strtolower($db->replacePrefix('#__kaar_documents'));

        if (!in_array($required, $tables, true)) {
            $path = JPATH_ADMINISTRATOR . '/components/com_kaarbooking/sql/install.mysql.utf8.sql';
            $sql = is_file($path) ? file_get_contents($path) : false;
            if ($sql === false) {
                throw new RuntimeException('Kaar Booking database schema file is unavailable.');
            }
            foreach ($db->splitSql($sql) as $query) {
                if (trim($query) !== '') {
                    $db->setQuery($query)->execute();
                }
            }
        }

        $vehicleColumns = $db->getTableColumns($db->replacePrefix('#__kaar_vehicles'), false);
        if (!isset($vehicleColumns['company_id'])) {
            $db->setQuery('ALTER TABLE ' . $db->quoteName('#__kaar_vehicles') . ' ADD ' . $db->quoteName('company_id') . ' BIGINT UNSIGNED DEFAULT NULL AFTER ' . $db->quoteName('id'))->execute();
        }
        $driverColumns = $db->getTableColumns($db->replacePrefix('#__kaar_drivers'), false);
        if (!isset($driverColumns['company_id'])) {
            $db->setQuery('ALTER TABLE ' . $db->quoteName('#__kaar_drivers') . ' ADD ' . $db->quoteName('company_id') . ' BIGINT UNSIGNED DEFAULT NULL AFTER ' . $db->quoteName('user_id'))->execute();
        }
        if (!isset($driverColumns['licence_number_masked'])) {
            $db->setQuery('ALTER TABLE ' . $db->quoteName('#__kaar_drivers') . ' ADD ' . $db->quoteName('licence_number_masked') . " VARCHAR(32) NOT NULL DEFAULT '' AFTER " . $db->quoteName('company_id'))->execute();
        }
        if (!isset($driverColumns['licence_number_hash'])) {
            $db->setQuery('ALTER TABLE ' . $db->quoteName('#__kaar_drivers') . ' ADD ' . $db->quoteName('licence_number_hash') . ' CHAR(64) DEFAULT NULL AFTER ' . $db->quoteName('licence_number_masked'))->execute();
        }
        $inspectionColumns = $db->getTableColumns($db->replacePrefix('#__kaar_vehicle_inspections'), false);
        if (!isset($inspectionColumns['driver_id'])) {
            $db->setQuery('ALTER TABLE ' . $db->quoteName('#__kaar_vehicle_inspections') . ' ADD ' . $db->quoteName('driver_id') . ' BIGINT UNSIGNED DEFAULT NULL AFTER ' . $db->quoteName('vehicle_id'))->execute();
        }

        return true;
    }
}
