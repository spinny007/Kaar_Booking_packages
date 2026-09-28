<?php
namespace KaarBooking\Plugin\Task\KaarBooking\Extension;
defined('_JEXEC') or die;
use Joomla\CMS\Factory; use Joomla\CMS\Plugin\CMSPlugin; use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent; use Joomla\Component\Scheduler\Administrator\Task\Status; use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait; use Joomla\Event\SubscriberInterface;
final class KaarBooking extends CMSPlugin implements SubscriberInterface
{
    use TaskPluginTrait;
    private const TASKS_MAP=['kaarbooking.compliance'=>['langConstPrefix'=>'PLG_TASK_KAARBOOKING_COMPLIANCE','method'=>'runCompliance','form'=>''],'kaarbooking.cleanup'=>['langConstPrefix'=>'PLG_TASK_KAARBOOKING_CLEANUP','method'=>'runCleanup','form'=>'']];
    public static function getSubscribedEvents(): array { return ['onTaskOptionsList'=>'advertiseRoutines','onExecuteTask'=>'standardRoutineHandler']; }
    private function runCompliance(ExecuteTaskEvent $event): int
    {
        $db=Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);$now=Factory::getDate()->toSql();
        $today=substr($now,0,10);$q=$db->getQuery(true)->update($db->quoteName('#__kaar_documents'))->set($db->quoteName('status').'='.$db->quote('expired'))->where($db->quoteName('expiry_date').' IS NOT NULL')->where($db->quoteName('expiry_date').' < '.$db->quote($today))->where($db->quoteName('status').' IN ('.$db->quote('verified').','.$db->quote('expiring').')');$db->setQuery($q)->execute();foreach(['vehicle'=>['#__kaar_vehicles','operational_status'],'driver'=>['#__kaar_drivers','online_state']] as $type=>$target){$sub=$db->getQuery(true)->select('DISTINCT subject_id')->from($db->quoteName('#__kaar_documents'))->where($db->quoteName('subject_type').'='.$db->quote($type))->where($db->quoteName('status').'='.$db->quote('expired'));$q=$db->getQuery(true)->update($db->quoteName($target[0]))->set($db->quoteName('compliance_status').'='.$db->quote('non_compliant'))->set($db->quoteName($target[1]).'='.$db->quote($type==='vehicle'?'blocked':'offline'))->where($db->quoteName('id').' IN ('.$sub.')');$db->setQuery($q)->execute();}return Status::OK;
    }
    private function runCleanup(ExecuteTaskEvent $event): int
    {
        $db=Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);$now=Factory::getDate()->toSql();
        $q=$db->getQuery(true)->update($db->quoteName('#__kaar_notifications'))->set($db->quoteName('status').'='.$db->quote('failed'))->where($db->quoteName('status').'='.$db->quote('queued'))->where($db->quoteName('attempts').' >= 10')->where($db->quoteName('available_at').' <= '.$db->quote($now));$db->setQuery($q)->execute();return Status::OK;
    }
}
