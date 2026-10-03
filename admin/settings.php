<?php
require_once 'includes/auth.php';
requireAdmin(); // Only admin can access this page
require_once '../includes/db.php';

if (!hasMenuPermission('settings')) {
    header("Location: index.php");
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    // Handle logo upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/uploads/';
        // Ensure folder exists
        if(!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
        
        $fileName = time() . '_' . basename($_FILES['logo']['name']);
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp'])) {
            $targetFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetFile)) {
                $logoPath = 'assets/uploads/' . $fileName;
                $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'logo_path'");
                $stmt->execute([$logoPath]);
            } else {
                $error = 'Failed to upload logo.';
            }
        } else {
            $error = 'Invalid logo file format. Only JPG, PNG, GIF, WEBP are allowed.';
        }
    }

    // Handle favicon upload
    if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/uploads/';
        if(!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
        
        $fileName = time() . '_favicon_' . basename($_FILES['favicon']['name']);
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Ico, png, etc are valid for favicon
        if (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'ico'])) {
            $targetFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['favicon']['tmp_name'], $targetFile)) {
                $faviconPath = 'assets/uploads/' . $fileName;
                
                // Auto-insert setting key if it doesn't exist
                $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = 'favicon_path'");
                $stmtCheck->execute();
                if ($stmtCheck->fetchColumn() == 0) {
                    $pdo->exec("INSERT INTO settings (setting_key, setting_value) VALUES ('favicon_path', '')");
                }

                $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'favicon_path'");
                $stmt->execute([$faviconPath]);
            } else {
                $error = 'Failed to upload favicon.';
            }
        } else {
            $error = 'Invalid favicon file format. Only ICO, JPG, PNG, GIF, WEBP are allowed.';
        }
    }

    // Update other settings
    $settingsToUpdate = [
        'site_name_en' => $_POST['site_name_en'] ?? '',
        'site_name_si' => $_POST['site_name_si'] ?? '',
        'site_name_ta' => $_POST['site_name_ta'] ?? '',
        'footer_description' => $_POST['footer_description'] ?? '',
        'contact_email' => $_POST['contact_email'] ?? '',
        'contact_phone' => $_POST['contact_phone'] ?? '',
        'base_url' => rtrim($_POST['base_url'] ?? '', '/'),
        'under_construction' => isset($_POST['under_construction']) ? '1' : '0',
        'show_officers_home' => isset($_POST['show_officers_home']) ? '1' : '0'
    ];

    foreach ($settingsToUpdate as $key => $value) {
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = ?");
        $stmtCheck->execute([$key]);
        if ($stmtCheck->fetchColumn() > 0) {
            $stmtUpdate = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
            $stmtUpdate->execute([$value, $key]);
        } else {
            $stmtInsert = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
            $stmtInsert->execute([$key, $value]);
        }
    }

    if (!$error) {
        $success = 'Settings updated successfully.';
    }
}

// Fetch current settings
$stmt = $pdo->prepare("SELECT setting_key, setting_value FROM settings");
$stmt->execute();
$settingsData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>General Settings - Gov Admin</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    
        <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 px-md-4 py-4">

    <div class="container mt-4">
        <h2>General Settings</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">General Settings</li>
            </ol>
        </nav>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="settings.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <h5 class="mb-3">Institute Name & Site Identity</h5>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Institute Name (English)</label>
                            <input type="text" class="form-control" name="site_name_en" value="<?php echo htmlspecialchars($settingsData['site_name_en'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Institute Name (Sinhala)</label>
                            <input type="text" class="form-control" name="site_name_si" value="<?php echo htmlspecialchars($settingsData['site_name_si'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Institute Name (Tamil)</label>
                            <input type="text" class="form-control" name="site_name_ta" value="<?php echo htmlspecialchars($settingsData['site_name_ta'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <h5 class="mb-3 mt-4">Logo configuration</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Update Logo (Leave blank to keep current)</label>
                            <input type="file" class="form-control" name="logo" accept="image/*">
                        </div>
                        <div class="col-md-6 d-flex align-items-center">
                            <?php if (!empty($settingsData['logo_path']) && file_exists('../' . $settingsData['logo_path'])): ?>
                                <img src="../<?php echo $settingsData['logo_path']; ?>" alt="Current Logo" style="max-height: 50px;">
                            <?php else: ?>
                                <span class="text-muted">No logo uploaded yet.</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <h5 class="mb-3 mt-4">Favicon Configuration</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Update Favicon (Recommended: 32x32px or 16x16px)</label>
                            <input type="file" class="form-control" name="favicon" accept="image/x-icon,image/png,image/jpeg">
                        </div>
                        <div class="col-md-6 d-flex align-items-center">
                            <?php if (!empty($settingsData['favicon_path']) && file_exists('../' . $settingsData['favicon_path'])): ?>
                                <img src="../<?php echo $settingsData['favicon_path']; ?>" alt="Current Favicon" style="max-height: 32px; border: 1px solid #ccc;">
                            <?php else: ?>
                                <span class="text-muted">No favicon uploaded yet.</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <h5 class="mb-3 mt-4">Contact Information & Footer</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Contact Email</label>
                            <input type="email" class="form-control" name="contact_email" value="<?php echo htmlspecialchars($settingsData['contact_email'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Phone</label>
                            <input type="text" class="form-control" name="contact_phone" value="<?php echo htmlspecialchars($settingsData['contact_phone'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Footer Description (About Institute snippet)</label>
                        <textarea class="form-control" name="footer_description" rows="3"><?php echo htmlspecialchars($settingsData['footer_description'] ?? ''); ?></textarea>
                    </div>

                    <h5 class="mb-3 mt-4 text-danger"><i class="fas fa-exclamation-triangle"></i> Advanced Settings</h5>
                    <div class="card bg-light border-0 mb-4 rounded-3 p-3">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">Base URL</label>
                            <input type="text" class="form-control" name="base_url" value="<?php echo htmlspecialchars($settingsData['base_url'] ?? ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\'))); ?>" placeholder="e.g. https://www.example.com">
                            <small class="d-block text-muted mt-1">The root URL of your website (used for images and relative paths).</small>
                        </div>
                        <div class="form-check form-switch fs-5">
                            <input class="form-check-input mt-2" type="checkbox" id="underConstruction" name="under_construction" value="1" <?php echo (isset($settingsData['under_construction']) && $settingsData['under_construction'] == '1') ? 'checked' : ''; ?>>
                            <label class="form-check-label fw-bold text-dark" for="underConstruction">Enable Under Construction Mode</label>
                            <small class="d-block text-muted fs-6 mt-1 mb-3">When turned on, public visitors will see a maintenance page. Only logged in users (Admins & Editors) will be able to view the live website.</small>
                        </div>
                        <div class="form-check form-switch fs-5 border-top pt-3">
                            <input class="form-check-input mt-2" type="checkbox" id="showOfficersHome" name="show_officers_home" value="1" <?php echo (isset($settingsData['show_officers_home']) && $settingsData['show_officers_home'] == '1') ? 'checked' : ''; ?>>
                            <label class="form-check-label fw-bold text-dark" for="showOfficersHome">Show Officers on Home Page</label>
                            <small class="d-block text-muted fs-6 mt-1">When turned on, the staff/officers module will be displayed at the bottom of the public home page.</small>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Settings</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
