<?php

defined('ABSPATH') or exit;

include_once RY_IFAMEGO_PLUGIN_DIR . 'includes/ry-paid/abstract-link-server.php';

final class RY_IFAMEGO_LinkServer extends RY_Abstract_Link_Server
{
    protected static ?self $_instance = null;

    protected string $plugin_slug = 'ry-invoice-for-amego';

    public static function instance(): RY_IFAMEGO_LinkServer
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    protected function get_base_info(): array
    {
        return [
            'plugin' => RY_IFAMEGO_VERSION,
            'php' => PHP_VERSION,
            'wp' => get_bloginfo('version'),
        ];
    }

    protected function get_user_agent()
    {
        return sprintf(
            'RY_IFAMEGO %s (WordPress/%s)',
            RY_IFAMEGO_VERSION,
            get_bloginfo('version'),
        );
    }
}
