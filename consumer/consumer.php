<?php
/*
 * Основной файл протокола
 */
require_once 'config.php';
ini_set('display_errors', 1);
    error_reporting(E_ALL);

class discover{
    public $urlSA;
    private function getContent($url){
        $mass = explode("/", $url, 4);
        $out = "GET /".$mass[3]." HTTP/1.1\r\n";
        $out .= "Host: ".$mass[2]."\r\n";
        $out .= "Accept: application/xrds+xml\r\n";
        $out .= "Connection: Close\r\n\r\n";
        $socket = fsockopen($mass[2], 80);
        fwrite($socket, $out);
        $s = "";
        while (!feof($socket)) {
                $s .= fgets($socket);
            }
        fclose($socket);
        return $s;
    }
    private function openFileDiscovery($url){
        $url .= "oaap-authprovider-url.txt";
        $urlSA = $this->getContent($url);
        return $urlSA;
    }
    private function urlHtmlDicovery($content){
        preg_match("/\<meta.*http\-equiv=\"X-OAAP-AuthProvider\".*\>/", $content,$match);
        preg_match("/content=\"(.*)\"/", $match[0],$urlSA);
        if(isset($urlSA[1])){
            return $urlSA[1];
        }else{
            return FALSE;
        }
    }
    private function urlHeaderDiscovery($content){
        $urlSA = FALSE;
        $mass = explode("\r\n\r\n", $content,2);
        $headers = explode("\r\n",$mass[0]);
        for($i = 1; $i < sizeof($headers);$i++){
            preg_match("/X\-OAAP\-AuthProvider\:(.*)/", $headers[$i],$match);
            if(isset($match[1])){
                $urlSA = $match[1];
            }
        }
        return $urlSA;
    }
    private function xrdsget($content) {
        $mas = explode("\r\n\r\n", $content);
        $tmp = explode("\r\n", $mas[0]);
        $headers = array();
        for($i = 0; $i < sizeof($tmp);$i++){
            $ttmp = explode(":", $tmp[$i],2);
            $headers[strtolower(trim($ttmp[0]))] = strtolower(trim($ttmp[1]));
        }
        if($headers['content-type'] == 'application/xrds+xml'){
            $xml = new SimpleXMLElement(trim($mas[1]));
            foreach ($xml->XRD->Service as $key => $value) {
                if($value->Type == "OAAP"){
                    return $value->URI;
                }
            }
        }  else {
            return FALSE;
        }
        return FALSE;
    }
    private function xrdsHeader($content){
        $xrdsURL = FALSE;
        $mass = explode("\r\n\r\n", $content,2);
        $headers = explode("\r\n",$mass[0]);
        for($i = 1; $i < sizeof($headers);$i++){
            preg_match("/X\-XRDS\-Location\:(.*)/", $headers[$i],$match);
            if(isset($match[1])){
                $xrdsURL = $match[1];
            }
        }
        if($xrdsURL){
            $content = $this->getContent($xrdsURL);
            $mas = explode("\r\n\r\n", $content);
            $xml = new SimpleXMLElement(trim($mas[1]));
            foreach ($xml->XRD->Service as $key => $value) {
                if($value->Type == "OAAP"){
                    return $value->URI;
                }
            }
        }
        return FALSE;
    }
    public function xrdsHTML($content) {
        preg_match("/\<meta.*http\-equiv=\"X\-XRDS\-Location\".*\>/", $content,$match);
        preg_match("/content=\"(.*)\"/", $match[0],$urlXRDS);
        $xrdsURL = FALSE;
        if(isset($urlXRDS[1])){
            $xrdsURL = $urlXRDS[1];
        }
        if($xrdsURL){
            $content = $this->getContent($xrdsURL);
            $mas = explode("\r\n\r\n", $content);
            $xml = new SimpleXMLElement(trim($mas[1]));
            foreach ($xml->XRD->Service as $key => $value) {
                if($value->Type == "OAAP"){
                    return $value->URI;
                }
            }
        }
        return FALSE;
    }
    public function __construct($url){
        $content = $this->getContent($url);
        $urlSA = $this->urlHeaderDiscovery($content);
        if(!$urlSA){
            $urlSA = $this->urlHtmlDicovery($content);
        }
        if(!$urlSA){
            $urlSA = $this->xrdsget($content);
        }
        if(!$urlSA){
            $urlSA = $this->openFileDiscovery($url);
        }
        if(!$urlSA){
            $urlSA = $this->xrdsHeader($content);
        }
        if(!$urlSA){
            $urlSA = $this->xrdsHTML($content);
        }
        $this->urlSA = $urlSA;
    }
}

