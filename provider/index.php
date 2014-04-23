<?php
header('X-OAAP-AuthProvider: http://localhost/diplom/provider/sa.php');   
?>
<html>
    <head>
        
        <!-- Главная страница П-С -->
        
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="viewport" content="width=device-width">
        <meta http-equiv="X-OAAP-AuthProvider" content="http://localhost/diplom/provider/sa.php">
        <title>Портал-сервер</title>
    </head>
    <body style="display: block; width: 800px; border: 3px solid red; margin: auto; text-align: center; padding-top: 100px; padding-bottom: 100px;">
        <h1>ПОРТАЛ-СЕРВЕР</h1>
        <?php if(isset($_GET['result'])){
            if($_GET['result'] == 'success'){
                ?><h3>Регистрация на Backend успешна!</h3><?php
            }else{
                ?><h3>Ошибка регистрации на Backend</h3><?php
            }
        }?>
        <form method="GET" action="http://backend/backend.php">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="stime" value="1000">
            <input type="hidden" name="url" value="http://oaap-provider">
            <input type="hidden" name="return_to" value="http://oaap-provider">
            <input type="submit" value="Регистрация на Backend">
        </form>
    </body>
</html>