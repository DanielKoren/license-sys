<?php

/*
 *
 * PHP class that allows easier interaction with the MySQLi db.
 * Uses procedural functions for MySQLi
 * 
 * Functions: 
 * --
 * 
 * status() - Returns MySQLi connection
 * get_error() - Returns MySQLi error string
 * 
 * query() - Sends query
 * prepare_query() - Safer function to send query when dealing with input
 * free_result() - Frees memory
 * 
 * fetch_assoc() - Retrieves a result row as an associative array
 * fetch_array() - Retrieves a result row as an array, both numeric and keys
 * num_rows() - Retrieves the number of rows in a result
 * 
 * get_all() - This function used to retrieve all the rows from a database query result and return them as an array of associative arrays
 * 
 * safe_post() - Safer function to retrieve $_POST variables
 * safe_get() - Safer function to retrieve $_GET variables
 * 
 */
class Database {
    private $db_host = 'localhost';
    private $db_user = 'root';
    private $db_pass = '';
    private $db_name = 'license_sys_db';
    private $db_charset = 'utf8';

    private $connection = null;

    /*
     * Connects to db and configures connection
     */
    public function __construct() {
        // connect to db
        $this->connection = mysqli_connect($this->db_host, $this->db_user, $this->db_pass, $this->db_name);
        if (mysqli_connect_errno() || !$this->connection) {
            die('MySQLi connection failed, ' . mysqli_connect_error());
        }

        // set db characterset
        mysqli_set_charset($this->connection, $this->db_charset);

        // set default timezone
        date_default_timezone_set('Asia/Jerusalem');
    }

    /*
     * Closes connection
     */
    public function __destruct() {
        // check connection before closing
        if ($this->connection) {
            mysqli_close($this->connection);
        }
    }

    /*
     * Returns if our connection to our db is valid
     */
    public function status() {
        return $this->connection;
    }

    /*
     * Returns a string value representing the description of the error from the last MySQLi function call
     */
    public function get_error() {
        return mysqli_error($this->connection);
    }

    /*
     * Perform query against a database
     * 
     * Returns mysqli_result object
     */
    public function query($sql) {
        // check connection
        if (!$this->status()) {
            die('connection to db failed, ' . $this->get_error());
        }

        // peform query
        $result = mysqli_query($this->connection, $sql);
        if (!$result) {
            die('failed sending query, ' . $this->get_error());
        }

        return $result;
    }

    /*
     * Use this function whenever working with dynamic data (for example user inputs, form submissions or data form API.)
     * This can reduce the risk of SQL injection
     * 
     * Returns mysqli_result object
     */
    public function prepare_query($sql, ...$params) {
        // check connection
        if (!$this->status()) {
            die('connection to db failed. ' . get_error());
        }

        // prepare an sql statement to query
        $stmt = mysqli_prepare($this->connection, $sql);
        if (!$stmt) {
            die('failed to prepare an sql statement to query, ' . $this->get_error());
        }

        // bind params if needed
        if (!empty($params)) {
            $types = "";
            $bindParams = [];

            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= "i";
                } elseif (is_float($param)) {
                    $types .= "d";
                } else {
                    $types .= "s";
                }

                $bindParams[] = $param;
            }
            
            mysqli_stmt_bind_param($stmt, $types, ...$bindParams);
        }

        // execute the prepared statement
        if (!mysqli_stmt_execute($stmt)) {
            die('failed to execute an sql statement, ' . $this->get_error());
        }

        // todo; check for some error code before returning

        $result = mysqli_stmt_get_result($stmt);

        // close the statement
        mysqli_stmt_close($stmt);
        return $result;
    }
    
    /*
     * This function accepts a result object as a parameter and frees the memory associated with it
     * Use only when calling query() function
     */
    public function free_result($result) {
        if ($result) {
            mysqli_free_result($result);
        }
    }

    /*
     * Fetches a result row as an associative array
     */
    public function fetch_assoc($result) {
        return mysqli_fetch_assoc($result);
    }

    /*
     * Fetches a result row as an associative array, a numeric array, or both
     */
    public function fetch_array($result) {
        return mysqli_fetch_array($result);
    }

    /*
     * Returns the number of rows in a result set
     */
    public function num_rows($result) {
        return mysqli_num_rows($result);
    }
    
    /*
     * This function used to retrieve all the rows from a database query result and return them as an array of associative arrays
     */
    public function get_all($result) {
        $rows = array();

        while ($row = $this->fetch_assoc($result)) {
            $rows[] = $row;
        }

        return $rows;
    }

    /*
     * Safer function to obtain $_POST variables
     */
    public function safe_post($key) {
        // check connection
        if (!$this->status()) {
            die('connection to db failed.' . $this->get_error());
        }

        // checks if the corresponding POST variable exists 
        if (isset($_POST[$key])) {
            // mysqli_real_escape_string escapes special characters in a string for use in an SQL query
            return mysqli_real_escape_string($this->connection, $_POST[$key]);
        }
        
        return null;
    }

    /*
     * Safer function to obtain $_GET variables
     */
    public function safe_get($key) {
        // check connection
        if (!$this->status()) {
            die('connection to db failed.' . $this->get_error());
        }

        // checks if the corresponding POST variable exists 
        if (isset($_GET[$key])) {
            // mysqli_real_escape_string escapes special characters in a string for use in an SQL query
            return mysqli_real_escape_string($this->connection, $_GET[$key]);
        }
        
        return null;
    }
}

?>