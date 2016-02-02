<?php
/*
  Plugin Name: WP e-Commerce Everypay
  Plugin URI: https://everypay.gr
  Description: Integrates your Everypay payment getway into your WP e-Commerce webshop.
  Version: 1.0.8
  Author: Everypay
  Text Domain: wpe-everypay
  Author URI: http://kostas.krevatas.net
 */

class WPEC_Everypay
{

    /**
     * $_instance
     * 
     * @var mixed
     * @public
     * @static
     */
    public static $_instance = NULL;

    /**
     * get_instance
     * 
     * Returns a new instance of self, if it does not already exist.
     * 
     * @static
     * @return object WC_Everypay
     */
    public static function get_instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * The class construct
     * 
     * @public
     * @return void
     */
    public function __construct()
    {
        // Load the plugin files
        $this->prepare_files();

        // Prepare hooks and filters
        $this->hooks_and_filters();

        // Setup the gateway in WP E-Commerce
        $this->setup_gateway();
    }

    /**
     * Includes all the vital plugin files containing functions and classes
     * 
     * @public
     * @return  void
     * @since   1.0.0
     */
    public function prepare_files()
    {
        $this->require_file('includes/classes/wpec-ep-settings.php');
        if (!class_exists('Everypay')) {
            $this->require_file('includes/classes/Everypay.php');
        }
    }

    /**
     * Prepares all the hooks and filters
     * 
     * @public
     * @return  void
     * @since   1.0.0
     */
    public function hooks_and_filters()
    {
        add_action('wp_enqueue_scripts', array($this, 'add_everypay_js'));

        if (isset($_REQUEST['wpsc_action']) && ($_REQUEST['wpsc_action'] == 'everypay_get_button')) {
            add_action('init', array($this, 'get_button_data'));
        }

        add_action('admin_enqueue_scripts', array($this, 'load_everypay_admin'));
    }

    public function load_everypay_admin()
    {
        if (isset($_GET['page']) && $_GET['page'] == 'wpsc-settings' && isset($_GET['tab']) && $_GET['tab'] == 'gateway' && isset($_GET['payment_gateway_id']) && $_GET['payment_gateway_id'] == 'Everypay') {
            wp_register_script('everypay_script1', plugins_url('assets/js/admin/mustache.min.js', __FILE__), array('jquery'), 'ver', true);
            wp_enqueue_script('everypay_script1');

            wp_register_script('everypay_script2', plugins_url('assets/js/admin/everypay.js', __FILE__), array('jquery'), 'ver', true);
            wp_enqueue_script('everypay_script2');
        }
    }

    public function get_button_data()
    {
        $jsonInit = array(
            'amount' => intval(wpsc_cart_total(false) * 100),
            'currency' => 'EUR',
            'key' => WPEC_EP_Settings::get('everypay_public_key'),
            'locale' => 'el',
            'callback' => 'handleEverypayToken',
            'sandbox' => WPEC_EP_Settings::get('everypay_sandbox'),
            'max_installments' => wpec_everypay_get_installments(wpsc_cart_total(false),  WPEC_EP_Settings::get('everypay_maximum_installments')),
        );

        die(json_encode($jsonInit));
    }

    /**
     * Checks for file availability and requires the file if it exists.
     * 
     * @private 
     * @param  string $file_path > the file to require.
     * @return boolean/void > returns FALSE if the requested file doesn't exist. Return void otherwise. 
     */
    private function require_file($file_path)
    {
        $dir_path = plugin_dir_path(__FILE__);

        if (!file_exists($dir_path . $file_path)) {
            return FALSE;
        }

        require_once( $dir_path . $file_path );
    }

    public function add_everypay_js()
    {
        //show only in checkout page
        if (!wpsc_is_checkout()) {
            return;
        }

        wp_register_script('everypay_script', "https://button.everypay.gr/js/button.js");
        wp_enqueue_script('everypay_script');

        wp_enqueue_script('everypay_script_js', plugins_url('/assets/js/everypay.js', __FILE__), array('jquery'));
    }

