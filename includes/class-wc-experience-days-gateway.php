<?php

if (!defined('ABSPATH')) {
    exit;
}

class Experience_Days_Gateway extends WC_Payment_Gateway {

    /**
     * Class constructor
     */
    public function __construct() {
        $this->id                 = 'experience_days_gateway'; // payment gateway plugin ID
        $this->icon               = ''; // URL of the icon that will be displayed on checkout page near your gateway name
        $this->method_title       = __('Experience Days Gateway', 'experience-days-gateway');
        $this->method_description = __('Pay with an Experience Days Voucher', 'experience-days-gateway'); // will be displayed on the options page
        $this->has_fields         = true; // in case you need a custom credit card form

        // gateways can support subscriptions, refunds, saved payment methods,
        $this->supports = array(
            'products'
        );

        // Method with all the options fields
        $this->init_form_fields();

        // Load the settings.
        $this->init_settings();

        $this->title       = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled     = $this->get_option('enabled');

        // This action hook saves the settings
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

        // Action hooks for data processing
        // add_action('woocommerce_checkout_update_order_meta', array($this, 'save_voucher_code'));
    }

    /**
     * Plugin options
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => __('Enable/Disable', 'experience-days-gateway'),
                'type'    => 'checkbox',
                'label'   => __('Enable Experience Days Gateway', 'experience-days-gateway'),
                'default' => 'no',
            ),
            'title' => array(
                'title'       => __('Title', 'experience-days-gateway'),
                'type'        => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'experience-days-gateway'),
                'default'     => __('Experience Days Voucher', 'experience-days-gateway'),
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => __('Description', 'experience-days-gateway'),
                'type'        => 'textarea',
                'description' => __('This controls the description which the user sees during checkout.', 'experience-days-gateway'),
                'default'     => __('Pay with an experience days voucher.', 'experience-days-gateway'),
                'desc_tip'    => true,
            ),
        );
    }

    /**
     * Add Input voucher code
     */
    public function payment_fields() {

        // Display the description if it's set
        if ($this->description) {
            echo wpautop(wp_kses_post($this->description));
        }

        // Define the field arguments with a unique ID
        $field_args = array(
            'type'        => 'text',
            'id'          => 'experience_days_voucher_gateway', // Unique ID for the field
            // 'label'       => __('Voucher Code', 'experience-days-gateway'),
            'placeholder' => __('Enter your voucher code', 'experience-days-gateway'),
            'class'       => array('form-row-wide'),
            'required'    => true,
        );

        // Render the field using woocommerce_form_field
        woocommerce_form_field('experience_days_voucher', $field_args, '');

        // you can also use Hardcoded HTML instead
        /* ?>
        <div id="experience_days_voucher_field_gateway">
            <p class="form-row form-row-wide">
                <label for="experience_days_voucher_gateway"><?php _e('Voucher Code', 'experience-days-gateway'); ?></label>
                <input type="text" class="input-text" name="experience_days_voucher" id="experience_days_voucher_gateway" placeholder="<?php _e('Enter your voucher code', 'experience-days-gateway'); ?>" />
            </p>
        </div>
        <?php */
    }

    /**
     * Validate voucher code
     */
    public function validate_fields() {
        if ($_POST['payment_method'] === 'experience_days_gateway' && empty($_POST['experience_days_voucher'])) {
            wc_add_notice(__('Please enter a voucher code.', 'experience-days-gateway'), 'error');
            return false;
        }
        return true;
    }

    /**
     * Process and sanitize voucher code from request
     */
    public function get_sanitized_voucher_code() {
      // Initialize voucher code variable
      $voucher_code = '';

      // Check if it's a block checkout request and sanitize the input
      if (isset($_POST['experiencedaysvoucher'])) {
          $voucher_code = sanitize_text_field($_POST['experiencedaysvoucher']);
      } else {
          // Fallback for classic checkout and sanitize the input
          if (isset($_POST['experience_days_voucher'])) {
              $voucher_code = sanitize_text_field($_POST['experience_days_voucher']);
          }
      }

      return $voucher_code;
    }


    /**
     * Save voucher code and add to customer notes
     */
    public function save_voucher_code($order_id, $voucher_code) {
      if (!empty($voucher_code)) {
          // Store value in metadata
          update_post_meta($order_id, '_experience_days_voucher', $voucher_code);

          // Add voucher code to customer notes if not already added
          $order = wc_get_order($order_id);
          $existing_note = $order->get_customer_note();
          if (strpos($existing_note, $voucher_code) === false) {
              $new_note = "Voucher: $voucher_code";
              if (!empty($existing_note)) {
                  $new_note = "$existing_note -- $new_note";
              }
              $order->set_customer_note($new_note);
              $order->save();
          }
      }
    }


    /**
     * Process payment
     */
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);

        // Get sanitized voucher code
        $voucher_code = $this->get_sanitized_voucher_code();
        
        // Save the sanitized voucher code
        $this->save_voucher_code($order_id, $voucher_code);

        // Mark the order as on-hold
        $order->update_status('on-hold', __('Awaiting voucher verification', 'experience-days-gateway'));

        // Reduce stock levels
        wc_reduce_stock_levels($order_id);

        // Remove cart
        WC()->cart->empty_cart();

        // Return thank you redirect
        return array(
            'result'   => 'success',
            'redirect' => $this->get_return_url($order),
        );
    }

    /**
     * Webhook handling code
     */
    // public function webhook() {
    //     // ...
    // }
}
