### 0x0) license-sys

The idea of this project is to allow software distribution more securely and provide source of income for the licensor.

Users have the ability to create an account and pay using Paypal for a daily/monthly/yearly subscription, 
once the payment received successfully the system grants the user access automatically with an expiration date.

The user logs in using a login app, to prevent multiple users from using a single account- the app 
will generate HWID token which consists of several HWID identifiers (CPU, GPU, RAM).
if HWID token isn't matching to the one on the server, the account will be banned.

once the user logged in successfully via the app it will download the intended software.

this system consists of 2 seperate projects:

* **web** - website panel (written in PHP and MySQL) allows users to create an account and pay for subscription (maybe add an admin panel to manage accounts table)

* **client** - gui app (written in C++) that allows to login and download and run the protected software. 

### 0x1) web files

```
/css/        - Contains all css files
/images/     - Contains all images

config.php - Contains global variables
database.class.php - PHP class that allows easier interaction with MySQLi db.

index.php - Homepage
register.php - Register page
login.php - Login page
logout.php - Self explainatory, destroys user's login session
dashboard.php - Main page for logged in users 

buy.php - Purhcase subscription page
paypal-cancel.php  - redirected if payment is canceled
paypal-success.php - redirected is payment is successful
paypal-payment.php - our IPN (Instant Payment Notification) 
    * The idea is when a transaction will occur PayPal will send a post request to our IPN which will be responsible to validate PayPal's transaction before we update our DB.

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
```

### 0x2) client files
