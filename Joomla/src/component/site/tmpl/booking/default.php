<?php
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
$tomorrow = (new DateTimeImmutable('tomorrow'))->format('Y-m-d\T09:00');
?>
<div class="kaar-booking kaar-booking__search" data-kaar-booking>
    <form action="<?php echo Route::_('index.php?option=com_kaarbooking&task=booking.submit'); ?>" method="post" class="kaar-booking__form">
        <fieldset class="kaar-booking__services"><legend class="visually-hidden">Service type</legend>
            <?php foreach (['outstation_one_way'=>'One-way','outstation_round_trip'=>'Round-trip','airport'=>'Airport','hourly'=>'Hourly','half_day'=>'Half day','full_day'=>'Full day'] as $value=>$label) : ?>
                <label><input type="radio" name="service_type" value="<?php echo $value; ?>" <?php echo $value === 'outstation_one_way' ? 'checked' : ''; ?>> <span><?php echo $label; ?></span></label>
            <?php endforeach; ?>
        </fieldset>
        <div class="kaar-booking__fields">
            <label>From<input name="origin_text" type="text" autocomplete="street-address" required></label>
            <button class="kaar-booking__swap" type="button" data-kaar-swap aria-label="Swap origin and destination">⇄</button>
            <label>To<input name="destination_text" type="text" autocomplete="street-address" required></label>
            <label>Departure<input name="departure_at" type="datetime-local" min="<?php echo $tomorrow; ?>" required></label>
            <label data-kaar-return>Return<input name="return_at" type="datetime-local" min="<?php echo $tomorrow; ?>"></label>
            <label>Passengers<input name="passengers" type="number" min="1" max="100" value="1" required></label>
            <label>Vehicle type<select name="vehicle_type_id"><option value="">Any eligible type</option><?php foreach ($this->vehicleTypes as $type) : ?><option value="<?php echo (int) $type['id']; ?>"><?php echo htmlspecialchars($type['title'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo (int) $type['passenger_capacity']; ?>)</option><?php endforeach; ?></select></label>
        </div>
        <details><summary>Customer and additional details</summary><div class="kaar-booking__fields">
            <label>Name<input name="customer_name" type="text" autocomplete="name" required></label>
            <label>Email<input name="customer_email" type="email" autocomplete="email" required></label>
            <label>Phone<input name="customer_phone" type="tel" autocomplete="tel"></label>
            <label>Booking mode<select name="driver_mode"><option value="with_driver">With driver</option><option value="self_drive">Without driver</option></select></label>
        </div></details>
        <p class="kaar-booking__notice">Your request will be reviewed and confirmed manually by our booking team.</p>
        <button class="kaar-booking__primary" type="submit">Request booking</button>
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
