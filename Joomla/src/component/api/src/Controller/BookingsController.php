<?php
namespace KaarBooking\Component\KaarBooking\Api\Controller;
defined('_JEXEC') or die;
use Joomla\CMS\Factory; use KaarBooking\Component\KaarBooking\Administrator\Domain\Booking\BookingReference;
final class BookingsController extends BaseJsonController
{
    public function create(): never
    {
        $this->requireAuthenticated(); $input=json_decode(file_get_contents('php://input'),true,64,JSON_THROW_ON_ERROR);
        $departure=new \DateTimeImmutable((string)($input['departureAt']??''),new \DateTimeZone('UTC'));$now=new \DateTimeImmutable('now',new \DateTimeZone('UTC'));if($departure<=$now)$this->respond(['code'=>'invalid_departure','message'=>'Departure must be in the future'],422);
        $user=$this->app->getIdentity();$db=Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);$ref=BookingReference::generate($now);
        $row=(object)['reference'=>$ref,'source'=>'flutter_customer','product_type'=>(string)($input['productType']??'vehicle'),'service_type'=>(string)($input['serviceType']??'full_day'),'customer_id'=>null,'customer_name'=>$user->name,'customer_email'=>$user->email,'customer_phone'=>'','package_id'=>isset($input['packageId'])?(int)$input['packageId']:null,'vehicle_type_id'=>isset($input['vehicleTypeId'])?(int)$input['vehicleTypeId']:null,'origin_text'=>(string)($input['origin']??''),'destination_text'=>(string)($input['destination']??''),'stops_json'=>json_encode($input['stops']??[],JSON_THROW_ON_ERROR),'departure_at'=>$departure->format('Y-m-d H:i:s'),'return_at'=>null,'passengers'=>max(1,(int)($input['passengers']??1)),'driver_mode'=>(string)($input['driverMode']??'with_driver'),'status'=>'pending_admin_review','payment_status'=>'unpaid','total_amount'=>0,'currency'=>'INR','created_by'=>(int)$user->id,'created'=>$now->format('Y-m-d H:i:s'),'modified_by'=>0,'version'=>1];$db->insertObject('#__kaar_bookings',$row);$this->respond(['data'=>['reference'=>$ref,'status'=>'pending_admin_review','manualConfirmation'=>true]],201);
    }
    public function display($cachable=false,$urlparams=[]): never
    {
        $this->requireAuthenticated();$ref=$this->app->getInput()->getString('reference');$db=Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);$q=$db->getQuery(true)->select(['reference','product_type','service_type','origin_text','destination_text','departure_at','return_at','passengers','status','payment_status','total_amount','currency'])->from($db->quoteName('#__kaar_bookings'))->where($db->quoteName('reference').'='.$db->quote($ref))->where($db->quoteName('created_by').'='.(int)$this->app->getIdentity()->id);$row=$db->setQuery($q)->loadAssoc();if(!$row)$this->respond(['code'=>'not_found','message'=>'Booking not found'],404);$this->respond(['data'=>$row]);
    }
}
