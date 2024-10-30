=== mpaqt for WooCommerce ===

Contributors:      alxgrlk
Tested up to:      6.6
Stable tag:        0.2.7
License:           GPLv2 or later
Tags:              eCommerce, WooCommerce, shipping, inventory, delivery

mpaqt provides storage and fulfillment solutions for eCommerce merchants.

== Description ==
The mpaqt for WooCommerce plugin integrates with your online shopping site to send order details to mpaqt.io for shipping to your customers.
* Your inventory stored, assembled and shipped all across the globe!
* Storage, Light Assembly and Fulfillment Solutions
* Take advantage of volume discounts and compare across carriers to decrease your delivery costs.
* Outsource storage, assembly and fulfillment so you can upscale your business
* Onboard a more efficient, cost-effective process so you can save time
* Increase your sales by focusing on your core business, thus boosting your growth.
* Cost effective! Since there are no minimums and a low barrier to entry, starting is a breeze.

This plugin relies on a 3rd party as a service to support shipping and fulfillment services for mpaqt customers.
More information about mpaqt can be found at https://mpaqt.io.  The plugin will call API endpoints at subdomains of mpaqt.io such as https://dev.mpaqt.io and https://app.mpaqt.io.

The privacy policy and terms of service can be found at https://mpaqt.io/privacy-policy/ and https://mpaqt.io/merchant-agreement/ respectively.

This plugin sends various customer and order-related fields when an order is processed. The following fields are transmitted:

- **site_name**: The name of the WordPress site, fetched using `get_bloginfo('name')`.
- **site_url**: The URL of the WordPress site, fetched using `get_bloginfo('url')`.
- **customer_name**: The customer's full name, composed of the billing first and last name.
- **customer_email**: The email address of the customer.
- **customer_phone**: The phone number of the customer.
- **address**: An array containing the billing address of the customer:
    - **line1**: First line of the billing address.
    - **line2**: Second line of the billing address (optional).
    - **city**: City for the billing address.
    - **postcode**: Postal code for the billing address.
    - **country**: Country for the billing address.
- **eauth**: The eAuth value for security or authorization purposes.
- **order_id**: Unique identifier for the order.
- **order_status**: Current status of the order.
- **order_total**: Total cost of the order.
- **shipping_method_name**: Name of the shipping method selected.
- **shipping_method_id**: ID of the shipping method.
- **customer_note**: Note provided by the customer, if any.
- **products**: An array of product information included in the order:
    - **sku**: The Stock Keeping Unit (SKU) of each product.
    - **quantity**: Quantity of each product ordered.


== Installation ==

= Installation from within WordPress =

1. Visit **Plugins > Add New**.
2. Search for **mpaqt for WooCommerce**.
3. Install and activate the mpaqt for WooCommerce plugin.

= Manual installation =

1. Upload the entire `mpaqtwoo` folder to the `/wp-content/plugins/` directory.
2. Visit **Plugins**.
3. Activate the Plugin Check plugin.
4. In the Dashboard Go to WooCommerce > Settings and look for a form field labeled "mpaqt eAuth Value".  Enter the token provided during mpaqt setup.

== Frequently Asked Questions ==

= Where can I learn more about mpaqt? =
* Go to our website www.mpaqt.io or contact us via social media @mpaqtdotio (Instagram, Twitter, Facebook, LinkedIn)

== Changelog ==
= 0.2.3 =

* revisions for plugin review for Wordpress.org submission. add documentation on service calls.

= 0.2.3 =

* add order processing status action.

= 0.2.2 =

* revisions for shipping and order status.

= 0.2.1 =

* update to pass product quantity and order_id

= 0.2.0 =

* Add customer phone and email data to payload.  Provide to beta customers.

= [0.1] 2024-06-15 =

Original version of the mpaqt for WooCommerce plugin, not a released version of the plugin, this changelog is here for historical purposes only.
