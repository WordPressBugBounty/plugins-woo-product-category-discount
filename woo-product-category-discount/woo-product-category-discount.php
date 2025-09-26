<?php
/**
 * Plugin Name:       Simple Discount Rules for Woocommerce
 * Plugin URI:        https://www.quanticedgesolutions.com
 * Description:       Easily create advanced discount rules for your WooCommerce store! Set up discounts based on categories, tags, cart value, or product quantity—with full scheduling, smart product matching, and smooth processing that works great even on large stores. Discounts apply in real time, with progress updates shown to the user.
 * Version:           5.8
 * Author:            QuanticEdge
 * Author URI:        https://www.quanticedgesolutions.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woo-product-category-discount
 * Domain Path:       /languages
 * Requires at least: 6.0
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
define( 'WPCD_CATEGORY_DISCOUNT_VERSION', '5.8' );

/**
 * Defines the path of base name of plugin.
 */
define( 'WPCD_PLUGIN_BASE_NAME', plugin_basename(__FILE__) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woo-product-category-discount-activator.php
 */
function activate_wpcd_category_discount() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-product-category-discount-activator.php';
	WPCD_Category_Discount_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woo-product-category-discount-deactivator.php
 */
function deactivate_wpcd_category_discount() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-product-category-discount-deactivator.php';
	WPCD_Category_Discount_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wpcd_category_discount' );
register_deactivation_hook( __FILE__, 'deactivate_wpcd_category_discount' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woo-product-category-discount.php';

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

if( !function_exists( 'wpcd_get_related_terms') ){
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
}

if( !function_exists( 'wpcd_get_admin_discount_status') ){
	/**
	 * Retrieves the discount status for the admin interface.
	 *
	 * @param array $discount_data The discount data which includes status, start date, and end date.
	 *
	 * @return string The status of the discount which can be 'active', 'inactive', 'scheduled', or 'processing'.
	 */
	function wpcd_get_admin_discount_status($discount_data){
		$today = date('Y-m-d');
		$start_date = isset($discount_data['start_date']) ? $discount_data['start_date'] : null;
		$end_date = isset($discount_data['end_date']) ? $discount_data['end_date'] : null;

		if( $discount_data['discount_type'] == 'quantity' || $discount_data['discount_type'] == 'cart' || $discount_data['discount_type'] == 2 || $discount_data['discount_type'] == 3 ){
			if( $discount_data['status'] == 0  ){
				return 'inactive';
			} else if (($start_date && $today < $start_date) || ($start_date && $end_date && ($today < $start_date || $today > $end_date))) {
				return 'scheduled';
			} else {
				return 'active';
			}
		}

		if ( $discount_data['status'] == 1 && $start_date && $today < $start_date) {
			return 'scheduled';
		}

		if ( $discount_data['status'] == 1 && $start_date && $end_date && ($today < $start_date || $today > $end_date)) {
			return 'scheduled';
		}

		if( $discount_data['processed_chunks'] >= 0 && $discount_data['processed_chunks'] < $discount_data['total_chunks'] ){
			return 'processing';
		}
		
		if ( $discount_data['status'] == 1) {
			return 'active';
		} else {
			return 'inactive';
		}
	}
}

if( !function_exists( 'wpcd_get_admin_discount_status_html') ){
	/**
	 * Returns HTML for the discount status toggle button.
	 *
	 * If the discount is active or inactive, this function returns a toggle button HTML.
	 * If the discount is processing, this function returns a loader GIF.
	 * If the discount is scheduled, this function returns a schedule icon.
	 *
	 * @param array $discount_data The discount data which includes status, start date, and end date.
	 *
	 * @return string The HTML of the discount status toggle button.
	 */
	function wpcd_get_admin_discount_status_html($discount_data){
		$status = wpcd_get_admin_discount_status($discount_data);
		if( $status == 'active' || $status == 'inactive' ){
			if( $status == 'inactive' && isset( $discount_data['end_date'] ) && $discount_data['end_date'] < date('Y-m-d') ){
				return sprintf(
					'<label class="discount-status">' . __('(Inactive)', 'woo-product-category-discount') . '</label>',
				);
			} else {
				$checked = $status == 'active' ? 'checked' : '';
				return sprintf(
					'<label class="wp-list-toggle">
						<input type="checkbox" class="toggle-status wpcd-status" id="toggle-status-%d" data-id="%d" %s>
						<span class="slider"></span>
					</label>
					<label class="discount-status">(%s)</label>',
					$discount_data['id'],
					$discount_data['id'],
					$checked,
					$status == 'active' ? __('Active', 'woo-product-category-discount') : __('Inactive', 'woo-product-category-discount'),
				);
			}
		}

		if( $status == 'processing' ){
			return '<img class="wpcd-status" data-id="' . $discount_data['id'] . '" id="toggle-status-' . $discount_data['id'] . '" src="' . plugin_dir_url( __FILE__ ) . 'admin/assets/images/loader.gif" alt="processing" height="50"><label for="status">(' . __('Processing', 'woo-product-category-discount') . ')</label>';
		}

		return '<span class="wpcd-status dashicons dashicons-calendar" data-id="' . $discount_data['id'] . '" id="toggle-status-' . $discount_data['id'] . '" style="margin-left: 8%;"></span><label for="status">(' . __('Scheduled', 'woo-product-category-discount') . ')</label>';
	}
}
