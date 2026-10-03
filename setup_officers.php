<?php
$pdo = new PDO('mysql:host=localhost;dbname=coopcomweb;charset=utf8mb4', 'root', 's&sdigital');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "CREATE TABLE IF NOT EXISTS officers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_name VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    designation VARCHAR(150) NOT NULL,
    photo_path VARCHAR(255) DEFAULT NULL,
    order_index INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$pdo->exec($sql);
echo "Table 'officers' created successfully.";

$stmtCheck = $pdo->query("SELECT COUNT(*) FROM settings WHERE setting_key = 'show_officers_home'");
if ($stmtCheck->fetchColumn() == 0) {
    $pdo->exec("INSERT INTO settings (setting_key, setting_value) VALUES ('show_officers_home', '0')");
    echo " Setting 'show_officers_home' added.";
}

?>
