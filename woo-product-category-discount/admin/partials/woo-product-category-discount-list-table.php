<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Simple Discount Rules','woo-product-category-discount'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=woo-product-category-discount&id=new&action=add'); ?>" class="page-title-action"><?php _e('Add New','woo-product-category-discount'); ?></a>
    <a href="https://www.wooextend.com/how-to-apply-category-discount-for-woocommerce/?utm-medium=plugin-help&urm-source=simple-discount-rules" class="page-title-action"><?php _e('Need help?','woo-product-category-discount'); ?></a>
    <hr class="wp-header-end"><?php
	$list_table->prepare_items();
    ?><form method="get">
	    <input type="hidden" name="page" value="woo-product-category-discount" /><?php
		$list_table->search_box(__('Search Discount Rules','woo-product-category-discount'), 'wcpd_discount_search');
		$list_table->display();
	?></form>
</div>