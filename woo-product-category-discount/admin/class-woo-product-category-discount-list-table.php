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
            SELECT d.id, d.name, d.discount_type, d.rule_type, d.start_date, d.end_date, d.discount_amount_type, d.discount_amount, d.status, d.total_chunks, d.processed_chunks, cr.cart_discount_type, cr.min_cart_value, cr.max_cart_value FROM $table d
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
                return $item[$column_name] == 0 ? __('All Products','woo-product-category-discount') : ( $item[$column_name] == 1 ? __('Taxonomy','woo-product-category-discount') : ( $item[$column_name] == 2 ? __('Cart Value','woo-product-category-discount') : __('Quantity','woo-product-category-discount')) );
            case 'discount_amount':
                if( $item['discount_type'] == 2 && $item['cart_discount_type'] == 1){
                    return __('Free Products', 'woo-product-category-discount');
                }
                return $item['discount_amount_type'] == 0 ? $item[$column_name] . '%' : $item[$column_name];
            case 'status':
                $today = date('Y-m-d');
                $start_date = isset($item['start_date']) ? $item['start_date'] : null;
                $end_date = isset($item['end_date']) ? $item['end_date'] : null;
                if( $item['discount_type'] == 2 || $item['discount_type'] == 3){
                    if( $item[$column_name] == 0  ){
                        return __('Inactive', 'woo-product-category-discount');
                    } else if (($start_date && $today < $start_date) || ($start_date && $end_date && ($today < $start_date || $today > $end_date))) {
                        return __('Scheduled', 'woo-product-category-discount');
                    } else {
                        return __('Active', 'woo-product-category-discount');
                    }
                }


                if( $item['processed_chunks'] > 0 && $item['processed_chunks'] < $item['total_chunks'] ){
                    return __('Processing', 'woo-product-category-discount');
                }

                if ( $item[$column_name] == 1 && $start_date && $today < $start_date) {
                    return __('Scheduled', 'woo-product-category-discount');
                }

                if ( $item[$column_name] == 1 && $start_date && $end_date && ($today < $start_date || $today > $end_date)) {
                    return __('Scheduled', 'woo-product-category-discount');
                }
                
                if ($item[$column_name] == 1) {
                    return __('Active', 'woo-product-category-discount');
                } else {
                    return __('Inactive', 'woo-product-category-discount');
                }
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
        $is_view = $item['processed_chunks'] > 0 && $item['processed_chunks'] < $item['total_chunks'];
        if( $is_view ){
            $view_url = admin_url('admin.php?page=woo-product-category-discount&action=view-progress&id=' . $item['id']);
            $actions['view'] = '<a href="' . esc_url($view_url) . '">' . __('View Progress', 'woo-product-category-discount') . '</a>';
            return '<strong onclick="statusCheck(this)" style="cursor:pointer;">' . esc_html($item['name']) . '</strong>' . $this->row_actions($actions);
        }

        $edit_url = admin_url('admin.php?page=woo-product-category-discount&action=edit&id=' . $item['id']);
        $delete_url = wp_nonce_url(
            admin_url('admin.php?page=woo-product-category-discount&action=delete&id=' . $item['id']),
            'wpcd_delete_discount_' . $item['id']
        );
        
        $actions = [
            'edit' => '<a href="' . esc_url($edit_url) . '">' . __('Edit', 'woo-product-category-discount') . '</a>',
            'delete' => '<a href="' . esc_url($delete_url) . '" onclick="return confirm(\'' . esc_js(__('Are you sure you want to delete this discount?', 'woo-product-category-discount')) . '\')">' . __('Delete', 'woo-product-category-discount') . '</a>',
        ];

        return '<strong><a href="' . esc_url($edit_url) . '">' . esc_html($item['name']) . '</a></strong>' . $this->row_actions($actions);
    }
}
