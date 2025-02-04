<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

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

    
    if (!validatePhoneNumber($phone)) {
        echo "Неверный формат номера телефона!";
    } else {
        
        $stmt = $conn->prepare("UPDATE users SET name = :name, phone = :phone, email = :email" . 
                                ($password ? ", password = :password" : "") . 
                                " WHERE id = :id");

        
        if ($password) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->execute(['name' => $name, 'phone' => $phone, 'email' => $email, 'password' => $hashed_password, 'id' => $_SESSION['user_id']]);
        } else {
            $stmt->execute(['name' => $name, 'phone' => $phone, 'email' => $email, 'id' => $_SESSION['user_id']]);
        }

        $_SESSION['name'] = $name;
        echo "Данные обновлены!";
    }
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Профиль</title>
</head>
<body>
    <h1>Профиль</h1>
    <form method="post">
        Имя: <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required><br>
        Телефон: <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required><br>
        Email: <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br>
        Новый пароль: <input type="password" name="password"><br>
        <button type="submit">Обновить данные</button>
    </form>
    <a href="index.php">На главную</a>
</body>
</html>