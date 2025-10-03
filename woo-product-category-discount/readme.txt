=== Simple Discount Rules for Woocommerce ===
Contributors: vidishp, quanticedge
Tags: discount rules, category discount, discount, woocommerce discount, bulk discount
Requires at least: 3.0.1
Tested up to: 6.8
Stable tag: trunk
Donate link: https://ko-fi.com/vidish
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Simple Discount Rules for Woocommerce allows administrator to add and remove discount to products based on Category.

== Description ==

"Simple Discount Rules for Woocommerce" enables administrator to apply discount on wide range of rules. Just few clicks & discount is applied. (Even to thousands of products)..!! 

<strong>Key features:</strong>
<ul>
<li>Storewide promotion ( <a href="https://youtu.be/fe2oEbjYUng" target="_blank">Check demo </a> )</li>
<li>Apply discounts based on Categories, tags, and more. ( <a href="https://youtu.be/NAR8CfyyVCg" target="_blank">Check demo </a> )</li>
<li>Cart based discounts ( <a href="https://youtu.be/9G1ntT5CWso" target="_blank">Check demo </a> )</li>
<li>Quantity based discounts ( <a href="https://youtu.be/Jw_fkIaJE4w" target="_blank">Check demo </a> )</li>
<li>Free gift for promotions</li>
</ul>

Don't believe it? Try it out here, its free..!!

Need more details on features? <a href="https://www.wooextend.com/how-to-apply-category-discount-for-woocommerce/">Review here</a>

<strong>Need a custom feature? Ask us here<a href="https://www.wooextend.com/woocommerce-expert/" target="_blank">WooExtend</a></strong>

Thank you for <a href="https://www.facebook.com/wooextend/reviews">LOVING this plugin..!!</a>

<strong>More plugins by WooExtend:</strong>
<ul>
<li><strong><a href="https://wordpress.org/plugins/build-your-own-basket-for-woocommerce">Build Your Own Basket for Woocommerce</a></strong></li>
<li><strong><a href="https://wordpress.org/plugins/woo-bulk-order/">Bulk Order for Woocommerce</a></strong></li>
<li><strong><a href="https://wordpress.org/plugins/woo-combo-offers/">Woocommerce Combo Offers</a></strong></li>
<li><strong><a href="https://wordpress.org/plugins/first-order-discount-woocommerce/">First Order Discount</a></strong></li>
<li><strong><a href="https://wordpress.org/plugins/woo-custom-fee/">Custom Fee</a></strong></li>
</ul>


== Installation ==

1. Upload `woo-product-category-discount.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPresss

== Frequently Asked Questions ==

= What does the status column mean? = 

If the discount applied has status "inactive", then it will not be active discount.

= How can I add multiple conditions? =

You can add multiple conditions by clicking on "Add Condition" button. Also you can specify whether you want "All" conditions to be met or "ANY".

= How can I remove the database while uninstalling the plugin? =

Please add the following line in wp-config.php before uninstalling the plugin:
define( 'WPCD_REMOVE_TABLES', true );

== Screenshots ==

1. Admin interface where you can locate the plugin in menu.
2. Step 1 to add new discount.
3. Step 2 to add new discount.
4. Final step to add new discount.
5. When discount is applied, the wait icon turn into success notification icon.

== Changelog ==

= 5.10 =
* Fixed the issue of adding table columns were failing for some users.

= 5.9 = 
* Resolved the table deletion error while uninstalling.

= 5.8 =
* Added notice when cron is not active on the site.
* Added active/deactive toggle on discount listing.
* Shows the taxonomy details and last activity on listing page.
* Deletion is not allowed when the discount is active.
* Added the option to process the discount via ajax instead of crons.

= 5.7 =
* Resolved the issue of blank page on settings.
* Added version requirement of wordpress.

= 5.6 =
* Changed the text domain for translations.
* Added settings links on plugins page.
* Removed the manual translation loading.
* Resolved the fatal error throwing when price was not set.

= 5.5 = 
* Resolved the issue of discount scheduler was not removed when discount was created in past versions.
* Added QuanticEdge main menu and made the plugin part of the same.

= 5.4 =
* Added the highest applicable discount to product.
* Automatically applies any other discount that is eligible for product.
* Disable editing and deleting while discount is in progress.
* Automatically adds the discount when the new product is created.
* Resolved the discount remove bug.

= 5.3 =
* Added check before deleting database tables.
* Added WPML compatibility
* Resolved the quantity based discount issue.
* Added check for plugin table creation if not created while activation.
* Calculated discount using regular price instead of already discounted price.

= 5.2 =
* Fix for file name

= 5.1 =
* Updated menu name

= 5.0 =
* Major release with advanced features

= 4.14 =
* Allowed shop manager access to discount page

= 4.13 =
* Added capability check for AJAX

= 4.12 =
* Added capability check

= 4.11 =
* Nonce verification for admin

= 4.10 =
* Fix for a fatal error for some variations.

= 4.7 =
* Fix for rare scenario while removing discount. It added regular price as sale price.

= 4.6 =
* Fix for floatval

= 4.5 =
* Fix for standards

= 4.4 =
* Fixed for header already sent warning

= 4.3 =
* Fixed for a warning

= 4.2 =
* Fixed conflict with woocommerce sku fields

= 4.1 =
* Fixed error for first time users

= 4.0 =
* Disabled all discount amount & type editing when sale is ongoing
* Fixed a query which could cause sometimes not applying discounts

= 3.3 =
* Remove product discount completely for previously discounted items on sale when discount is turned off using plugin

= 3.2 =
* Fixed links

= 3.1 =
* WP 5.4 compatibility tested
* Woocommerce 4.1 Compatible

= 3.0 =
* Feature : Allow category % discount

= 2.8 =
* Version release of 3.3 for Pro version.

= 2.7 =
* Woocommerce 3.4 compatible.

= 2.6 =
* Added thank you note.

= 2.5 =
* Fix for handling empty instances of product from database which may brake process

= 2.4 =
* Added condition for more consistency

= 2.3 =
* Added support for woocommerce 3.x

= 2.2 =
* This is a minor update with fixes to update discounts.

= 2.1 = 
* Removed button based "Save" functionality. Discounts will be updated for each category as soon as user updates it.
* Ajax based discount application for best performance and larger systems.

= 1.1 =
* Fix for multiple categories can be assigned to one product while applying discount. The maximum amount of discount will be applied to the product.

= 1.0 =
* Initial release of plugin.

== Upgrade Notice ==
