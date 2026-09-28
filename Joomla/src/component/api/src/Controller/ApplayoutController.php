<?php
namespace KaarBooking\Component\KaarBooking\Api\Controller;
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
final class ApplayoutController extends BaseJsonController { public function display($cachable=true,$urlparams=[]): never { $db=Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);$q=$db->getQuery(true)->select(['version','platform','locale','schema_json','published_at'])->from($db->quoteName('#__kaar_app_layouts'))->where($db->quoteName('status').'='.$db->quote('published'))->order($db->quoteName('published_at').' DESC');$row=$db->setQuery($q,0,1)->loadAssoc();$this->respond($row?['schemaVersion'=>1,'contentVersion'=>(int)$row['version'],'locale'=>$row['locale'],'publishedAt'=>$row['published_at'],'sections'=>json_decode($row['schema_json'],true,512,JSON_THROW_ON_ERROR)]:['schemaVersion'=>1,'contentVersion'=>0,'locale'=>'*','sections'=>[]]); } }
