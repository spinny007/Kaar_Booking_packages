<?php
namespace KaarBooking\Component\KaarBooking\Administrator\Domain\Kyc;

defined('_JEXEC') or die;

final class OtpService
{
    public function issue(): array
    {
        $otp = (string) random_int(100000, 999999);
        return ['plain' => $otp, 'hash' => password_hash($otp, PASSWORD_DEFAULT)];
    }

    public function verify(string $otp, string $hash): bool
    {
        return preg_match('/^\d{6}$/', $otp) === 1 && password_verify($otp, $hash);
    }
}
