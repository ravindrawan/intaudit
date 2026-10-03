<?php
$pdo = new PDO('mysql:host=localhost;dbname=coopcomweb;charset=utf8mb4', 'root', 's&sdigital');
$stmt = $pdo->query('SHOW TABLES');
print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
?>
