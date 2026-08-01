<?php

namespace RY\Invoice\Amego\Admin\Page;

defined('ABSPATH') or exit;

use RY\General\V20260801\AbstractAdminPage;
use RY\General\V20260801\Utils;
use RY\Invoice\Amego\Main;

final class Option extends AbstractAdminPage
{
    public static function init_menu(): void
    {
        add_filter('ry_invoice-navs', [__CLASS__, 'add_nav']);
        add_action('ry_invoice-show_page-amego-option', [__CLASS__, 'pre_show_page']);
        add_action('admin_post_ry-invoice-amego-option', [__CLASS__, 'admin_action']);
    }

    public static function add_nav(array $navs): array
    {
        $navs[] = [
            'name' => __('Amego options', 'ry-invoice-for-amego'),
            'type' => 'amego-option',
        ];

        return $navs;
    }

    protected function do_init(): void {}

    public function output_page(): void
    {
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        include __DIR__ . '/html/option.php';
        Utils::the_action_form_button('invoice-amego-option', 'save-option', __('Save Changes', 'ry-invoice-for-amego'), 'submit', 'button-primary');
        echo '</form>';
    }

    protected function do_admin_action(string $action, string $real_action): void
    {
        if ('ry-invoice-amego-option' !== $action) {
            return;
        }

        if ($real_action !== '' && is_callable([$this, $real_action])) {
            $this->$real_action();
        }

        wp_safe_redirect(admin_url('admin.php?page=ry-invoice&type=amego-option'));
        exit;
    }

    private function save_option(): void
    {
        check_ajax_referer('save-option', '_ajax_nonce');

        Main::update_option('log', Utils::bool_to_string($_POST['log'] ?? ''));
        $api_info = [
            'testmode' => Utils::bool_to_string($_POST['testmode'] ?? ''),
            'invoice' => sanitize_locale_name($_POST['invoice'] ?? ''),
            'AppKey' => sanitize_locale_name($_POST['AppKey'] ?? ''),
        ];
        Main::update_option('apiinfo', $api_info, false);
        $this->add_notice('success', __('Settings saved.', 'ry-invoice-for-amego'));
    }
}
