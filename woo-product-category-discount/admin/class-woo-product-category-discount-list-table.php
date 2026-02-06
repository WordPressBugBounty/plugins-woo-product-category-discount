<?php
class WPCD_Discount_List_Table extends WPCD_List_Table {

    /**
     * Retrieves the list of columns for the discount list table.
     *
     * The array keys represent the internal column names and the array values
     * provide the corresponding column titles, which are translated for display.
     *
     * @return array An associative array of column identifiers and their display titles.
     */
    public function get_columns() {
        return [
            'name' => __('Name', 'woo-product-category-discount'),
            'discount_type' => __('Discount Type', 'woo-product-category-discount'),
            'discount_amount' => __('Amount', 'woo-product-category-discount'),
            'start_date' => __('Start Date', 'woo-product-category-discount'),
            'end_date' => __('End Date', 'woo-product-category-discount'),
            'status' => __('Status', 'woo-product-category-discount'),
        ];
    }

    /**
     * Retrieves the list of sortable columns for the discount list table.
     *
     * The array keys represent the internal column names and the array values
     * provide the corresponding column titles, which are translated for display.
     *
     * @return array An associative array of column identifiers and their display titles.
     */
    public function get_sortable_columns() {
        return [
            'name' => ['name', true],
        ];
    }
    
