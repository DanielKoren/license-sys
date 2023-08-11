<?php 

session_start();

require_once('config.php');
require_once('database.class.php');

$db = new Database();

if (!isset($_SESSION['username']) || !isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

// send request to paypal
// use the following variables
// https://developer.paypal.com/api/nvp-soap/paypal-payments-standard/integration-guide/Appx-websitestandard-htmlvariables/ 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [];
    foreach ($_POST as $key => $value) {
        $sanitizedValue = htmlspecialchars(stripslashes($value), ENT_QUOTES, 'UTF-8');
        $data[$key] = $sanitizedValue;
        //echo $key . $data[$key] . '<br>';
    }

    // append subscription option to the custom variable
    $data['custom'] .= "&option=" . $data['os0'];

    // generate URL-encoded query string
    $query_str = http_build_query($data);
    $paypal_url = PAYPAL_SANDBOX ? "https://www.sandbox.paypal.com/cgi-bin/webscr" : "https://www.paypal.com/cgi-bin/webscr";
    header('location:' . $paypal_url . '?' . $query_str);
    exit();
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
    <div class="small-container">
        <h1>Buy subscription</h1>
        <form method="post">

            <input type="hidden" name="cmd" value="_xclick-subscriptions">
            <input type="hidden" name="business" value="<?php echo PAYPAL_EMAIL; ?>">
            <input type="hidden" name="rm" value="2">
            <input type="hidden" name="lc" value="US">
            <input type="hidden" name="item_name" value="<?php echo PAYPAL_ITEM_NAME; ?>">
            <input type="hidden" name="no_note" value="1">
            <input type="hidden" name="no_shipping" value="1">
            <input type="hidden" name="src" value="0">

            <?php
            
            $url = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER["REQUEST_URI"]);

            $paypal_notify_url = $url . "/paypal-payment.php";
            $paypal_return_url = $url . "/paypal-success.php";
            $paypal_cancel_url = $url . "/paypal-cancel.php";

            ?>

            <input type="hidden" name="notify_url" value="<?php echo $paypal_notify_url; ?>" /> 
			<input type="hidden" name="return" value="<?php echo $paypal_return_url; ?>" /> 
			<input type="hidden" name="cancel_return" value="<?php echo $paypal_cancel_url; ?>" /> 
            
            <input type="hidden" name="bn" value="PP-SubscriptionsBF:btn_subscribeCC_LG.gif:NonHosted">
            
            <input type="hidden" name="on0" value="Multiple Options">
            <p style="width:100%; margin-bottom:10px;">Subscription Type :</p>
            
            <select name="os0">
            <option value="daily">$ 10.00 USD - Daily Subscription</option>
            <option value="weekly">$ 30.00 USD - Weekly Subscription</option>
            <option value="monthly">$ 50.00 USD - Monthly Subscription</option>
            <option value="yearly">$ 200.00 USD - Yearly Subscription</option>
            </select>

            <input type="hidden" name="currency_code" value="USD">

            <input type="hidden" name="option_select0" value="daily">
            <input type="hidden" name="option_amount0" value="10.00">
            <input type="hidden" name="option_period0" value="D">
            <input type="hidden" name="option_frequency0" value="1">

            <input type="hidden" name="option_select1" value="weekly">
            <input type="hidden" name="option_amount1" value="30.00">
            <input type="hidden" name="option_period1" value="W">
            <input type="hidden" name="option_frequency1" value="1">

            <input type="hidden" name="option_select2" value="monthly">
            <input type="hidden" name="option_amount2" value="50.00">
            <input type="hidden" name="option_period2" value="M">
            <input type="hidden" name="option_frequency2" value="1">

            <input type="hidden" name="option_select3" value="yearly">
            <input type="hidden" name="option_amount3" value="200.00">
            <input type="hidden" name="option_period3" value="Y">
            <input type="hidden" name="option_frequency3" value="1">
            <input type="hidden" name="option_index" value="0">
            
            <input type="hidden" name="custom" value="username=<?php echo $_SESSION['username']; ?>">

            <input type="submit" value="PayPal - The safer, easier way to pay online!">
        </form>
    </div>
</body>
</html>