<?php
ini_set('display_errors', 1);
    error_reporting(E_ALL);

class backend{
    public $params;
    public function __construct() {
        $this->params = $_GET;
        switch ($this->params['action']){
            case "add": $this->add(); return;
            case "get": $this->get(); return;
        }
        if(empty($this->params['action'])){
            $this->_return($_SERVER['HTTP_REFERER']);
            echo "empty req";
        }
    }

    public function _return($url){
        $str = http_build_query($this->params,"&");
        echo $str;
        header("Location: ".$url."?".$str);
    }

    public function add(){
        $time = trim($this->params['stime']);
        $url = trim($this->params['url']);
        $rett = trim($this->params['return_to']);
        $ref = trim($_SERVER['HTTP_REFERER']);
        unset($this->params['action']);
        unset($this->params['stime']);
        unset($this->params['url']);
        unset($this->params['return_to']);
        preg_match("/https?:\/\/([^\/]+)/", $url,$match);
        preg_match("/https?:\/\/([^\/]+)/", $rett,$match1);
        preg_match("/https?:\/\/([^\/]+)/", $ref,$match2);
        if(($match[1] === $match1[1]) && ($match[1] === $match2[1])){
            $time = time() + $time;
            $mass['url'] = $url;
            $mass['sdate'] = $time;
            $mass['date'] = time();
            $json = json_encode($mass);
            setcookie($match[1], $json);
            $this->params['result'] = "success";
            $this->_return($rett);
        }else{
            $this->params['result'] = "error";
            $this->_return($rett);
        }
    }
    
    public function get(){
        $rett = $this->params['return_to'];
        unset($this->params['return_to']);
        unset($this->params['action']);
        $fmas = array();
        foreach ($_COOKIE as $key => $value) {
            if($mass = json_decode($value)){
                $fmas[] = $mass;
            }
        }
        function my_sort($a, $b){
            $time = time();
            if((($a->sdate < $time) && ($b->sdate < $time)) || (($a->sdate >= $time) && ($b->sdate >= $time))){
                return ($a->date < $b->date) ? 1 : -1;
            }
            else{
                if($a->sdate > $time){
                    return -1;
                }else{
                    return 1;
                }
            }
            return 0;
        }   
        usort($fmas, "my_sort");   
        $s = "";
        for($i = 0; $i < sizeof($fmas);$i++){
            $s .= $fmas[$i]->url;
            if(isset($fmas[$i]->def) && ($fmas[$i]->def == 1)){
                $s .= "*";
            }
            if(($i + 1) != sizeof($fmas)){
                $s .= ",";
            }
        }
        $this->params['pss'] = $s;
        $this->_return($rett);
    }
}
new backend();
?>