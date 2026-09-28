<?php defined('_JEXEC') or die; use Joomla\CMS\Router\Route; ?>
<div class="kaar-booking kaar-booking__package-grid">
<?php foreach ($this->packages as $package) : ?>
    <article class="kaar-booking__package-card">
        <div class="kaar-booking__package-body">
            <h2><?php echo htmlspecialchars($package['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <p><strong><?php echo (int) $package['duration_days']; ?> days / <?php echo (int) $package['duration_nights']; ?> nights</strong></p>
            <p><?php echo htmlspecialchars($package['summary'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
            <details><summary>Show package details</summary>
                <ul><?php foreach ($package['items'] as $item) : ?><li><strong><?php echo htmlspecialchars($item['item_type'], ENT_QUOTES, 'UTF-8'); ?>:</strong> <?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></li><?php endforeach; ?></ul>
            </details>
            <p class="kaar-booking__price"><?php echo htmlspecialchars($package['currency'], ENT_QUOTES, 'UTF-8'); ?> <?php echo number_format((float) $package['base_price'], 2); ?></p>
        </div>
        <a class="kaar-booking__primary" href="<?php echo Route::_('index.php?option=com_kaarbooking&view=booking&package_id=' . (int) $package['id']); ?>">Book</a>
    </article>
<?php endforeach; ?>
<?php if (!$this->packages) : ?><p>No packages are currently available.</p><?php endif; ?>
</div>
