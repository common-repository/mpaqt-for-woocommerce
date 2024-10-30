<?php
/**
 * Plugin Name: mpaqt for WooCommerce
 * Description: Sends a webhook with order details when a customer transacts.
 * Version: 0.2.7
 * Author: mpaqt
 * Tested up to: 6.6
 * Requires Plugins: woocommerce
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

add_action('woocommerce_thankyou', 'mpaqt_post_data_webhook', 10, 1);
add_action('woocommerce_order_status_cancelled', 'mpaqt_post_data_webhook', 10, 1);
add_action('woocommerce_order_status_failed', 'mpaqt_post_data_webhook', 10, 1);
add_action('woocommerce_order_status_processing', 'mpaqt_post_data_webhook', 10, 1);

// Add a new tab to WooCommerce settings
add_filter('woocommerce_settings_tabs_array', 'mpaqt_add_settings_tab', 50);

function mpaqt_add_settings_tab($settings_tabs) {
    $settings_tabs['mpaqt_webhook'] = __('mpaqt Webhook', 'mpaqt-for-woocommerce');
    return $settings_tabs;
}

// Add settings to the new mpaqt Webhook tab
add_action('woocommerce_settings_tabs_mpaqt_webhook', 'mpaqt_settings_tab_content');

function mpaqt_settings_tab_content() {
    woocommerce_admin_fields(mpaqt_get_settings());
}

// Save the settings for the new tab
add_action('woocommerce_update_options_mpaqt_webhook', 'mpaqt_update_settings');

function mpaqt_update_settings() {
    woocommerce_update_options(mpaqt_get_settings());
}

// Define the custom fields for the new mpaqt Webhook tab
function mpaqt_get_settings() {
    $settings = array(
        'section_title' => array(
            'title'    => __('mpaqt Webhook Settings', 'mpaqt-for-woocommerce'),
            'type'     => 'title',
            'desc'     => __('Configure the settings for mpaqt webhooks, including the eAuth value and remote webhook URL. Make sure the remote URL uses HTTPS.', 'mpaqt-for-woocommerce'),
            'id'       => 'mpaqt_webhook_settings_section_title',
        ),
        'mpaqt_eauth_value' => array(
            'title'    => __('mpaqt eAuth Value', 'mpaqt-for-woocommerce'),
            'desc_tip' => __('This is the eAuth value for mpaqt webhooks.', 'mpaqt-for-woocommerce'),
            'id'       => 'woocommerce_mpaqt_eauth_value',
            'type'     => 'text',
            'desc'     => __('Enter the mpaqt eAuth value.', 'mpaqt-for-woocommerce'),
        ),
        'mpaqt_remote_url' => array(
            'title'    => __('mpaqt Remote URL', 'mpaqt-for-woocommerce'),
            'desc_tip' => __('Enter the mpaqt remote webhook URL (must be HTTPS).', 'mpaqt-for-woocommerce'),
            'id'       => 'woocommerce_mpaqt_remote_url',
            'type'     => 'url',
            'desc'     => __('The URL for the remote webhook. Must be an HTTPS URL.', 'mpaqt-for-woocommerce'),
            'custom_attributes' => array(
                'pattern' => 'https://.*',  // Ensure the URL must be HTTPS
            ),
        ),
        'section_end' => array(
            'type' => 'sectionend',
            'id'   => 'mpaqt_webhook_settings_section_end',
        ),
    );
    return $settings;
}


function mpaqt_post_data_webhook($order_id) {
    if (!$order_id) return;


    try {

      $order = wc_get_order($order_id);
        if (!$order) {
            throw new Exception('Invalid order');
        }
        // Fetch eAuth value from WooCommerce settings
        $eauth_value = get_option('woocommerce_mpaqt_eauth_value');
        $mpaqt_remote_url = get_option('woocommerce_mpaqt_remote_url');

        // Prepare shipping method details
        $shipping_methods = $order->get_shipping_methods();
        $shipping_method = !empty($shipping_methods) ? reset($shipping_methods) : null;
        $shipping_method_name = $shipping_method ? $shipping_method->get_method_title() : 'N/A';
        $shipping_method_id = $shipping_method ? $shipping_method->get_method_id() : 'N/A';
        // Fetch customer note
        $customer_note = $order->get_customer_note();

        if($order->get_billing_email()!="") {
        $customer_email = $order->get_billing_email();
        } else if ($order->get_billing_email()!="") {
        $customer_email = $order->get_meta('_shipping_email');
        } else {
          $user_id = $order->get_user_id();
          if ($user_id) {
              $user_info = get_userdata($user_id);
              $user_email = $user_info->user_email;
              $customer_email =  $user_email ;
          }
        }

        if (empty($customer_email)) {
          $customer_email = get_option('admin_email');
        }


        // Initialize the customer phone variable
        $customer_phone = '';

        // Check if the billing phone number is available
        if ($order->get_billing_phone() != "") {
            $customer_phone = $order->get_billing_phone();
        } else {
            // Check for a custom shipping phone number if it exists
            $shipping_phone = $order->get_meta('_shipping_phone');
            if ($shipping_phone != "") {
                $customer_phone = $shipping_phone;
            } else {
                // Check if the order is associated with a user and get the user's phone number if it exists
                $user_id = $order->get_user_id();
                if ($user_id) {
                    $user_phone = get_user_meta($user_id, 'billing_phone', true);
                    if ($user_phone != "") {
                        $customer_phone = $user_phone;
                    }
                }
            }
        }

        // If no phone number is found, use the website's primary phone number (if it's stored as an option)
        if (empty($customer_phone)) {
            $customer_phone = get_option('admin_phone'); // Assume 'admin_phone' is the option name for the website's primary phone number
        }

        // Prepare data to be sent
        $data = array(
            'site_name' => get_bloginfo('name'),
            'site_url' => get_bloginfo('url'),
            'customer_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'customer_email' => $customer_email,
            'customer_phone' => $customer_phone,  // Include the customer's phone number
            'address' => array(
                   'line1' => $order->get_billing_address_1(),
                   'line2' => $order->get_billing_address_2(),
                   'city' => $order->get_billing_city(),
                   'postcode' => $order->get_billing_postcode(),
                   'country' => $order->get_billing_country()
               ),
            'eauth' => $eauth_value,  // Include the eAuth value
            'order_id' => $order->get_id(),
            'order_status' => $order->get_status(),
            'order_total' => $order->get_total(),
            'shipping_method_name' => $shipping_method_name,
            'shipping_method_id' => $shipping_method_id,
            'customer_note' => $customer_note,
             'products' => array()
        );

        // Loop through order items
         foreach ($order->get_items() as $item_id => $item) {
             $product = $item->get_product();
             if (!$product) {
                // throw new Exception('Invalid product');
             }
             $data['products'][] = array(
                 'sku' => $product->get_sku(),
                 'quantity' => $item->get_quantity()
             );
         }

        // Send the data


          // Check if $mpaqt_remote_url is blank, and if so, use the default value
          if (empty($mpaqt_remote_url)) {
              $remote_url = 'https://dev.mpaqt.io/hooks/woo.php';
          } else {
              $remote_url = $mpaqt_remote_url;
          }

          $response = wp_remote_post($remote_url, array(
            'method' => 'POST',
            'body' => wp_json_encode($data),
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
        ));

        if (is_wp_error($response)) {
            throw new Exception('Error sending webhook: ' . $response->get_error_message());
        }
    } catch (Exception $e) {

    }
}

function mpaqt_post_new_order_webhook($order_id) {
    if (!$order_id) return;

    // Get the order object
    $order = wc_get_order($order_id);
    if (!$order) return;

    // Call the same webhook function used for status changes
    mpaqt_post_data_webhook($order_id, '', $order->get_status(), $order);
}
