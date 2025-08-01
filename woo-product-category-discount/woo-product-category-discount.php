<?php
/**
 * Plugin Name:       Simple Discount Rules for Woocommerce
 * Plugin URI:        https://www.quanticedgesolutions.com
 * Description:       Easily create advanced discount rules for your WooCommerce store! Set up discounts based on categories, tags, cart value, or product quantity—with full scheduling, smart product matching, and smooth processing that works great even on large stores. Discounts apply in real time, with progress updates shown to the user.
 * Version:           5.5
 * Author:            QuanticEdge
 * Author URI:        https://www.quanticedgesolutions.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wpcd-category-discount
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 5.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WPCD_CATEGORY_DISCOUNT_VERSION', '5.5' );

/**
 * Defines the path of base name of plugin.
 */
define( 'WPCD_PLUGIN_BASE_NAME', plugin_basename(__FILE__) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wpcd-category-discount-activator.php
 */
function activate_wpcd_category_discount() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpcd-category-discount-activator.php';
	WPCD_Category_Discount_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wpcd-category-discount-deactivator.php
 */
function deactivate_wpcd_category_discount() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpcd-category-discount-deactivator.php';
	WPCD_Category_Discount_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wpcd_category_discount' );
register_deactivation_hook( __FILE__, 'deactivate_wpcd_category_discount' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wpcd-category-discount.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    5.0
 */
function run_wpcd_category_discount() {
	if( !class_exists('QuanticEdge_Updater') ){
		include plugin_dir_path( __FILE__ ) . 'quanticedge/quanticedge.php';
	}
	$plugin = new WPCD_Category_Discount();
	$plugin->run();

}
run_wpcd_category_discount();

/**
 * Retrieves related term IDs for the given terms within a specified taxonomy.
 *
 * This function checks if WPML is active and, if so, fetches translations for
 * the provided terms using the 'wpml_get_element_translations' filter. It then
 * returns the original terms along with their related term IDs.
 *
 * @param array|int $terms    A single term ID or an array of term IDs.
 * @param string    $type     The type of terms (e.g., 'taxonomy' or 'post').
 * @param string    $post_type The post type or taxonomy to which the terms belong.
 *
 * @return array The array of original and related term IDs.
 */
function wpcd_get_related_terms( $terms, $type, $post_type ) {
	// If WPML is not active, return as-is.
	if ( ! defined( 'ICL_SITEPRESS_VERSION' ) || ! has_filter( 'wpml_get_element_translations' ) ) {
		return $terms;
	}

	$related_term_ids = [];

	if( !is_array( $terms ) && is_integer( $terms ) ){
		$terms = [ $terms ];
	}

	foreach ( $terms as $term_id ) {
		$trid = apply_filters( 'wpml_element_trid', null, $term_id, $type == 'taxonomy' ? 'tax_' . $post_type : 'post_' . $post_type);
		$translations = apply_filters( 'wpml_get_element_translations', null, $trid, $type == 'taxonomy' ? 'tax_' . $post_type : 'post_' . $post_type );
		foreach ( $translations as $translation ) {
			$related_term_ids[] = $type == 'taxonomy' ? $translation->term_id : $translation->ID;
		}
	}

	$related_term_ids = array_values( array_unique( $related_term_ids ) );

	return array_unique( array_merge( $terms, $related_term_ids ) );
}
