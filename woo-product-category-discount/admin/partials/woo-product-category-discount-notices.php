<?php
if (!empty($_GET['wpcd_deleted'])) {
    ?><div class="notice notice-success is-dismissible"><p><?php esc_html_e('Discount deleted.', 'woo-product-category-discount'); ?></p></div><?php
}