    /**
     * Prepares the gateway
     * 
     * @public
     * @return void
     */
    public function setup_gateway()
    {
        global $nzshpcrt_gateways, $gateway_checkout_form_fields;
        $num = time() . '1';
        $nzshpcrt_gateways[$num]['name'] = 'Everypay';
        $nzshpcrt_gateways[$num]['internalname'] = 'Everypay';

        $nzshpcrt_gateways[$num]['function'] = 'wpec_everypay_gateway';
        $nzshpcrt_gateways[$num]['form'] = 'wpec_everypay_gateway_form';
        $nzshpcrt_gateways[$num]['submit_function'] = 'wpec_everypay_gateway_submit';

        //$gateway_checkout_form_fields[$nzshpcrt_gateways[$num]['internalname']] = '<tr><td>Πληρώστε με την πιστωτική ή την χρεωστική σας κάρτα<div class="button-holder" style="display:none"></div></td></tr>';
    }
}

/**
 * Send the token to Everypay Gateway
 * 
 * @param [[Type]] $seperator [[Description]]
 * @param [[Type]] $sessionid [[Description]]
 */
function wpec_everypay_gateway($seperator, $sessionid)
{
    global $wpdb, $wpsc_cart;
    $errors = array();
    try {      

        $purchase_log_sql = $wpdb->prepare("SELECT * FROM `" . WPSC_TABLE_PURCHASE_LOGS . "` WHERE `sessionid`= %s LIMIT 1", $sessionid);
        $purchase_log = $wpdb->get_results($purchase_log_sql, ARRAY_A);

        $cart_sql = "SELECT * FROM `" . WPSC_TABLE_CART_CONTENTS . "` WHERE `purchaseid`='" . $purchase_log[0]['id'] . "'";
        $cart = $wpdb->get_results($cart_sql, ARRAY_A);

        $purchase_id = $cart[0]['purchaseid'];
        $merchant = new wpsc_merchant($purchase_id);
        
        $token = isset($_POST['everypayToken']) ? $_POST['everypayToken'] : 0;
        if (!$token) {
            $merchant->set_error_message('Opps. Something went wrong. Please retry');        
            $merchant->return_to_checkout();
            exit;
        }        

        $amount = intval(wpsc_cart_total(false) * 100);
        $description = get_bloginfo('name') . ' / '
            . __('Order') . ' #' . $purchase_id;

        $cart_data = $merchant->cart_data;

        $data = array(
            'description' => $description,
            'amount' => $amount,
            'payee_email' => $cart_data['email_address'],
            'token' => $token,
            'max_installments' => wpec_everypay_get_installments(wpsc_cart_total(false),  WPEC_EP_Settings::get('everypay_maximum_installments')),
        );

        if (WPEC_EP_Settings::get('everypay_sandbox')) {
            Everypay::setTestMode();
        }

        Everypay::setApiKey(WPEC_EP_Settings::get('everypay_secret_key'));
        $response = Everypay::addPayment($data);

        if (isset($response['body']['error'])) {
            $errors[] = $response['body']['error']['message'];
        } else {
            wpsc_update_purchase_log_details($purchase_id, array('transactid' => $response['token']));
        }
    } catch (\Exception $e) {
        $errors[] = $e->getMessage();
    }

    if (!empty($errors)) {
        foreach ($errors as $error_row) {
            $merchant->set_error_message($error_row);
        }
        $merchant->return_to_checkout();
    } else {
        $merchant->set_purchase_processed_by_purchid(3);
        $merchant->go_to_transaction_results($merchant->cart_data['session_id']);
    }

    exit();
}

function wpec_everypay_get_installments($total, $ins)
{   
    $ins = str_replace("\\", '', $ins);
    if ($ins) {
        $installments = json_decode($ins, true);
        
        $counter = 1;
        $max = 0;
        $max_installments = 0;
        foreach ($installments as $i) {
            if ($i['to'] > $max) {
                $max = $i['to'];
                $max_installments = $i['max'];
            }

            if (($counter == (count($installments)) && $total >= $max)) {
                return $max_installments;
            }

            if ($total >= $i['from'] && $total <= $i['to']) {
                return $i['max'];
            }
            $counter++;
        }
    }
    return 0;
}

