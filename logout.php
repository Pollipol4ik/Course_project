<?php
session_start();

// Уничтожаем все данные сессии
session_destroy();

// Перенаправляем пользователя на страницу входа или другую страницу по вашему выбору
header("Location: enter.php");
exit();
?>
