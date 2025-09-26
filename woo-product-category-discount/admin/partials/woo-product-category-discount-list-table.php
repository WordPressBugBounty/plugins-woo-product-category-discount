<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Simple Discount Rules','woo-product-category-discount'); ?></h1>
    <a href="<?php echo esc_url( add_query_arg( array( 'page' => self::$menu_slug, 'id' => 'new', 'action' => 'add' ), admin_url( 'admin.php' ) ) ); ?>" class="page-title-action"><?php esc_html_e('Add New','woo-product-category-discount'); ?></a>
    <a href="<?php echo admin_url('admin.php?page=' . esc_attr(self::$menu_slug) . '&action=settings'); ?>" class="page-title-action"><?php _e('Settings', 'woo-product-category-discount'); ?></a>
    <a href="https://www.wooextend.com/how-to-apply-category-discount-for-woocommerce/?utm-medium=plugin-help&urm-source=simple-discount-rules" class="page-title-action" target="_blank"><?php _e('Need help?','woo-product-category-discount'); ?></a>
    <hr class="wp-header-end"><?php
	$list_table->prepare_items();
    ?><form method="get">
	    <input type="hidden" name="page" value="<?php echo esc_attr(self::$menu_slug); ?>" /><?php		$list_table->search_box(__('Search Discount Rules','woo-product-category-discount'), 'wcpd_discount_search');
		$list_table->display();
	?></form>
</div>