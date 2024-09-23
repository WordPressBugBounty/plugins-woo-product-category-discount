<?php
/*
 * Plugin Name: Category Discount Woocommerce
 * Author: QuanticEdge
 * Author URI: https://quanticedgesolutions.com/?utm-source=free-plugin&utm-medium=wooextend
 * Version: 4.14
 * Requires at least: 4.0
 * Tested up to: 6.4
 * Description: "Category Discount Woocommerce" lets you apply discount on product based on Woocommerce category product categories.
 * WC tested up to: 8.2.1
 */

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    
    define('WPCD_VER', '4.14');
    require_once ('cd-admin.php');

}

?>