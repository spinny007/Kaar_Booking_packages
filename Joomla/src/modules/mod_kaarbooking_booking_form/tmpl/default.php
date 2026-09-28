<?php
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
$services = [];
if ($params->get('show_one_way',1)) $services['outstation_one_way']='One-way';
if ($params->get('show_round_trip',1)) $services['outstation_round_trip']='Round-trip';
if ($params->get('show_airport',1)) $services['airport']='Airport';
if ($params->get('show_hourly',1)) $services['hourly']='Hourly';
if ($params->get('show_day',1)) { $services['half_day']='Half day'; $services['full_day']='Full day'; }
$tomorrow=(new DateTimeImmutable('tomorrow'))->format('Y-m-d\T09:00');
$rootId='kaar-booking-form-'.$module->id;
?>
<div id="<?php echo $rootId; ?>" class="kaar-booking kaar-booking__search kaar-booking__search--<?php echo htmlspecialchars($params->get('layout_mode','horizontal'),ENT_QUOTES,'UTF-8'); ?>" data-kaar-booking style="--kaar-align:<?php echo htmlspecialchars($params->get('alignment','inherit'),ENT_QUOTES,'UTF-8'); ?>">
<form action="<?php echo Route::_('index.php?option=com_kaarbooking&task=booking.submit'); ?>" method="post" class="kaar-booking__form">
<fieldset class="kaar-booking__services"><legend class="visually-hidden">Service type</legend><?php $first=true; foreach($services as $value=>$label): ?><label><input type="radio" name="service_type" value="<?php echo $value; ?>" <?php echo $first?'checked':''; $first=false; ?>> <span><?php echo $label; ?></span></label><?php endforeach; ?></fieldset>
<div class="kaar-booking__fields"><label>From<input name="origin_text" type="text" required></label><button class="kaar-booking__swap" type="button" data-kaar-swap aria-label="Swap origin and destination">⇄</button><label>To<input name="destination_text" type="text" required></label><label>Departure<input name="departure_at" type="datetime-local" min="<?php echo $tomorrow; ?>" required></label><label data-kaar-return>Return<input name="return_at" type="datetime-local" min="<?php echo $tomorrow; ?>"></label><label>Pickup passengers<input name="passengers" type="number" min="1" value="1" required></label><label>Vehicle<select name="vehicle_type_id"><option value="">Any eligible vehicle</option><?php foreach($vehicleTypes as $type): ?><option value="<?php echo (int)$type['id']; ?>"><?php echo htmlspecialchars($type['title'],ENT_QUOTES,'UTF-8'); ?></option><?php endforeach; ?></select></label></div>
<details><summary>Contact details</summary><div class="kaar-booking__fields"><label>Name<input name="customer_name" required></label><label>Email<input name="customer_email" type="email" required></label><label>Phone<input name="customer_phone" type="tel"></label><label>Mode<select name="driver_mode"><option value="with_driver">With driver</option><option value="self_drive">Without driver</option></select></label></div></details>
<p class="kaar-booking__notice">Scheduled bookings are manually reviewed and confirmed.</p><button type="submit" class="kaar-booking__primary">Request booking</button><?php echo HTMLHelper::_('form.token'); ?>
</form></div>
