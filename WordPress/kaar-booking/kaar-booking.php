<?php
/**
 * Plugin Name: Kaar Booking Connector
 * Description: Displays Kaar vehicle booking and package interfaces backed by the Joomla Kaar Booking API.
 * Version: 0.1.0
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * License: GPL-2.0-or-later
 */
defined('ABSPATH') || exit;

final class Kaar_Booking_Connector
{
    private const OPTION = 'kaar_booking_settings';

    public static function boot(): void
    {
        add_action('admin_menu', [self::class, 'admin_menu']);
        add_action('admin_init', [self::class, 'register_settings']);
        add_action('wp_enqueue_scripts', [self::class, 'register_assets']);
        add_shortcode('kaar_booking_form', [self::class, 'booking_form']);
        add_shortcode('kaar_packages', [self::class, 'packages']);
    }

    public static function admin_menu(): void
    {
        add_options_page('Kaar Booking', 'Kaar Booking', 'manage_options', 'kaar-booking', [self::class, 'settings_page']);
    }

    public static function register_settings(): void
    {
        register_setting('kaar_booking', self::OPTION, ['type'=>'array','sanitize_callback'=>static function($value): array {
            return ['api_url'=>esc_url_raw(rtrim((string)($value['api_url']??''),'/')),'timeout'=>max(2,min(20,(int)($value['timeout']??8)))];
        }]);
    }

    public static function register_assets(): void
    {
        wp_register_style('kaar-booking', plugins_url('assets/kaar-booking.css', __FILE__), [], '0.1.0');
    }

    public static function settings_page(): void
    {
        if (!current_user_can('manage_options')) return;
        $settings=get_option(self::OPTION,[]); ?>
        <div class="wrap"><h1>Kaar Booking connector</h1><form method="post" action="options.php"><?php settings_fields('kaar_booking'); ?>
        <table class="form-table"><tr><th><label for="kaar-api">Joomla API URL</label></th><td><input id="kaar-api" class="regular-text" type="url" required name="<?php echo esc_attr(self::OPTION); ?>[api_url]" value="<?php echo esc_attr($settings['api_url']??''); ?>" placeholder="https://example.com/api/index.php/v1/kaar"></td></tr><tr><th><label for="kaar-timeout">Timeout</label></th><td><input id="kaar-timeout" type="number" min="2" max="20" name="<?php echo esc_attr(self::OPTION); ?>[timeout]" value="<?php echo (int)($settings['timeout']??8); ?>"></td></tr></table><?php submit_button(); ?></form></div><?php
    }

    private static function get(string $path): array
    {
        $settings=get_option(self::OPTION,[]);$base=rtrim((string)($settings['api_url']??''),'/');if($base==='')return [];
        $key='kaar_'.md5($base.$path);$cached=get_transient($key);if(is_array($cached))return $cached;
        $response=wp_safe_remote_get($base.'/'.$path,['timeout'=>(int)($settings['timeout']??8),'headers'=>['Accept'=>'application/json']]);
        if(is_wp_error($response)||wp_remote_retrieve_response_code($response)!==200)return [];
        $decoded=json_decode(wp_remote_retrieve_body($response),true);$data=is_array($decoded)?($decoded['data']??$decoded):[];if(is_array($data))set_transient($key,$data,5*MINUTE_IN_SECONDS);return is_array($data)?$data:[];
    }

    public static function booking_form(array $atts=[]): string
    {
        wp_enqueue_style('kaar-booking');$settings=get_option(self::OPTION,[]);$base=rtrim((string)($settings['api_url']??''),'/');
        $joomlaBase=preg_replace('~/api/index\.php/v1/kaar$~','',(string)$base);$action=$joomlaBase.'/index.php?option=com_kaarbooking&view=booking';
        ob_start(); ?><div class="kaar-booking"><form class="kaar-booking__form" action="<?php echo esc_url($action); ?>" method="get"><input type="hidden" name="option" value="com_kaarbooking"><input type="hidden" name="view" value="booking"><div class="kaar-booking__fields"><label>From<input name="origin" required></label><label>To<input name="destination" required></label><label>Departure<input type="date" name="departure" min="<?php echo esc_attr(gmdate('Y-m-d',time()+DAY_IN_SECONDS)); ?>" required></label><label>Service<select name="service"><option value="outstation_one_way">One-way</option><option value="outstation_round_trip">Round-trip</option><option value="airport">Airport</option><option value="hourly">Hourly</option><option value="full_day">Full day</option></select></label></div><button class="kaar-booking__primary" type="submit">Continue booking</button></form></div><?php return (string)ob_get_clean();
    }

    public static function packages(array $atts=[]): string
    {
        wp_enqueue_style('kaar-booking');$atts=shortcode_atts(['limit'=>6,'show_details'=>'1'], $atts);$packages=array_slice(self::get('packages'),0,max(1,min(24,(int)$atts['limit'])));$settings=get_option(self::OPTION,[]);$base=preg_replace('~/api/index\.php/v1/kaar$~','',rtrim((string)($settings['api_url']??''),'/'));
        ob_start(); ?><div class="kaar-booking kaar-booking__package-grid"><?php foreach($packages as $package): ?><article class="kaar-booking__package-card"><div class="kaar-booking__package-body"><h3><?php echo esc_html($package['title']??''); ?></h3><p><strong><?php echo (int)($package['duration_days']??1); ?> days / <?php echo (int)($package['duration_nights']??0); ?> nights</strong></p><p><?php echo esc_html($package['summary']??''); ?></p><?php if($atts['show_details']==='1'): ?><details><summary>Show included items</summary><ul><?php foreach(($package['items']??[]) as $item): ?><?php if(($item['item_type']??'')==='included'): ?><li><?php echo esc_html($item['title']??''); ?></li><?php endif; ?><?php endforeach; ?></ul></details><?php endif; ?><p class="kaar-booking__price"><?php echo esc_html(($package['currency']??'INR').' '.number_format((float)($package['base_price']??0),2)); ?></p></div><a class="kaar-booking__primary" href="<?php echo esc_url($base.'/index.php?option=com_kaarbooking&view=booking&package_id='.(int)($package['id']??0)); ?>">Book</a></article><?php endforeach; ?><?php if(!$packages): ?><p>Packages are temporarily unavailable.</p><?php endif; ?></div><?php return (string)ob_get_clean();
    }
}
Kaar_Booking_Connector::boot();
