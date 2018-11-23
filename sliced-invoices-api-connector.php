<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.topfloormarketing.net
 * @since             1.0.0
 * @package           Sliced_Invoices_Api_Connector
 *
 * @wordpress-plugin
 * Plugin Name:       Sliced Invoices API Connector
 * Plugin URI:        http://www.thinkinggreenlands.com/
 * Description:       Extends the features of Sliced Invoice plugin to be reachable by external apps via WP native REST API.
 * Version:           1.0.0
 * Author:            JC Perez
 * Author URI:        https://www.topfloormarketing.net
 * License:           GNU GPLv3
 * License URI:       https://spdx.org/licenses/GPL-3.0.html
 * Text Domain:       sliced-invoices-api-connector
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PLUGIN_NAME_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-sliced-invoices-api-conector-activator.php
 */
function activate_sliced_invoices_api_connector() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sliced-invoices-api-connector-activator.php';
	Sliced_Invoices_Api_Connector_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-sliced-invoices-api-connector-deactivator.php
 */
function deactivate_sliced_invoices_api_connector() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sliced-invoices-api-connector-deactivator.php';
	Sliced_Invoices_Api_Connector_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_sliced_invoices_api_connector' );
register_deactivation_hook( __FILE__, 'deactivate_sliced_invoices_api_connector' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-quote.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-invoice.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_sliced_invoices_api_connector() {

    $controllers = array(
        'Quote',
        'Invoice',
    );

    foreach ( $controllers as $controller ) {
        new $controller();
        //$this->$controller->register_routes();
    }

}
run_sliced_invoices_api_connector();
