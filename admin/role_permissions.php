<?php
require_once 'includes/auth.php';
requireSuperAdmin(); // ONLY super_admin can configure menu permissions
require_once '../includes/db.php';

$success = '';
$error = '';

$menuItems = [
    'pages' => ['icon' => 'fa-file-alt', 'label' => 'Manage Pages'],
    'about_settings' => ['icon' => 'fa-building', 'label' => 'Manage About Us'],
    'services' => ['icon' => 'fa-clipboard-list', 'label' => 'Manage Services'],
    'contacts' => ['icon' => 'fa-address-book', 'label' => 'Manage Contacts'],
    'feedback' => ['icon' => 'fa-comments', 'label' => 'Manage Feedback'],
    'news' => ['icon' => 'fa-newspaper', 'label' => 'Manage News'],
    'downloads' => ['icon' => 'fa-download', 'label' => 'Manage Downloads'],
    'download_tags' => ['icon' => 'fa-tags', 'label' => 'File Categories'],
    'menu' => ['icon' => 'fa-bars', 'label' => 'Manage Menus'],
    'sliders' => ['icon' => 'fa-images', 'label' => 'Manage Slider'],
    'settings' => ['icon' => 'fa-cog', 'label' => 'General Settings'],
    'appearance' => ['icon' => 'fa-palette', 'label' => 'Appearance Settings'],
    'users' => ['icon' => 'fa-users', 'label' => 'Manage Users']
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $permissions = [
        'admin' => [],
        'editor' => []
    ];

    foreach (['admin', 'editor'] as $role) {
        foreach ($menuItems as $key => $item) {
            // Check if the checkbox was checked for this role and menu item
            $permissions[$role][$key] = isset($_POST['perms'][$role][$key]) ? true : false;
        }
    }

    $jsonStr = json_encode($permissions);

    // Save to settings table
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = 'menu_permissions'");
    $stmtCheck->execute();
    if ($stmtCheck->fetchColumn() > 0) {
        $stmtUpdate = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'menu_permissions'");
        $stmtUpdate->execute([$jsonStr]);
    } else {
        $stmtInsert = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('menu_permissions', ?)");
        $stmtInsert->execute([$jsonStr]);
    }

    $success = 'Role permissions updated successfully.';
}

// Fetch current permissions
$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'menu_permissions'");
$stmt->execute();
$json = $stmt->fetchColumn();

// Default permissions if none exist
$currentPerms = [
    'admin' => [],
    'editor' => []
];

if ($json) {
    $currentPerms = json_decode($json, true);
} else {
    // Populate defaults initially (admin sees all, editor sees some)
    foreach ($menuItems as $key => $item) {
        $currentPerms['admin'][$key] = true;
        $currentPerms['editor'][$key] = in_array($key, ['pages', 'news', 'downloads', 'services', 'contacts', 'feedback']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Role Permissions - Gov Admin</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .perm-table th, .perm-table td {
            vertical-align: middle;
        }
    </style>
</head>
<body class="bg-light">
    
        <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 px-md-4 py-4">

    <div class="container mt-4 mb-5">
        <h2><i class="fas fa-key text-warning me-2"></i> Role Permissions</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Role Permissions</li>
            </ol>
        </nav>
        
        <p class="text-muted">Configure which sidebar menus are visible to specific user roles. <strong>Super Admins</strong> inherently have full access and bypass these restrictions.</p>

        <?php if ($success): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i> <?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 mt-4">
            <div class="card-body p-0">
                <form action="role_permissions.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    
                    <div class="table-responsive">
                        <table class="table table-hover perm-table mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th class="ps-4">Menu Section</th>
                                    <th class="text-center" style="width: 25%;">Admin Role</th>
                                    <th class="text-center pe-4" style="width: 25%;">Editor Role</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($menuItems as $key => $item): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-secondary">
                                        <i class="fas <?php echo $item['icon']; ?> me-2 text-primary" style="width: 20px;"></i> 
                                        <?php echo $item['label']; ?>
                                    </td>
                                    <td class="text-center text-primary">
                                        <div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input fs-5" type="checkbox" role="switch" name="perms[admin][<?php echo $key; ?>]" value="1" <?php echo (isset($currentPerms['admin'][$key]) && $currentPerms['admin'][$key]) ? 'checked' : ''; ?>>
                                        </div>
                                    </td>
                                    <td class="text-center pe-4 border-start">
                                        <div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input fs-5" type="checkbox" role="switch" name="perms[editor][<?php echo $key; ?>]" value="1" <?php echo (isset($currentPerms['editor'][$key]) && $currentPerms['editor'][$key]) ? 'checked' : ''; ?>>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="p-4 bg-light border-top text-end">
                        <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="fas fa-save me-2"></i> Save Permissions</button>
                    </div>
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
