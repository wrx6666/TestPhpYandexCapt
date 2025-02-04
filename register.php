<?php
session_start();
require 'db.php';

function validatePhoneNumber($phone) {
    
    $phone = preg_replace('/[^\d]/', '', $phone);
    
   
    if (preg_match('/^\d{11}$/', $phone)) {
        return true; 
    } else {
        return false; 
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (!validatePhoneNumber($phone)) {
        echo "Неверный формат номера телефона!";
    } elseif ($password !== $confirm_password) {
        echo "Пароли не совпадают!";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email OR phone = :phone");
        $stmt->execute(['email' => $email, 'phone' => $phone]);
        if ($stmt->rowCount() > 0) {
            echo "Пользователь с таким email или телефоном уже существует!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, phone, email, password) VALUES (:name, :phone, :email, :password)");
            $stmt->execute(['name' => $name, 'phone' => $phone, 'email' => $email, 'password' => $hashed_password]);
            header("Location: login.php");
            exit(); 
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
</head>
<body>
    <h1>Регистрация</h1>
    <form method="post">
        Имя: <input type="text" name="name" placeholder="Alex"required><br>
        Телефон: <input type="text" name="phone" placeholder="+79140000000"required><br>
        Email: <input type="email" name="email" placeholder="email@mail.ru"required><br>
        Пароль: <input type="password" name="password" placeholder="*******"required><br>
        Повторите пароль: <input type="password" name="confirm_password" placeholder="*******"required><br>
        <button type="submit">Зарегистрироваться</button>
    </form>
</body>
</html>