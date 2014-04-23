<?php

/*
 * Файл настроек
 */
class config {
    public static $host = "localhost";
    public static $user = "root";
    public static $password = "1";
    public static $database = "p-s";
    public static $algo = "SHA-1,SHA-256"; /*Допустимые алгоритмы шифрования*/
    public static $key_time = 1000; /*Время жизни ключа*/
    public static $qurl = "http://oaap-provider/api.php"; /* URL использования API*/
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