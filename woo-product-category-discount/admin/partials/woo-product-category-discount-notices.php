<?php
if (!empty($_GET['wpcd_deleted'])) {
    ?><div class="notice notice-success is-dismissible"><p><?php esc_html_e('Discount deleted.', 'woo-product-category-discount'); ?></p></div><?php
}
if( defined('DISABLE_WP_CRON') && DISABLE_WP_CRON){
    ?><div class="notice notice-error"><p><?php esc_html_e('WP Cron is disabled. Any scheduled discount will not work.', 'woo-product-category-discount'); ?></p></div><?php
}