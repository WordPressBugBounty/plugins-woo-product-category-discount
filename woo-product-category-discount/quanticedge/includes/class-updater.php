<?php

defined("ABSPATH") || exit();
class QuanticEdge_Updater{
	
	/**
	 * Constructor
	 *
	 * Registers the admin menu and enqueue scripts during the class construction.
	 *
	 * @since 1.0.0
	 */
	public function __construct(){
		add_action("admin_menu", array($this, "register_quanticedge_menu_page"));
		add_action("admin_enqueue_scripts", array($this, 'enqueue_scripts'));
	}

	/**
	 * Registers the QuanticEdge menu page.
	 *
	 * This function is called during the `admin_menu` action hook and registers the
	 * QuanticEdge page with the WordPress admin menu.
	 *
	 * @since 1.0.0
	 */
	public function register_quanticedge_menu_page(){
		add_menu_page('QuanticEdge', 'QuanticEdge', 'manage_options', 'quanticedge', array($this, 'quanticedge'), plugin_dir_url(__DIR__) . 'includes/images/logox22.png', 30);
	}

	/**
	 * Enqueue admin styles for the QuanticEdge page.
	 *
	 * This function checks if the current admin page is the 'quanticedge' page
	 * and enqueues the 'qc-admin-styles' stylesheet for that page.
	 */
	public function enqueue_scripts(){
		if (isset($_GET['page']) && $_GET['page'] == 'quanticedge') {
			wp_enqueue_style("qc-admin-styles", plugin_dir_url(__FILE__) . "assets/css/styles.css", array(), wp_rand(1, 1000));
		}
	}

	/**
	 * quanticedge
	 *
	 * Outputs the welcome page content.
	 *
	 * @since 1.0.0
	 */
	public function quanticedge(){
		?><div class="qc-welcome-page wrap">
			<h1 class="entry-title"><?php echo esc_html("QuanticEdge | We Give You The Edge"); ?></h1>
			<div class="banner">
				<div class="left">
					<div class="logo-bg">
						<img src="<?php echo plugin_dir_url(__FILE__) . 'images/wooextend.png'; ?>" alt="WooXtend Logo">
					</div>
					<h2>Check our popular plugins here</h2>
					<a href="https://www.wooextend.com/shop/" target="_blank" class="view-more-btn"><span>View More</span>
					<svg xmlns="http://www.w3.org/2000/svg"
						viewBox="0 0 640 640">
						<path fill="#ffffff"
							d="M566.6 342.6C579.1 330.1 579.1 309.8 566.6 297.3L406.6 137.3C394.1 124.8 373.8 124.8 361.3 137.3C348.8 149.8 348.8 170.1 361.3 182.6L466.7 288L96 288C78.3 288 64 302.3 64 320C64 337.7 78.3 352 96 352L466.7 352L361.3 457.4C348.8 469.9 348.8 490.2 361.3 502.7C373.8 515.2 394.1 515.2 406.6 502.7L566.6 342.7z" />
					</svg></a>
				</div>
				<div class="right">
					<div class="plugin-item">
						<div class="icon"><img src="<?php echo plugin_dir_url( __FILE__ ) . 'images/checkbox.png'; ?>" alt="checkbox"></div>
						<div class="text">
							<a href="https://www.wooextend.com/product/woo-product-category-discount-pro/" target="_blank">Woo
								Product Category Discount Pro</a>
							<p>Apply category, tag, or brand-based discounts with scheduling in WooCommerce</p>
						</div>
					</div>

					<div class="plugin-item">
						<div class="icon"><img src="<?php echo plugin_dir_url( __FILE__ ) . 'images/checkbox.png'; ?>" alt="checkbox"></div>
						<div class="text">
							<a href="https://www.wooextend.com/product/order-promotion-woocommerce-pro/"
								target="_blank">Woocommerce Order Promotion Pro</a>
							<p>Order Promotion WooCommerce Pro offers discounts or freebies to boost sales.</p>
						</div>
					</div>

					<div class="plugin-item">
						<div class="icon"><img src="<?php echo plugin_dir_url( __FILE__ ) . 'images/checkbox.png'; ?>" alt="checkbox"></div>
						<div class="text">
							<a href="https://www.wooextend.com/product/group-stock-manager-shared-stock-woocommerce/"
								target="_blank">Shared Stock Woocommerce</a>
							<p>Manage shared stock across multiple products or variations.</p>
						</div>
					</div>

					<div class="plugin-item">
						<div class="icon"><img src="<?php echo plugin_dir_url( __FILE__ ) . 'images/checkbox.png'; ?>" alt="checkbox"></div>
						<div class="text">
							<a href="https://www.wooextend.com/product/first-order-discount-woocommerce/" target="_blank">First
								Order Discount</a>
							<p>First Order Discount WooCommerce lets you offer special discounts on first purchase.</p>
						</div>
					</div>

					<div class="plugin-item">
						<div class="icon"><img src="<?php echo plugin_dir_url( __FILE__ ) . 'images/checkbox.png'; ?>" alt="checkbox"></div>
						<div class="text">
							<a href="https://www.wooextend.com/product/woocommerce-combo-offers/" target="_blank">Combo Offers
								Woocommerce</a>
							<p>Create product combos at discounted prices to boost sales.</p>
						</div>
					</div>

					<div class="plugin-item">
						<div class="icon"><img src="<?php echo plugin_dir_url( __FILE__ ) . 'images/checkbox.png'; ?>" alt="checkbox"></div>
						<div class="text">
							<a href="https://www.wooextend.com/product/woocommerce-bulk-order/" target="_blank">Bulk Order
								Woocommerce</a>
							<p>WooCommerce Bulk Order lets customers buy multiple products from one page.</p>
						</div>
					</div>

					<div class="plugin-item">
						<div class="icon"><img src="<?php echo plugin_dir_url( __FILE__ ) . 'images/checkbox.png'; ?>" alt="checkbox"></div>
						<div class="text">
							<a href="https://www.wooextend.com/product/simple-discount-rules-for-woocommerce/"
								target="_blank">Simple Discount Rules for Woocommerce</a>
							<p>Apply discounts by category, tags, attributes – works for thousands of products.</p>
						</div>
					</div>
				</div>
    		</div>
			<div class="qc-info">
				<div class="qc-about card">
					<h2><?php echo esc_html("About"); ?></h2>
					<p><?php echo esc_html("QuanticEdge is a dynamic digital solutions provider with a wide range of experience in mobile app development, web app development, CMS development, spanning both front-end and back-end service offerings. Our services also feature API integration, SEO and ecommerce solutions that stand the test of time!"); ?></p>
				</div>
				<div class="qc-contact card">
					<h2><?php echo esc_html("Visit Us"); ?></h2>
					<div>
						<span class="dashicons dashicons-admin-site"></span> <a href="https://quanticedgesolutions.com" class="qc-website"><?php echo esc_html("https://quanticedgesolutions.com"); ?></a>
					</div>
					<div>
						<span class="dashicons dashicons-email"></span> <a href="mailto:info@quanticedge.co.in" class="qc-email"><?php echo esc_html("info@quanticedge.co.in"); ?></a>
					</div>
				</div>
			</div>
		</div><?php
	}
}

new QuanticEdge_Updater();
