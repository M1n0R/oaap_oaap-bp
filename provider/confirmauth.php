<!DOCTYPE html>
<html>
    <head>
        <title>Подтверждение</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width">
    </head>
    <body>
        
        <!-- Файл подтверждения намерений (так же можно подключить авторизацию)-->
        
        <div style="width: 800px; border: 1px solid black; margin: auto; padding: 10px;">
            ....<br>
            Авторизация<br>
            ....<br>
            <form method="GET" action="#" style="display: block; border-top: 1px solid black;padding-top: 10px;">
                <input type="hidden" name="step" value="authnconf_user">
                <input type="hidden" name="session-id" value="<?php echo $_GET['session-id']; ?>">
                <input type="hidden" name="algorithm" value="<?php echo $_GET['algorithm']; ?>">
                <input type="hidden" name="client" value="<?php echo $_GET['client']; ?>">
                <input type="hidden" name="return_to" value="<?php echo $_GET['return_to']; ?>">
                <input type="hidden" name="nonce" value="<?php echo $_GET['nonce']; ?>">
                Выберете Функции:<br>
                Имя <input type="checkbox" name="name" value="1"><br>
                Фамилия <input type="checkbox" name="sname" value="1"><br>
                E-mail <input type="checkbox" name="email" value="1"><br>
                ...<br>
                <b>Вы разрешаете сайту <?php echo $_GET['client']; ?> использовать эти данные?</b><br>
                Да, отвечать так автоматически <input type="radio" name="result" value="1"><br>
                Да, только сейчас <input type="radio" name="result" value="2"><br>
                Нет, отменить процедуру <input type="radio" checked name="result" value="3"><br>
                <input type="submit">
            </form>
        </div>
    </body>
</html>
