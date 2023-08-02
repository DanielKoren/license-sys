### 0x0) license-sys

The idea of this project is to allow software distribution more securely and provide source of income for the licensor.

Users have the ability to create an account and pay using Paypal for a monthly subscription, 
once the payment received successfully the system grants the user access automatically with and expiration date.

The user logs in using a login app, to prevent multiple users from using a single account- the app 
will generate HWID token which consists of several HWID identifiers (CPU, GPU, RAM).
if HWID token isn't matching to the one on the server, the account will be banned.

once the user logged in succesfully via the app it will download the intended software.

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
register.php - Register an account
login.php - Allows to login
logout.php - Self explainatory, destroys user's login session
dashboard.php - Main page for logged in users 
```

create 'license_sys_db' database and use the following to create the tables : 

```sql
CREATE TABLE `accounts` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(100) NOT NULL,
    `password` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `added_date` timestamp NOT NULL DEFAULT current_timestamp(),
    `expiration_date` timestamp NOT NULL DEFAULT current_timestamp(),
    `ip` varchar(100) NOT NULL,
    `hwid` varchar(100) NOT NULL,
    `banned` boolean NOT NULL,
    `admin` boolean NOT NULL,
    
    PRIMARY KEY(`id`)
)ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
```

### 0x2) client files
