<?php

/**
 * Plugin Name: RY Invoice for Amego
 * Plugin URI: https://ry-plugin.com/ry-invoice-for-amego
 * Description: Amego E-invoice, support WooCommerce.
 * Version: 2026.7.2
 * Requires at least: 6.8
 * Requires PHP: 8.2
 * Author: Richer Yang
 * Author URI: https://richer.tw/
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Update URI: https://ry-plugin.com/ry-invoice-for-amego
 *
 * Text Domain: ry-invoice-for-amego
 * Domain Path: /languages
 */

defined('ABSPATH') or exit;

define('RY_IFAMEGO_VERSION', '2026.7.2');
define('RY_IFAMEGO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RY_IFAMEGO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RY_IFAMEGO_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('RY_IFAMEGO_PLUGIN_LANGUAGES_DIR', plugin_dir_path(__FILE__) . '/languages');

require_once RY_IFAMEGO_PLUGIN_DIR . 'includes/main.php';

register_activation_hook(__FILE__, ['RY_IFAMEGO', 'plugin_activation']);
register_deactivation_hook(__FILE__, ['RY_IFAMEGO', 'plugin_deactivation']);

function RY_IFAMEGO(): RY_IFAMEGO
{
    return RY_IFAMEGO::instance();
}

RY_IFAMEGO();
