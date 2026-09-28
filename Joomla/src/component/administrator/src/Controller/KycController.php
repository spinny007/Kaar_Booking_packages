<?php
namespace KaarBooking\Component\KaarBooking\Administrator\Controller;
defined('_JEXEC') or die;
use Joomla\CMS\Factory; use Joomla\CMS\HTML\HTMLHelper; use Joomla\CMS\MVC\Controller\BaseController; use Joomla\CMS\Router\Route; use Joomla\CMS\Session\Session;
final class KycController extends BaseController
{
    public function preview():void
    {
        Session::checkToken('get') or jexit('Invalid token');
        $user=$this->app->getIdentity();if(!$user->authorise('kaarbooking.kyc.preview','com_kaarbooking'))throw new \RuntimeException('Not authorised',403);$id=$this->input->getInt('id');$db=Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);$q=$db->getQuery(true)->select(['file_key','mime_type'])->from($db->quoteName('#__kaar_documents'))->where($db->quoteName('id').'='.$id)->where($db->quoteName('deleted_at').' IS NULL');$doc=$db->setQuery($q,0,1)->loadObject();if(!$doc||$doc->file_key==='')throw new \RuntimeException('Document original is unavailable',404);
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
        $document=$db->setQuery($db->getQuery(true)->select(['subject_id','subject_type'])->from($db->quoteName('#__kaar_documents'))->where($db->quoteName('id').'='.(int)$id),0,1)->loadObject();
        if(!$document)throw new \RuntimeException('Compliance document not found',404);$subjectId=(int)$document->subject_id;
        $db->transactionStart();
        try{
            $verifiedAt=$decision==='verified'?$db->quote($now):'NULL';
            $q=$db->getQuery(true)->update($db->quoteName('#__kaar_documents'))->set($db->quoteName('status').'='.$db->quote($decision))->set($db->quoteName('verified_by').'='.(int)$user->id)->set($db->quoteName('verified_at').'='.$verifiedAt)->where($db->quoteName('id').'='.(int)$id);$db->setQuery($q)->execute();
            $this->recomputeCompliance($db,(string)$document->subject_type,$subjectId,$now);
            $review=(object)['document_id'=>$id,'reviewer_id'=>(int)$user->id,'decision'=>$decision,'reason'=>$reason,'created'=>$now];$db->insertObject('#__kaar_document_reviews',$review);
            $db->transactionCommit();$this->app->enqueueMessage('KYC decision saved.','success');
        }catch(\Throwable $error){$db->transactionRollback();$this->app->enqueueMessage('KYC decision could not be saved.','error');}
        $this->setRedirect(Route::_('index.php?option=com_kaarbooking&view=kyc',false));
    }
    public function upload():void
    {
        Session::checkToken('post') or jexit('Invalid token');$user=$this->app->getIdentity();if(!$user->authorise('kaarbooking.document.submit','com_kaarbooking')&&!$user->authorise('core.manage','com_kaarbooking'))throw new \RuntimeException('Not authorised',403);
        $subjectType=$this->input->post->getCmd('subject_type');$subjectId=$this->input->post->getInt('subject_id');$documentType=$this->input->post->getCmd('document_type');$expiry=trim($this->input->post->getString('expiry_date'));
        if(!in_array($subjectType,['customer','driver','vehicle'],true)||$subjectId<1)throw new \DomainException('Invalid document owner.');
        $db=Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);$type=$db->setQuery($db->getQuery(true)->select(['expiry_required'])->from($db->quoteName('#__kaar_document_types'))->where($db->quoteName('subject_type').'='.$db->quote($subjectType))->where($db->quoteName('code').'='.$db->quote($documentType))->where('state=1'),0,1)->loadObject();if(!$type)throw new \DomainException('Invalid document type.');if((int)$type->expiry_required===1&&!preg_match('/^\d{4}-\d{2}-\d{2}$/',$expiry))throw new \DomainException('An expiry date is required.');
        $file=$this->input->files->get('proof',null,'array');if(!is_array($file)||($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK)throw new \DomainException('Document file is required.');if((int)$file['size']<1||(int)$file['size']>5242880)throw new \DomainException('Document must not exceed 5 MB.');$tmp=(string)$file['tmp_name'];$mime=(new \finfo(FILEINFO_MIME_TYPE))->file($tmp);$ext=['application/pdf'=>'pdf','image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];if(!isset($ext[$mime])||(str_starts_with($mime,'image/')&&@getimagesize($tmp)===false))throw new \DomainException('Use a valid PDF, JPEG, PNG or WebP file.');
        $configured=trim((string)\Joomla\CMS\Component\ComponentHelper::getParams('com_kaarbooking')->get('private_storage_path',''));$root=$configured!==''?$configured:dirname(JPATH_ROOT).DIRECTORY_SEPARATOR.'kaarbooking-private';if(!is_dir($root)&&!mkdir($root,0700,true)&&!is_dir($root))throw new \RuntimeException('Private storage is unavailable.');$root=realpath($root);$public=realpath(JPATH_ROOT)?:JPATH_ROOT;if($root===false||str_starts_with(str_replace('\\','/',$root),rtrim(str_replace('\\','/',$public),'/').'/'))throw new \RuntimeException('Private storage must be outside the public web root.');
        $key='compliance_'.bin2hex(random_bytes(24)).'.'.$ext[$mime];$path=$root.DIRECTORY_SEPARATOR.$key;if(!move_uploaded_file($tmp,$path))throw new \RuntimeException('Document could not be stored.');@chmod($path,0600);$now=Factory::getDate()->toSql();$row=(object)['subject_type'=>$subjectType,'subject_id'=>$subjectId,'document_type'=>$documentType,'masked_reference'=>'','file_key'=>$key,'file_hash'=>hash_file('sha256',$path),'mime_type'=>$mime,'issue_date'=>null,'expiry_date'=>$expiry!==''?$expiry:null,'status'=>'under_review','scan_status'=>'pending','verified_by'=>null,'verified_at'=>null,'deleted_at'=>null,'created_by'=>(int)$user->id,'created'=>$now,'modified'=>null];try{$db->insertObject('#__kaar_documents',$row,'id');}catch(\Throwable $e){@unlink($path);throw $e;}$this->app->enqueueMessage('Document submitted for verification.','success');$this->setRedirect(Route::_('index.php?option=com_kaarbooking&view=kyc',false));
    }
    public function saveType():void
    {
        Session::checkToken('post') or jexit('Invalid token');$user=$this->app->getIdentity();if(!$user->authorise('kaarbooking.document.manage_types','com_kaarbooking')&&!$user->authorise('core.admin','com_kaarbooking'))throw new \RuntimeException('Not authorised',403);$title=trim($this->input->post->getString('title'));$code=strtolower($this->input->post->getCmd('code'));$subject=$this->input->post->getCmd('type_subject');if($title===''||!preg_match('/^[a-z][a-z0-9_]{1,63}$/',$code)||!in_array($subject,['customer','driver','vehicle'],true))throw new \DomainException('Valid title, code and owner type are required.');$db=Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);$row=(object)['title'=>mb_substr($title,0,190),'code'=>$code,'subject_type'=>$subject,'expiry_required'=>$this->input->post->getInt('expiry_required')?1:0,'required_for_compliance'=>$this->input->post->getInt('required_for_compliance')?1:0,'state'=>1,'ordering'=>0];$db->insertObject('#__kaar_document_types',$row,'id');$this->app->enqueueMessage('Document type added.','success');$this->setRedirect(Route::_('index.php?option=com_kaarbooking&view=kyc',false));
    }
    private function recomputeCompliance($db,string $subjectType,int $subjectId,string $now):void
    {
        $map=['customer'=>['#__kaar_customers','kyc_status'],'driver'=>['#__kaar_drivers','compliance_status'],'vehicle'=>['#__kaar_vehicles','compliance_status']];if(!isset($map[$subjectType]))return;$required=(int)$db->setQuery($db->getQuery(true)->select('COUNT(*)')->from($db->quoteName('#__kaar_document_types'))->where($db->quoteName('subject_type').'='.$db->quote($subjectType))->where('required_for_compliance=1')->where('state=1'))->loadResult();$valid=(int)$db->setQuery($db->getQuery(true)->select('COUNT(DISTINCT d.document_type)')->from($db->quoteName('#__kaar_documents','d'))->join('INNER',$db->quoteName('#__kaar_document_types','t').' ON t.code=d.document_type AND t.subject_type=d.subject_type')->where('d.subject_type='.$db->quote($subjectType))->where('d.subject_id='.$subjectId)->where('d.status='.$db->quote('verified'))->where('d.deleted_at IS NULL')->where('(d.expiry_date IS NULL OR d.expiry_date >= '.$db->quote(substr($now,0,10)).')')->where('t.required_for_compliance=1')->where('t.state=1'))->loadResult();$status=$required>0&&$valid>=$required?'verified':'non_compliant';$q=$db->getQuery(true)->update($db->quoteName($map[$subjectType][0]))->set($db->quoteName($map[$subjectType][1]).'='.$db->quote($status))->set($db->quoteName('modified').'='.$db->quote($now))->where($db->quoteName('id').'='.$subjectId);$db->setQuery($q)->execute();
    }
}
