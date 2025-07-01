<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.quanticedgesolutions.com
 * @since      1.0.0
 *
 * @package    WPCD_Category_Discount
 * @subpackage WPCD_Category_Discount/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    WPCD_Category_Discount
 * @subpackage WPCD_Category_Discount/includes
 * @author     QuanticEdge <info@quanticedge.co.in>
 */
class WPCD_Category_Discount_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'wpcd-category-discount',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
