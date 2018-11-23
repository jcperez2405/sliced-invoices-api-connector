<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.topfloormarketing.net
 * @since      1.0.0
 *
 * @package    Sliced_Invoices_Api_Connector
 * @subpackage Sliced_Invoices_Api_Connector/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Sliced_Invoices_Api_Connector
 * @subpackage Sliced_Invoices_Api_Connector/includes
 * @author     JC Perez <jn.perez@topfloormarketing.net>
 */
class Sliced_Invoices_Api_Connector_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'sliced-invoices-api-connector',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
