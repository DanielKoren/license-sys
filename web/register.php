<?php 

require_once('config.php');
require_once('database.class.php');

$db = new Database();

$errors = array();
$account_created = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $db->safe_post("username");
    $password = $db->safe_post("password");
    $password2 = $db->safe_post("password2");
    $email = $db->safe_post("email");
    
    // check if username valid
    $result = $db->prepare_query("SELECT * FROM accounts WHERE username=?", $username);
    if ($db->num_rows($result) > 0) {
        array_push($errors, "Username is taken");
    }

    // check if email valid
    $result = $db->prepare_query("SELECT * FROM accounts WHERE email=?", $email);
    if ($db->num_rows($result) > 0) {
        array_push($errors, "Email is taken");
    }

    // check if password matches
    if ($password != $password2) {
        array_push($errors, "Password doesn't match");
    }

    // create account if no errors found
    if (count($errors) == 0) {
        $added_date = date('Y-m-d H:i:s');
        $expiration_date = '0000-00-00 00:00:00';
        //$expiration_date = '1970-1-1 12:00:00';
        $ip_address = $_SERVER["REMOTE_ADDR"];
        $hwid_token = '-';
        $banned = 0;
        $admin = 0;
        
        $result = $db->prepare_query("INSERT INTO `accounts` (`username`, `password`, `email`, `added_date`, `expiration_date`, `ip`, `hwid`, `banned`, `admin`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", $username, $password, $email, $added_date, $expiration_date, $ip_address, $hwid_token, $banned, $admin);
        $account_created = true;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo $site_title;?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>
    <div class="small-container">
        <h1>Register</h1>
        <form method="post">
            <input type="text" placeholder="Username" name="username" id="username" required>
            <br>
            <input type="password" placeholder="Password" name="password" id="password" required>
            <br>
            <input type="password" placeholder="Repeat Password" name="password2" id="password2" required>
            <br>
            <input type="email" placeholder="Email" name="email" id="email" required>
            <br>
            <?php
            // display errors msgs
            foreach($errors as $error) {
                echo '<p style="color: red; text-align: center;width:100%;">' . $error . '.</p>';
            }
            // display success msg
            if ($account_created) {
                echo '<p  style="color: green; text-align: center;width:100%;">Account created successfully.</p>';
            }
            ?>
            <input type="submit" value="Register">
        </form>
    </div>
</body>
</html>