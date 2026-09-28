<?php
namespace KaarBooking\Component\KaarBooking\Administrator\Service;defined('_JEXEC') or die;
use Joomla\CMS\Factory;
final class MediaUploadService
{
 public static function store(array $upload,string $subjectType,int $subjectId,int $userId,int $minimum,int $maximum,string $purpose='gallery'):array
 {
  $files=self::normalise($upload);if(count($files)<$minimum||count($files)>$maximum)throw new \DomainException("Upload between $minimum and $maximum images.");$dir=JPATH_ROOT.'/media/com_kaarbooking/'.$subjectType;if(!is_dir($dir)&&!mkdir($dir,0755,true)&&!is_dir($dir))throw new \RuntimeException('Image directory is unavailable.');$db=Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);$stored=[];
  try{foreach($files as $order=>$file){if(($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK||(int)($file['size']??0)<1||(int)$file['size']>5242880)throw new \DomainException('Each image must be no larger than 5 MB.');$tmp=(string)$file['tmp_name'];$mime=(new \finfo(FILEINFO_MIME_TYPE))->file($tmp);$ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];if(!isset($ext[$mime])||@getimagesize($tmp)===false)throw new \DomainException('Images must be valid JPEG, PNG or WebP files.');$name=bin2hex(random_bytes(20)).'.'.$ext[$mime];$absolute=$dir.'/'.$name;if(!move_uploaded_file($tmp,$absolute))throw new \RuntimeException('Image could not be stored.');$path='media/com_kaarbooking/'.$subjectType.'/'.$name;$stored[]=$absolute;$db->insertObject('#__kaar_media',(object)['subject_type'=>$subjectType,'subject_id'=>$subjectId,'purpose'=>$purpose,'path'=>$path,'mime_type'=>$mime,'file_hash'=>hash_file('sha256',$absolute),'alt_text'=>'','ordering'=>$order,'created_by'=>$userId,'created'=>Factory::getDate()->toSql()]);}return $stored;}catch(\Throwable $e){foreach($stored as $path)@unlink($path);throw $e;}
 }
 private static function normalise(array $upload):array{if(!isset($upload['name']))return[];if(!is_array($upload['name']))return [self::one($upload)];$files=[];foreach($upload['name'] as $i=>$name)if(($upload['error'][$i]??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_NO_FILE)$files[]=self::one(['name'=>$name,'type'=>$upload['type'][$i]??'','tmp_name'=>$upload['tmp_name'][$i]??'','error'=>$upload['error'][$i]??UPLOAD_ERR_NO_FILE,'size'=>$upload['size'][$i]??0]);return $files;}
 private static function one(array $file):array{return $file;}
}
