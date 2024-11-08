<?php
/*
 * Plugin Name: Category Discount Woocommerce
 * Author: QuanticEdge
 * Author URI: https://quanticedgesolutions.com/?utm-source=free-plugin&utm-medium=wooextend
 * Version: 4.15
 * Requires at least: 4.0
 * Tested up to: 6.6.2
 * Description: "Category Discount Woocommerce" lets you apply discount on product based on Woocommerce category product categories.
 * WC tested up to: 9.3.3
 */

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    
    define('WPCD_VER', '4.15');
    require_once ('cd-admin.php');

}

?>