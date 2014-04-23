<?php

/*
 * Файл настроек
 */
class config {
    public static $host = "localhost";
    public static $user = "root";
    public static $password = "1";
    public static $database = "p-c";
    public static $siteName = "oaap-consumer"; /*Будет использовано для п6.4 поле "Client"*/
    public static $reternUserTo = "http://oaap-consumer/consumer.php"; /*Будет использовано для п6.4 поле "Return_to"*/
}
class db{
    /**
     * @var mysqli
     */
    public $mysqli;
    
    public function __construct() {
        $this->mysqli = new mysqli(config::$host, config::$user, config::$password, config::$database);
        $this->mysqli->set_charset("utf8");
    }
}
?>