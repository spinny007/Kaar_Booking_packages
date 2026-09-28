<?php
namespace KaarBooking\Plugin\Webservices\KaarBooking\Extension;defined('_JEXEC') or die;
use Joomla\CMS\Component\ComponentHelper;use Joomla\CMS\Plugin\CMSPlugin;use Joomla\CMS\Router\ApiRouter;use Joomla\Router\Route;use Joomla\Event\SubscriberInterface;
final class KaarBooking extends CMSPlugin implements SubscriberInterface
{
 public static function getSubscribedEvents():array{return ['onBeforeApiRoute'=>'onBeforeApiRoute'];}
 public function onBeforeApiRoute(&$router):void
 {
  if(!$router instanceof ApiRouter)return;$p=ComponentHelper::getParams('com_kaarbooking');if(!(int)$p->get('api_enabled',1))return;$d=['component'=>'com_kaarbooking'];$public=['component'=>'com_kaarbooking','public'=>true];$routes=[];
  if((int)$p->get('api_otp_login_enabled',1))$routes=array_merge($routes,[new Route(['POST'],'v1/kaar/auth/challenge','auth.challenge',[],$public),new Route(['POST'],'v1/kaar/auth/verify','auth.verify',[],$public),new Route(['POST'],'v1/kaar/auth/refresh','auth.refresh',[],$public),new Route(['POST'],'v1/kaar/auth/logout','auth.logout',[],$d)]);
  $routes=array_merge($routes,[new Route(['GET'],'v1/kaar/config','config.display',[],$d),new Route(['GET'],'v1/kaar/vehicle-types','vehicletypes.display',[],$d),new Route(['GET'],'v1/kaar/packages','packages.display',[],$d),new Route(['GET'],'v1/kaar/app-layout','applayout.display',[],$d)]);
  if((int)$p->get('api_booking_enabled',1))$routes=array_merge($routes,[new Route(['POST'],'v1/kaar/bookings','bookings.create',[],$d),new Route(['GET'],'v1/kaar/bookings/:reference','bookings.display',['reference'=>'([A-Za-z0-9-]+)'],$d)]);
  if((int)$p->get('api_kyc_enabled',1))$routes=array_merge($routes,[new Route(['GET'],'v1/kaar/kyc','kyc.display',[],$d),new Route(['POST'],'v1/kaar/kyc/address-proof','kyc.upload',[],$d),new Route(['DELETE'],'v1/kaar/kyc/address-proof','kyc.delete',[],$d)]);
  if((int)$p->get('api_realtime_enabled',0))$routes=array_merge($routes,[new Route(['POST'],'v1/kaar/rides','rides.create',[],$d),new Route(['GET'],'v1/kaar/rides/:id','rides.display',['id'=>'(\d+)'],$d),new Route(['PATCH'],'v1/kaar/rides/:id','rides.update',['id'=>'(\d+)'],$d),new Route(['GET'],'v1/kaar/driver/offers','rides.offers',[],$d),new Route(['POST'],'v1/kaar/rides/:id/accept','rides.accept',['id'=>'(\d+)'],$d),new Route(['POST'],'v1/kaar/rides/:id/arrive','rides.arrive',['id'=>'(\d+)'],$d),new Route(['POST'],'v1/kaar/rides/:id/start','rides.start',['id'=>'(\d+)'],$d),new Route(['POST'],'v1/kaar/rides/:id/complete','rides.complete',['id'=>'(\d+)'],$d),new Route(['POST'],'v1/kaar/rides/:id/cancel','rides.cancel',['id'=>'(\d+)'],$d),new Route(['PATCH'],'v1/kaar/driver/availability','rides.availability',[],$d),new Route(['POST'],'v1/kaar/driver/location','rides.location',[],$d)]);
  $router->addRoutes($routes);
 }
}
