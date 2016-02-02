<?php
$wpec_trans = array(
    'TXT_WPEC_INSTALLMENTS' => 'Δόσεις',
    'TXT_WPEC_API_PUBLIC_KEY_TIP' => 'Το δημόσιο κλειδί API σας . Μπορείτε να το βρείτε στην καρτέλα <a target="_blank" href="https://dashboard.everypay.gr/settings/api-keys">Ρυθμίσεις > Κλειδιά Αpi</a> μέσα στο <a href="https://dashboard.everypay.gr/" target="_ blank">Διαχειριστικό περιβάλλον.</a>',
    'TXT_WPEC_API_SECRET_KEY_TIP' => 'Το Ιδιωτικό κλειδί API σας . Μπορείτε να το βρείτε στην καρτέλα <a target="_blank" href="https://dashboard.everypay.gr/settings/api-keys">Ρυθμίσεις > Κλειδιά Αpi</a> μέσα στο <a href="https://dashboard.everypay.gr/" target="_ blank">Διαχειριστικό περιβάλλον.</a>',
    'TXT_WPEC_SANDBOX_TIP' => 'Αν ενεργοποιήσετε αυτή την επιλογή, οι πληρωμές δεν θα πραγματοποιούν πραγματική χρέωση στην κάρτα. Όλες οι συναλλαγές θα γίνονται στο <a href="https://sandbox-dashboard.everypay.gr/" target="_blank">δοκιμαστικό περιβάλλον sandbox </a>',
    'TXT_WPEC_SANDBOX_MODE' => 'Δοκιμαστική λετουργία (Sandbox)',
    'TXT_WPEC_FROM_AMOUNT'=> 'Από (Ποσό σε &euro;)',
    'TXT_WPEC_TO_AMOUNT' => 'Eως (Ποσό σε &euro;)',
    'TXT_WPEC_INSTALLMENTS_NUMBER' => 'Μέγιστος Αρ. Δόσεων',
    'TXT_WPEC_ADD' => 'Προσθήκη',
    'TXT_WPEC_ACCOUNT_DETAILS' => 'Λεπτομέρειες λογαριασμού',
    'TXT_WPEC_PUBLIC_KEY' => 'Δημόσιο κλειδί',
    'TXT_WPEC_SECRET_KEY' => 'Ιδιωτικό Κλειδί',
    'TXT_WPEC_ORDER' => 'Παραγγελία',
    'TXT_WPEC_OOPS' => 'Oops! Προέκυψε κάποιο σφάλμα. Παρακαλούμε ξαναδοκιμάστε.',
    'TXT_WPEC_PLEASE_WAIT' => 'Παρακαλούμε περιμένετε'
);

foreach ($wpec_trans as $n=>$d){
    define($n, $d);
}