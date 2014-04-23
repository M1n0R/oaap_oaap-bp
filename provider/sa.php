<?php
/*
 * Основной файл протокола
 */
require_once './config.php';
ini_set('display_errors', 1);
    error_reporting(E_ALL);
    
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

    public function setPG($prime, $generator){
        $this->prime = $prime;
        $this->generator = $generator;
    }
    
    public function generateKeys(){
        $dfm = new Crypt_DiffieHellman_Math();
        $x = $dfm->rand(10, 555);
        $x = $dfm->fromBinary($x);
        $m = explode(".", $x);
        $x = $m[0];
        $this->x = $x;
        $options = array(
            'prime' => $this->prime,
            'generator' => $this->generator,
            'private' => $this->x
        );
        $this->entity = new Crypt_DiffieHellman($options['prime'], $options['generator'], $options['private']);
        $this->entity->generateKeys();
        $this->publicKey = $this->entity->getPublicKey();
        $this->publicKey = str_replace(".", "", $this->publicKey);
    }
    
    public function generateSharedKey($pKey){
        $this->sharedKey = $this->entity->computeSecretKey($pKey)->getSharedSecretKey();
    }

    public function __construct() {
        require_once 'Crypt/DiffieHellman.php';
    }
}

class oaap{
    public $shredKey;
    public $algo;
    public $time_key;
    
    public function generateStep(){
        $df = new df();
        $df->setPG(trim($_GET['p']), trim($_GET['g']));
        $df->generateKeys();
        $df->generateSharedKey($_GET['a']);
        $db = new db();
        $time = time() + config::$key_time;
        $query = "INSERT INTO `oaap-process` (`time_key`, `key`) VALUE (".$time.", '".$df->sharedKey."')";
        $db->mysqli->query($query) or die("CANT INSERT ON A SERVER");
        var_dump($db->mysqli->insert_id);
        $string = "step:generate_key\n";
        $string .= "b:".$df->publicKey."\n";
        $string .= "time_key:".config::$key_time."\n";
        $string .= "session-id:".$db->mysqli->insert_id."\n";
        $string .= "allowable_algorithms:".config::$algo."\n";
        echo $string;
    }
    
    public function checkRttClnt($urlReturnTo, $urlClient){
        /*
        * Нереализовано, функция проверки связи между параметром Client и Return_to 
        */
        return TRUE;
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
        /*
        * нереализована, реализует каждый П-С сам
        */
        return TRUE;
    }
    
    public function getUserLocalId(){
        /*
         * Нереализовано, на усмотрение пользователя
         */
        return 19920205;
    }

    public function setResponse($array){
        $s = "";
        foreach ($array as $key => $value) {
            $s .= $key.":".$value."\n";
        }
        return $s;
    }

    public function checkUserStep(){
        $db = new db();
        $query = $db->mysqli->query("SELECT `key` FROM `oaap-process` WHERE `id`=".$_GET['session-id']) or die("CANT SELECT");
        $mss = $query->fetch_assoc();
        $this->algo = $_GET['algorithm'];
        $this->shredKey = $mss['key'];
        if($this->checkRttClnt($_GET['return_to'], $_GET['client'])){
            $ms = $_GET;
            unset($ms['signature']);
            $sig = $this->generateSig($ms);
            if($_GET['signature'] === $sig){
                if(!$this->userAuth()){
                    /* РЕАЛИЗАЦИЯ АВТОРИЗАЦИИ*/
                }else{
                    $locid = $this->getUserLocalId();
                    $qr = $db->mysqli->query("SELECT `autoauth` FROM `oaap-users` WHERE `uid`=".$locid." AND `url`='".$_GET['client']."'");
                    $msk = $qr->fetch_assoc();
                    if((isset($msk['autoauth'])) && ($msk['autoauth'] == 1)){
                        $this->userToClient(FALSE);
                    }else{
                        include './confirmauth.php';
                    }
                }
            }else{
                $str = "step:check_user\n
                    error:bad_hash\n
                    description:".$_GET['signature'];
                echo $str;
                exit();
            }
            
        }else{
            $str = "step:check_user\n
                error:bad_parameters\n
                description:different client url and return_to url";
            echo $str;
            exit();
        }
    }
    
