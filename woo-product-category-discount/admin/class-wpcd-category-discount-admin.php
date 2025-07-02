<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.quanticedgesolutions.com
 * @since      1.0.0
 *
 * @package    WPCD_Category_Discount
 * @subpackage WPCD_Category_Discount/admin
 */
class WPCD_Category_Discount_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The slug of admin menu
	 * 
	 * @since	1.0.0
	 * @access	private
	 * @var		string		$menu_slug		The slug of the admin menu
	 */
	private static $menu_slug = 'wpcd-category-discount';

	/**
	 * wpdb object
	 * 
	 * @since	1.0.0
	 * @access  private
	 * @var		object 		$wpdb 			Object of wpdb
	 */
	private $wpdb;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		global $wpdb;
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->wpdb = $wpdb;
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		if( isset( $_GET['page'], $_GET['id'] ) && $_GET['page'] == self::$menu_slug && ( $_GET['action'] == 'add' || $_GET['action'] == 'edit' ) ) {
			wp_enqueue_script('wpcd-react-app', plugin_dir_url( __FILE__ ) . 'components/wizard/build/bundle.js', array(), time(), 'all' );
			
			$excluded_taxonomies = ['product_type', 'product_visibility', 'product_shipping_class'];

			$taxonomies = get_object_taxonomies('product', 'objects');

			$taxonomies = array_filter($taxonomies, function($taxonomy) use ($excluded_taxonomies) {
				return !in_array($taxonomy->name, $excluded_taxonomies);
			});

			$taxonomies = array_map(function($taxonomy) {
				return [
					'label' => $taxonomy->labels->singular_name,
					'value' => $taxonomy->name
				];
			}, $taxonomies);
			$taxonomy_list = array_values($taxonomies);
			
			wp_localize_script('wpcd-react-app', 'wpcd_data', [
				'api_url'     => esc_url_raw(rest_url('wpcd/v1/')),
				'nonce'   	  => wp_create_nonce('wp_rest'),
				'redirect_url'=> admin_url( "admin.php?page=" . self::$menu_slug),
				'discount_id' => sanitize_text_field( $_GET['id'] ),
				'taxonomies'  => $taxonomy_list,
				'i18n'        => [
					'discount_type' 				=> __('Discount Type','wpcd-category-discount'), 
					'schedule' 						=> __('Schedule','wpcd-category-discount'), 
					'confirmation'  				=> __('Confirmation','wpcd-category-discount'),
					'finish'      					=> __('Finish', 'wpcd-category-discount'),	
					'next'     						=> __('Next', 'wpcd-category-discount'),
					'back'							=> __('Back', 'wpcd-category-discount'),
					'all_products' 					=> __('All Products', 'wpcd-category-discount'),
					'by_taxonomy' 					=> __('Taxonomy', 'wpcd-category-discount'),
					'match_type'					=> __('Match Type', 'wpcd-category-discount'),
					'match_all'						=> __('Match All', 'wpcd-category-discount'),
					'match_any'						=> __('Match Any', 'wpcd-category-discount'),
					'taxonomy'						=> __('Taxonomy', 'wpcd-category-discount'),
					'operator'						=> __('Operator', 'wpcd-category-discount'),
					'equal_to'						=> __('Equal To', 'wpcd-category-discount'),
					'not_equal_to'					=> __('Not Equal To', 'wpcd-category-discount'),
					'in' 							=> __('In', 'wpcd-category-discount'),
					'not_in' 						=> __('Not In', 'wpcd-category-discount'),
					'terms'							=> __('Terms', 'wpcd-category-discount'),
					'remove'						=> __('Remove', 'wpcd-category-discount'),
					'add_condition' 				=> __('+ Add Condition', 'wpcd-category-discount'),
					'select_schedule' 			  	=> __('Select Schedule', 'wpcd-category-discount'),
					'estimated_affected_products'	=> __('Estimated Affected Products: ', 'wpcd-category-discount'),
					'schedule_discount' 		  	=> $_GET['id'] == 'new' ? __('Schedule Discount', 'wpcd-category-discount') : __('Update Discount', 'wpcd-category-discount'),
					'start_date'					=> __('Start Date', 'wpcd-category-discount'),
					'end_date'						=> __('End Date', 'wpcd-category-discount'),
					'schedule_notice'				=> __('Note: If no schedule is set, the discount will be applied immediately.', 'wpcd-category-discount'),
					'status_label'					=> __('Status', 'wpcd-category-discount'),
					'status_active'					=> __('Active', 'wpcd-category-discount'),
					'status_inactive'				=> __('Inactive', 'wpcd-category-discount'),
					'failed_schedule_message'		=> __('Failed to schedule the discount. Please try again.', 'wpcd-category-discount'),
					'discount_name'					=> __('Discount Name', 'wpcd-category-discount'),
					'discount_name_required'		=> __('Discount name is required.', 'wpcd-category-discount'),
					'at_least_one_rule_required'	=> __('At least one taxonomy rule is required.', 'wpcd-category-discount'),
					'required'						=> __('Required', 'wpcd-category-discount'),
					'discount_amount_label'			=> __('Discount Amount', 'wpcd-category-discount'),
					'amount'						=> __('Amount', 'wpcd-category-discount'),
					'amount_type'					=> __('Type','wpcd-category-discount'),
					'percent'						=> __('Percentage (%)','wpcd-category-discount'),
					'flat'							=> __('Flat Amount', 'wpcd-category-discount'),
					'discount_amount_required'		=> __('Discount amount is required.', 'wpcd-category-discount'),
					'discount_amount_type_required' => __('Discount amount type is required.', 'wpcd-category-discount'),
					'something_went_wrong'			=> __('Something went wrong','wpcd-category-discount'),
					'by_cart'						=> __('Cart Value', 'wpcd-category-discount'),
					'cart_discount_type_label'		=> __('Cart Discount Type', 'wpcd-category-discount'),
					'cart_value_label'				=> __('Cart Value Based', 'wpcd-category-discount'),
					'free_product_label'			=> __('Free Product Giweaway', 'wpcd-category-discount'),
					'cart_discount_conditions_label'=> __('Cart Discount Conditions', 'wpcd-category-discount'),
					'min_cart_value_label' 			=> __('Minimum Cart Value (Optional)', 'wpcd-category-discount'),
					'max_cart_value_label' 			=> __('Maximum Cart Value (Optional)', 'wpcd-category-discount'),
					'cart_applicability_with_other_disc_label' => __('Discount Applicable with Other Discounts', 'wpcd-category-discount'),
					'yes_label'						=> __('Yes', 'wpcd-category-discount'),
					'no_label'						=> __('No', 'wpcd-category-discount'),
					'free_product_label' 			=> __('Free Product Giveaway Settings', 'wpcd-category-discount'),
					'automatically_add_label'		=> __('Whether it should be automatically added?', 'wpcd-category-discount'),
					'automatically_add_yes_label'	=> __('Yes, add it automatically', 'wpcd-category-discount'),
					'automatically_add_no_label'	=> __('No, prompt user to add it', 'wpcd-category-discount'),
					'products_label'				=> __('Products', 'wpcd-category-discount'),
					'max_value_should_be_greater_than_min_value' => __('Max value should be greater than min value', 'wpcd-category-discount'),
					'by_quantity' 					=> __('Quantity', 'wpcd-category-discount'),
					'quantity_discount_conditions_label' => __('Quantity Discount Conditions', 'wpcd-category-discount'),
					'min_quantity_label' 			=> __('Minimum Quantity', 'wpcd-category-discount'),
					'max_quantity_label' 			=> __('Maximum Quantity (Optional)', 'wpcd-category-discount'),
					'min_quantity_required' 		=> __('Minimum quanity is required.', 'wpcd-category-discount'),
				],
			]);
		}

		if( isset( $_GET['page'], $_GET['id'], $_GET['action'] ) && $_GET['page'] == self::$menu_slug && $_GET['action'] == 'view-progress' ) {
			wp_enqueue_script( 'wpcd-discount-progress', plugin_dir_url( __FILE__ ) . 'components/discount-progress/build/bundle.js', array(), time(), true );
			wp_localize_script( 'wpcd-discount-progress', 'wpcd_data', [
				'api_url'     		=> esc_url_raw(rest_url('wpcd/v1/')),
				'nonce'   	  		=> wp_create_nonce('wp_rest'),
				'discount_id' 		=> sanitize_text_field( $_GET['id'] ),
				'discount_list_url' => admin_url( 'admin.php?page=wpcd-category-discount' ),
				'i18n'		  		=> [
					'discount_progress_loading_message' => __('Discount progress is loading...', 'wpcd-category-discount'),
					'back_button_text'					=> __('Back to Discount List', 'wpcd-category-discount'),
					'alert_message'						=> __('You may safely leave this page. The discount is being applied or removed in the background and will continue to process automatically.','wpcd-category-discount'),
				]
			]);
		}
	}

	/**
	 * Add the admin menu.
	 *
	 * @since    1.0.0
	 */
	public function admin_menu() {
		add_menu_page(
			__( 'Simple Discount Rules', 'wpcd-category-discount' ),
			__( 'Simple Discount Rules', 'wpcd-category-discount' ),
			'manage_options',
			self::$menu_slug,
			array( $this, 'render_category_discount' ),
			'dashicons-tag',
			6
		);
	}

	/**
	 * Render the category discount add or list page.
	 *
	 * @since    1.0.0
	 */
	public function render_category_discount() {
		if( isset( $_GET['id'] ) ) {
			if( isset( $_GET['action'] ) && $_GET['action'] == 'delete' ) {
				$this->delete_discount_action();
			} else if ( isset( $_GET['action'] ) && $_GET['action'] == 'view-progress' ) {
				$this->show_discount_progress();
		 	} else {
				$this->render_add_discount();
			}
		} else {
			$this->render_discount_list();
		}
	}

	/**
	 * Deletes a discount and its associated taxonomy rules.
	 *
	 * This function is called when the user clicks on the "Delete" link.
	 *
	 * @since 1.0.0
	 */
	public function delete_discount_action() {
		$id = absint($_GET['id']);
		$nonce = $_REQUEST['_wpnonce'] ?? '';

		if (!wp_verify_nonce($nonce, 'wpcd_delete_discount_' . $id)) {
			wp_die(__('Security check failed', 'wpcd-category-discount'));
		}

		$discount_data = $this->get_scheduled_discount_data( $id );
		$this->delete_discount( $discount_data );
		wp_redirect( admin_url( 'admin.php?wpcd_deleted=1&page=' . self::$menu_slug ) );
		exit();
	}

	/**
	 * Renders the add discount page.
	 *
	 * This function is called when the user clicks on the "Add Discount" link.
	 *
	 * @since 1.0.0
	 */
	public function render_add_discount() {
		include_once plugin_dir_path( __FILE__ ) . 'partials/wpcd-category-add-discount.php';
	}

	/**
	 * Renders the discount list page.
	 *
	 * This function is called when the user is on the plugin's main page.
	 *
	 * @since 1.0.0
	 */
	public function render_discount_list() {
		$list_table = new WPCD_Discount_List_Table();
		include_once plugin_dir_path( __FILE__ ) . 'partials/wpcd-category-list-table.php';
	}

	/**
	 * Outputs any admin notices.
	 *
	 * This function is called on the admin_notices action hook.
	 *
	 * @since 1.0.0
	 */
	public function admin_notices(){
		include_once plugin_dir_path( __FILE__ ) . 'partials/wpcd-category-notices.php';
	}

	/**
	 * Renders the discount progress bar.
	 *
	 * This function is called when the user is on the add discount page and a scheduled discount is being processed.
	 *
	 * @since 1.0.0
	 */
	public function show_discount_progress(){
		include_once plugin_dir_path( __FILE__ ) . 'partials/wpcd-category-discount-progress.php';
	}

	/**
	 * Register the REST API endpoints used by the plugin.
	 *
	 * This function is called when the plugin is loaded.
	 *
	 * @since 1.0.0
	 */
	public function register_rest_routes(){
		register_rest_route('wpcd/v1', '/taxonomy-terms', [
			'methods'             => 'GET',
			'callback'            => [$this, 'get_taxonomies_terms'],
			'permission_callback' => function () {
				return current_user_can('manage_woocommerce');
			},
			'args' => [
				'taxonomy' => [
					'required' => true,
					'sanitize_callback' => 'sanitize_key',
				],
				'search' => [
					'required' => false,
					'sanitize_callback' => 'sanitize_text_field',
				]
			]
		]);

		register_rest_route('wpcd/v1', '/estimate-affected-products', [
			'methods'             => 'POST',
			'callback'            => [$this, 'estimate_products'],
			'permission_callback' => function () {
				return current_user_can('manage_woocommerce');
			}
		]);

		register_rest_route('wpcd/v1', '/schedule-discount', [
			'methods'             => 'POST',
			'callback'            => [$this, 'schedule_discount'],
			'permission_callback' => function () {
				return current_user_can('manage_woocommerce');
			}
		]);

		register_rest_route('wpcd/v1', '/get-scheduled-discount/(?P<id>[a-zA-Z0-9_-]+)', [
			'methods'				=> 'GET',
			'callback'				=> [$this, 'get_scheduled_discount'],
			'permission_callback' 	=> function () {
				return current_user_can('manage_woocommerce');
			}
		]);

		register_rest_route('wpcd/v1', '/discount-progress/(?P<id>[a-zA-Z0-9_-]+)', [
			'methods' 				=> 'GET',
			'callback' 				=> [$this, 'get_discount_progress'],
			'permission_callback' 	=> function () {
				return current_user_can('manage_woocommerce');
			},
		]);

		register_rest_route('wpcd/v1', '/products', [
			'methods'             => 'GET',
			'callback'            => [$this, 'get_products'],
			'permission_callback' => function () {
				return current_user_can('manage_woocommerce');
			},
			'args' => [
				'search' => [
					'required' => false,
					'sanitize_callback' => 'sanitize_text_field',
				]
			]
		]);
	}

	/**
	 * Returns a list of terms of a given taxonomy.
	 *
	 * @param WP_REST_Request $request {
	 *     @type string $taxonomy The taxonomy to retrieve terms from.
	 *     @type string $search   Optional search query.
	 * }
	 *
	 * @return WP_Error|array Array of term objects on success.
	 */
	public function get_taxonomies_terms( WP_REST_Request $request ) {
		$taxonomy = $request['taxonomy'];
		$search   = $request['search'] ?? '';

		if ( ! taxonomy_exists( $taxonomy ) ) {
			return new WP_Error( 'invalid_taxonomy', __( 'Invalid taxonomy', 'wpcd-category-discount' ), [ 'status' => 400 ] );
		}

		$terms = get_terms( [
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'search'     => $search,
			'number'     => 50,
		] );

		if ( is_wp_error( $terms ) ) {
			return $terms;
		}

		$response = array_map( function ( $term ) {
			return [
				'id'    => $term->term_id,
				'label' => $term->name . ' (' . $term->slug . ')',
				'value' => $term->term_id,
			];
		}, $terms );

		return rest_ensure_response( $response );
	}

	/**
	 * Estimate the number of products that will be discounted with the given discount options.
	 *
	 * @param WP_REST_Request $request {
	 *     @type string $discountType Discount type: 'fixed' or 'percentage'.
	 *     @type string $matchType Match type: 'all' or 'any'.
	 *     @type array $taxonomyRules Taxonomy rules.
	 * }
	 *
	 * @return WP_Error|array {
	 *     @type int $count The number of products that will be discounted.
	 * }
	 */
	public function estimate_products(WP_REST_Request $request){
		$params = $request->get_json_params();

		$discount_type = $params['discountType'] ?? '';
		if ( $discount_type === 'cart' ){
			return rest_ensure_response(['count' => __('Cart discount does not apply to fixed number of products.', 'wpcd-category-discount')]);
		}

		if( $discount_type === 'quantity' ){
			return rest_ensure_response(['count' => __('Quantity discount does not apply to fixed number of products.', 'wpcd-category-discount')]);
		}

		$match_type = $params['matchType'] ?? '';
		$taxonomy_rules = $params['taxonomyRules'];

		$query = $this->get_products_query($discount_type, $match_type, $taxonomy_rules);

		return rest_ensure_response(['count' => $query->found_posts]);
	}

	
	/**
	 * Schedules a discount with the given options.
	 *
	 * @param WP_REST_Request $request {
	 *     @type string $discount_id Discount ID.
	 *     @type string $discountName Discount name.
	 *     @type string $discountType Discount type: 'fixed', 'percentage', 'cart', 'free_product'.
	 *     @type string $matchType Match type: 'all', 'any'.
	 *     @type array $taxonomyRules Taxonomy rules.
	 *     @type string $startDate Start date.
	 *     @type string $endDate End date.
	 *     @type string $discountAmountType Discount amount type: 'fixed', 'percentage'.
	 *     @type string $discountAmount Discount amount.
	 *     @type int $status Status.
	 *     @type int $cart_discount_type Cart discount type.
	 *     @type float $min_cart_value Minimum cart value.
	 *     @type float $max_cart_value Maximum cart value.
	 *     @type int $discount_applicable_with_other_discount Discount applicable with other discount.
	 *     @type int $automatically_add_type Automatically add type.
	 *     @type array $cart_rule_products Cart rule products.
	 * }
	 *
	 * @return WP_Error|array Array of term objects on success.
	 */
	public function schedule_discount(WP_REST_Request $request) {
		$params = $request->get_json_params();

		$discount_id = $params['discount_id'] ?? '';
		if( $discount_id == 'new' ){
			$discount_id = null;
		}

		$discount_name = $params['discountName'] ?? '';
		$discount_type = $params['discountType'] ?? '';
		$ruleType = $params['matchType'] ?? '';
		$start_date = $params['startDate'] ?? '';
		$end_date = $params['endDate'] ?? '';
		$discount_amount_type = $params['discountAmountType'] ?? '';
		$discount_amount = $params['discountAmount'] ?? '';
		$status = $params['status'] ?? 0;
		$cart_discount_type = $request['cartDiscountType'] == 'free_product' ? 1 : 0;
		$min_cart_value = $request['minCartValue'] ?? null;
		$max_cart_value = $request['maxCartValue'] ?? null;
		$discount_applicable_with_other_discount = $request['discApplicableWithOtherDisc'] == 'yes' ? 1 : 0;
		$automatically_add_type = $request['automaticallyAddType'] == 'yes' ? 1 : 0;
		$cart_rule_products = $request['selectedProducts'] ?? [];

		if( empty($discount_name) ) {
			return new WP_Error('invalid_data', __('Discount name is required', 'wpcd-category-discount'), ['status' => 400]);
		}
		
		if (empty($discount_type) ) {
			return new WP_Error('invalid_data', __('Invalid discount type provided', 'wpcd-category-discount'), ['status' => 400]);
		}

		if (!empty( $start_date) && !empty( $end_date ) && (strtotime($start_date) > strtotime($end_date))) {
			return new WP_Error('invalid_dates', __('Start date must be before end date', 'wpcd-category-discount'), ['status' => 400]);
		} else if (empty( $start_date ) && !empty( $end_date ) && strtotime($end_date) < time()) {
			return new WP_Error('invalid_dates', __('Invalid end date', 'wpcd-category-discount'), ['status' => 400]);
		} else if (empty( $end_date ) && !empty( $start_date ) && strtotime($start_date) < time()) {
			return new WP_Error('invalid_dates', __('Invalid start date', 'wpcd-category-discount'), ['status' => 400]);
		}

		if ($discount_type === 'taxonomy') {
			$rules = $params['taxonomyRules'] ?? [];
			if (empty($rules)) {
				return new WP_Error('invalid_data', __('No taxonomy rules provided', 'wpcd-category-discount'), ['status' => 400]);
			}
		} else {
			$rules = $discount_type === 'quantity' ? $params['taxonomyRules'] : [];
		}

		if($discount_type === 'cart' || $discount_type === 'quantity' ){
			if( !empty( $min_cart_value ) && !empty( $max_cart_value ) && ( $min_cart_value > $max_cart_value ) ){
				return new WP_Error('invalid_data', __('Minimum cart value must be less than maximum cart value', 'wpcd-category-discount'), ['status' => 400]);
			}

			if( $cart_discount_type === 'free_product' && empty( $cart_rule_products ) ){
				return new WP_Error('invalid_data', __('Please select at least one product', 'wpcd-category-discount'), ['status' => 400]);
			} 
		}

		if( !empty( $discount_id ) ){
			$discount_data = $this->get_scheduled_discount_data( $discount_id );
			$this->maybe_inactive_discount( $discount_data );

			$this->wpdb->delete(
				$this->wpdb->prefix . 'wpcd_taxonomy_discount_terms',
				['discount_id'=> $discount_id],
				['%d']
			);

			$this->wpdb->delete(
				$this->wpdb->prefix . 'wpcd_cart_discount_rules',
				['discount_id'=> $discount_id],
				['%d']
			);

			$this->wpdb->delete(
				$this->wpdb->prefix . 'wpcd_cart_discount_rules_products',
				['discount_id'=> $discount_id],
				['%d']
			);

			$this->wpdb->update(
				$this->wpdb->prefix . 'wpcd_discounts',
				[
					'name'					=> sanitize_text_field($discount_name),
					'discount_type'			=> $discount_type == 'all_products' ? 0 : ($discount_type == 'taxonomy' ? 1 : ($discount_type == 'cart' ? 2 : 3)),
					'rule_type'    			=> $ruleType == 'any' ? 1 : 0,
					'start_date'   			=> !empty($start_date) ? sanitize_text_field($start_date) : null,
					'end_date'     			=> !empty($end_date) ? sanitize_text_field($end_date) : null,
					'discount_amount_type' 	=> $discount_amount_type == 'percentage' ? 0 : 1,
					'discount_amount' 		=> sanitize_text_field($discount_amount),
					'status'        		=> intval($status),
				],
				[
					'id'					=> $discount_id
				],
				['%s', '%d', '%d', '%s', '%s', '%d', '%f', '%d'],
				['%d']
			);
		} else {
			$this->wpdb->insert(
				$this->wpdb->prefix . 'wpcd_discounts',
				[
					'name'					=> sanitize_text_field($discount_name),
					'discount_type'			=> $discount_type == 'all_products' ? 0 : ($discount_type == 'taxonomy' ? 1 : ($discount_type == 'cart' ? 2 : 3)),
					'rule_type'    			=> $ruleType == 'any' ? 1 : 0,
					'start_date'   			=> !empty($start_date) ? sanitize_text_field($start_date) : null,
					'end_date'     			=> !empty($end_date) ? sanitize_text_field($end_date) : null,
					'discount_amount_type' 	=> $discount_amount_type == 'percentage' ? 0 : 1,
					'discount_amount' 		=> sanitize_text_field($discount_amount),
					'status'        		=> intval($status),
				],
				['%s', '%d', '%d', '%s', '%s', '%d', '%f', '%d']
			);

			$discount_id = $this->wpdb->insert_id;
		}

		if( $discount_type == 'taxonomy' || $discount_type == 'quantity' ){
			foreach( $rules as $rule ){
				if( empty( $rule['taxonomy'] ) || empty( $rule['terms'] ) || empty( $rule['operator'] ) ){
					continue;
				}
				if( !is_array( $rule['terms'] ) ){
					$rule['terms'] = [$rule['terms']];
				}
				$this->wpdb->insert(
					$this->wpdb->prefix . 'wpcd_taxonomy_discount_terms',
					[
						'discount_id' => $discount_id,
						'taxonomy' => $rule['taxonomy'],
						'terms' => implode(',', array_map('sanitize_text_field', $rule['terms'])),
						'operator' => $this->translate_operator_id( $rule['operator'] )
					],
					['%d', '%s', '%s', '%d']
				);
			}
		} 
		
		if ( $discount_type == 'cart' || $discount_type == 'quantity' ){
			$this->wpdb->insert(
				$this->wpdb->prefix . 'wpcd_cart_discount_rules',
				[
					'discount_id' => $discount_id,
					'cart_discount_type' => $cart_discount_type,
					'min_cart_value' => $min_cart_value,
					'max_cart_value' => $max_cart_value,
					'discount_applicable_with_other_discount' => $discount_applicable_with_other_discount,
					'automatically_add_type' => $automatically_add_type
				],
				['%d', '%d', '%f', '%f', '%d', '%d']
			);

			if( $cart_discount_type == 1 ){
				foreach( $cart_rule_products as $product_id ){
					$this->wpdb->insert(
						$this->wpdb->prefix . 'wpcd_cart_discount_rules_products',
						[
							'discount_id' => $discount_id,
							'product_id' => $product_id,
						],
						['%d', '%d']
					);
				}
			}
		}

		$discount_data = $this->get_scheduled_discount_data( $discount_id );

		if( $discount_data['status'] == 1 && ($discount_data['discount_type'] !== 'cart' || $discount_data['discount_type'] !== 'quantity') ){
			$time = empty( $discount_data['start_date'] ) ? time() : strtotime($discount_data['start_date'] . ' 00:00:00');
			wp_schedule_single_event($time, 'wpcd_apply_discount_setup', [$discount_data]);

			if( $discount_data['end_date'] ){
				wp_schedule_single_event(strtotime($discount_data['end_date'] . ' 23:59:59'), 'wpcd_remove_discount_setup', [$discount_data]);
			}
		}

		return rest_ensure_response(['data' => ['status' => 200], 'message' => $params['discount_id'] !== 'new' ? __('Discount updated successfully.', 'wpcd-category-discount') : __('Discount scheduled successfully.', 'wpcd-category-discount')]);
	}

	/**
	 * Handles the request to get a scheduled discount data.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The response object.
	 */
	public function get_scheduled_discount(WP_REST_Request $request){
		$id = $request['id'];
		$data = $this->get_scheduled_discount_data( $id );
		if( empty( $data ) ){
			return new WP_Error('not_found', __('Discount not found.','wpcd-category-discount'), ['status' => 404]);
		}
		return rest_ensure_response(['data' => ['status' => 200, 'data' => $data], 'message' => __('Discount fetched successfully.', 'wpcd-category-discount')]);
	}

	/**
	 * Handles the request to get a scheduled discount progress.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The response object.
	 */
	public function get_discount_progress(WP_REST_Request $request){
		$id = $request['id'];
		$data = $this->get_scheduled_discount_data( $id );
		if( empty( $data ) ){
			return new WP_Error('not_found', __('Discount not found.','wpcd-category-discount'), ['status' => 404]);
		}
		return rest_ensure_response(['data' => ['status' => 200, 'data' => ['total' => $data['total_chunks'], 'processed' => $data['processed_chunks'], 'status' => $data['processed_chunks'] == $data['total_chunks'] ? __('Completed', 'wpcd-category-discount') : __('Processing', 'wpcd-category-discount')]], 'message' => __('Discount progress fetched successfully.', 'wpcd-category-discount')]);
	}

	/**
	 * Handles the request to search for products.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The response object.
	 */
	public function get_products(WP_REST_Request $request){
		$search = sanitize_text_field($request['search']);

		$args = [
			'status' => 'publish',
			'limit' => 50
		];

		if (!empty($search)) {
			$args['search'] = $search;
		}

		$found_products = wc_get_products($args);
		$products = [];

		foreach ($found_products as $product) {
			if( $product->is_type('variable') ){
				foreach( $product->get_children() as $child_product_id ){
					$products[] = [
						'id'    => $child_product_id,
						'label'  => get_the_title($child_product_id),
						'value' => $child_product_id
					];
				}
			} else {
				$products[] = [
					'id'    => $product->get_id(),
					'label'  => $product->get_name(),
					'value' => $product->get_id(),
				];
			}
		}

		return rest_ensure_response($products);
	}

	/**
	 * Apply discount to products.
	 *
	 * This function takes a query object based on the discount type and rules,
	 * and applies the discount to the products in chunks of 10.
	 *
	 * @param array $data Contains the discount id, discount amount, discount amount type, and taxonomy rules.
	 */
	public function apply_discount_setup( $data ){
		$query = $this->get_products_query( $data['discount_type'], $data['rule_type'], $data['taxonomy_rules'] );
		$product_chunks = array_chunk($query->posts, 10);
		$this->wpdb->update( $this->wpdb->prefix . 'wpcd_discounts', ['total_chunks' => count( $product_chunks )], ['id' => $data['id']] );
		$time = time();
		foreach( $product_chunks as $chunk ){
			wp_schedule_single_event($time, 'wpcd_apply_discount', [$data['id'], $data['discount_amount'], $data['discount_amount_type'], $chunk]);
			$time = $time + 10;
		}
	}

	/**
	 * Applies a discount to products.
	 *
	 * This function takes a discount id, discount amount, discount amount type, and a list of product ids,
	 * and applies the discount to the products.
	 *
	 * @param int $discount_id The id of the discount.
	 * @param float $discount_amount The amount of the discount.
	 * @param string $discount_amount_type The type of the discount amount, either 'percentage' or 'fixed'.
	 * @param array $product_ids The list of product ids to which the discount should be applied.
	 */
	public function apply_discount( $discount_id, $discount_amount, $discount_amount_type, $product_ids ){
		foreach( $product_ids as $product_id ){
			$product = wc_get_product( $product_id );
			if( $product->is_type('variable') ){
				$variations = $product->get_children();
				foreach( $variations as $variation_id ){
					$discount_applied = get_post_meta( $variation_id, '_wpcd_discount_id', true );
					if( !empty( $discount_applied ) ){
						continue;
					}
					$variation = wc_get_product( $variation_id );
					$regular_price = $variation->get_regular_price();
					$sale_price = $variation->get_sale_price();
					$price = $variation->get_price();
					if( empty( $price ) ){
						continue;
					}
					$new_price = $discount_amount_type == 'percentage' ? ($price - ($price * $discount_amount / 100)) : ($price - $discount_amount);
					$variation->set_sale_price( $new_price );
					$variation->save();
					update_post_meta( $variation_id, '_wpcd_discount_id', $discount_id );
					update_post_meta( $variation_id, '_wpcd_original_regular_price', $regular_price );
					update_post_meta( $variation_id, '_wpcd_original_sale_price', $sale_price );
					update_post_meta( $variation_id, '_wpcd_original_price', $price );
				}
			} else {
				$discount_applied = get_post_meta( $product_id, '_wpcd_discount_id', true );
				if( !empty( $discount_applied ) ){
					continue;
				}
				$regular_price = $product->get_regular_price();
				$sale_price = $product->get_sale_price();
				$price = $product->get_price();
				if( empty( $price ) ){
					continue;
				}
				$new_price = $discount_amount_type == 'percentage' ? ($price - ($price * $discount_amount / 100)) : ($price - $discount_amount);
				$product->set_sale_price( $new_price );
				$product->save();
				update_post_meta( $product_id, '_wpcd_discount_id', $discount_id );
				update_post_meta( $product_id, '_wpcd_original_regular_price', $regular_price );
				update_post_meta( $product_id, '_wpcd_original_sale_price', $sale_price );
				update_post_meta( $product_id, '_wpcd_original_price', $price );
			}
		}
		$this->wpdb->query( $this->wpdb->prepare( "UPDATE {$this->wpdb->prefix}wpcd_discounts SET processed_chunks = processed_chunks + 1 WHERE id = %d", $discount_id ) );
	}

	/**
	 * Sets up the removal of a discount.
	 *
	 * This function takes the discount id, discount type, rule type, and taxonomy rules,
	 * and schedules the removal of the discount from the products in chunks of 10.
	 *
	 * @param array $data Contains the discount id, discount type, rule type, and taxonomy rules.
	 */
	public function remove_discount_setup( $data ){
		$query = $this->get_products_query( $data['discount_type'], $data['rule_type'], $data['taxonomy_rules'] );
		$product_chunks = array_chunk($query->posts, 10);
		$this->wpdb->update( $this->wpdb->prefix . 'wpcd_discounts', ['processed_chunks' => 0], ['id' => $data['id']] );
		$time = time();
		foreach( $product_chunks as $chunk ){
			wp_schedule_single_event($time, 'wpcd_remove_discount', [$data['id'], $chunk]);
			$time = $time + 10;
		}
	}

	/**
	 * Removes a discount from a list of products.
	 *
	 * This function takes a discount id and a list of product ids, and removes the discount
	 * from the products. If the discount is not present on a product, the function
	 * does nothing.
	 *
	 * @param int $discount_id The id of the discount.
	 * @param array $product_ids The list of product ids from which the discount should be removed.
	 */
	public function remove_discount( $discount_id, $product_ids ) {
		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );
			if ( ! $product ) continue;

			if ( $product->is_type( 'variable' ) ) {
				$variations = $product->get_children();
				foreach ( $variations as $variation_id ) {
					$discount_applied = get_post_meta( $variation_id, '_wpcd_discount_id', true );
					if ( empty( $discount_applied ) || $discount_applied != $discount_id ) {
						continue;
					}

					$variation = wc_get_product( $variation_id );
					if ( ! $variation ) continue;

					$regular_price = get_post_meta( $variation_id, '_wpcd_original_regular_price', true );
					$sale_price    = get_post_meta( $variation_id, '_wpcd_original_sale_price', true );

					$variation->set_regular_price( $regular_price );
					$variation->set_sale_price( $sale_price );
					$variation->save();

					delete_post_meta( $variation_id, '_wpcd_discount_id' );
					delete_post_meta( $variation_id, '_wpcd_original_regular_price' );
					delete_post_meta( $variation_id, '_wpcd_original_sale_price' );
					delete_post_meta( $variation_id, '_wpcd_original_price' );

					wc_delete_product_transients( $variation_id );
				}
			} else {
				$discount_applied = get_post_meta( $product_id, '_wpcd_discount_id', true );
				if ( empty( $discount_applied ) || $discount_applied != $discount_id ) {
					continue;
				}

				$regular_price = get_post_meta( $product_id, '_wpcd_original_regular_price', true );
				$sale_price    = get_post_meta( $product_id, '_wpcd_original_sale_price', true );

				$product->set_regular_price( $regular_price );
				$product->set_sale_price( $sale_price );
				$product->save();

				delete_post_meta( $product_id, '_wpcd_discount_id' );
				delete_post_meta( $product_id, '_wpcd_original_regular_price' );
				delete_post_meta( $product_id, '_wpcd_original_sale_price' );
				delete_post_meta( $product_id, '_wpcd_original_price' );

				wc_delete_product_transients( $product_id );
			}
		}

		$this->wpdb->query( $this->wpdb->prepare( "UPDATE {$this->wpdb->prefix}wpcd_discounts SET processed_chunks = processed_chunks + 1 WHERE id = %d", $discount_id ) );
	}

	/**
	 * Retrieves a list of products that match the given discount type and taxonomy rules.
	 *
	 * @param string $discount_type The type of discount to apply. Can be 'taxonomy', 'attribute', or 'all'.
	 * @param string $match_type The type of match to apply. Can be 'all' or 'any'.
	 * @param array $taxonomy_rules The taxonomy rules to apply. Each rule should contain the following keys:
	 *                              - taxonomy: The taxonomy slug.
	 *                              - operator: The operator to use. Can be 'eq', 'not in', 'in', 'exists', or 'not exists'.
	 *                              - terms: An array of term ids or slugs.
	 *
	 * @return WP_Query The query object with the results.
	 */
	private function get_products_query($discount_type, $match_type, $taxonomy_rules){	
		$filter = [
			'post_type'      => 'product',
			'posts_per_page' => -1,
			'fields'         => 'ids'
		];

		$rules = $taxonomy_rules ?? [];
		if ($discount_type !== 'taxonomy' || empty($rules)) {
			return new WP_Query($filter);
		}

		$tax_query = ['relation' => $match_type == 'all' ? 'AND' : 'OR' ];

		foreach ($rules as $rule) {
			$taxonomy = sanitize_text_field($rule['taxonomy'] ?? '');
			$operator = sanitize_text_field($rule['operator'] ?? 'eq');
			$terms    = $rule['terms'] ?? [];

			if( !is_array( $terms ) ) {
				$terms = [$terms];
			}

			if (!$taxonomy || empty($terms)) continue;

			$tax_query[] = [
				'taxonomy' => $taxonomy,
				'field'    => 'term_id',
				'terms'    => array_map('sanitize_text_field', $terms),
				'operator' => $this->translate_operator($operator),
			];
		}

		$filter['tax_query'] = $tax_query;

		return new WP_Query($filter);
	}
	
	/**
	 * Retrieves a scheduled discount data.
	 *
	 * This function takes a discount id and retrieves the discount data from the database.
	 * The data includes the discount id, name, discount type, rule type, start date, end date,
	 * discount amount type, discount amount, status, and taxonomy rules.
	 *
	 * @param int $discount_id The id of the discount.
	 *
	 * @return array|false The discount data or false if the discount does not exist.
	 */
	public function get_scheduled_discount_data( $discount_id ){
		$discount_id = sanitize_text_field( $discount_id );
		
		$discount_row = $this->wpdb->get_row( $this->wpdb->prepare( "SELECT * FROM {$this->wpdb->prefix}wpcd_discounts WHERE id=%d", $discount_id ) );
		if( empty( $discount_row ) ){
			return false;
		}
		$discount_data = [
			'id' => $discount_id,
			'name' => $discount_row->name ?? '',
			'discount_type' => $discount_row->discount_type == 0 ? 'all_products' : ( $discount_row->discount_type == 1 ? 'taxonomy' : ($discount_row->discount_type == 2 ? 'cart' : 'quantity') ),
			'rule_type' => $discount_row->rule_type == 0 ? 'all' : 'any',
			'start_date' => $discount_row->start_date ?? '',
			'end_date' => $discount_row->end_date ?? '',
			'discount_amount_type' => $discount_row->discount_amount_type == 0 ? 'percentage' : 'flat',
			'discount_amount' => $discount_row->discount_amount ?? 0,
			'status' => $discount_row->status ?? 0,
			'total_chunks' => $discount_row->total_chunks ?? 0,
			'processed_chunks' => $discount_row->processed_chunks ?? 0,
			'taxonomy_rules' => [],
			'cart_discount_type' => 0,
			'min_cart_value' => null,
			'max_cart_value' => null,
			'discount_applicable_with_other_discount' => 1,
			'automatically_add_type' => 1,
			'free_products' => [],
		];

		if( $discount_row->discount_type == 1 || $discount_row->discount_type == 3 ){
			$taxonomy_rules = [];
			$discount_taxonomies = $this->wpdb->get_results( $this->wpdb->prepare( "SELECT * FROM {$this->wpdb->prefix}wpcd_taxonomy_discount_terms WHERE discount_id=%d", $discount_id ) );
			foreach( $discount_taxonomies as $discount_taxonomy ){
				$taxonomy_rules[] = [
					'taxonomy' => $discount_taxonomy->taxonomy ?? '',
					'terms' => empty( $discount_taxonomy->terms ) ? [] : explode(',', $discount_taxonomy->terms),
					'operator' => $this->translate_operator_key( $discount_taxonomy->operator ?? '' )
				];
			}

			$discount_data['taxonomy_rules'] = $taxonomy_rules;
		} 
		
		if ( $discount_row->discount_type == 2 || $discount_row->discount_type == 3 ) {
			$cart_discount_data = $this->wpdb->get_row( $this->wpdb->prepare( "SELECT * FROM {$this->wpdb->prefix}wpcd_cart_discount_rules WHERE discount_id=%d", $discount_id ) );
			$discount_data['cart_discount_type'] = $cart_discount_data->cart_discount_type == 1 ? 'free_product' : 'cart_value';
			$discount_data['min_cart_value'] = $cart_discount_data->min_cart_value ?? null;
			$discount_data['max_cart_value'] = $cart_discount_data->max_cart_value ?? null;
			$discount_data['discount_applicable_with_other_discount'] = $cart_discount_data->discount_applicable_with_other_discount == 1 ? 'yes' : 'no';
			$discount_data['automatically_add_type'] = $cart_discount_data->automatically_add_type == 1 ?'yes' : 'no';
			if( $cart_discount_data->cart_discount_type == 1 ){
				$free_products = $this->wpdb->get_col( $this->wpdb->prepare( "SELECT product_id FROM {$this->wpdb->prefix}wpcd_cart_discount_rules_products WHERE discount_id=%d", $discount_id ) );
				$discount_data['free_products'] = $free_products;
				$free_products_data = [];
				foreach( $free_products as $free_product_id ){
					$free_product = wc_get_product( $free_product_id );
					$free_products_data[] = [
						'value' => $free_product_id,
						'label' => get_the_title( $free_product_id ),
					];
				}
				$discount_data['free_products_data'] = $free_products_data;
			}
		}

		return $discount_data;
	}

	/**
	 * Schedule or unschedule discount events based on the discount's status and dates.
	 *
	 * This function checks the status of a discount and its start and end dates
	 * to determine whether to schedule or unschedule events related to discount
	 * application and removal.
	 *
	 * @param array $discount_data The discount data which includes status, start date, and end date.
	 */
	private function maybe_inactive_discount( $discount_data ){
		if( empty( $discount_data ) || !is_array( $discount_data ) || !isset( $discount_data['status'], $discount_data['start_date'], $discount_data['end_date'] ) || $discount_data['discount_type'] == 'cart' || $discount_data['discount_type'] == 'quantity' ){
			return;
		}

		if( $discount_data['status'] == 1 ){
			if( !empty( $discount_data['start_date'] ) && strtotime( $discount_data['start_date'] . ' 00:00:00' ) > time() ){
				wp_unschedule_event( strtotime( $discount_data['start_date'] . ' 00:00:00' ), 'wpcd_apply_discount_setup', [$discount_data] );
			} else {
				wp_schedule_single_event(time(), 'wpcd_remove_discount_setup', [$discount_data]);
			}

			if( !empty( $discount_data['end_date'] ) && strtotime( $discount_data['end_date'] . ' 23:59:59' ) > time() ){
				wp_unschedule_event( strtotime( $discount_data['end_date'] . ' 23:59:59' ), 'wpcd_remove_discount_setup', [$discount_data] );
			}
		}
	}

	/**
	 * Deletes a discount and any associated taxonomy rules.
	 *
	 * @param array $discount_data The discount data to delete.
	 */
	private function delete_discount( $discount_data ){
		if( empty( $discount_data ) || !is_array( $discount_data ) || !isset( $discount_data['id'] ) ){
			return;
		}

		$this->maybe_inactive_discount( $discount_data );
		$this->wpdb->delete( $this->wpdb->prefix . 'wpcd_discount_taxonomies', [ 'discount_id' => $discount_data['id'] ] );
		$this->wpdb->delete( $this->wpdb->prefix . 'wpcd_discounts', [ 'id' => $discount_data['id'] ] );
	}

	/**
	 * Translate the given operator from the frontend to the value expected by WordPress's taxonomy queries.
	 *
	 * @param string $operator The operator to translate. Can be 'eq', 'neq', 'in', 'not_in'.
	 *
	 * @return string The translated operator.
	 */
	private function translate_operator(string $operator): string {
		switch ($operator) {
			case 'eq':
			case 'in':
				return 'IN';
			case 'neq':
			case 'not_in':
				return 'NOT IN';
			default:
				return 'IN';
		}
	}

	/**
	 * Translate the given operator from the frontend to the id expected by the database.
	 *
	 * @param string $operator The operator to translate. Can be 'eq', 'neq', 'in', 'not_in'.
	 *
	 * @return int The translated id.
	 */
	private function translate_operator_id($operator){
		switch ($operator) {
			case 'eq': return 1;
			case 'neq': return 0;
			case 'in': return 2;
			case 'not_in': return 3;
			default: return 2;
		}
	}

	/**
	 * Translate the given id to the corresponding operator key used in taxonomy queries.
	 *
	 * @param int $id The id to translate. Can be 1, 0, 2, or 3.
	 *
	 * @return string The translated operator key. Returns 'eq', 'neq', 'in', or 'not_in'.
	 */
	private function translate_operator_key($id){
		switch ($id) {
			case 1: return 'eq';
			case 0: return 'neq';
			case 2: return 'in';
			case 3: return 'not_in';
			default: return 'in';
		}
	}
}
