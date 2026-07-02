<?php

defined('ABSPATH') or exit;

final class RY_IFAMEGO_Updater
{
    protected static ?self $_instance = null;

    public static function instance(): RY_IFAMEGO_Updater
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
            self::$_instance->do_init();
        }

        return self::$_instance;
    }

    protected function do_init(): void
    {
        add_filter('update_plugins_ry-plugin.com', [$this, 'update_plugin'], 10, 2);

        add_filter('plugins_api', [$this, 'modify_plugin_details'], 10, 3);
    }

    public function update_plugin($update, $plugin_data)
    {
        if (RY_IFAMEGO::PLUGIN_NAME !== $plugin_data['Name']) {
            return $update;
        }

        return RY_IFAMEGO_LinkServer::instance()->check_version();
    }

    public static function modify_plugin_details($result, $action, $args)
    {
        if ($action !== 'plugin_information') {
            return $result;
        }

        if ($args->slug !== 'ry-invoice-for-amego') {
            return $result;
        }

        $response = RY_Ifamego_LinkServer::instance()->get_info();
        if (!empty($response)) {
            return (object) $response;
        }

        return $result;
    }
}
