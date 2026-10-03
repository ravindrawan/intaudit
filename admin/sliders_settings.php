<?php
require_once 'includes/auth.php';
requireAdmin();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = trim($_POST['slider_animation_type']);
    $speed = (int)$_POST['slider_animation_speed'];

    // Ensure valid speed values
    if ($speed < 0) $speed = 0;

    $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
    $stmt->execute([$type, 'slider_animation_type']);
    $stmt->execute([(string)$speed, 'slider_animation_speed']);

    header("Location: sliders.php?msg=settings_updated");
    exit();
}
header("Location: sliders.php");
exit();
?>
