<?php
namespace KaarBooking\Component\KaarBooking\Administrator\Domain\Notification;
defined('_JEXEC') or die;
interface NotificationProviderInterface
{
    public function send(string $channel, string $recipient, string $template, array $safeVariables): string;
}
