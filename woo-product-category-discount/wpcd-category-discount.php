<?php
/**
 * Plugin Name:       Simple Discount Rules for Woocommerce
 * Plugin URI:        https://www.quanticedgesolutions.com
 * Description:       Easily create advanced discount rules for your WooCommerce store! Set up discounts based on categories, tags, cart value, or product quantity—with full scheduling, smart product matching, and smooth processing that works great even on large stores. Discounts apply in real time, with progress updates shown to the user.
 * Version:           5.1
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
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WPCD_CATEGORY_DISCOUNT_VERSION', '5.1' );

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
 * @since    1.0.0
 */
function run_wpcd_category_discount() {

	$plugin = new WPCD_Category_Discount();
	$plugin->run();

}
run_wpcd_category_discount();
