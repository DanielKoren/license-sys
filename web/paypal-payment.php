<?php

require_once('config.php');
require_once('database.class.php');

$db = new Database();

function txn_id_exist($tnx_id) {
    global $db;
    $exist = false;

    $result = $db->query("SELECT * FROM payments WHERE txn_id = '$tnx_id'");
    if ($db->num_rows($result) > 0) {
        $exist = true;
    }
    $db->free_result($result);

    return $exist;
}

function verify_transaction($data) {
    $paypal_url = PAYPAL_SANDBOX ? "https://www.sandbox.paypal.com/cgi-bin/webscr" : "https://www.paypal.com/cgi-bin/webscr";

    $req = 'cmd=_notify-validate';
    foreach ($data as $key => $value) {
        $value = urlencode(stripslashes($value));
        $value = preg_replace('/(.*[^%^0^D])(%0A)(.*)/i', '${1}%0D%0A${3}', $value); // IPN fix
        $req .= "&$key=$value";
    }

    $ch = curl_init($paypal_url);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
    curl_setopt($ch, CURLOPT_SSLVERSION, 6);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
    $res = curl_exec($ch);

    if (!$res) {
        $errno = curl_errno($ch);
        $errstr = curl_error($ch);
        curl_close($ch);
        throw new Exception("cURL error: [$errno] $errstr");
    }

    $info = curl_getinfo($ch);

    // Check the http response
    $httpCode = $info['http_code'];
    if ($httpCode != 200) {
        throw new Exception("PayPal responded with http code $httpCode");
    }

    curl_close($ch);

    return $res === 'VERIFIED';
}

// handle paypal response
if (isset($_POST["txn_id"]) && isset($_POST["txn_type"])) {
    $payment_email = $db->safe_post('payer_email');
    $payment_date = date('Y-m-d H:i:s');
    $payment_status = $db->safe_post('payment_status');
    $payment_amount = $db->safe_post('mc_gross');
    $payment_currency = $db->safe_post('mc_currency');
    $payment_txnid = $db->safe_post('txn_id');
    $custom = $db->safe_post('custom');
    parse_str($custom, $custom_var);
    $username = $custom_var['username'];
    $option = $custom_var['option'];
    
    // verify_transaction will send a request to paypal to verify the transaction
    // txn_id_exist makes sure that the txn_id doesn't exist in payments table
    if (verify_transaction($_POST) && txn_id_exist($payment_txnid) == false) {
        $db->prepare_query("INSERT INTO payments (`username`, `email`, `status`, `amount`, `txn_id`, `date`) VALUES (?,?,?,?,?,?)", $username, $payment_email, $payment_status, $payment_amount, $payment_txnid, $payment_date);

        // update 'expiration_date' of the subscribed account
        $curr_date = date('Y-m-d H:i:s');
        $new_date = "";

        if ($option == "daily") {
            $timestamp = strtotime($curr_date . " + 1 day");
            $new_date = date('Y-m-d H:i:s', $timestamp);
        } else if ($option == "weekly") {
            $timestamp = strtotime($curr_date . " + 1 week");
            $new_date = date('Y-m-d H:i:s', $timestamp);
        } else if ($option == "monthly") {
            $timestamp = strtotime($curr_date . " + 1 month");
            $new_date = date('Y-m-d H:i:s', $timestamp);
        } else if ($option == "yearly") {
            $timestamp = strtotime($curr_date . " + 1 year");
            $new_date = date('Y-m-d H:i:s', $timestamp);
        }

        $db->query("UPDATE accounts SET expiration_date = '$new_date' WHERE username = '$username'");
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo SITE_TITLE; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>
</body>
</html>