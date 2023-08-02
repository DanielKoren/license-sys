<?php

// site settings
$site_title = "License System"; 

// paypal settings
$paypal_sandbox = true;
	
$url = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER["REQUEST_URI"]);
	
$paypal_url = "";
if ($paypal_sandbox) {
	$paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
} else {
	$paypal_url = "https://www.paypal.com/cgi-bin/webscr";
}

$paypal_notify_url = $url . "/paypal-payment.php";
$paypal_return_url = $url . "/paypal-success.php";
$paypal_cancel_url = $url . "/paypal-cancel.php";

$paypal_currency = "ILS";

// crypto settings

?>