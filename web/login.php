<?php 

session_start();

require_once('config.php');
require_once('database.class.php');

$db = new Database();
$status = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $db->safe_post("username");
    $password = $db->safe_post("password");

    $result = $db->prepare_query("SELECT * FROM accounts WHERE username=?", $username);
    
    if ($db->num_rows($result) > 0) {
        $account = $db->fetch_assoc($result);
        
        if (password_verify($password, $account['password'])) {
            if ($account['banned']) {
                $status = "Account is banned.";
            } else {
                $_SESSION['username'] = $username;
                $_SESSION['id'] = $account['id'];
                header('Location: dashboard.php');
                exit();
            }
        }
    } else {
        $status = "Invalid username or password.";
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
        <h1>Login</h1>
        <form method="post">
            <input type="text" placeholder="Username" name="username" id="username" required>
            <br>
            <input type="password" placeholder="Password" name="password" id="password" required>
            <br>
            <p style="color: red;text-align: center;"><?php echo $status; ?></p>
            <input type="submit" value="Login">
        </form>
    </div>
</body>
</html>