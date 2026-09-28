<?php
namespace KaarBooking\Component\KaarBooking\Administrator\Controller;
defined('_JEXEC') or die;
use Joomla\CMS\Factory; use Joomla\CMS\HTML\HTMLHelper; use Joomla\CMS\MVC\Controller\BaseController; use Joomla\CMS\Router\Route; use Joomla\CMS\Session\Session;
final class KycController extends BaseController
{
    public function preview():void
    {
        Session::checkToken('get') or jexit('Invalid token');
        $user=$this->app->getIdentity();if(!$user->authorise('kaarbooking.kyc.preview','com_kaarbooking'))throw new \RuntimeException('Not authorised',403);$id=$this->input->getInt('id');$db=Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);$q=$db->getQuery(true)->select(['file_key','mime_type'])->from($db->quoteName('#__kaar_documents'))->where($db->quoteName('id').'='.$id)->where($db->quoteName('subject_type').'='.$db->quote('customer'))->where($db->quoteName('deleted_at').' IS NULL');$doc=$db->setQuery($q,0,1)->loadObject();if(!$doc||$doc->file_key==='')throw new \RuntimeException('KYC original is unavailable',404);
        $configured=trim((string)\Joomla\CMS\Component\ComponentHelper::getParams('com_kaarbooking')->get('private_storage_path',''));$root=$configured!==''?$configured:dirname(JPATH_ROOT).DIRECTORY_SEPARATOR.'kaarbooking-private';$base=realpath($root);$path=$base!==false?realpath($base.DIRECTORY_SEPARATOR.basename((string)$doc->file_key)):false;if($base===false||$path===false||!str_starts_with($path,$base.DIRECTORY_SEPARATOR)||!is_file($path))throw new \RuntimeException('KYC original is unavailable',404);
        $audit=(object)['actor_id'=>(int)$user->id,'action'=>'kyc.preview','subject_type'=>'document','subject_id'=>$id,'correlation_id'=>bin2hex(random_bytes(12)),'ip_hash'=>hash_hmac('sha256',(string)$this->input->server->get('REMOTE_ADDR',''),(string)$this->app->get('secret')),'metadata_json'=>'{}','created'=>Factory::getDate()->toSql()];$db->insertObject('#__kaar_audit_log',$audit);while(ob_get_level())ob_end_clean();header('Content-Type: '.(string)$doc->mime_type);header('Content-Disposition: inline; filename="verification-preview"');header('Cache-Control: no-store, private, max-age=0');header('Pragma: no-cache');header("Content-Security-Policy: default-src 'none'; img-src 'self' data:; style-src 'unsafe-inline'; sandbox");header('X-Content-Type-Options: nosniff');readfile($path);$this->app->close();
    }
    public function decide():void
    {
        Session::checkToken('post') or jexit('Invalid token');
        $user=$this->app->getIdentity();
        if(!$user->authorise('kaarbooking.kyc.verify','com_kaarbooking'))throw new \RuntimeException('Not authorised',403);
        $id=$this->input->post->getInt('id');$decision=$this->input->post->getCmd('decision');
        if(!in_array($decision,['verified','rejected','resubmission_required'],true))throw new \DomainException('Invalid KYC decision');
        $reason=trim($this->input->post->getString('reason'));
        if($reason==='')throw new \DomainException('A decision reason is required.');
        $db=Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);$now=Factory::getDate()->toSql();
        $subjectId=(int)$db->setQuery($db->getQuery(true)->select('subject_id')->from($db->quoteName('#__kaar_documents'))->where($db->quoteName('id').'='.(int)$id)->where($db->quoteName('subject_type').'='.$db->quote('customer')),0,1)->loadResult();
        if($subjectId<1)throw new \RuntimeException('KYC document not found',404);
        $db->transactionStart();
        try{
            $verifiedAt=$decision==='verified'?$db->quote($now):'NULL';
            $q=$db->getQuery(true)->update($db->quoteName('#__kaar_documents'))->set($db->quoteName('status').'='.$db->quote($decision))->set($db->quoteName('verified_by').'='.(int)$user->id)->set($db->quoteName('verified_at').'='.$verifiedAt)->where($db->quoteName('id').'='.(int)$id)->where($db->quoteName('subject_type').'='.$db->quote('customer'));$db->setQuery($q)->execute();
            $q=$db->getQuery(true)->update($db->quoteName('#__kaar_customers'))->set($db->quoteName('kyc_status').'='.$db->quote($decision))->set($db->quoteName('modified').'='.$db->quote($now))->where($db->quoteName('id').'='.$subjectId);$db->setQuery($q)->execute();
            $review=(object)['document_id'=>$id,'reviewer_id'=>(int)$user->id,'decision'=>$decision,'reason'=>$reason,'created'=>$now];$db->insertObject('#__kaar_document_reviews',$review);
            $db->transactionCommit();$this->app->enqueueMessage('KYC decision saved.','success');
        }catch(\Throwable $error){$db->transactionRollback();$this->app->enqueueMessage('KYC decision could not be saved.','error');}
        $this->setRedirect(Route::_('index.php?option=com_kaarbooking&view=kyc',false));
    }
}