/**
 * Prints the gateway settings field in wp-admin
 *
 * @return string > the form fields
 */
function wpec_everypay_gateway_form()
{
    // Generate output.
    $output = '<tr><td colspan="2"><strong>Account Details</strong></td></tr>';

    // PUBLIC API key.
    $output .= '<tr><td><label for="everypay_public_key">Public Key</label></td>';
    $output .= '<td><input name="everypay_public_key" id="everypay_public_key" type="text" value="' . WPEC_EP_Settings::get('everypay_public_key') . '"/><br/>';
    $output .= WPEC_EP_Settings::field_hint('Your Payment Window agreement API key. Found in the "Integration" tab inside the Everypay manager.');
    $output .= '</td></tr>';

    // SECRET key.
    $output .= '<tr><td><label for="everypay_secret_key">Secret Key</label></td>';
    $output .= '<td><input name="everypay_secret_key" id="everypay_secret_key" type="text" value="' . WPEC_EP_Settings::get('everypay_secret_key') . '"/><br/>';
    $output .= WPEC_EP_Settings::field_hint('Your Payment Window agreement private key. Found in the "Integration" tab inside the Everypay manager.');
    $output .= '</td></tr>';

    //SANDBOX enable
    $output .= '<tr><td><label for="everypay_sandbox">Sandbox mode</label></td>';
    $output .= '<td><input name="everypay_sandbox" id="everypay_sandbox" value="1"' . (WPEC_EP_Settings::get('everypay_sandbox') == '1' ? ' checked="checked"' : '') . ' type="checkbox"/> Yes<br/>';
    $output .= WPEC_EP_Settings::field_hint('If a transaction fails or is cancelled and the user returns to your webshop, do you wish the contents of the users shopping basket to be kept? Otherwise it will be emptied.');
    $output .= '</td></tr>';

    $output .= '<tr><td><label>Δόσεις</label>';
    $output .= '<script type="text/javascript">var save_installments = "' . WPEC_EP_Settings::get('everypay_maximum_installments') . '" </script>';
    $output .= '<input type="hidden" value="" name="everypay_maximum_installments" id="everypay_maximum_installments"></td>';
    $output .= '<td><div id="installments"></div>
                <div id="installment-table" style="display:none">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Από (Ποσό σε &euro;)</th>
                                <th>Eως (Ποσό σε &euro;)</th>
                                <th>Μέγιστος Αρ. Δόσεων</th>
                                <th>
                                    <a class="button-primary" href="#" id="add-installment" style="width:101px;">                        
                                        <i class="icon icon-plus-sign"></i> <span class="ab-icon"></span>  Προσθήκη
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <style type="text/css">
                    #everypay-installments table{}
                    .remove-installment{font-size: 2em; text-decoration: none !important;color:#ee5f5b}
                    #installment-table table{width:600px;background: white;}                    
                    #installment-table tr td{border:1px solid #111;}                    
                    #installments table{width:601px;max-width: 601px;background: #fff; padding:16px;}
                    #installments table input[type="number"] {width: 99px;}
                </style>
                </td>';

    return $output;
}

/**
 * Saves the gateway settings
 * 
 * @return boolean
 */
function wpec_everypay_gateway_submit()
{
    WPEC_EP_Settings::update_on_post('everypay_public_key');
    WPEC_EP_Settings::update_on_post('everypay_secret_key');
    WPEC_EP_Settings::update_on_post('everypay_sandbox');
    WPEC_EP_Settings::update_on_post('everypay_maximum_installments');

    return true;
}

class load_language 
{
    public function __construct()
    {
    add_action('init', array($this, 'load_my_transl'));
    }

     public function load_my_transl()
    {
        load_plugin_textdomain('wp-ecommerce-everypay', FALSE, dirname(plugin_basename(__FILE__)).'/languages/');
    }
}

$lang = new load_language;
echo __("mymessage1", 'wp-ecommerce-everypay');


if (!function_exists('WPEC_EP')) {

    function WPEC_EP()
    {
        return WPEC_Everypay::get_instance();
    }
    WPEC_EP();
}

?>