class df{
    public $prime;
    public $generator;
    public $x;
    public $publicKey;
    public $sharedKey;
    /**
     * @var Crypt_DiffieHellman 
     */
    public $entity;


    public function generatePG(){
        $dfm = new Crypt_DiffieHellman_Math();
        $prime = $dfm->rand(10, 555);
        $prime = $dfm->fromBinary($prime);
        $m = explode(".", $prime);
        $prime = $m[0];
        $this->prime = $prime;
        $this->generator = rand(3,10);
    }

    public function generateKeys(){
        $dfm = new Crypt_DiffieHellman_Math();
        $x = $dfm->rand(10, 555);
        $x = $dfm->fromBinary($x);
        $m = explode(".", $x);
        $x = $m[0];
        $this->x = $x;
        $this->entity = new Crypt_DiffieHellman($this->prime, $this->generator, $this->x);
        $this->entity->generateKeys();
        $this->publicKey = $this->entity->getPublicKey();
    }
    
    public function generateSharedKey($pKey){
        $this->sharedKey = $this->entity->computeSecretKey($pKey)->getSharedSecretKey();
    }

    public function __construct() {
        require_once 'Crypt/DiffieHellman.php';
    }
}

class oaap{
    public $url;
    public $shredKey;
    public $algo;
    public $urlSA;
    public $sessionId;
    /**
     *
     * @var db
     */
    public $db;

    public function sendQuery($url,$params){
        $prms = http_build_query($params,"","&");

        $mass = explode("/", $url, 4);
        $out = "GET /".$mass[3]."?".$prms." HTTP/1.1\r\n";
        $out .= "Host: ".$mass[2]."\r\n";
        $out .= "Connection: Close\r\n";
        $out .= "\r\n";
        $socket = fsockopen($mass[2], 80);
        fwrite($socket, $out);
        $s = "";
        while (!feof($socket)) {
                $s .= fgets($socket);
            }
        fclose($socket);
        return $s;
    }
    
    public function parsRes($string){
        $s = explode("\r\n\r\n", $string);
        $s = $s[1];
        $ms = explode("\n", $s);
        $resPrms = array();
        for($i = 0; $i < sizeof($ms);$i++){
            $tmp = explode(":",$ms[$i]);
            if(sizeof($tmp) == 2)
                $resPrms[$tmp[0]] = $tmp[1];
        }
        return $resPrms;
    }
    
    public function generateKey(){
        $df = new df();
        $df->generatePG();
        $df->generateKeys();
        $prms = array("step" => "generate_key",
            "p" => $df->prime,
            "g" => $df->generator,
            "a" => $df->publicKey);
        echo "<h2>".$df->publicKey."</h2>";
        $s = $this->sendQuery($this->urlSA, $prms);
        $params = $this->parsRes($s);
        if($params['step'] != "generate_key"){
            die("<h1>ERROR</h1>");
        }
        $df->generateSharedKey($params['b']);
        $db = new db();
        $algo = "SHA-256";
        $query = "INSERT INTO `oaap-process` (`id`,`time_key`,`url`,`algo`,`key`) "
                . "VALUE (".$params['session-id'].", ".$params['time_key'].", '".$this->url."',"
                ." '".$algo."', '".$df->sharedKey."')";
        $db->mysqli->query($query) or die("CANT INSERT");
        $this->shredKey = $df->sharedKey;
        $this->algo = $algo;
        $this->sessionId = $params['session-id'];
        $this->db = $db;
    }

