<?php

namespace RY\Invoice\Amego\WooCommerce\Admin\Settings;

defined('ABSPATH') or exit;

final class Invoice
{
    private static ?self $_instance = null;

    public static function instance(): Invoice
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
            self::$_instance->do_init();
        }

        return self::$_instance;
    }

    protected function do_init(): void
    {
        add_filter('woocommerce_get_sections_rytools', [$this, 'add_sections'], 11);
        add_filter('woocommerce_get_settings_rytools', [$this, 'add_setting'], 10, 2);
        add_action('woocommerce_update_options_rytools_amego_invoice', [$this, 'check_option']);
    }

    public function add_sections($sections)
    {
        if (isset($sections['tools'])) {
            $add_idx = array_search('tools', array_keys($sections));
            $sections = array_slice($sections, 0, $add_idx) + [
                'amego_invoice' => __('Amego invoice', 'ry-invoice-for-amego'),
            ] + array_slice($sections, $add_idx);
        } else {
            $sections['amego_invoice'] = __('Amego invoice', 'ry-invoice-for-amego');
        }

        return $sections;
    }

    public function add_setting($settings, $current_section)
    {
        if ($current_section === 'amego_invoice') {
            $order_statuses = wc_get_order_statuses();
            $paid_status = [];
            foreach (wc_get_is_paid_statuses() as $status) {
                $paid_status[] = $order_statuses['wc-' . $status];
            }
            $paid_status = implode(', ', $paid_status);

            $settings = [
                [
                    'title' => __('Base options', 'ry-invoice-for-amego'),
                    'id' => 'base_options',
                    'type' => 'title',
                ],
                [
                    'title' => __('Show invoice number', 'ry-invoice-for-amego'),
                    'id' => \RY_IFAMEGO::OPTION_PREFIX . 'show_invoice_number',
                    'type' => 'checkbox',
                    'default' => 'no',
                    'desc' => __('Show invoice number in Frontend order list', 'ry-invoice-for-amego'),
                ],
                [
                    'title' => __('Move billing company', 'ry-invoice-for-amego'),
                    'id' => \RY_IFAMEGO::OPTION_PREFIX . 'move_billing_company',
                    'type' => 'checkbox',
                    'default' => 'no',
                    'desc' => __('Move billing company to invoice area', 'ry-invoice-for-amego'),
                ],
                [
                    'id' => 'base_options',
                    'type' => 'sectionend',
                ],
                [
                    'title' => __('Invoice options', 'ry-invoice-for-amego'),
                    'id' => 'invoice_options',
                    'type' => 'title',
                ],
                [
                    'title' => __('Get mode', 'ry-invoice-for-amego'),
                    'id' => \RY_IFAMEGO::OPTION_PREFIX . 'get_mode',
                    'type' => 'select',
                    'default' => 'manual',
                    'options' => [
                        'manual' => _x('manual', 'get mode', 'ry-invoice-for-amego'),
                        'auto_paid' => _x('auto ( when order paid )', 'get mode', 'ry-invoice-for-amego'),
                        'auto_completed' => _x('auto ( when order completed )', 'get mode', 'ry-invoice-for-amego'),
                    ],
                    'desc' => sprintf(
                        /* translators: %s: paid status */
                        __('Order paid status: %s', 'ry-invoice-for-amego'),
                        $paid_status,
                    ),
                ],
                [
                    'title' => __('Skip foreign orders', 'ry-invoice-for-amego'),
                    'id' => \RY_IFAMEGO::OPTION_PREFIX . 'skip_foreign_order',
                    'type' => 'checkbox',
                    'default' => 'no',
                    'desc' => __('Disable auto get invoice for order billing country and shipping country are not in Taiwan.', 'ry-invoice-for-amego'),
                    'autoload' => false,
                ],
                [
                    'title' => __('Delay time (hours)', 'ry-invoice-for-amego'),
                    'id' => \RY_IFAMEGO::OPTION_PREFIX . 'get_delay_time',
                    'type' => 'number',
                    'default' => '0',
                    'custom_attributes' => [
                        'min' => '0',
                        'max' => '336',
                        'step' => '1',
                    ],
                    'desc' => __('After N hours get invoice.', 'ry-invoice-for-amego')
                        . __('According to WordPress cron job, the actual execution time will be later than the specified time.', 'ry-invoice-for-amego'),
                    'autoload' => false,
                ],
                [
                    'title' => __('Invalid mode', 'ry-invoice-for-amego'),
                    'id' => \RY_IFAMEGO::OPTION_PREFIX . 'invalid_mode',
                    'type' => 'select',
                    'default' => 'manual',
                    'options' => [
                        'manual' => _x('manual', 'invalid mode', 'ry-invoice-for-amego'),
                        'auto_cancel' => _x('auto ( when order status cancelled OR refunded )', 'invalid mode', 'ry-invoice-for-amego'),
                    ],
                ],
                [
                    'title' => __('Trade no prefix', 'ry-invoice-for-amego'),
                    'id' => \RY_IFAMEGO::OPTION_PREFIX . 'prefix',
                    'type' => 'text',
                    'desc' => __('The prefix string of trade no. Only letters and numbers allowed.', 'ry-invoice-for-amego'),
                    'desc_tip' => true,
                    'autoload' => false,
                ],
                [
                    'title' => __('Custom track code', 'ry-invoice-for-amego'),
                    'id' => \RY_IFAMEGO::OPTION_PREFIX . 'trackcode',
                    'type' => 'text',
                    'default' => '',
                    'autoload' => false,
                ],
                [
                    'id' => 'invoice_options',
                    'type' => 'sectionend',
                ],
            ];
        }

        return $settings;
    }

    public function check_option()
    {
        if (!preg_match('/^[a-z0-9]{0,3}$/i', \RY_IFAMEGO::get_option('prefix', ''))) {
            \WC_Admin_Settings::add_error(__('Trade no prefix only letters and numbers allowed, and maximum length is 3 characters.', 'ry-invoice-for-amego'));
            \RY_IFAMEGO::update_option('prefix', '', false);
        }
    }
}
