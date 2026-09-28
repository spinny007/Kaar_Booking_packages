<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$labels = [
    'pending_admin_review' => 'Awaiting review',
    'paid_pending_confirmation' => 'Paid, awaiting confirmation',
    'confirmed' => 'Confirmed',
    'completed' => 'Completed',
    'cancelled' => 'Cancelled',
    'captured_value' => 'Captured value',
];
?>
<div class="container-fluid kaar-booking kaar-booking--admin">
    <h1><?php echo Text::_('COM_KAARBOOKING_DASHBOARD_TITLE'); ?></h1>
    <div class="row g-3" role="list">
        <?php foreach ($this->metrics as $key => $value) : ?>
            <div class="col-12 col-sm-6 col-xl-2" role="listitem">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted"><?php echo htmlspecialchars($labels[$key] ?? $key, ENT_QUOTES, 'UTF-8'); ?></div>
                    <strong class="fs-3"><?php echo htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?></strong>
                </div></div>
            </div>
        <?php endforeach; ?>
    </div>
    <h2 class="mt-4">Manual action queue</h2>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead><tr><th>Reference</th><th>Customer</th><th>Product</th><th>Departure</th><th>Status</th><th>Payment</th><th>Amount</th></tr></thead>
            <tbody>
            <?php foreach ($this->queue as $item) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['reference'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($item['customer_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($item['product_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($item['departure_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($item['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($item['payment_status'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars(number_format((float) $item['total_amount'], 2), ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$this->queue) : ?><tr><td colspan="7">No bookings need action.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
