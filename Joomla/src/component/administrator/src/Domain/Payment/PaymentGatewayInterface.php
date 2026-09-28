<?php
namespace KaarBooking\Component\KaarBooking\Administrator\Domain\Payment;
defined('_JEXEC') or die;
interface PaymentGatewayInterface
{
    public function createOrder(string $bookingReference, string $idempotencyKey, int $minorAmount, string $currency): array;
    public function verifyWebhook(string $rawBody, array $headers): array;
    public function refund(string $paymentReference, int $minorAmount, string $idempotencyKey): array;
}
