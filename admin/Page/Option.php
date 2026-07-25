<?php

namespace RY\Invoice\Amego\Admin\Page;

defined('ABSPATH') or exit;

use RY\General\V20260724\AbstractAdminPage;

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
        echo '<form method="post" action="admin-post.php">';
        echo '<input type="hidden" name="action" value="ry-invoice-amego-option">';
        wp_nonce_field('ry-invoice-amego-option');
        include __DIR__ . '/html/option.php';
        submit_button();
        echo '</form>';
    }

    public function do_admin_action(string $action): void
    {
        if ('ry-invoice-amego-option' !== $action) {
            return;
        }

        if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'ry-invoice-amego-option')) {
            wp_die('Invalid nonce');
        }

        $log = sanitize_locale_name($_POST['log'] ?? '') === 'yes' ? 'yes' : 'no';
        \RY_IFAMEGO::update_option('log', $log);
        $api_info = [
            'testmode' => sanitize_locale_name($_POST['testmode'] ?? '') === 'yes' ? 'yes' : 'no',
            'invoice' => sanitize_locale_name($_POST['invoice'] ?? ''),
            'AppKey' => sanitize_locale_name($_POST['AppKey'] ?? ''),
        ];
        \RY_IFAMEGO::update_option('apiinfo', $api_info, false);
        $this->add_notice('success', __('Settings saved.', 'ry-invoice-for-amego'));

        wp_safe_redirect(admin_url('admin.php?page=ry-invoice&type=amego-option'));
    }
}
