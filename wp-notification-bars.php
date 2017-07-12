<?php

/**
 * @wordpress-plugin
 * Plugin Name:       WP Notification Bars
 * Plugin URI:        https://mythemeshop.com/plugins/wp-notification-bars/
 * Description:       WP Notification Bars is a custom notification and alert bar plugin for WordPress which is perfect for marketing promotions, alerts, increasing click throughs to other pages and so much more.
 * Version:           1.0.1
 * Author:            MyThemeShop
 * Author URI:        https://mythemeshop.com/
 * Text Domain:       wp-notification-bars
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Plugin directory
if ( !defined( 'MTSNBF_PLUGIN_DIR') )
    define( 'MTSNBF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

if ( !defined( 'MTSNBF_PLUGIN_URL') )
    define( 'MTSNBF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

if ( !defined( 'MTSNBF_PLUGIN_BASE') )
    define( 'MTSNBF_PLUGIN_BASE', plugin_basename(__FILE__) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-notification-bars-activator.php
 */
function activate_mts_notification_bar_f() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-notification-bars-activator.php';
	MTSNBF_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-notification-bars-deactivator.php
 */
function deactivate_mts_notification_bar_f() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-notification-bars-deactivator.php';
	MTSNBF_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_mts_notification_bar_f' );
register_deactivation_hook( __FILE__, 'deactivate_mts_notification_bar_f' );

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-notification-bars.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0
 */
function run_mts_notification_bar_f() {

	$plugin = new MTSNBF();
	$plugin->run();

}
run_mts_notification_bar_f();