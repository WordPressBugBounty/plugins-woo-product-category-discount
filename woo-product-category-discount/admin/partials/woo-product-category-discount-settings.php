<div class="wrap">
    <h1><?php esc_html_e('Woo Product Category Discount - Settings', 'woo-product-category-discount'); ?></h1>
    <form method="post" action="options.php">
        <?php
        settings_fields('wpcd_settings_group');
        do_settings_sections('wpcd-settings');
        submit_button();
        ?>
    </form>
</div>