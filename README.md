### 0x0) license-sys

The idea of this project is to allow software distribution more securely and provide source of income for the licensor.

Users have the ability to create an account and pay using Paypal for a daily/monthly/yearly subscription, 
once the payment received successfully the system grants the user access automatically with an expiration date.

The user logs in using a login app, to prevent multiple users from using a single account- the app 
will generate HWID token which consists of several HWID identifiers (CPU, GPU, RAM).
if HWID token isn't matching to the one on the server, the account will be banned.

once the user logged in successfully via the app it will download the intended software.

this system consists of 2 seperate projects:

* **web** - website panel (written in PHP and MySQL) allows users to create an account and pay for subscription

* **client** - gui app (written in C++) that allows to login and download and run the protected software. 

### 0x1) web files

```
/css/        - Contains css files
/images/     - Contains images
/files/      - Contains exe files
 * has .htaccess file with Deny from all to block all web access to that directory and its contents 

config.php - Contains global variables
database.class.php - PHP class wrapper around MySQLi procedural functions.

index.php - Homepage
register.php - Register page
login.php - Login page
logout.php - Self explainatory, destroys user's login session
dashboard.php - Main page for logged in users 
client.php - Downloads client app if the subscription is active

forgot-password.php - generates a token with an expiration date and sends it to the specified email
reset-password.php - handles the password reset process by verifying the token's validity and expiration, then updating the account's password in the db

buy.php - Purhcase subscription page
paypal-cancel.php  - redirected if payment is canceled
paypal-success.php - redirected is payment is successful
paypal-payment.php - our IPN (Instant Payment Notification) 
    * The idea is when a transaction will occur PayPal will send a post request to our IPN which is responsible to validate PayPal's transaction before we update our DB.

auth.php - this page handles user authentication request which is sent by the client app (verifying username, password & hwid) and returning JSON response.

```

create 'license_sys_db' database and use the following to create the tables : 

```sql
CREATE TABLE `accounts` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(100) NOT NULL,
    `password` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
    `expired_date` timestamp NOT NULL DEFAULT current_timestamp(),
    `ip` varchar(100) NOT NULL,
    `hwid` varchar(100) NOT NULL,
    `banned` boolean NOT NULL,
    `admin` boolean NOT NULL,
    PRIMARY KEY(`id`)
)ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `payments` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(100) NOT NULL, # username of the account
    `email` varchar(100) NOT NULL,  # paypal email of the buyer
    `status` varchar(100) NOT NULL, # status of the payment
    `amount` double(10,2) NOT NULL, # amount of the payment
    `txn_id` varchar(100) NOT NULL, # paypal transaction identification number for the payment
    `date` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY(`id`)
)ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `tokens` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `email` varchar(100) NOT NULL,
    `token` varchar(100) NOT NULL,
    `expiration` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY(`id`)
)ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
```

* check global variables in config.php

### 0x2) client files

```
main.cpp - main function

window.cpp - class wrapper for creating and managing windows GUI
http.cpp - class wrapper around WinINET lib
utils.cpp - 
json.cpp - json lib for cpp 11 ( https://github.com/dropbox/json11 )
base64.cpp - base64 encoding and decoding ( https://github.com/ReneNyffenegger/cpp-base64 )
config.cpp - load & save config file that holds username and password (remember me option)
```

Using win32 api for GUI rendering, inside ```main.cpp``` resides the function  ```window_procedure()```  which is a callback used for handling Windows messages. it also handles requests to ```auth.php```.

auth.php script will handle POST request with the following data which is sent by the client
* username
* password
* hwid

and send a JSON format response back to the client.

example -
```JSON
{
  "username": "root",
  "success": false,
  "error_msg": "Subscription is expired",
  "data": "" 
}
```

the ```success``` key is a boolean used to indicate whether authentication was successful or not, 
if the ```success``` key is true then ```data``` key will contain our executable file encoded in a base64 format (this is to ensure that the data can be transmitted without corruption)

the client will decode the base64 string to BYTE array and run the file dynamically.
