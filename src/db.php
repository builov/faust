<?php

$host = 'db'; // Имя сервиса из docker-compose
$db   = 'my_database';
$user = 'user';
$pass = 'user_password';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass);
    echo "Успешное подключение к базе!";
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
