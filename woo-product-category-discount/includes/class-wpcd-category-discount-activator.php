<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.quanticedgesolutions.com
 * @since      1.0.0
 *
 * @package    WPCD_Category_Discount
 * @subpackage WPCD_Category_Discount/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WPCD_Category_Discount
 * @subpackage WPCD_Category_Discount/includes
 * @author     QuanticEdge <info@quanticedge.co.in>
 */
class WPCD_Category_Discount_Activator {

	/**
	 * Creates the required database tables and sets up the initial plugin state.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$discounts_table = $wpdb->prefix . 'wpcd_discounts';
		$taxonomy_terms_table = $wpdb->prefix . 'wpcd_taxonomy_discount_terms';
		$cart_rules_table = $wpdb->prefix . 'wpcd_cart_discount_rules';
		$cart_rules_products_table  = $wpdb->prefix . 'wpcd_cart_discount_rules_products';

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		$sql1 = "
			CREATE TABLE IF NOT EXISTS $discounts_table (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				name VARCHAR(255) NOT NULL,
				discount_type TINYINT(1) NOT NULL COMMENT '0 - all_products, 1 - taxonomy, 2 - cart, 3 - quantity',
				rule_type TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 - all, 1 - any',
				start_date DATE NULL,
				end_date DATE NULL,
				discount_amount_type TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 - percentage, 1 - flat',
				discount_amount DECIMAL(12,2) NOT NULL,
				status TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 - disabled, 1 - enabled',
				total_chunks INT NOT NULL DEFAULT 0,
				processed_chunks INT NOT NULL DEFAULT 0,
				PRIMARY KEY (id)
			) $charset_collate;
		";

		$sql2 = "
			CREATE TABLE IF NOT EXISTS $taxonomy_terms_table (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				discount_id BIGINT UNSIGNED NOT NULL,
				taxonomy VARCHAR(255) NOT NULL,
				terms TEXT NOT NULL,
				operator TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1-equal to, 0-not equal to, 2-in, 3-not in', 
				PRIMARY KEY (id),
				KEY discount_id (discount_id),
				FOREIGN KEY (discount_id) REFERENCES $discounts_table(id) ON DELETE CASCADE
			) $charset_collate;
		";

		$sql3 = "
			CREATE TABLE IF NOT EXISTS $cart_rules_table (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				discount_id BIGINT UNSIGNED NOT NULL,
				cart_discount_type TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 - Cart Value, 1 - Free Products',
				min_cart_value DECIMAL(12,2),
				max_cart_value DECIMAL(12,2),
				discount_applicable_with_other_discount TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 - No, 1 - Yes',
				automatically_add_type TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0 - No, 1 - Yes',
				PRIMARY KEY (id),
				KEY discount_id (discount_id),
				FOREIGN KEY (discount_id) REFERENCES $discounts_table(id) ON DELETE CASCADE
			) $charset_collate;
		";

		$sql4 = "
			CREATE TABLE IF NOT EXISTS $cart_rules_products_table (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				discount_id BIGINT UNSIGNED NOT NULL,
				product_id BIGINT UNSIGNED NOT NULL,
				PRIMARY KEY (id),
				KEY discount_id (discount_id),
				FOREIGN KEY (discount_id) REFERENCES $discounts_table(id) ON DELETE CASCADE
			) $charset_collate;
		";

		dbDelta($sql1);
		dbDelta($sql2);
		dbDelta($sql3);
		dbDelta($sql4);

		update_option('wpcd_tables_created', 'yes');
	}

}
