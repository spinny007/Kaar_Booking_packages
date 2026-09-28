<?php
namespace KaarBooking\Component\KaarBooking\Administrator\Controller;
defined('_JEXEC') or die;
use Joomla\CMS\Factory; use Joomla\CMS\MVC\Controller\BaseController; use Joomla\CMS\Router\Route; use Joomla\CMS\Session\Session; use KaarBooking\Component\KaarBooking\Administrator\Domain\Booking\BookingStatus;
final class BookingController extends BaseController
{
    public function transition(): void
    {
        Session::checkToken('post') or jexit('Invalid token');$user=$this->app->getIdentity();if(!$user->authorise('kaarbooking.booking.confirm','com_kaarbooking'))throw new \RuntimeException('Not authorised',403);
        $id=$this->input->post->getInt('id');$nextRaw=$this->input->post->getCmd('status');$reason=trim($this->input->post->getString('reason'));
        try{$next=BookingStatus::from($nextRaw);$db=Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);$q=$db->getQuery(true)->select(['status','version'])->from($db->quoteName('#__kaar_bookings'))->where($db->quoteName('id').'='.(int)$id);$currentRow=$db->setQuery($q)->loadAssoc();if(!$currentRow)throw new \DomainException('Booking not found');$current=BookingStatus::from($currentRow['status']);if(!$current->canTransitionTo($next,false))throw new \DomainException('Invalid scheduled booking transition');$now=Factory::getDate()->toSql();$update=$db->getQuery(true)->update($db->quoteName('#__kaar_bookings'))->set($db->quoteName('status').'='.$db->quote($next->value))->set($db->quoteName('modified').'='.$db->quote($now))->set($db->quoteName('modified_by').'='.(int)$user->id)->set($db->quoteName('version').'='.$db->quoteName('version').'+1')->where($db->quoteName('id').'='.(int)$id)->where($db->quoteName('version').'='.(int)$currentRow['version']);$db->setQuery($update)->execute();if($db->getAffectedRows()!==1)throw new \RuntimeException('Booking changed by another user; refresh and try again.');$history=(object)['booking_id'=>$id,'from_status'=>$current->value,'to_status'=>$next->value,'actor_id'=>(int)$user->id,'reason'=>$reason,'correlation_id'=>bin2hex(random_bytes(12)),'created'=>$now];$db->insertObject('#__kaar_booking_history',$history);$this->app->enqueueMessage('Booking updated.','success');}catch(\Throwable $error){$this->app->enqueueMessage($error->getMessage(),'error');}
        $this->setRedirect(Route::_('index.php?option=com_kaarbooking&view=bookings',false));
    }
}
