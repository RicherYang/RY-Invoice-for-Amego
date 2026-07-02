<?php

defined('ABSPATH') or exit;

include_once RY_IFAMEGO_PLUGIN_DIR . 'includes/ry-general/abstract-basic.php';

final class RY_IFAMEGO extends RY_Abstract_Basic
{
    public const OPTION_PREFIX = 'RY_IFAMEGO_';

    public const PLUGIN_NAME = 'RY Invoice for Amego';

    protected static ?self $_instance = null;

    public static function instance(): RY_IFAMEGO
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
            self::$_instance->do_init();
        }

        return self::$_instance;
    }

    protected function do_init(): void
    {
        load_plugin_textdomain('ry-invoice-for-amego', false, plugin_basename(dirname(__DIR__)) . '/languages');
        include_once RY_IFAMEGO_PLUGIN_DIR . 'includes/composer/vendor/woocommerce/action-scheduler/action-scheduler.php';

        if (is_admin()) {
            include_once RY_IFAMEGO_PLUGIN_DIR . 'includes/update.php';
            RY_IFAMEGO_Update::update();
        }

        add_action('init', [$this, 'do_wp_init'], 11);
    }

    public function do_wp_init(): void
    {
        include_once RY_IFAMEGO_PLUGIN_DIR . 'includes/ry-general/logs.php';
        RY_Logs::set_log(RY_IFAMEGO::get_option('log', 'no') === 'yes');

        include_once RY_IFAMEGO_PLUGIN_DIR . 'includes/functions.php';
        include_once RY_IFAMEGO_PLUGIN_DIR . 'includes/license.php';
        include_once RY_IFAMEGO_PLUGIN_DIR . 'includes/link-server.php';
        include_once RY_IFAMEGO_PLUGIN_DIR . 'includes/updater.php';
        RY_IFAMEGO_Updater::instance();

        if (is_admin()) {
            include_once RY_IFAMEGO_PLUGIN_DIR . 'includes/ry-paid/admin-license.php';
            include_once RY_IFAMEGO_PLUGIN_DIR . 'includes/ry-general/admin-logs.php';
            include_once RY_IFAMEGO_PLUGIN_DIR . 'admin/admin.php';
            RY_IFAMEGO_Admin::instance();
        }

        if (RY_IFAMEGO_License::instance()->is_activated()) {
            include_once RY_IFAMEGO_PLUGIN_DIR . 'includes/abstracts/abstract-amego.php';
            include_once RY_IFAMEGO_PLUGIN_DIR . 'includes/invoice.php';

            include_once RY_IFAMEGO_PLUGIN_DIR . 'includes/cron.php';
            RY_IFAMEGO_Cron::add_action();
        }

        if (has_action('woocommerce_init')) {
            include_once RY_IFAMEGO_PLUGIN_DIR . 'woocommerce/invoice-basic.php';
            RY_IFAMEGO_WC_Invoice_Basic::instance();

            if (RY_IFAMEGO_License::instance()->is_activated()) {
                include_once RY_IFAMEGO_PLUGIN_DIR . 'woocommerce/invoice.php';
                RY_IFAMEGO_WC_Invoice::instance();
            }
        }
    }

    public static function plugin_activation() {}

    public static function plugin_deactivation()
    {
        wp_unschedule_hook(self::OPTION_PREFIX . 'check_expire');
    }
}
