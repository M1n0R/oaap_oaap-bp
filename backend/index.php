<?php
if(isset($_GET['def'])){
    $str = urldecode($_GET['def']);
    $true;
    foreach ($_COOKIE as $key => $value) {
        $ms = json_decode($value);
        if($ms->url === $str){
            $true = $ms;
        }
        if(isset($ms->def) && ($ms->def == 1)){
            unset($ms->def);
            $str = json_encode($ms);
            preg_match("/https?:\/\/([^\/]+)/", $ms->url,$match);
            setcookie($match[1],$str);
        }
    }
    $true->def = "1";
    $str = json_encode($true);
    preg_match("/https?:\/\/([^\/]+)/", $true->url,$match);
    setcookie($match[1],$str);
}
        ?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html" charset="UTF-8">
        <title>BACKEND</title>
    </head>
    <body style="display: block; width: 800px; border: 3px solid blue; margin: auto; text-align: center; padding-top: 100px; padding-bottom: 100px;">
        <h2>Выберете портал-сервер поумолчанию</h2>
        <form method="GET" action="#">
        <?php
       foreach ($_COOKIE as $key => $value) {
            if($mass = json_decode($value)){
                $fmas[] = $mass;
            }
        }
        for($i = 0;$i <sizeof($fmas);$i++){
            echo $fmas[$i]->url; ?> <input type="radio" name="def" value="<?php echo $fmas[$i]->url; ?>"><br><br>
            <?php
        }
       ?>
                <input type="submit">
        </form>
    </body>
</html>
