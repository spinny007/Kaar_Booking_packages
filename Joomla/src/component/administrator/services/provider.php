<?php
defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use KaarBooking\Component\KaarBooking\Administrator\Extension\KaarBookingComponent;

return new class implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->registerServiceProvider(new MVCFactory('KaarBooking\\Component\\KaarBooking'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('KaarBooking\\Component\\KaarBooking'));
        $container->set(ComponentInterface::class, static function (Container $container): ComponentInterface {
            $component = new KaarBookingComponent($container->get(ComponentDispatcherFactoryInterface::class));
            $component->setMVCFactory($container->get(MVCFactoryInterface::class));
            return $component;
        });
    }
};
