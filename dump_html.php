<?php
require 'includes/db.php';
$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'about_chairman_msg'");
$stmt->execute();
$val = $stmt->fetchColumn();
file_put_contents('chairman_html.txt', $val);
echo "Dumped.";
?>
