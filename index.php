<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Главная страница</title>
</head>
<body>
    <h1>Добро пожаловать!</h1>
    <?php if (isset($_SESSION['user_id'])): ?>
        <p>Вы авторизованы как <?= htmlspecialchars($_SESSION['name']) ?>. <a href="profile.php">Профиль</a> | <a href="logout.php">Выйти</a></p>
    <?php else: ?>
        <p><a href="login.php">Войти</a> | <a href="register.php">Зарегистрироваться</a></p>
    <?php endif; ?>
</body>
</html>