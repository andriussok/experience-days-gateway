<?php
/*
Plugin Name: Experience Days Gateway
Description: A custom WooCommerce payment gateway for handling Experience Days Vouchers.
Version: 1.0.0
Author: Andrius Sok
Author URI: andriuss.lt
License: GPL-3.0-or-later
License URI: https://www.gnu.org/licenses/gpl-3.0.txt
Text Domain: experience-days-gateway
*/

// Init plugin
add_action('plugins_loaded', 'woocommerce_experience_days_gateway_init', 0);

function woocommerce_experience_days_gateway_init() {
    if (!class_exists('WC_Payment_Gateway')) return;


    // CLASSIC CHECKOUT


    // Include the gateway class
    require_once(plugin_dir_path(__FILE__) . 'includes/class-wc-experience-days-gateway.php');

    // This action hook registers our PHP class as a WooCommerce payment gateway
    add_filter('woocommerce_payment_gateways', 'woocommerce_add_experience_days_gateway');
    function woocommerce_add_experience_days_gateway($gateways) {
        $gateways[] = 'Experience_Days_Gateway';
        return $gateways;
    }


    // GUTENBERG BLOCKS CHECKOUT


    // Declare compatibility with the cart_checkout_blocks feature
    add_action('before_woocommerce_init', 'declare_cart_checkout_blocks_compatibility');
    function declare_cart_checkout_blocks_compatibility() {
        if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
                'cart_checkout_blocks',
                __FILE__,
                true // true (compatible, default) or false (not compatible)
            );
        }
    }


    // Register the payment method type for blocks support
    add_action('woocommerce_blocks_loaded', 'register_order_approval_payment_method_type');
    function register_order_approval_payment_method_type() {

        // including "gateway block support class"
        require_once plugin_dir_path(__FILE__) . 'includes/class-wc-experience-days-blocks-support.php';

        // registering the PHP class we have just included
        add_action(
            'woocommerce_blocks_payment_method_type_registration',
            function (Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
                // Register an instance of Experience_Days_Gateway_Blocks
                $payment_method_registry->register(new Experience_Days_Gateway_Blocks());
            }
        );
    }

    
    /* 
     * Hooks in Gateways
     * It’s important to note that adding hooks inside gateway classes may not trigger.
     * Gateways are only loaded when needed, such as during checkout and on the settings page in admin.
     * You should keep hooks outside of the class or use WC-API if you need to hook into WordPress events from your class.
     */


    // Add the voucher code field to the order admin page
    add_action('woocommerce_admin_order_data_after_billing_address', 'display_voucher_code_in_admin_order');
    function display_voucher_code_in_admin_order($order) {
        error_log('display_voucher_code_in_admin_order called');
        $voucher_code = get_post_meta($order->get_id(), '_experience_days_voucher', true);
        if (!empty($voucher_code)) {
            echo '<p><strong>' . __('Voucher Code', 'experience-days-gateway') . ':</strong> ' . esc_html($voucher_code) . '</p>';
        }
    }
  
}