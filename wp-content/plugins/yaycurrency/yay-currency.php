<?php

/**
 * Plugin Name:       YayCurrency
 * Plugin URI:        https://yaycommerce.com/yaycurrency-woocommerce-multi-currency-switcher/
 * Description:       Provide multiple currencies for WooCommerce. Let your potential customers switch currency on the go.
 * Version:           1.6.5
 * Author:            YayCommerce
 * Author URI:        https://yaycommerce.com
 * Text Domain:       yay-currency
 * Domain Path:       /languages
 * Requires at least: 4.7
 * Requires PHP:      5.4
 * WC requires at least: 3.0.0
 * WC tested up to: 5.8.0
 *
 * @package yaycommerce/yaycurrency
 */

namespace Yay_Currency;

defined( 'ABSPATH' ) || exit;

if ( function_exists( 'Yay_Currency\\plugin_init' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/Fallback.php';
	add_action(
		'admin_init',
		function() {
			deactivate_plugins( plugin_basename( __FILE__ ) );
		}
	);
}


if ( ! defined( 'YAY_CURRENCY_VERSION' ) ) {
	define( 'YAY_CURRENCY_VERSION', '1.6.5' );
}
if ( ! defined( 'YAY_CURRENCY_PLUGIN_URL' ) ) {
	define( 'YAY_CURRENCY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

}
if ( ! defined( 'YAY_CURRENCY_PLUGIN_DIR' ) ) {
	define( 'YAY_CURRENCY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

}
if ( ! defined( 'YAY_CURRENCY_BASE_NAME' ) ) {
	define( 'YAY_CURRENCY_BASE_NAME', plugin_basename( __FILE__ ) );
}

spl_autoload_register(
	function ( $class ) {
		$prefix   = __NAMESPACE__; // project-specific namespace prefix
		$base_dir = __DIR__ . '/includes'; // base directory for the namespace prefix

		$len = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 ) { // does the class use the namespace prefix?
			return; // no, move to the next registered autoloader
		}

		$relative_class_name = substr( $class, $len );

		// replace the namespace prefix with the base directory, replace namespace
		// separators with directory separators in the relative class name, append
		// with .php
		$file = $base_dir . str_replace( '\\', '/', $relative_class_name ) . '.php';

		if ( file_exists( $file ) ) {
			require $file;
		}
	}
);

if ( ! function_exists( 'Yay_Currency\\plugin_init' ) ) {

	function plugin_init() {
		Plugin::activate();
		Settings::getInstance();
		WooCommerceCurrency::getInstance();
		Ajax::getInstance();
		PostType::getInstance();
		Widget::getInstance();
		SingleProductDropdown::getInstance();
		MenuDropdown::getInstance();
		Shortcode::getInstance();
		MenuShortcode::getInstance();
		I18n::loadPluginTextdomain();
		WooCommerceFilterReport::getInstance();
		WooCommerceFilterAnalytics::getInstance();
		if ( function_exists( 'register_block_type' ) ) {
			require_once YAY_CURRENCY_PLUGIN_DIR . 'blocks/init.php';
		}
	}
}

add_action( 'plugins_loaded', 'Yay_Currency\\plugin_init' );

register_activation_hook( __FILE__, array( 'Yay_Currency\\Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Yay_Currency\\Plugin', 'deactivate' ) );
