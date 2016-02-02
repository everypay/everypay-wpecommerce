<?php
$wpec_trans = array(
    'TXT_WPEC_INSTALLMENTS' => 'Installments',
    'TXT_WPEC_API_PUBLIC_KEY_TIP' => 'Your Public API key. Found in the <a href="https://dashboard.everypay.gr/settings/api-keys" target="_blank">Settings > Api keys</a> tab inside the <a href="https://dashboard.everypay.gr/" target="_blank">Everypay dashboard.</a>',
    'TXT_WPEC_API_SECRET_KEY_TIP' => 'Your Secret API key. Found in the <a href="https://dashboard.everypay.gr/settings/api-keys" target="_blank">Settings > Api keys</a> tab inside the <a href="https://dashboard.everypay.gr/" target="_blank">Everypay dashboard.</a>',
    'TXT_WPEC_SANDBOX_TIP' => 'If you enable this option payments will not be really charged. Alla actions will be done to the <a href="https://sandbox-dashboard.everypay.gr/" target="_blank">sandbox environment</a>',
    'TXT_WPEC_SANDBOX_MODE' => 'Sandbox mode',
    'TXT_WPEC_FROM_AMOUNT'=> 'From (Amount in &euro;)',
    'TXT_WPEC_TO_AMOUNT' => 'To (Amount in &euro;)',
    'TXT_WPEC_INSTALLMENTS_NUMBER' => 'Maximum Installments',
    'TXT_WPEC_ADD' => 'Add',
    'TXT_WPEC_ACCOUNT_DETAILS' => 'Account Details',
    'TXT_WPEC_PUBLIC_KEY' => 'Public Key',
    'TXT_WPEC_SECRET_KEY' => 'Secret Key',
    'TXT_WPEC_ORDER' => 'Order',
    'TXT_WPEC_OOPS' => 'Oops! Something went wrong. Please try again',
    'TXT_WPEC_PLEASE_WAIT' => 'Please wait'
);

foreach ($wpec_trans as $n=>$d){
    define($n, $d);
}