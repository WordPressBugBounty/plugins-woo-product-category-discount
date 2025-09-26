<label>
    <input type="radio" name="wpcd_process_method" value="ajax" <?php checked($value, 'ajax'); ?> />
    <?php esc_html_e('AJAX', 'woo-product-category-discount'); ?>
</label><br>
<label>
    <input type="radio" name="wpcd_process_method" value="cron" <?php checked($value, 'cron'); ?> />
    <?php esc_html_e('Cron', 'woo-product-category-discount'); ?>
</label>
<p class="description">
    <?php esc_html_e('If your store has a large number of products and you prefer not to wait while discounts are applied, choose Cron. Otherwise, keep AJAX. Cron will be the default method for scheduled discounts. Please note: Cron must be enabled to schedule discounts or to use Cron as the process method.', 'woo-product-category-discount'); ?>
</p>