    public function toBdUserInform($result){
        $fns = "";
        if(isset($_GET['name'])){
            $fns .= "name,";
        }
        if(isset($_GET['sname'])){
            $fns .= "sname,";
        }
        if(isset($_GET['email'])){
            $fns .= "email,";
        }
        /* ... */ 
        $db = new db();
        $locid = $this->getUserLocalId();
        $qr = $db->mysqli->query("SELECT * FROM `oaap-users` WHERE `uid`=".$locid." AND `url`='".$_GET['client']."'");
        if($qr->num_rows != 0){
            $query = "UPDATE `oaap-users` SET"
                    . " `time_key`='".$this->time_key."',"
                    . " `functions`='".$fns."',"
                    . " `key`='".$this->shredKey."',"
                    . " `autoauth`=".$result
                    . " WHERE `uid`='".$locid."' AND `url`='".$_GET['client']."'";
        }  else {
            $query = "INSERT INTO `oaap-users` (`uid`, `time_key`, `url`, `functions`, `key`, `autoauth`) VALUE ('"
                . $this->getUserLocalId()."', '"
                . $this->time_key."', '"
                . $_GET['client']."', '"
                . $fns."', '"
                . $this->shredKey."', '"
                . $result."')";
        }
        $db->mysqli->query($query) or die("cant q");;
    }
    public function updateUserInform(){
        $db = new db();
        $str = "UPDATE `oaap-users` SET `time_key`='".$this->time_key."', `key`='".$this->shredKey."' WHERE "
                . "`uid`='".$this->getUserLocalId()."' AND `url`='".$_GET['client']."'";
        $qr = $db->mysqli->query($str);
    }
    public function userToClient($insert){
        $db = new db();
        $qr = $db->mysqli->query("SELECT * FROM `oaap-process` WHERE `id`=".$_GET['session-id']);
        $ms = $qr->fetch_assoc();
        $this->shredKey = $ms['key'];
        $this->time_key = $ms['time_key'];
        $this->algo = $_GET['algorithm'];
        if($_GET['result'] != '3'){
            if($insert){
                $this->toBdUserInform($_GET['result']);
            }else{
                $this->updateUserInform();
            }
            $mas = array(
                "step" => "check_user",
                "algorithm" => $_GET['algorithm'],
                "result" => "successful",
                "query_URL" => config::$qurl,
                "local_id" => $this->getUserLocalId(),
                "nonce" => $_GET['nonce'],
                "session-id" => $_GET['session-id']
            );
            $hash = $this->generateSig($mas);
            $mas['signature'] = $hash;
            $str = http_build_query($mas);
            header("Location: ".$_GET['return_to']."?".$str);
            exit();
        }else{
            $mas = array(
                "step" => "check_user",
                "algorithm" => $_GET['algorithm'],
                "result" => "canceled",
                "nonce" => $_GET['nonce'],
                "session-id" => $_GET['session-id']
            );
            $hash = $this->generateSig($mas);
            $mas['signature'] = $hash;
            $str = http_build_query($mas);
            echo $_GET['return_to']."?".$str;
            header("Location: ".$_GET['return_to']."?".$str);
            exit();
        }
    }

    public function __construct() {
        if($_GET['step'] == "generate_key"){ /*Пункт 6.3*/
            $this->generateStep();
        }
        if($_GET['step'] == "check_user"){ /*Пункт 6.4*/
        
            $this->checkUserStep();            
        }
        if($_GET['step'] == "authnconf_user"){
            $this->userToClient(TRUE);
        }
    }
}
$oaap = new oaap(); /*Запускаем процесс*/
unset($oaap);
?>