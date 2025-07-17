<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.quanticedgesolutions.com
 * @since      1.0.0
 *
 * @package    WPCD_Category_Discount
 * @subpackage WPCD_Category_Discount/public
 */
class WPCD_Category_Discount_Public {

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
	 * The database object.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      object    $wpdb    The database object.
	 */
	private $wpdb;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		global $wpdb;
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->wpdb = $wpdb;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wpcd-category-discount-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wpcd-category-discount-public.js', array( 'jquery' ), $this->version, false );
		if( is_cart() ){
			$gifts = array();

			$cart = WC()->cart;
			$subtotal = $cart->get_cart_contents_total();
			$tax_total = $cart->get_cart_contents_tax();
			$discount_total = $cart->get_discount_total();
			$cart_total = $subtotal + $tax_total - $discount_total;
			
			$applicable_discounts = $this->get_the_available_discounts( $cart_total, "free_product", "no" );

			foreach( $applicable_discounts as $discount ){
				foreach( $discount['free_products'] as $free_product_id ){
					$product = wc_get_product( $free_product_id );
					$gifts[] = [
						'id'     => $product->get_id(),
						'type'   => $product->get_type(),
						'name'   => $product->get_name(),
						'image'  => wp_get_attachment_image_url($product->get_image_id(), 'medium'),
						'price'  => '<span class="price"><del>' . wc_price( $product->get_regular_price() ) . '</del> ' . wc_price( 0 ) . '</span>',
						'parent_id' => $product->is_type('variation') ? $product->get_parent_id() : 0,
					];
				}
			}

			if( !empty( $gifts ) ){
				$added_items = array();
				foreach( $cart->get_cart() as $cart_item_key => $cart_item ){
					if( !empty( $cart_item['is_free_gift_optional'] ) ){
						$variation_id = !empty($cart_item['variation_id']) ? $cart_item['variation_id'] : 0;
						$product_id = $variation_id > 0 ? $variation_id : $cart_item['product_id'];
						$added_items[] = $product_id;
					}
				}

				foreach( $gifts as $key => $gift ){
					if( in_array( $gift['id'], $added_items ) ){
						unset( $gifts[$key] );
					}
				}
			}

			if( !empty( $gifts ) ){
				wp_enqueue_script( $this->plugin_name . '-free-gift-popup', plugin_dir_url( __FILE__ ) . 'js/wpcd-category-discount-public-free-gift-popup.js', array( 'jquery' ), $this->version, false );
				wp_localize_script( $this->plugin_name . '-free-gift-popup', 'wpcd_free_gift_popup', array( 
					'ajaxurl' 	=> admin_url( 'admin-ajax.php' ),
					'nonce' 	=> wp_create_nonce( 'wpcd_free_gift_nonce' ),
					'gifts' 	=> $gifts,
					'i18n'  	=> array(
						'close' 			=> __( 'Close', 'wpcd-category-discount' ),
						'add_to_cart' 		=> __( 'Add to cart', 'wpcd-category-discount' ),
						'free_gift_label'	=> __( 'Free gifts', 'wpcd-category-discount' ),
					)
				) );
			}
		}
	}

	/**
	 * Adds the available discount to the cart.
	 *
	 * @param WC_Cart $cart The cart object.
	 *
	 * @return WC_Cart The updated cart object.
	 */
	public function add_discount_to_cart( $cart ){
		if( is_admin() && !defined( 'DOING_AJAX' ) ){
			return;
		}

		$subtotal = $cart->get_cart_contents_total();
		$tax_total = $cart->get_cart_contents_tax();
		$discount_total = $cart->get_discount_total();
		$cart_total = $subtotal + $tax_total - $discount_total;

		$discounts = $this->get_the_available_discounts($cart_total, "cart_value");

		$exclusive_discounts = [0];
		$non_exclusive_discount = 0;

		foreach( $discounts as $discount ){
			if( $discount['discount_amount_type'] == 'percentage' ){
				$discount_amount = $cart_total * $discount['discount_amount'] / 100;
			} else {
				$discount_amount = $discount['discount_amount'];
			}

			if( $discount['discount_applicable_with_other_discount'] ){
				$non_exclusive_discount += $discount_amount;
			} else {
				$exclusive_discounts[] = $discount_amount;
			}
		}

		$exclusive_discount = max($exclusive_discounts);

		$final_discount = $exclusive_discount > $non_exclusive_discount ? $exclusive_discount : $non_exclusive_discount;

		$quantity_discount = $this->get_the_quantity_discounts();
		if( !empty( $quantity_discount ) ){
			$final_discount += array_sum( $quantity_discount );
		}

		if( $final_discount > $cart_total ){
			$final_discount = $cart_total;
		}

		if( $final_discount > 0 ){
			$cart->add_fee( __('Discount', 'wpcd-category-discount'), -$final_discount );
		}

		return $cart;
	}

	/**
	 * This function is an action on the woocommerce_add_to_cart action and is
	 * responsible for setting a flag to indicate that free products should be
	 * added to the cart when the cart is refreshed.
	 *
	 * The function will only set the flag if the following conditions are met:
	 * 1. The request is not from an AJAX call (i.e. DOING_AJAX is not defined)
	 * 2. The request is not from an admin page (i.e. is_admin() is true)
	 */
	public function set_cart_refresh_flag(){
		if ( is_admin() && !defined('DOING_AJAX') ) return;
    	WC()->session->set('should_add_free_items', true);
	}

	/**
	 * This function is an action on the woocommerce_cart_refresh action and is
	 * responsible for adding free products to the cart when the cart is refreshed.
	 *
	 * The function will only add free products if the
	 * 'should_add_free_items' flag is set in the WC session.
	 *
	 * Note: The flag is set in the set_cart_refresh_flag function which is an
	 * action on the woocommerce_applied_coupon action.
	 */
	public function add_free_products_to_cart(){
		if ( is_admin() && !defined('DOING_AJAX') ) return;

		if ( WC()->session->get('should_add_free_items') ) {
			WC()->session->set('should_add_free_items', false);
			$this->auto_add_free_discount_products();
		}
	}

	/**
	 * Adds optional free gift products to cart via an AJAX request.
	 *
	 * The function is called when a user clicks the "Add to cart" button
	 * on the free gift popup modal. It checks that the request is valid
	 * by checking the nonce, and then adds the free products to the cart
	 * based on the applicable discounts.
	 *
	 * @return array|bool JSON response containing the added items or an error message.
	 */
	public function add_optional_gift_to_cart(){
		check_ajax_referer( 'wpcd_free_gift_nonce', 'nonce' );

		$cart = WC()->cart;
		$subtotal = $cart->get_cart_contents_total();
		$tax_total = $cart->get_cart_contents_tax();
		$discount_total = $cart->get_discount_total();
		$cart_total = $subtotal + $tax_total - $discount_total;
		
		$applicable_discounts = $this->get_the_available_discounts( $cart_total, "free_product", "no" );

		$added_items = array();
		foreach( $cart->get_cart() as $cart_item_key => $cart_item ){
			if( !empty( $cart_item['is_free_gift_optional'] ) ){
				$variation_id = !empty($cart_item['variation_id']) ? $cart_item['variation_id'] : 0;
				$product_id = $variation_id > 0 ? $variation_id : $cart_item['product_id'];
				$added_items[] = $product_id;
			}
		}

		foreach( $applicable_discounts as $discount ){
			foreach( $discount['free_products'] as $free_product_id ){
				if( in_array( $free_product_id, $added_items ) ){
					continue;
				}
				
				$product = wc_get_product( $free_product_id );
				if( $product->is_type('variation')){
					foreach ( $product->get_attributes() as $attribute_name => $attribute_value ) {
						$taxonomy = 'attribute_' . sanitize_title( $attribute_name );
						$variation_attributes[ $taxonomy ] = $attribute_value;
					}
					$cart->add_to_cart($product->get_parent_id(), 1, $free_product_id, $variation_attributes, ['is_free_gift_optional' => true]);
				} else {
					$cart->add_to_cart($free_product_id, 1, 0, [], ['is_free_gift_optional' => true]);
				}
			}
		}

		return wp_send_json_success();
	}

	/**
	 * Remove the optional free gifts from the cart.
	 *
	 * The function will remove the optional free gifts from the cart if the
	 * cart total is no longer applicable for the discounts.
	 *
	 * @return void
	 */
	public function remove_optional_gift_from_cart(){
		$cart = WC()->cart;

		$added_free_items = array();

		foreach( $cart->get_cart() as $cart_item_key => $cart_item ){
			if( !empty( $cart_item['is_free_gift_optional'] ) ){
				$variation_id = !empty($cart_item['variation_id']) ? $cart_item['variation_id'] : 0;
				$product_id = $variation_id > 0 ? $variation_id : $cart_item['product_id'];
				$added_free_items[] = $product_id;
			}
		}

		if( empty( $added_free_items ) ){
			return;
		}

		$subtotal = $cart->get_cart_contents_total();
		$tax_total = $cart->get_cart_contents_tax();
		$discount_total = $cart->get_discount_total();
		$cart_total = $subtotal + $tax_total - $discount_total;
		
		$applicable_discounts = $this->get_the_available_discounts( $cart_total, "free_product", "no" );
		$applicable_products = [];
		foreach( $applicable_discounts as $discount ){
			foreach( $discount['free_products'] as $free_product_id ){
				$applicable_products[] = $free_product_id;
			}
		}

		foreach( WC()->cart->get_cart() as $cart_item_key => $cart_item ){
			$is_free_gift_optional = !empty( $cart_item['is_free_gift_optional'] );
			$variation_id = !empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : 0;
			$product_id = $variation_id > 0 ? $variation_id : $cart_item['product_id'];

			if( $is_free_gift_optional && !in_array( $product_id, $applicable_products ) ){
				$cart->remove_cart_item( $cart_item_key );
			}
		}
	}

	/**
	 * Get formatted item data.
	 *
	 * @param array $item_data Item data.
	 * @param array $cart_item_data Cart item data.
	 * @return array
	 */
	public function get_item_data( $item_data, $cart_item_data ){
		if( ( isset( $cart_item_data['is_free_gift'] ) && !empty( $cart_item_data['is_free_gift'] ) ) || ( isset( $cart_item_data['is_free_gift_optional'] ) && !empty( $cart_item_data['is_free_gift_optional'] ) ) ){
			$item_data[] = array(
				'key' => '_free_gift',
				'value' => 'yes',
			);
		}
		return $item_data;
	}

	/**
	 * Adds free products to cart as per the discounts
	 *
	 * This function will add free products to the cart based on the available discounts.
	 * It will also remove any free products from the cart that are no longer applicable.
	 *
	 * @return void
	 */
	private function auto_add_free_discount_products(){
		$cart = WC()->cart;

		$subtotal = $cart->get_cart_contents_total();
		$tax_total = $cart->get_cart_contents_tax();
		$discount_total = $cart->get_discount_total();
		$cart_total = $subtotal + $tax_total - $discount_total;

		$discounts = $this->get_the_available_discounts($cart_total, 'free_product');
		$free_product_ids = [];
		foreach( $discounts as $discount ){
			if( !isset( $discount['free_products'] ) || empty( $discount['free_products'] ) ){
				continue;
			}
			$free_product_ids = array_merge( $free_product_ids, $discount['free_products']);
		}

		$existing_free_gift_ids = [];

		foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
			if (!empty($cart_item['is_free_gift'])) {
				$existing_free_gift_ids[] = $cart_item['product_id'];
			}
		}
		
		foreach ($free_product_ids as $product_id) {
			if (!in_array($product_id, $existing_free_gift_ids)) {
				$product = wc_get_product( $product_id );
				if( $product->is_type('variation')){
					foreach ( $product->get_attributes() as $attribute_name => $attribute_value ) {
						$taxonomy = 'attribute_' . sanitize_title( $attribute_name );
						$variation_attributes[ $taxonomy ] = $attribute_value;
					}
					$cart->add_to_cart($product->get_parent_id(), 1, $product_id, $variation_attributes, ['is_free_gift' => true]);
				} else {
					$cart->add_to_cart($product_id, 1, 0, [], ['is_free_gift' => true]);
				}
			}
		}

		foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
			$is_free_gift = !empty($cart_item['is_free_gift']);
			$variation_id = !empty($cart_item['variation_id']) ? $cart_item['variation_id'] : 0;
			$product_id = $variation_id > 0 ? $variation_id : $cart_item['product_id'];

			if ( $is_free_gift && !in_array($product_id, $free_product_ids) ) {
				$cart->remove_cart_item($cart_item_key);
			}
    	}
	}

	/**
	 * Sets the price of free discount products to zero in the cart.
	 *
	 * Additionally, it sets the quantity of free discount products to 1.
	 *
	 * @param WC_Cart $cart
	 */
	public function set_free_discount_products_zero_price($cart){
		if (is_admin() && !defined('DOING_AJAX')) {
			return;
		}

		foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
			if (!empty($cart_item['is_free_gift']) || !empty($cart_item['is_free_gift_optional'])) {
				$cart_item['data']->set_price(0);
			}

			if ( (!empty( $cart_item['is_free_gift'] ) && $cart_item['quantity'] != 1) || ( !empty( $cart_item['is_free_gift_optional'] ) && $cart_item['quantity'] != 1) ) {
				$cart->set_quantity( $cart_item_key, 1 );
			}
		}
	}

	/**
	 * Disables quantity input for free gift items in the cart.
	 *
	 * This function is called to ensure that the quantity of free gift items
	 * in the WooCommerce cart is always set to 1 and cannot be changed.
	 *
	 * @param string $product_quantity The current quantity HTML for the cart item.
	 * @param string $cart_item_key The cart item key.
	 * @param array $cart_item The cart item data array.
	 *
	 * @return string The modified quantity HTML for free gift items or the original for others.
	 */
	public function disable_quantity_for_free_gift($product_quantity, $cart_item_key, $cart_item ){
		if ( ! empty( $cart_item['is_free_gift'] ) || ! empty( $cart_item['is_free_gift_optional'] ) ) {
			return sprintf( '<input type="hidden" name="cart[%s][qty]" value="1" />1', $cart_item_key );
		}
		return $product_quantity;
	}

	/**
	 * Gets the available discounts given the total cart value and cart discount type.
	 * 
	 * @param int $total_cart_value The total cart value.
	 * @param string $cart_discount_type The cart discount type. Either 'cart_value' or 'free_product'.
	 * 
	 * @return array The available discounts.
	 * 
	 * @since 1.0.0
	 */
	private function get_the_available_discounts($total_cart_value=0, $cart_discount_type='cart_value', $auto_add_to_cart='yes'){
		$date = date('Y-m-d');

		$cart_discount_type = ($cart_discount_type == 'cart_value') ? 0 : 1;

		$automatically_add_type = ($auto_add_to_cart == 'yes') ? 1 : 0;

		$where = "cr.cart_discount_type='{$cart_discount_type}'";

		$query = $this->wpdb->prepare("
			SELECT d.id, d.discount_amount_type, d.discount_amount, cr.cart_discount_type, cr.discount_applicable_with_other_discount, cr.automatically_add_type FROM {$this->wpdb->prefix}wpcd_discounts d 
			LEFT JOIN {$this->wpdb->prefix}wpcd_cart_discount_rules cr ON d.id=cr.discount_id 
			WHERE 
			d.discount_type=2 AND 
			$where AND
			d.status=1 AND (
				(cr.min_cart_value IS NULL AND cr.max_cart_value IS NULL) OR
				(cr.min_cart_value IS NULL AND %d <= cr.max_cart_value) OR
				(cr.max_cart_value IS NULL AND %d >= cr.min_cart_value) OR
				(%d >= cr.min_cart_value AND %d <= cr.max_cart_value)
			) AND (
				(d.start_date IS NULL AND d.end_date IS NULL) OR
				(d.start_date IS NULL AND %s <= d.end_date) OR 
				(d.end_date IS NULL AND %s >= d.start_date) OR
				(%s >= d.start_date AND %s <= d.end_date)
			) AND cr.automatically_add_type=%d
		", $total_cart_value, $total_cart_value, $total_cart_value, $total_cart_value, $date, $date, $date, $date, $automatically_add_type );

		$discounts = $this->wpdb->get_results( $query, ARRAY_A );

		foreach( $discounts as $discount_id => $discount ){
			$discounts[$discount_id]['discount_amount_type'] = $discount['discount_amount_type'] == 0 ? 'percentage' : 'flat';
			if( $discount['cart_discount_type'] == 1 ){
				$discounts[$discount_id]['cart_discount_type'] = 'free_product';
				$discounts[$discount_id]['free_products'] = $this->wpdb->get_col( $this->wpdb->prepare( "SELECT product_id FROM {$this->wpdb->prefix}wpcd_cart_discount_rules_products WHERE discount_id=%d", $discount['id'] ) );
			} else {
				$discounts[$discount_id]['cart_discount_type'] = 'cart_value';
			}

			$discounts[$discount_id]['discount_applicable_with_other_discount'] = $discount['discount_applicable_with_other_discount'] == 1;
			$discounts[$discount_id]['automatically_add_type'] = $discount['automatically_add_type'] == 1;
		}

		return $discounts;
	}

	/**
	 * Get the applicable quantity discounts.
	 *
	 * @return array An array of applicable quantity discounts.
	 */
	private function get_the_quantity_discounts(){
		$date = date('Y-m-d');

		$query = $this->wpdb->prepare("
			SELECT d.id, d.rule_type, d.discount_amount_type, d.discount_amount, cr.min_cart_value, cr.max_cart_value FROM {$this->wpdb->prefix}wpcd_discounts d 
			INNER JOIN {$this->wpdb->prefix}wpcd_cart_discount_rules cr ON d.id=cr.discount_id 
			WHERE 
			d.discount_type=3 AND 
			d.status=1 AND (
				(d.start_date IS NULL AND d.end_date IS NULL) OR
				(d.start_date IS NULL AND %s <= d.end_date) OR 
				(d.end_date IS NULL AND %s >= d.start_date) OR
				(%s >= d.start_date AND %s <= d.end_date)
			)
		", $date, $date, $date, $date );
		
		$discounts = $this->wpdb->get_results( $query, ARRAY_A );

		$applicable_discounts = array();
		
		foreach( $discounts as $discount_id => $discount ){
			$matched_quantity = 0;
			$matched_total = 0;

			$terms = $this->wpdb->get_results( $this->wpdb->prepare( "SELECT * FROM {$this->wpdb->prefix}wpcd_taxonomy_discount_terms WHERE discount_id=%d", $discount['id'] ) );
	
			if( empty( $terms ) ){
				if( WC()->cart->get_cart_contents_count() >= $discount['min_cart_value'] && ( empty( $discount['max_cart_value'] ) || WC()->cart->get_cart_contents_count() <= $discount['max_cart_value'] ) ){
					$applicable_discounts[] = $discount['discount_amount_type'] == 0 ? (( WC()->cart->get_subtotal() * $discount['discount_amount'] ) / 100) : $discount['discount_amount'];
				}
				continue;
			}

			$discount_terms = array();
			foreach( $terms as $term ){
				$terms = explode(',', $term->terms);

				$terms = wpcd_get_related_terms( $terms, 'taxonomy', $term->taxonomy );

				$discount_terms[] = array(
					'taxonomy' => $term->taxonomy,
					'terms' => $terms,
					'operator' => $term->operator == 2 ? 'IN' : 'NOT IN',
				);
			}
			
			foreach( WC()->cart->get_cart() as $item_key => $item ){
				$product_id = $item['product_id'];

				if ($this->product_matches_conditions($product_id, $discount_terms, $discount['rule_type'] == 0 ? 'AND' : 'OR')) {
					$matched_quantity += $item['quantity'];
					$matched_total += $item['data']->get_price() * $item['quantity'];
				}
			}

			if( $matched_quantity >= $discount['min_cart_value'] && ( empty( $discount['max_cart_value'] ) || $matched_quantity <= $discount['max_cart_value'] ) ){
				$applicable_discounts[] = $discount['discount_amount_type'] == 0 ? (( $matched_total * $discount['discount_amount'] ) / 100) : $discount['discount_amount'];
			}
		}

		return $applicable_discounts;
	}

	/**
	 * Check if a product matches the given conditions.
	 *
	 * @param int $product_id The product id to check.
	 * @param array $conditions The conditions to check against.
	 * @param string $match_type The type of match to apply. 'AND' or 'OR'.
	 *
	 * @return boolean True if the product matches the conditions, false otherwise.
	 */
	private function product_matches_conditions($product_id, $conditions, $match_type = 'AND') {
		$results = [];

		foreach ($conditions as $condition) {
			$taxonomy = $condition['taxonomy'] ?? null;
			$terms = $condition['terms'] ?? [];
			$operator = strtoupper($condition['operator'] ?? 'IN');

			if (!$taxonomy || empty($terms)) {
				$results[] = false;
				continue;
			}

			$product_terms = wc_get_product_term_ids($product_id, $taxonomy);

			$intersect = array_intersect($product_terms, $terms);

			if ($operator === 'IN') {
				$results[] = !empty($intersect);
			} elseif ($operator === 'NOT IN') {
				$results[] = empty($intersect);
			} else {
				$results[] = false;
			}
		}

		if ($match_type === 'AND') {
			return !in_array(false, $results, true);
		} elseif ($match_type === 'OR') {
			return in_array(true, $results, true);
		}

		return false;
	}

}