    /**
     * Prepares the list of items for displaying.
     *
     * @since 5.0
     *
     * @return void
     */
    public function prepare_items() {
        global $wpdb;

        $table = $wpdb->prefix . 'wpcd_discounts';
        $cart_rule_table = $wpdb->prefix . 'wpcd_cart_discount_rules';

        $per_page = 10;
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;

        $orderby = isset($_REQUEST['orderby']) ? esc_sql($_REQUEST['orderby']) : 'id';
        $order = isset($_REQUEST['order']) && $_REQUEST['order'] === 'asc' ? 'ASC' : 'DESC';

        $search = '';
        if (!empty($_REQUEST['s'])) {
            $like = '%' . $wpdb->esc_like($_REQUEST['s']) . '%';
            $search = $wpdb->prepare("WHERE name LIKE %s", $like);
        }

        // Query total items
        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table $search");

        // Query items for current page
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT d.id, d.name, d.discount_type, d.rule_type, d.start_date, d.end_date, d.discount_amount_type, d.discount_amount, d.status, d.total_chunks, d.processed_chunks, d.user_id, d.updated_at, cr.cart_discount_type, cr.min_cart_value, cr.max_cart_value FROM $table d
            LEFT JOIN $cart_rule_table cr ON d.id = cr.discount_id 
            $search 
            ORDER BY $orderby $order 
            LIMIT %d OFFSET %d
        ", $per_page, $offset), ARRAY_A);

        $this->items = $results;
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ]);

        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns()];
    }

    /**
     * Handles output for the default column.
     *
     * @param array $item        The current item.
     * @param string $column_name Identifier for the custom column.
     *
     * @return string Text or HTML to be placed inside the column <td>
     */
    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'discount_type':
                switch( $item[$column_name] ){
                    case 0:
                        $output = __('All Products','woo-product-category-discount');
                        break;

                    case 1:
                        global $wpdb;
                        $output = __('Taxonomy','woo-product-category-discount');
                        $relations = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wpcd_taxonomy_discount_terms WHERE discount_id = %d", $item['id'] ) );
                        if( count( $relations ) > 0 ){
                           $terms_output = [];
                            foreach ( $relations as $rel ) {
                                $terms = explode(',', $rel->terms);
                                foreach( $terms as $term_id){
                                    $term = get_term( (int) $term_id, $rel->taxonomy );
                                    if ( $term && ! is_wp_error( $term ) ) {
                                        $terms_output[$rel->taxonomy][] = $term;
                                    }
                                }
                            }

                            $output .= '<div class="taxonomy-details">';
                            $elements = 0;
                            foreach( $terms_output as $taxonomy => $terms ){
                                $output .= '<div class="taxonomy-data ' . ($elements > 0 ? 'wpcd-hidden' : '') . '">';
                                $output .= '<span class="tax-name">' . esc_html( get_taxonomy( $taxonomy )->labels->singular_name ). ': </span>';
                                $output .= '<span class="tax-terms">';

                                foreach( $terms as $term ){
                                    $output .= '<span class="tax-term ' . ($elements > 0 ? 'wpcd-hidden' : '') . '"><a target="_blank" href="' . get_edit_term_link( (int) $term->term_id, $taxonomy ) . '">' . esc_html( $term->name ) . '</a></span>';
                                    ++$elements;
                                }

                                $output .= '</span></div>';
                            }

                            if( $elements > 1 ){
                                $output .= '<div class="view-more"><a class="view-more-btn" href="javascript:void(0);">' . __('View More', 'woo-product-category-discount') . '</a></div>';
                            }
                        }
                        break;

                    case 2:
                        $output = __('Cart Value','woo-product-category-discount');
                        break;
                        
                    case 3:
                        $output = __('Cart Quantity','woo-product-category-discount');
                        break;
                        
                    default:
                        $output = $item[$column_name];
                    }
                return $output;

            case 'discount_amount':
                if( $item['discount_type'] == 2 && $item['cart_discount_type'] == 1){
                    return __('Free Products', 'woo-product-category-discount');
                }
                return $item['discount_amount_type'] == 0 ? $item[$column_name] . '%' : $item[$column_name];
            
            case 'start_date':
            case 'end_date':
                return !is_null( $item[$column_name] ) ? date_i18n( get_option( 'date_format' ), strtotime( $item[$column_name] ) ) : __('N/A', 'woo-product-category-discount');    
            
            default:
                return $item[$column_name];
        }
    }

    /**
     * Handles output for the name column.
     *
     * @param array $item The current item.
     *
     * @return string Text or HTML to be placed inside the column <td>
     */
    public function column_name($item) {
        $is_view = wpcd_get_admin_discount_status( $item ) == 'processing';
        if( $is_view ){
            $view_url = admin_url('admin.php?page=woo-product-category-discount&action=view-progress&id=' . $item['id']);
            $actions['view'] = '<a href="' . esc_url($view_url) . '">' . __('View Progress', 'woo-product-category-discount') . '</a>';
            if( empty($item['end_date']) && get_option('wpcd_process_method', 'ajax') == 'ajax' ){
                $actions['view'] .= ' | <a data-discount-id="' . $item['id'] . '" class="terminate-progress-link" href="' . esc_url($view_url) . '">' . __('Terminate Process', 'woo-product-category-discount') . '</a>';
            }
            $output = '<strong onclick="statusCheck(this)" style="cursor:pointer;">' . esc_html($item['name']) . '</strong>' . $this->row_actions($actions);
        } else {
            $edit_url = admin_url('admin.php?page=woo-product-category-discount&action=edit&id=' . $item['id']);
            $delete_url = wp_nonce_url(
                admin_url('admin.php?page=woo-product-category-discount&action=delete&id=' . $item['id']),
                'wpcd_delete_discount_' . $item['id']
            );
            
            $actions = [
                'edit' => '<a href="' . esc_url($edit_url) . '">' . __('Edit', 'woo-product-category-discount') . '</a>',
            ];

            if( $item['status'] == 0 ){
                $actions['delete'] = '<a href="' . esc_url($delete_url) . '" onclick="return confirm(\'' . esc_js(__('Are you sure you want to delete this discount?', 'woo-product-category-discount')) . '\')">' . __('Delete', 'woo-product-category-discount') . '</a>';
            }

            $output = '<strong><a href="' . esc_url($edit_url) . '">' . esc_html($item['name']) . '</a></strong>' . $this->row_actions($actions);
        }

        if ( ! empty( $item['updated_at'] ) ) {
            $updated_timestamp = strtotime( $item['updated_at'] );
            $user             = get_userdata( $item['user_id'] );
            $user_name        = $user ? esc_html( $user->display_name ) : __( 'Unknown', 'woo-product-category-discount' );

            $tooltip_text = sprintf(
                /* translators: 1: user display name, 2: formatted date */
                __( 'Last Updated: %1$s by %2$s', 'woo-product-category-discount' ),
                date_i18n( get_option( 'date_format' ), $updated_timestamp ),
                $user_name
            );

            $inner_output = sprintf(
                '<span class="description" title="%s">%s</span>',
                esc_attr( $tooltip_text ),
                esc_attr( $tooltip_text ),
            );
        }

        $output = '<span id="wpcd-item-' . $item['id'] . '">' . $output;

        if ( isset( $inner_output ) ) {
            $output .= $inner_output;
        }

        $output .= '</span>';

        return $output;
    }

    /**
     * Handles output for the status column.
     *
     * @param array $item The current item.
     *
     * @return string Text or HTML to be placed inside the column <td>
     */
    public function column_status( $item ){
        echo wpcd_get_admin_discount_status_html($item);
    }
}
