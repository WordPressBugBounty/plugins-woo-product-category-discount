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
