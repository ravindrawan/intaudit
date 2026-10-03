<?php
require_once 'includes/auth.php';
requireAdmin();

if (isset($_FILES['file']['name'])) {
    if (!$_FILES['file']['error']) {
        $name = time() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', basename($_FILES['file']['name']));
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        
        // Basic validation
        $allowed_exts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar', 'txt', 'csv'];
        if (in_array($ext, $allowed_exts)) {
            $uploadDir = '../assets/uploads/files/';
            if(!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $destination = $uploadDir . $name;
            $location = $_FILES["file"]["tmp_name"];
            
            if (move_uploaded_file($location, $destination)) {
                // Return the public URL
                $publicUrl = 'assets/uploads/files/' . $name;
                
                require_once '../includes/db.php';
                global $pdo;
                $stmtBaseUrl = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'base_url'");
                $stmtBaseUrl->execute();
                $dbBaseUrl = $stmtBaseUrl->fetchColumn();
                
                if (!empty($dbBaseUrl)) {
                    $full_url = rtrim($dbBaseUrl, '/') . '/' . $publicUrl;
                } else {
                    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
                    $app_path = dirname(dirname($_SERVER['SCRIPT_NAME'])); 
                    $app_path = str_replace('\\', '/', $app_path);
                    if ($app_path === '/') $app_path = '';
                    
                    // full url 
                    $full_url = $base_url . $app_path . '/' . $publicUrl;
                }
                echo json_encode(['url' => $full_url, 'name' => basename($_FILES['file']['name'])]);
            } else {
                header('HTTP/1.1 500 Internal Server Error');
                echo json_encode(['error' => 'Failed to move uploaded file.']);
            }
        } else {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => 'Invalid file format. Only doc, docx, pdf, zip, etc. are allowed.']);
        }
    } else {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['error' => 'Upload error occurred.']);
    }
}
?>
