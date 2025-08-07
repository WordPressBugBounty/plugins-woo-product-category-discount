<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.quanticedgesolutions.com
 * @since      5.0
 *
 * @package    WPCD_Category_Discount
 * @subpackage WPCD_Category_Discount/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      5.0
 * @package    WPCD_Category_Discount
 * @subpackage WPCD_Category_Discount/includes
 * @author     QuanticEdge <info@quanticedge.co.in>
 */
class WPCD_Category_Discount {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    5.0
	 * @access   protected
	 * @var      WPCD_Category_Discount_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    5.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    5.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    5.0
	 */
	public function __construct() {
		if ( defined( 'WPCD_CATEGORY_DISCOUNT_VERSION' ) ) {
			$this->version = WPCD_CATEGORY_DISCOUNT_VERSION;
		} else {
			$this->version = '5.0';
		}
		$this->plugin_name = 'woo-product-category-discount';

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - WPCD_Category_Discount_Loader. Orchestrates the hooks of the plugin.
	 * - WPCD_Category_Discount_i18n. Defines internationalization functionality.
	 * - WPCD_Category_Discount_Admin. Defines all hooks for the admin area.
	 * - WPCD_Category_Discount_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    5.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-product-category-discount-loader.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woo-product-category-discount-admin.php';

		/**
		 * The class responsible for defining all core list table functionality.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woo-product-category-discount-list-table-original.php';

		/**
		 * The class responsible for all the discount list table functionality.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woo-product-category-discount-list-table.php';
		
		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-woo-product-category-discount-public.php';

		/**
		 * The class responsible for all migration related features.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woo-product-category-discount-migrate-legacy.php';

		$this->loader = new WPCD_Category_Discount_Loader();

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    5.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new WPCD_Category_Discount_Admin( $this->get_plugin_name(), $this->get_version() );
		$legacy_migrate = new WPCD_Category_Discount_Migrate_Legacy( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu' );
		$this->loader->add_action( 'rest_api_init', $plugin_admin, 'register_rest_routes' );
		$this->loader->add_action( 'wpcd_apply_discount_setup', $plugin_admin, 'apply_discount_setup', 10, 1 );
		$this->loader->add_action( 'wpcd_apply_discount', $plugin_admin, 'apply_discount', 10, 4 );
		$this->loader->add_action( 'wpcd_remove_discount_setup', $plugin_admin, 'remove_discount_setup', 10, 1 );
		$this->loader->add_action( 'wpcd_remove_discount', $plugin_admin, 'remove_discount', 10, 2 );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'admin_notices');
		$this->loader->add_action( 'woocommerce_process_product_meta', $plugin_admin, 'apply_discount_price_on_product_save', 99, 2);
		$this->loader->add_action( 'updated_postmeta', $plugin_admin, 'change_price_keys', 99, 4);
	
		$this->loader->add_action( 'admin_init', $legacy_migrate, 'migrate_data');
		$this->loader->add_action( 'wpcd_discount_legacy_migrate', $legacy_migrate, 'set_migration_keys', 10, 2);
		$this->loader->add_action( 'upgrader_process_complete', $legacy_migrate, 'cron_updates', 10, 2);
		$this->loader->add_filter( 'plugin_action_links_' . WPCD_PLUGIN_BASE_NAME, $plugin_admin, 'add_settings_link', 10, 1 );

		if ( defined( 'ICL_SITEPRESS_VERSION' ) ){
			$this->loader->add_action( 'admin_head', $plugin_admin, 'hide_wpml_menu');
			$this->loader->add_action( 'current_screen', $plugin_admin, 'force_wpml_language', 10, 1);
		}
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    5.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new WPCD_Category_Discount_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'woocommerce_cart_calculate_fees', $plugin_public, 'add_discount_to_cart', 10, 1 );
		$this->loader->add_action( 'woocommerce_cart_updated', $plugin_public, 'set_cart_refresh_flag');
		$this->loader->add_action( 'wp', $plugin_public, 'add_free_products_to_cart' );
		$this->loader->add_action( 'woocommerce_before_calculate_totals', $plugin_public, 'set_free_discount_products_zero_price');
		$this->loader->add_filter( 'woocommerce_cart_item_quantity', $plugin_public, 'disable_quantity_for_free_gift', 10, 3 );
		$this->loader->add_action( 'wp_ajax_wpcd_add_optional_gift_to_cart', $plugin_public, 'add_optional_gift_to_cart' );
		$this->loader->add_action( 'wp_ajax_nopriv_wpcd_add_optional_gift_to_cart', $plugin_public, 'add_optional_gift_to_cart' );
		$this->loader->add_action( 'template_redirect', $plugin_public, 'remove_optional_gift_from_cart' );
		$this->loader->add_filter( 'woocommerce_get_item_data', $plugin_public, 'get_item_data', 10, 2 );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    5.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     5.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     5.0
	 * @return    WPCD_Category_Discount_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     5.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
