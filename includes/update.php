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
            RY_IFAMEGO::update_option('version', RY_IFAMEGO_VERSION, true);
            return;
        }

        if (version_compare($now_version, '2026.7.4', '<')) {
            RY_IFAMEGO::update_option('version', '2026.7.4', true);
        }
    }
}
