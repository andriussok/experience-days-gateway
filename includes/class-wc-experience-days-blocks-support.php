<?php
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class Experience_Days_Gateway_Blocks extends AbstractPaymentMethodType {

    private $gateway;

    protected $name = 'experience_days_gateway'; // your payment gateway name

    public function initialize() {
        $this->settings = get_option('woocommerce_experience_days_gateway_settings', []);
        
        // Initialize your payment gateway here
        $this->gateway = new Experience_Days_Gateway();
    }

    public function is_active() {
        return $this->gateway->is_available();
    }

    public function get_payment_method_script_handles() {
        $asset_path   = plugin_dir_path( __DIR__ ) . 'build/experience-days-gateway.asset.php';
        $version      = null;
        $dependencies = array();
        if( file_exists( $asset_path ) ) {
            $asset        = include $asset_path;
            $version      = isset( $asset['version'] ) ? $asset['version'] : $version;
            $dependencies = isset( $asset['dependencies'] ) ? $asset['dependencies'] : $dependencies;
        }
        
        wp_register_script( 
            'experience_days_gateway-blocks-integration', 
            plugin_dir_url(__FILE__) . '../build/experience-days-gateway.js', 
            $dependencies, 
            $version, 
            true 
        );

        if (function_exists('wp_set_script_translations')) {            
            wp_set_script_translations('experience_days_gateway-blocks-integration');
        }

        return ['experience_days_gateway-blocks-integration'];
    }

    public function get_payment_method_data() {
        return [
            'title' => $this->gateway->title,
            //'description' => $this->gateway->description,
        ];
    }
}