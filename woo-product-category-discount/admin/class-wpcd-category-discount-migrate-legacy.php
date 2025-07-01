<?php

/**
 * The legacy migration functionality of the plugin.
 *
 * @link       https://www.quanticedgesolutions.com
 * @since      1.0.0
 *
 * @package    WPCD_Category_Discount
 * @subpackage WPCD_Category_Discount/admin
 */
class WPCD_Category_Discount_Migrate_Legacy {

	/**
	 * wpdb object
	 * 
	 * @since	1.0.0
	 * @access  private
	 * @var		object 		$wpdb 			Object of wpdb
	 */
	private $wpdb;


    /**
     * admin_object object
     * 
     * @since 1.0.0
     * @access private
     * @var     object 		$admin_object 			Object of admin class
     */
    private $admin_object;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {
		global $wpdb;
		$this->wpdb = $wpdb;
        $this->admin_object = new WPCD_Category_Discount_Admin( $plugin_name, $version );
	}

    /**
     * Migrate the data from the old plugin to the new plugin.
     *
     * This function is used to migrate the data from the old plugin to the new plugin.
     *
     * @since 1.0.0
     * @return void
     */
    public function migrate_data(){
        if (get_option('wpcd_migration_complete')) {
            return;
        }

        $category_discount = get_option('wpcd_category_discount', serialize( array() ) );
        $category_discount = maybe_unserialize( $category_discount );

        if (is_array($category_discount) && count($category_discount) > 0) {
            foreach ($category_discount as $category_id => $discount) {
                $this->insert_discount( 'product_cat', $category_id, $discount );
            }
        }

        $attribute_discount = get_option('wpcd_attr_discount', serialize( array() ) );
        $attribute_discount = maybe_unserialize( $attribute_discount );

        if (is_array($attribute_discount) && count($attribute_discount) > 0) {
            foreach ($attribute_discount as $attribute_id => $discount) {
                $term = get_term( $attribute_id );
                if( is_wp_error( $term ) ){
                    continue;
                }
                $this->insert_discount( $term->taxonomy, $attribute_id, $discount );
            }
        }

        $brand_discount = get_option('wpcd_brand_discount', serialize( array() ) );
        $brand_discount = maybe_unserialize( $brand_discount );

        if (is_array($brand_discount) && count($brand_discount) > 0) {
            foreach ($brand_discount as $brand_id => $discount) {
                $this->insert_discount( 'product_brand', $brand_id, $discount );
            }
        }

        $tag_discount = get_option('wpcd_tag_discount', serialize( array() ) );
        $tag_discount = maybe_unserialize( $tag_discount );

        if (is_array($tag_discount) && count($tag_discount) > 0) {
            foreach ($tag_discount as $tag_id => $discount) {
                $this->insert_discount( 'product_tag', $tag_id, $discount );
            }
        }

        update_option('wpcd_migration_complete', 1);
    }

    /**
     * Insert a discount for a given term id and discount data into the database.
     *
     * @param string $taxonomy The taxonomy of the term.
     * @param int $term_id The id of the term.
     * @param array $discount The discount data.
     *
     * @return void
     */
    private function insert_discount( $taxonomy, $term_id, $discount ){
        $start_date = !empty( $discount['fDate'] ) ? DateTime::createFromFormat('Y/m/d H:i', $discount['fDate']) : null;
        if( !is_null( $start_date ) ){
            $start_date = $start_date->format('Y-m-d');
        }

        $end_date = !empty( $discount['tDate'] ) ? DateTime::createFromFormat('Y/m/d H:i', $discount['tDate']) : null;
        if( !is_null( $end_date ) ){
            $end_date = $end_date->format('Y-m-d');
        }

        $term = get_term( $term_id );
        
        if( is_wp_error( $term ) ){
            return;
        }

        $discount_data = [
            'name' => 'Migrated Discount ' . $term->name,
            'discount_type' => 1,
            'rule_type' => 0,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'discount_amount_type' => $discount['type'] == '% of Price' ? 0 : 1,
            'discount_amount' => $discount['value'],
            'status' => ($discount['isActive'] === "true" || $discount['isScheduled'] === "true") ? 1 : 0,
            'total_chunks' => 0,
        ];

        $this->wpdb->insert( $this->wpdb->prefix . 'wpcd_discounts', $discount_data );

        $discount_id = $this->wpdb->insert_id;

        $taxonomy_data = [
            'discount_id' => $discount_id,
            'taxonomy' => $taxonomy,
            'terms' => $term_id,
            'operator' => 2
        ];

        $this->wpdb->insert( $this->wpdb->prefix . 'wpcd_taxonomy_discount_terms', $taxonomy_data );
    
        $discount_data = $this->admin_object->get_scheduled_discount_data( $discount_id );

        if( $discount_data['status'] == 1 && $discount['isScheduled'] === "true" ){
            if( strtotime( $start_date . ' 00:00:00' ) > time() ){
                wp_schedule_single_event( strtotime( $start_date . ' 00:00:00'), 'wpcd_apply_discount_setup', [$discount_data] );
            }

            if( !empty( $end_date ) && strtotime( $end_date . ' 23:59:59' ) > time() ){
                wp_schedule_single_event( strtotime( $end_date . ' 23:59:59'), 'wpcd_remove_discount_setup', [$discount_data] );
            }
        }

        if( $discount_data['status'] == 1 || ( $discount['isScheduled'] === "true" && ( $start_date >= date('Y-m-d') || $end_date >= date('Y-m-d') ) ) ){
            $product_ids = $this->wpdb->get_col( $this->wpdb->prepare( "SELECT post_id FROM {$this->wpdb->postmeta} WHERE meta_key = '_wpcd_cats' AND meta_value = %d", $term_id ) );
            $product_ids = array_chunk( $product_ids, 50 );
            $time = time();
            foreach( $product_ids as $product_ids_chunk ){
                wp_schedule_single_event( $time, 'wpcd_discount_legacy_migrate', [$discount_id, $product_ids_chunk] );
                $time += 10;
            }
        }
    }

    /**
     * Set migration keys for a given discount id and product ids.
     *
     * @param int $discount_id The id of the discount.
     * @param array $product_ids The list of product ids to which the discount should be applied.
     *
     * @return void
     */
    public function set_migration_keys( $discount_id, $product_ids ){
        foreach( $product_ids as $product_id ){
            $product = wc_get_product( $product_id );
            if( $product->is_type('variable') ){
                foreach( $product->get_children() as $variation_id ){
                    $variation = wc_get_product( $variation_id );
                    update_post_meta( $variation_id, '_wpcd_discount_id', $discount_id );
                    update_post_meta( $variation_id, '_wpcd_original_regular_price', $variation->get_regular_price() );
                    update_post_meta( $variation_id, '_wpcd_original_sale_price', '' );
                    update_post_meta( $variation_id, '_wpcd_original_price', $variation->get_regular_price() );
                }
            } else {
                update_post_meta( $product_id, '_wpcd_discount_id', $discount_id );
                update_post_meta( $product_id, '_wpcd_original_regular_price', $product->get_regular_price() );
                update_post_meta( $product_id, '_wpcd_original_sale_price', '' );
                update_post_meta( $product_id, '_wpcd_original_price', $product->get_regular_price() );
            }
        }
    }
}