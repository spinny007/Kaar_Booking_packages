<?php
namespace KaarBooking\Plugin\ApiAuthentication\KaarBooking\Extension;
defined('_JEXEC') or die;
use Joomla\CMS\Authentication\Authentication; use Joomla\CMS\Event\User\AuthenticationEvent; use Joomla\CMS\Plugin\CMSPlugin; use Joomla\Database\DatabaseAwareTrait; use Joomla\Event\SubscriberInterface;
final class KaarBooking extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;
    public static function getSubscribedEvents(): array { return ['onUserAuthenticate'=>'onUserAuthenticate']; }
    public function onUserAuthenticate(AuthenticationEvent $event): void
    {
        $response=$event->getAuthenticationResponse();$response->type='Token';$response->status=Authentication::STATUS_FAILURE;$response->error_message='Authentication required.';
        $header=(string)$this->getApplication()->getInput()->server->get('HTTP_AUTHORIZATION','','string');if($header==='')$header=(string)$this->getApplication()->getInput()->server->get('REDIRECT_HTTP_AUTHORIZATION','','string');
        if(!preg_match('/^Bearer\s+(kba_[A-Za-z0-9_-]{40,})$/i',trim($header),$match))return;$token=$match[1];$db=$this->getDatabase();$now=gmdate('Y-m-d H:i:s');
        try{$session=$db->setQuery($db->getQuery(true)->select(['id','user_id'])->from($db->quoteName('#__kaar_api_sessions'))->where($db->quoteName('access_hash').'='.$db->quote(hash('sha256',$token)))->where($db->quoteName('revoked_at').' IS NULL')->where($db->quoteName('access_expires').'>='.$db->quote($now)),0,1)->loadObject();if(!$session)return;$user=\Joomla\CMS\Factory::getContainer()->get(\Joomla\CMS\User\UserFactoryInterface::class)->loadUserById((int)$session->user_id);if(!$user||!$user->id||$user->block||!empty($user->requireReset))return;$response->status=Authentication::STATUS_SUCCESS;$response->error_message='';$response->username=$user->username;$response->email=$user->email;$response->fullname=$user->name;$event->stopPropagation();$db->setQuery($db->getQuery(true)->update($db->quoteName('#__kaar_api_sessions'))->set($db->quoteName('last_used').'='.$db->quote($now))->where($db->quoteName('id').'='.(int)$session->id))->execute();}catch(\Throwable $error){$response->error_message='Mobile session authentication failed.';}
    }
}
