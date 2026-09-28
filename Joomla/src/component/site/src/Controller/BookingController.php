<?php
namespace KaarBooking\Component\KaarBooking\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use KaarBooking\Component\KaarBooking\Administrator\Domain\Booking\BookingReference;

final class BookingController extends BaseController
{
    public function submit(): void
    {
        Session::checkToken('post') or jexit(Text::_('JINVALID_TOKEN'));
        $app = $this->app;
        $input = $app->getInput();

        try {
            $departure = new \DateTimeImmutable($input->post->getString('departure_at'), new \DateTimeZone('UTC'));
            $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
            if ($departure <= $now) {
                throw new \DomainException('Departure must be in the future.');
            }
            $returnRaw = trim($input->post->getString('return_at'));
            $return = $returnRaw === '' ? null : new \DateTimeImmutable($returnRaw, new \DateTimeZone('UTC'));
            if ($return && $return <= $departure) {
                throw new \DomainException('Return must be after departure.');
            }

            $serviceTypes = ['outstation_one_way', 'outstation_round_trip', 'airport', 'hourly', 'half_day', 'full_day', 'tour_package'];
            $serviceType = $input->post->getCmd('service_type');
            if (!in_array($serviceType, $serviceTypes, true)) {
                throw new \DomainException('Invalid service type.');
            }

            $name = trim($input->post->getString('customer_name'));
            $email = trim($input->post->getString('customer_email'));
            if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \DomainException('A valid customer name and email are required.');
            }

            $db = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);
            $reference = BookingReference::generate($now);
            $row = (object) [
                'reference' => $reference, 'source' => 'website', 'product_type' => $serviceType === 'tour_package' ? 'tour_package' : 'vehicle',
                'service_type' => $serviceType, 'customer_id' => null, 'customer_name' => $name, 'customer_email' => $email,
                'customer_phone' => trim($input->post->getString('customer_phone')), 'package_id' => $input->post->getInt('package_id') ?: null,
                'vehicle_type_id' => $input->post->getInt('vehicle_type_id') ?: null,
                'origin_text' => trim($input->post->getString('origin_text')), 'destination_text' => trim($input->post->getString('destination_text')),
                'stops_json' => json_encode(array_values(array_filter($input->post->get('stops', [], 'array'))), JSON_THROW_ON_ERROR),
                'departure_at' => $departure->format('Y-m-d H:i:s'), 'return_at' => $return?->format('Y-m-d H:i:s'),
                'passengers' => max(1, $input->post->getInt('passengers', 1)),
                'driver_mode' => $input->post->getCmd('driver_mode', 'with_driver') === 'self_drive' ? 'self_drive' : 'with_driver',
                'status' => 'pending_admin_review', 'payment_status' => 'unpaid', 'pricing_snapshot' => null,
                'total_amount' => 0, 'currency' => 'INR', 'created_by' => (int) $app->getIdentity()->id,
                'created' => $now->format('Y-m-d H:i:s'), 'modified_by' => 0, 'version' => 1,
            ];
            $db->insertObject('#__kaar_bookings', $row);
            $app->enqueueMessage(Text::sprintf('COM_KAARBOOKING_REQUEST_RECEIVED', $reference), 'success');
        } catch (\Throwable $error) {
            $app->enqueueMessage($error->getMessage(), 'error');
        }

        $this->setRedirect(Route::_('index.php?option=com_kaarbooking&view=booking', false));
    }
}