    public function generateSig($params){
        $str = "";
        foreach ($params as $key => $value) {
            $str .= $key."=".$value;
        }
        $str .= $this->shredKey;
        $hash = "";
        switch ($this->algo){
            case "SHA-256" : $hash = hash("sha256", $str); break;
            case "SHA-1" : $hash = hash("sha1", $str); break;
        }
        return $hash;
    }

    public function userAuth(){
        $nonce = rand(10000, 1000000000);
        $string = "UPDATE `oaap-process` SET `nonce`=".$nonce." WHERE `id`=".$this->sessionId;
        $this->db->mysqli->query($string);
        $mass = array(
            "step" => "check_user",
            "algorithm" => $this->algo,
            "client" => config::$siteName,
            "return_to" => config::$reternUserTo,
            "nonce" => $nonce,
            "session-id" => $this->sessionId 
        );
        $mass['signature'] = $this->generateSig($mass);
        header("Location: ".$this->urlSA."?".  http_build_query($mass,"&"));
    }
    
    public function userReturned(){
        $db = new db();
        $query = "SELECT * FROM `oaap-process` WHERE `id`=".$_GET['session-id'];
        $res = $db->mysqli->query($query);
        $ms = $res->fetch_assoc();
        $this->algo = $ms['algo'];
        $this->sessionId = $_GET['session-id'];
        $this->shredKey = $ms['key'];
        $this->url = $ms['url'];
        $mss = $_GET;
        unset($mss['signature']);
        $sig = $this->generateSig($mss);
        if($sig == $_GET['signature']){
            if($_GET['nonce'] == $ms['nonce']){
                $db->mysqli->query("UPDATE `oaap-process` SET `nonce`=''");
                if($_GET['result'] != "successful"){
                    die("<h1>Canceled</h1>");
                }
                $q = $db->mysqli->query("SELECT * FROM `oaap-users` WHERE `provider_id`=".$_GET['local_id']);
                if($q->num_rows != 0){
                    $query = "UPDATE `oaap-users` SET "
                            . "`time_key`='".$ms['time_key']."', "
                            . "`qurl`='".$_GET['query_URL']."', "
                            . "`algo`='".$this->algo."', "
                            . "`key`='".$this->shredKey."' "
                            . "WHERE `provider_id`=".$_GET['local_id'];
//                    echo $query;
                }else{
                    $query = "INSERT INTO `oaap-users` (`provider_id`,`time_key`,`qurl`,`algo`,`key`) VALUE ('"
                            .$_GET['local_id']."', '"
                            .$ms['time_key']."', '"
                            .$_GET['query_URL']."', '"
                            .$this->algo."', '"
                            .$this->shredKey. "')";
                }
                $db->mysqli->query($query);
//                echo "<h1>SUCCESSFUL</h1>";
                setcookie("locid", $_GET['local_id']);
                header("Location: index.php");
                return TRUE;
            }
            else{
                die("<h1>Error: bad nonce</h1>");
            }
        }
        else{
            echo $sig."<br>".$_GET['signature'];
            var_dump($_GET);
            die("<h1>Error: bad signature</h1>");
        }
    }

    public function __construct($url) {
        if($_GET['step'] != 'check_user'){ /*Если пользователь только инициировал процесс*/
            $this->url = $url;
            $dscvr = new discover($url); /* пункт 6.2*/
            $this->urlSA = $dscvr->urlSA;
            $this->generateKey(); /*пункт 6.3*/
            $this->userAuth(); /*пункт 6.4*/
        }else{/*Если пользователь вернулся с П-С после подтверждения намерения*/
            $this->userReturned(); /*пунукт 6.5*/
        }
    }
}

echo "<h1>I am a client library</h1>";
$url;
if(isset($_GET['url-ps'])){
    $url = $_GET['url-ps'];   
}
$oaap = new oaap($url); /*Запускаем процесс*/

unset($oaap);

?>