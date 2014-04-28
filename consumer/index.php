<?php
if(isset($_GET['pss']) && ($_GET['pss'] != "")){
        $str = $_GET['pss'];
        $ms1 = explode(",", $str);
        for($i = 0; $i < sizeof($ms1);$i++){
            if(preg_match("/(.*)\*$/", $ms1[$i],$match)){
                header("Location: consumer.php?url-ps=".$match[1]);
                $ms1[$i] = $match[1];
            }
        }
}
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html" charset="UTF-8">
        <title>Портал-клиент</title>
        <style type="text/css">
            .link{
                display: block;
                width: 100%;
                height: 25px;
                line-height: 25px;
                color: rgb(50,50,255);
                text-decoration: none;
                font-size: 18px;
            }
            .link:hover{
                background-color: rgb(100,150,100);
                color: blue;
            }
        </style>
    </head>
    <body style="display: block; width: 800px; border: 3px solid blue; margin: auto; text-align: center; padding-top: 100px; padding-bottom: 100px;">
        
<?php
if(isset($_COOKIE['locid'])){
?>
    <b style="color:green;">ВЫ УСПЕШНО ВОШЛИ!</b>
<?php
//setcookie ("locid", "", time() - 3600);
require_once './config.php';
$db = new db();
$q = $db->mysqli->query("SELECT * FROM `oaap-users` WHERE `provider_id`=".$_COOKIE['locid']);
$ms = $q->fetch_assoc();
$hash = "function=get_emailalgorithm=SHA-256local_id=".$ms['provider_id'].$ms['key'];
$hash = hash('sha256',$hash);
$params = array('step' => 'api_usage_query',
        'function' => 'get_email',
        'algorithm' => 'SHA-256',
        'local_id' => $ms['provider_id'],
        'signature' => $hash);
$getprms = http_build_query($params, "&");
$text = file_get_contents($ms['qurl']."?".$getprms);
$json_object = json_decode($text);
echo "<br>Ваш e-mail на П-С: <b>".$json_object->email."<b><br>";
}else{
    ?>
    <b style="color:red;">вы ещё не зарегистрированы</b>
    <?php
}
?>
        <h1>ПОРТАЛ-КЛИЕНТ</h1>
        <b>Введите URL адрес портала-сервера:</b>
        <form action="consumer.php" method="GET">
            URL: <input type="text" name="url-ps"><br>
            <input type="submit">
        </form>
        <br><br>
        <b>Или воспользуйтесь Backend:</b>
<?php
    if(isset($_GET['pss']) && ($_GET['pss'] != "")){
?>
        <div style="width: 500px; margin: auto; border: 1px black dotted;">
<?php
        for($i = 0; $i < sizeof($ms1);$i++){      
?>
            <a class="link" href="consumer.php?url-ps=<?php echo $ms1[$i]; ?>" ><?php echo $ms1[$i]; ?></a>
<?php
        }
?>
        </div>
<?php    
    }else{
        if(isset($_GET['pss']) && ($_GET['pss'] == "")){
?>
        <p style="color: red;"><b>:(</b> нет данных о Ваших регистрация на порталах-серверах</p>
<?php
        }
?>
        <form method="GET" action="http://backend/backend.php">
            <input type="hidden" name="action" value="get">
            <input type="hidden" name="return_to" value="http://oaap-consumer">
            <input type="submit" value="авторизация">
        </form>
<?php
    }
?>
    </body>
</html>