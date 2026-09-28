<?php
namespace KaarBooking\Component\KaarBooking\Api\Controller;
defined('_JEXEC') or die;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
final class KycController extends BaseJsonController
{
    private function db(){return Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);}
    private function customerId():int
    {
        $db=$this->db();$user=$this->app->getIdentity();$id=(int)$db->setQuery($db->getQuery(true)->select('id')->from($db->quoteName('#__kaar_customers'))->where($db->quoteName('user_id').'='.(int)$user->id),0,1)->loadResult();if($id>0)return $id;$row=(object)['user_id'=>(int)$user->id,'display_name'=>$user->name,'email_verified_at'=>Factory::getDate()->toSql(),'kyc_status'=>'email_verified','marketing_consent'=>0,'created'=>Factory::getDate()->toSql(),'modified'=>null];$db->insertObject('#__kaar_customers',$row,'id');return (int)$row->id;
    }
    private function storageRoot():string
    {
        $configured=trim((string)ComponentHelper::getParams('com_kaarbooking')->get('private_storage_path',''));$root=$configured!==''?$configured:dirname(JPATH_ROOT).DIRECTORY_SEPARATOR.'kaarbooking-private';if(!is_dir($root)&&!mkdir($root,0700,true)&&!is_dir($root))throw new \RuntimeException('Private storage is unavailable.');$real=realpath($root);$public=realpath(JPATH_ROOT)?:JPATH_ROOT;if($real===false||str_starts_with(str_replace('\\','/',$real),rtrim(str_replace('\\','/',$public),'/').'/'))throw new \RuntimeException('Private storage must be outside the public web root.');return $real;
    }
    public function display($cachable=false,$urlparams=[]):never
    {
        $this->requireAuthenticated();$customer=$this->customerId();$db=$this->db();$q=$db->getQuery(true)->select(['id','document_type','masked_reference','status','created','verified_at','deleted_at'])->from($db->quoteName('#__kaar_documents'))->where($db->quoteName('subject_type').'='.$db->quote('customer'))->where($db->quoteName('subject_id').'='.$customer)->order('created DESC');$this->respond(['data'=>['documents'=>$db->setQuery($q)->loadAssocList()?:[]]]);
    }
    public function upload():never
    {
        $this->requireAuthenticated();$customer=$this->customerId();$file=$this->app->getInput()->files->get('proof',null,'array');$proofType=$this->app->getInput()->getCmd('proofType','address_proof');if(!is_array($file)||($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK)$this->respond(['code'=>'upload_required','message'=>'Address proof file is required.'],422);$size=(int)($file['size']??0);if($size<=0||$size>5*1024*1024)$this->respond(['code'=>'invalid_file_size','message'=>'File must be between 1 byte and 5 MB.'],422);$tmp=(string)$file['tmp_name'];$mime=(new \finfo(FILEINFO_MIME_TYPE))->file($tmp);$extensions=['application/pdf'=>'pdf','image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];if(!isset($extensions[$mime]))$this->respond(['code'=>'invalid_file_type','message'=>'Use PDF, JPEG, PNG or WebP.'],422);$binary=file_get_contents($tmp);if($binary===false)$this->respond(['code'=>'upload_failed','message'=>'File could not be read.'],500);if(str_starts_with($mime,'image/')&&@getimagesize($tmp)===false)$this->respond(['code'=>'invalid_image','message'=>'Image is invalid.'],422);
        $root=$this->storageRoot();$key='kyc_'.bin2hex(random_bytes(24)).'.'.$extensions[$mime];$path=$root.DIRECTORY_SEPARATOR.$key;if(file_put_contents($path,$binary,LOCK_EX)!==strlen($binary))$this->respond(['code'=>'storage_failed','message'=>'File could not be stored.'],500);@chmod($path,0600);$db=$this->db();$now=Factory::getDate()->toSql();$row=(object)['subject_type'=>'customer','subject_id'=>$customer,'document_type'=>$proofType,'masked_reference'=>'','file_key'=>$key,'file_hash'=>hash('sha256',$binary),'mime_type'=>$mime,'issue_date'=>null,'expiry_date'=>null,'status'=>'under_review','scan_status'=>'pending','verified_by'=>null,'verified_at'=>null,'deleted_at'=>null,'created_by'=>(int)$this->app->getIdentity()->id,'created'=>$now,'modified'=>null];$db->insertObject('#__kaar_documents',$row,'id');$db->setQuery($db->getQuery(true)->update($db->quoteName('#__kaar_customers'))->set($db->quoteName('kyc_status').'='.$db->quote('under_review'))->where($db->quoteName('id').'='.$customer))->execute();$this->respond(['data'=>['documentId'=>(int)$row->id,'status'=>'under_review']],201);
    }
    public function delete():never
    {
        $this->requireAuthenticated();if(!(int)ComponentHelper::getParams('com_kaarbooking')->get('kyc_delete_after_verify',1))$this->respond(['code'=>'retention_required','message'=>'The current retention policy does not permit immediate deletion.'],409);$customer=$this->customerId();$db=$this->db();$q=$db->getQuery(true)->select(['id','file_key','status'])->from($db->quoteName('#__kaar_documents'))->where($db->quoteName('subject_type').'='.$db->quote('customer'))->where($db->quoteName('subject_id').'='.$customer)->where($db->quoteName('deleted_at').' IS NULL')->order('created DESC');$doc=$db->setQuery($q,0,1)->loadObject();if(!$doc)$this->respond(['code'=>'not_found','message'=>'No KYC original found.'],404);if($doc->status!=='verified')$this->respond(['code'=>'not_verified','message'=>'The original can be deleted after verification.'],409);$path=$this->storageRoot().DIRECTORY_SEPARATOR.basename((string)$doc->file_key);if(is_file($path)&&!unlink($path))$this->respond(['code'=>'delete_failed','message'=>'Original could not be deleted.'],500);$now=Factory::getDate()->toSql();$update=$db->getQuery(true)->update($db->quoteName('#__kaar_documents'))->set($db->quoteName('file_key').' = NULL')->set($db->quoteName('deleted_at').'='.$db->quote($now))->set($db->quoteName('status').'='.$db->quote('original_deleted'))->where($db->quoteName('id').'='.(int)$doc->id);$db->setQuery($update)->execute();$this->respond(['data'=>['deleted'=>true,'deletedAt'=>$now]]);
    }
}
