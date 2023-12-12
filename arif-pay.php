<?php

/**
 * Plugin Name: Arifpay Payment for Woocommerce
 * Plugin URI: https://example.com
 * Author Name: Abel Alem @ arifpay
 * Description: This plugin allows for local content payment systems.
 * Version: 0.1.0
 * License: 0.1.0
 * License URL: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: arif-pay
*/ 

add_action('plugins_loaded', 'woocommerce_arif_pay_init', 0);
function woocommerce_arif_pay_init() 
{
	if ( !class_exists( 'WC_Payment_Gateway' ) ) return;
	/**
 	 * Localisation
	 */
	load_plugin_textdomain('arif-pay', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');
    
	/**
 	 * Gateway class
 	 */
	class WC_arif_pay extends WC_Payment_Gateway {
     /**
     * Checkout page title
     *
     * @var string
     */
        public $title;

        /**
        *   Checkout page description
         *
        * @var string
        */
        public $description;

        /**
        * Is gateway enabled?
         *
        * @var bool
        */
        public $enabled;
        public $ApiKey;
        public $accountNumber;
        public $cancelUrl;
        public $errorUrl;
        public $notifyUrl;
        public $successUrl;
        public $invoice_prefix;
        public $swift;
        public function __construct()
    {
        $this->id                 = 'arif_pay';
        $this->icon = apply_filters( 'woocommerce_noob_icon',  plugins_url('/assets/images.png', __FILE__ ));
       // $this->method_title       = 'Arifpay';
        $this->accountNumber     = __('Add Bank Details', 'woocommerce');
        $this->cancelUrl     = __('Add CancelUrl', 'woocommerce');
        $this->errorUrl     = __('Add ErrorUrl', 'woocommerce');
        $this->notifyUrl     = __('Add NotifyUrl', 'woocommerce');
        $this->successUrl     = __('Add successUrl', 'woocommerce');
        $this->swift     = __('Add Bank Name', 'woocommerce');
        $this->ApiKey       = __('Add API key', 'woocommerce');
        $this->order_button_text = __('Proceed to Arifpay', 'woocommerce');
        $this->method_title      = __('Arifpay', 'woocommerce');
        $this->method_description = sprintf('Start accepting money with arifpay');
        $this->has_fields = false;
        // Load the form fields.
        $this->init_form_fields();
        // Load the settings.
        $this->init_settings();
        // Get setting values.
        $this->title       = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled     = $this->get_option('enabled');
        $this->swift     = $this->get_option('swift');
        $this->cancelUrl  = $this->get_option('cancelUrl');
        $this->errorUrl  = $this->get_option('errorUrl');
        $this->notifyUrl  = $this->get_option('NotifyUrl');
        $this->successUrl  = $this->get_option('successUrl');
        $this->accountNumber  = $this->get_option('accountNumber');
        $this->ApiKey       = $this->get_option('ApiKey');
        $this->invoice_prefix = $this->get_option('invoice_prefix');
       add_action('admin_notices', array($this, 'admin_notices'));
       // add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
       add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
     }
     public function admin_notices()
     {
 
         if ('no' === $this->enabled) {
             return;
         }
         $pattern = "/^\\w+:(\\/\\/)[^\\s]+$/";
         // Check required fields.
         if (!(preg_match($pattern, $this->successUrl))&&(preg_match($pattern, $this->cancelUrl))&&(preg_match($pattern, $this->errorUrl))&&(preg_match($pattern, $this->notifyUrl))) {
             echo '<div class="error"><p>' . sprintf('Please enter your ArifPay URLs details correctly, use the format http://example.com , Click <a href="%s">here</a> to start editing those settings.', admin_url('admin.php?page=wc-settings&tab=checkout&section=arif_pay')) . '</p></div>';
             return;
         }
         if(!($this->ApiKey )) {
            echo '<div class="error"><p>' . sprintf('Please enter your Arifpay API details, Click <a href="%s">here</a> to start editing that setting.', admin_url('admin.php?page=wc-settings&tab=checkout&section=arif_pay')) . '</p></div>';
            return;
         }
     }
        public function init_form_fields()
        {
         
         $this->form_fields = array(
            'enabled'         => array(
                'title'       => __('Enable/Disable', 'arif-pay'),
                'label'       => __('Enable Arifpay', 'arif-pay'),
                'type'        => 'checkbox',
                'description' => __('Enable Arifpay as a payment option on the checkout page.', 'arif-pay'),
                'default'     => 'no',
                'desc_tip'    => false,
            ),
            'title'           => array(
                'title'       => __('Title', 'arif-pay'),
                'type'        => 'text',
                'description' => __('This controls the payment method title which the user sees during checkout.', 'arif-pay'),
                'desc_tip'    => false,
                'default'     => __('ArifPay', 'arif-pay'),
            ),
            'accountNumber'           => array(
                'title'       => __('Bank Account Number', 'arif-pay'),
                'type'        => 'text',
                'description' => __('Enter Bank Details here Please', 'arif-pay'),
                'desc_tip'    => false,
            ),
            'swift' => array(
                'title' => __('Bank Name', 'arif-pay'),
                'type' => 'select',
                'options' => array(
                    'ABYSETAA' => 'BANK OF ABYSSINIA' ,
                    'ABAYETAA' => 'ABAY BANK S.C' ,
                    'CBETETAA' => 'COMMERCIAL BANK OF ETHIOPIA',
                    'BUNAETAA' => 'BUNNA INTERNATIONAL BANK S.C',
                    'AWINETAA' => 'AWASH INTERNATIONAL BANK' ,
                    'NIBIETTA' => 'Nib International Bank',
                    'UNTDETAA' => 'Hibret Bank',
                    'ABSCETAA' => 'Addis International Bank' ,
                    'BERHETAA'=>'Berhan International Bank',
                    'CBORETAA'=>'Cooperative Bank of Oromia'  ,
                    'WEGAETAA'=> 'wegagen bank',
                ),
                'description' => __('choose your bank.', 'custom_gateway'),
                //'default' => 'BANK OF ABYSSINIA',
                'desc_tip' => true,
            ),
            'cancelUrl'           => array(
                'title'       => __('cancelUrl', 'arif-pay'),
                'type'        => 'text',
                'description' => __('Enter cancelUrl here', 'arif-pay'),
                'desc_tip'    => false,
            ),
            'errorUrl'           => array(
                'title'       => __('errorUrl', 'arif-pay'),
                'type'        => 'text',
                'description' => __('Enter errorUrl here', 'arif-pay'),
                'desc_tip'    => false,
            ),
            'NotifyUrl'           => array(
                'title'       => __('NotifyUrl', 'arif-pay'),
                'type'        => 'text',
                'description' => __('Enter NotifyUrl here', 'arif-pay'),
                'desc_tip'    => false,
            ),
            'successUrl'           => array(
                'title'       => __('successUrl', 'arif-pay'),
                'type'        => 'text',
                'description' => __('Enter successUrl here', 'arif-pay'),
                'desc_tip'    => false,
            ),
            'ApiKey'           => array(
                'title'       => __('Api Key', 'arif-pay'),
                'type'        => 'text',
                'description' => __('Enter Api key here Please', 'arif-pay'),
                'desc_tip'    => false,
            ),
            'description'     => array(
                'title'       => __('Description', 'arif-pay'),
                'type'        => 'textarea',
                'description' => __('This controls the payment method description which the user sees during checkout.', 'arif_pay'),
                'desc_tip'    => false,
            ),
            'invoice_prefix' => array(
                'title'       => __('Invoice Prefix', 'arif-pay'),
                'type'        => 'text',
                'description' => __('Please enter a prefix for your invoice numbers. If you use your Arifpay account for multiple stores ensure this prefix is unique as Arifpay will not allow orders with the same invoice number.', 'arif-pay'),
                'default'     => 'WC_',
                'desc_tip'    => false,
            ),
            

            );
        }
     
		// Go wild in here
        public function process_payment( $order_id )
        {
        global $woocommerce;
        $order = new WC_Order( $order_id );
        $cart_items = $woocommerce->cart->get_cart();
        $items = array();
        $total_amount = 0;
        foreach ( $cart_items as $cart_item ) {
            $product = $cart_item['data'];
            $image_id = $product->get_image_id(); // Get the image ID
            $image_url = wp_get_attachment_url( $image_id ); // Get the image URL
            $items[] = array(    
                'name' => $product->get_name(),
                'quantity' => $cart_item['quantity'],
                'price' => $product->get_price(),
                'description' => $product->get_description(),
                // in production use $image_url as image
                'image' => "https://rb.gy/od65py"
                //wp_get_attachment_url( $product->get_image_id() )
                
                );
                $total_amount += $product->get_price() * $cart_item['quantity'];

                $data_string = print_r($this->swift, true);
                $file = 'C:/Users/Abel/Desktop/data2.txt';
                file_put_contents($file, $data_string);

            }
        $request_body = array(
            'items' => $items,
            'beneficiaries' => array(array(
                'accountNumber' => $this->accountNumber,
                'bank' => $this->swift,
                'amount' => $total_amount
            )),
            'cancelUrl' => $this->cancelUrl,
            'errorUrl' => $this->errorUrl,
            'notifyUrl' => $this->notifyUrl,
            'successUrl' => $this->successUrl,
            'paymentMethods' => array(
                "TELEBIRR"
            ),
            "expireDate" => "2025-02-01T03:45:27",
            "lang" => "EN",
            "nonce" => uniqid('',true)
        );

        $response = wp_remote_post('https://gateway.arifpay.org/api/sandbox/checkout/session', array(
            'method' => 'POST',
            'headers' => array(
                'Content-Type' => 'application/json',
                'x-arifpay-key' => $this->ApiKey // replace with your merchant API key
            ),
            'body' => json_encode( $request_body ),
        ));
        $response_body = json_decode( wp_remote_retrieve_body( $response ), true );
    
// //isset( $response_body['data']['paymentUrl']
$response_code = wp_remote_retrieve_response_code( $response );
$data_value = $response_body;

// // Convert the 'data' value to a string $this->apikey
$data_string = print_r($data_value, true);
$file = 'C:/Users/Abel/Desktop/data.txt';
file_put_contents($file, $data_string);




        if ( isset( $response_body['data']['paymentUrl']))  {
            return array(
                'result' => 'success',
                'redirect' =>  $response_body['data']['paymentUrl']
            );
        }
            return array(
                'result'   => 'success',
                'redirect' => 'https://example.com',
            );
        }
	}
	
	/**
 	* Add the Gateway to WooCommerce
 	**/
	function woocommerce_arif_pay_gateway($methods) {
		$methods[] = 'WC_arif_pay';
		return $methods;
	}
	
	add_filter('woocommerce_payment_gateways', 'woocommerce_arif_pay_gateway' );
}
