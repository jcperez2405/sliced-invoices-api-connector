<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.topfloormarketing.net
 * @since      1.0.0
 *
 * @package    Sliced_Invoices_Api_Connector
 * @subpackage Sliced_Invoices_Api_Connector/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Sliced_Invoices_Api_Connector
 * @subpackage Sliced_Invoices_Api_Connector/includes
 * @author     JC Perez <jn.perez@topfloormarketing.net>
 */
class Sliced_Invoices_Api_Connector_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
        if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            die('WooCommerce plugin is required.');
        }

        if ( ! in_array( 'sliced-invoices/sliced-invoices.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            die('Sliced Invoices plugin is required.');
        }
	}

}
