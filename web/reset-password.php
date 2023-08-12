<?php 

session_start();

require_once('config.php');
require_once('database.class.php');

$db = new Database();

$token_valid = false;
$error_message = "";
$success_message = "";

if (isset($_GET['token'])) {
    // check if token exist in DB and validate the expiration
    $token = $db->safe_get('token');
    $result = $db->prepare_query("SELECT * FROM tokens WHERE token=?", $token);

    if ($db->num_rows($result) > 0) {
        $row = $db->fetch_assoc($result);
        $expiration = $row['expiration'];
        $current = date('Y-m-d H:i:s');

        if ($current < $expiration) {
            // token is valid
            $token_valid = true;

            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $password = $db->safe_post("password");
                $password2 = $db->safe_post("password2");
                
                if ($password != $password2) {
                    $error_message = "Password doesn't match";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    $db->prepare_query("UPDATE accounts SET password=? WHERE email=?", $hashed_password, $row['email']);
                    $success_message = "Password changed successfully";                    
                }
            }
        }
    }
}

// exit the script if the token is invalid
if (!$token_valid)
    exit();

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
        <h1>Enter new password</h1>
        <form method="post">
            <input type="password" placeholder="Password" name="password" id="password" required>
            <br>
            <input type="password" placeholder="Repeat Password" name="password2" id="password2" required>
            <br>
            <?php if (!empty($success_message)): ?>
            <p style="color: green;"><?php echo $success_message ?></p>
            <?php endif; ?>
            <?php if (!empty($error_message)): ?>
            <p style="color: red;"><?php echo $error_message ?></p>
            <?php endif; ?>
            <input type="submit" value="Change password">
        </form>
    </div>
</body>
</html>