<?php 

session_start();

require_once('config.php');
require_once('database.class.php');

$db = new Database();

$error_message = "";
$success_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // check if email exist
    $email = $db->safe_post("email");
    $result = $db->prepare_query("SELECT * FROM accounts WHERE email=?", $email);
    
    if ($db->num_rows($result) > 0) {
        // generate a token with an expiration date
        $token = bin2hex(random_bytes(32));
        $expiration = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $db->prepare_query("INSERT INTO tokens (`email`,`token`,`expiration`) VALUES (?,?,?)", $email, $token, $expiration);

        // send mail that contains reset-password link
        $reset_password_url = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER["REQUEST_URI"]);
        $reset_password_url .= '/reset-password.php?token=' . $token;

        $mail_to = $email;
        $mail_title = SITE_TITLE . " - Password Reset";
        $mail_message = '<p>Click <a href="' . $reset_password_url . '">here</a> to reset your password, the link will expire in 1 hour.</p>';
        $mail_message .= '<p style="color:red;">If this was a mistake, just ignore this email and nothing will happen.</p>';
        $mail_headers = "MIME-Version: 1.0" . "\r\n";
        $mail_headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

        if (mail($mail_to, $mail_title, $mail_message, $mail_headers)) {
            $success_message = "Email sent successfully";
        } else {
            $error_message = "Failed sending email";
        }
    } else {
        $error_message = "Email doesn't exist";
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
    <div class="small-container">
        <h1>Reset your password</h1>
        <br>
        <p>Enter your email address and we will<br>send back a link to reset your password.</p>
        <form method="post">
            <input type="text" placeholder="Email address" name="email" id="email" required>
            <?php if (!empty($success_message)): ?>
            <p style="color: green;"><?php echo $success_message ?></p>
            <?php endif; ?>
            <?php if (!empty($error_message)): ?>
            <p style="color: red;"><?php echo $error_message ?></p>
            <?php endif; ?>
            <input type="submit" value="Request a reset link">
        </form>
    </div>
</body>
</html>