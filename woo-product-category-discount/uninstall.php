<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://www.quanticedgesolutions.com
 * @since      1.0.0
 *
 * @package    Wpcd_Category_Discount
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if( !defined( 'WPCD_REMOVE_TABLES') ){
	exit;
}

global $wpdb;

// Drop tables
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wpcd_discounts" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wpcd_taxonomy_discount_terms" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wpcd_cart_discount_rules" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wpcd_cart_discount_rules_products" );