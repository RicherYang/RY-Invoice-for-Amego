<?php

defined('ABSPATH') or exit;

final class RY_IFAMEGO_Admin_Page_Option extends RY_Abstract_Admin_Page
{
    protected static $_instance = null;

    public static function init_menu(): void
    {
        add_filter('ry-invoice-navs', [__CLASS__, 'add_nav']);
        add_submenu_page('', __('Amego options', 'ry-invoice-for-amego'), '', 'manage_options', 'ry-invoice-amego-option', [__CLASS__, 'pre_show_page']);
        add_action('load-admin_page_ry-invoice-amego-option', [__CLASS__, 'instance']);
        add_action('admin_post_ry-invoice-amego-option', [__CLASS__, 'admin_action']);
    }

    public static function add_nav(array $navs): array
    {
        $navs[] = [
            'name' => __('Amego options', 'ry-invoice-for-amego'),
            'slug' => 'ry-invoice-amego-option',
        ];

        return $navs;
    }

    protected function do_init(): void
    {
        global $_wp_menu_nopriv, $_wp_real_parent_file, $submenu_file;

        if ($_wp_menu_nopriv) {
            $_wp_menu_nopriv['ry-invoice-amego-option'] = true;
            $_wp_real_parent_file['ry-invoice-amego-option'] = RY_IFAMEGO_Admin::instance()->main_slug;
            $submenu_file = 'ry-invoice';
        }
    }

    public function output_page(): void
    {
        echo '<div class="wrap">';

        $show_type = 'ry-invoice-amego-option';
        include RY_IFAMEGO_PLUGIN_DIR . 'admin/page/html/nav.php';

        echo '<form method="post" action="admin-post.php">';
        echo '<input type="hidden" name="action" value="ry-invoice-amego-option">';
        wp_nonce_field('ry-invoice-amego-option');
        include RY_IFAMEGO_PLUGIN_DIR . 'admin/page/html/option.php';
        submit_button();
        echo '</form>';

        echo '</div>';
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
        RY_IFAMEGO::update_option('log', $log);
        $api_info = [
            'testmode' => sanitize_locale_name($_POST['testmode'] ?? '') === 'yes' ? 'yes' : 'no',
            'invoice' => sanitize_locale_name($_POST['invoice'] ?? ''),
            'AppKey' => sanitize_locale_name($_POST['AppKey'] ?? ''),
        ];
        RY_IFAMEGO::update_option('apiinfo', $api_info, false);
        $this->add_notice('success', __('Settings saved.', 'ry-invoice-for-amego'));

        wp_safe_redirect(admin_url('admin.php?page=ry-invoice-amego-option'));
    }
}

RY_IFAMEGO_Admin_Page_Option::init_menu();
