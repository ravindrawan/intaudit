<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

function isAdmin() {
    return isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'super_admin');
}

function isSuperAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin';
}

function requireAdmin() {
    if (!isAdmin()) {
        die("Unauthorized access. Admin privileges required.");
    }
}

function requireSuperAdmin() {
    if (!isSuperAdmin()) {
        die("Unauthorized access. Super Admin privileges required.");
    }
}

function hasMenuPermission($menuKey, $pdo = null) {
    // Super admins always have access
    if (isSuperAdmin()) {
        return true;
    }
    
    // Default to true if no role is set, but this shouldn't happen for logged in users
    if (!isset($_SESSION['role'])) {
        return false;
    }

    $role = $_SESSION['role'];
    
    if ($pdo === null) {
        global $pdo; // fallback just in case
    }
    
    if ($pdo !== null) {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'menu_permissions'");
        $stmt->execute();
        $json = $stmt->fetchColumn();
        
        if ($json) {
            $permissions = json_decode($json, true);
            if (isset($permissions[$role]) && isset($permissions[$role][$menuKey])) {
                return $permissions[$role][$menuKey] === true || $permissions[$role][$menuKey] === '1';
            }
        }
    }
    
    // Default to true (visible) if no permissions are explicitly defined
    return true;
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        die("Invalid CSRF token. Request aborted.");
    }
}
?>
