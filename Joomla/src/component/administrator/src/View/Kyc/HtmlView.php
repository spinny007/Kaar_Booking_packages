<?php
namespace KaarBooking\Component\KaarBooking\Administrator\View\Kyc;
defined('_JEXEC') or die;
use Joomla\CMS\Factory; use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
final class HtmlView extends BaseHtmlView
{
    public array $items=[];
    public array $subjects=['customer'=>[],'driver'=>[],'vehicle'=>[]];
    public array $documentTypes=[];
    public function display($tpl=null):void
    {
        $user=Factory::getApplication()->getIdentity();if(!$user->authorise('kaarbooking.kyc.verify','com_kaarbooking'))throw new \RuntimeException('Not authorised',403);
        $db=Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);
        $this->items=$db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName('#__kaar_documents'))->order('created DESC'))->loadAssocList()?:[];
        $this->subjects['customer']=$db->setQuery($db->getQuery(true)->select(['id','display_name AS label'])->from($db->quoteName('#__kaar_customers'))->order('display_name'))->loadAssocList()?:[];
        $this->subjects['driver']=$db->setQuery($db->getQuery(true)->select(['id','display_name AS label'])->from($db->quoteName('#__kaar_drivers'))->order('display_name'))->loadAssocList()?:[];
        $this->subjects['vehicle']=$db->setQuery($db->getQuery(true)->select(['id','registration_number AS label'])->from($db->quoteName('#__kaar_vehicles'))->order('registration_number'))->loadAssocList()?:[];
        $this->documentTypes=$db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName('#__kaar_document_types'))->where('state=1')->order('subject_type, ordering, title'))->loadAssocList()?:[];
        $labels=[];foreach($this->subjects as $type=>$rows)foreach($rows as $row)$labels[$type.':'.$row['id']]=$row['label'];
        foreach($this->items as &$item)$item['subject_label']=$labels[$item['subject_type'].':'.$item['subject_id']]??('#'.$item['subject_id']);unset($item);
        parent::display($tpl);
    }
}
