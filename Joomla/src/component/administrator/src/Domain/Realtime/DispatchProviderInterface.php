<?php
namespace KaarBooking\Component\KaarBooking\Administrator\Domain\Realtime;
defined('_JEXEC') or die;
interface DispatchProviderInterface
{
    public function requestRide(array $request): array;
    public function cancelRide(string $rideReference, string $reason): void;
    public function updateDriverLocation(int $driverId, float $latitude, float $longitude, \DateTimeImmutable $recordedAt): void;
}
