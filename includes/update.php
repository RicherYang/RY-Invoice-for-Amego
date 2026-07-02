<?php

defined('ABSPATH') or exit;

final class RY_IFAMEGO_Update
{
    public static function update()
    {
        $now_version = RY_IFAMEGO::get_option('version', '0.0.0');

        if (RY_IFAMEGO_VERSION === $now_version) {
            return;
        }

        if ($now_version === '0.0.0') {
            $api_info = get_option('RY_WAI_apiinfo');
            if (is_array($api_info)) {
                RY_IFAMEGO::update_option('general', [
                    'abnormal_mode' => $api_info['abnormal_mode'] ?? '',
                    'abnormal_product' => $api_info['abnormal_product'] ?? '',
                ], false);
                RY_IFAMEGO::update_option('apiinfo', [
                    'testmode' => ($api_info['testmode'] ?? '') === 'yes' ? 'yes' : 'no',
                    'invoice' => $api_info['invoice'] ?? '',
                    'AppKey' => $api_info['AppKey'] ?? '',
                ], false);
                RY_IFAMEGO::update_option('prefix', $api_info['prefix'] ?? '', false);
                RY_IFAMEGO::update_option('trackcode', $api_info['trackcode'] ?? '', false);
            }
            foreach (['show_invoice_number', 'move_billing_company', 'get_mode', 'invalid_mode'] as $option_name) {
                $option_value = get_option('RY_WAI_' . $option_name);
                if ($option_value) {
                    if ($option_name === 'invalid_mode' && $option_value === 'auto_cancell') {
                        $option_value = 'auto_cancel';
                    }
                    RY_IFAMEGO::update_option($option_name, $option_value);
                }
            }
            foreach (['skip_foreign_order'] as $option_name) {
                $option_value = get_option('RY_WAI_' . $option_name);
                if ($option_value) {
                    RY_IFAMEGO::update_option($option_name, $option_value, false);
                }
            }

            RY_IFAMEGO::update_option('version', RY_IFAMEGO_VERSION, true);
            return;
        }

        if (version_compare($now_version, '2026.7.2', '<')) {
            RY_IFAMEGO::update_option('version', RY_IFAMEGO_VERSION, true);
        }
    }
}
