<?php
require_once 'config.php';
ini_set('display_errors', 1);
    error_reporting(E_ALL);
class api {
    public $db;
    public $params;
    public $shared_key;
    public function check(){
        /*
         * Реализовать проверку на наличие функции
         */
        unset($this->params['step']);
        $hash = "function=".$this->params['function'].
                "algorithm=".$this->params['algorithm'].
                "local_id=".$this->params['local_id'].$this->shared_key;
        $hash = hash('sha256',$hash);
        if($hash === $this->params['signature']){
            return TRUE;
        }
        return FALSE;
    }
    public function error(){
        die("<h1>ERROR</h1>");
    }

    public function get_email(){
        return 'test_email@gmail.com';
    }
    public function response($data){
        $mas = array('step' => 'api_usage_respose',
            'function' => $this->params['function'],
            'email' => $data,
            'local_id' => $this->params['local_id'],
            'signature' => 'gsgds-tmp_sig-gfghd3gfs');
        $json = json_encode($mas);
        echo $json;
    }

    public function __construct() {
        $this->params = $_GET;
        require_once 'config.php';
        $db = new db();
        $q = $db->mysqli->query("SELECT * FROM `oaap-users` WHERE `uid`=".$this->params['local_id']);
        $ms = $q->fetch_assoc();
        
        $this->shared_key = $ms['key'];
        if($this->params['step'] != 'api_usage_query'){
            $this->error();
        }
        if(!$this->check()){
            $this->error();
        }
        $data = "";
        switch ($this->params['function']){
            case 'get_email': $data = $this->get_email(); break;
        }
        $this->response($data);
    }
}
new api();
