<?php
session_start();
require 'db.php';

define('SMARTCAPTCHA_SERVER_KEY','ysc2_VW9uP7wSGzMPN7AsNKV8yfh095laK1c7CAwRz25O5e42ecc1'); 

function check_captcha($token) {
    $ch = curl_init();
    $args = http_build_query([
        "secret" => SMARTCAPTCHA_SERVER_KEY,
        "token" => $token,
    ]);

    $url = "https://smartcaptcha.yandexcloud.net/validate?$args";
    error_log("SmartCaptcha URL: " . $url); 
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

    $server_output = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    error_log("SmartCaptcha HTTP Code: " . $httpcode); 
    error_log("SmartCaptcha Response: " . $server_output); 

    curl_close($ch);

    if ($httpcode !== 200) {
        echo "Ошибка доступа: код=$httpcode; сообщение=$server_output\n";
        return false;
    }
    $resp = json_decode($server_output);
    return $resp->status === "ok";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = $_POST['login'];
    $password = $_POST['password'];
    $captchaResponse = $_POST['smart-token'];

    if (!check_captcha($captchaResponse)) {
        echo "Капча не пройдена. Попробуйте еще раз.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :login OR phone = :login");
        $stmt->execute(['login' => $login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            header("Location: profile.php");
            exit;
        } else {
            echo "Неверный логин или пароль!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Авторизация</title>
    <script src="https://smartcaptcha.yandexcloud.net/captcha.js" defer></script>
</head>
<body>
    <h1>Авторизация</h1>
    <form method="post" id="loginForm">
        Логин (email или телефон): <input type="text" name="login" required><br>
        Пароль: <input type="password" name="password" placeholder="*******" required><br>

         <div
    style="height: 100px"
    id="captcha-container"
    class="smart-captcha"
    data-sitekey="ysc1_VW9uP7wSGzMPN7AsNKV8pu1yX05ZXmyatGKq9UgP53149aae"
    data-callback="captchaCallback"
    data-hl="ru"  
        ></div>
        <input type="hidden" name="smart-token" id="smart-token">
        <button type="submit">Войти</button>
    </form>

    <script>
        function captchaCallback(token) {
            document.getElementById('smart-token').value = token;
            document.getElementById('loginForm').submit();
        }

        document.getElementById('loginForm').addEventListener('submit', function(event) {
            if (document.getElementById('smart-token').value === '') {
                event.preventDefault();
                alert('Пожалуйста, пройдите капчу.');
            }
        });
    </script>
</body>
</html>