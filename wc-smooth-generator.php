<?php
/**
 * Plugin Name: WooCommerce Smooth Generator
 * Plugin URI: https://woocommerce.com
 * Description: A smooth product, order, customer, and coupon generator for WooCommerce.
 * Version: 1.3.0
 * Author: Automattic
 * Author URI: https://woocommerce.com
 *
 * Tested up to: 6.9
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * WC requires at least: 5.0.0
 * WC tested up to: 10.5
 * Woo: 000000:0a0a0a0a0a0a0a0a0a0a0a0a0a0a0a0
 *
 * @package WooCommerce
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load the plugin autoloader.
 */
function wc_smooth_generator_bootstrap_autoloader() {
	static $autoloaded = false;

	if ( $autoloaded ) {
		return;
	}

	$composer_autoloader = __DIR__ . '/vendor/autoload.php';

	if ( file_exists( $composer_autoloader ) ) {
		require_once $composer_autoloader;
		$autoloaded = true;

		return;
	}

	spl_autoload_register( 'wc_smooth_generator_autoload' );
	$autoloaded = true;
}

/**
 * Fallback autoloader for plugin classes when Composer artifacts are unavailable.
 *
 * @param string $class_name Fully qualified class name.
 */
function wc_smooth_generator_autoload( $class_name ) {
	$namespace = 'WC\\SmoothGenerator\\';

	if ( 0 !== strpos( $class_name, $namespace ) ) {
		return;
	}

	$relative_class = substr( $class_name, strlen( $namespace ) );
	$class_file     = __DIR__ . '/includes/' . str_replace( '\\', '/', $relative_class ) . '.php';

	if ( file_exists( $class_file ) ) {
		require_once $class_file;
	}
}

if ( ! class_exists( \WC\SmoothGenerator\Plugin::class, false ) ) {
	wc_smooth_generator_bootstrap_autoloader();
}

/**
 * Fetch instance of plugin.
 *
 * @return \WC\SmoothGenerator\Plugin
 */
function wc_smooth_generator() {
	static $instance;

	if ( is_null( $instance ) ) {
		$instance = new \WC\SmoothGenerator\Plugin( __FILE__ );
	}

	return $instance;
}

/**
 * Init plugin when WordPress loads.
 */
function load_wc_smooth_generator() {
	wc_smooth_generator();
}

if ( version_compare( PHP_VERSION, '7.4', '>=' ) ) {
	add_action( 'plugins_loaded', 'load_wc_smooth_generator', 20 );
}

/**
 * Declare HPOS compatibility.
 */
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

/**
 * Show action links on the plugin screen.
 *
 * @param array $links Plugin Action links.
 *
 * @return array
 */
function wc_smooth_generator_plugin_action_links( $links ) {
	$action_links = array(
		'settings' => '<a href="' . esc_url( admin_url( 'tools.php?page=smoothgenerator' ) ) . '" aria-label="' . esc_attr__( 'View WooCommerce Smooth Generator settings', 'wc-smooth-generator' ) . '">' . esc_html__( 'Settings', 'wc-smooth-generator' ) . '</a>',
	);

	return array_merge( $action_links, $links );
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_smooth_generator_plugin_action_links' );
