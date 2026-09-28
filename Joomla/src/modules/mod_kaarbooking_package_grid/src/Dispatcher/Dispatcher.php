<?php
namespace KaarBooking\Module\PackageGrid\Site\Dispatcher;
defined('_JEXEC') or die;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher; use Joomla\CMS\Factory;
final class Dispatcher extends AbstractModuleDispatcher
{
    protected function getLayoutData(): array
    {
        $data=parent::getLayoutData(); $db=Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class); $data['packages']=[];
        try { $q=$db->getQuery(true)->select('*')->from($db->quoteName('#__kaar_packages'))->where($db->quoteName('state').' = 1')->order('ordering,title'); $rows=$db->setQuery($q,0,max(1,min(24,(int)$data['params']->get('limit',6))))->loadAssocList()?:[];
            foreach($rows as &$row){$iq=$db->getQuery(true)->select(['item_type','title'])->from($db->quoteName('#__kaar_package_items'))->where($db->quoteName('package_id').'='.(int)$row['id'])->where($db->quoteName('state').' = 1')->order('ordering');$row['items']=$db->setQuery($iq)->loadAssocList()?:[];} unset($row);$data['packages']=$rows;
        } catch(\Throwable $error){}
        Factory::getApplication()->getDocument()->getWebAssetManager()->registerAndUseStyle('mod_kaarbooking_package_grid','media/com_kaarbooking/css/site.css'); return $data;
    }
}
