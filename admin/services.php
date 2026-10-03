<?php
require_once 'includes/auth.php';
requireAdmin();
require_once '../includes/db.php';

if (!hasMenuPermission('services')) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_home_visibility'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    
    $showServicesHome = isset($_POST['show_services_home']) ? '1' : '0';
    
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = 'show_services_home'");
    $stmtCheck->execute();
    if ($stmtCheck->fetchColumn() > 0) {
        $stmtUpdate = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'show_services_home'");
        $stmtUpdate->execute([$showServicesHome]);
    } else {
        $stmtInsert = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('show_services_home', ?)");
        $stmtInsert->execute([$showServicesHome]);
    }
    
    header("Location: services.php?msg=settings_updated");
    exit();
}

$stmtConfig = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'show_services_home'");
$stmtConfig->execute();
$showServicesHome = $stmtConfig->fetchColumn();
if ($showServicesHome === false) $showServicesHome = '1';

// Handle deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    // get image path to delete it
    $stmt = $pdo->prepare("SELECT image_path FROM services WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $service = $stmt->fetch();
    
    if($service && !empty($service['image_path']) && file_exists('../'.$service['image_path'])){
        @unlink('../'.$service['image_path']);
    }
    
    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: services.php?msg=deleted");
    exit();
}

$stmt = $pdo->query("SELECT * FROM services ORDER BY created_at DESC");
$services = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Services - Gov Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    
        <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 px-md-4 py-4">

    <div class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage Institutional Services</h2>
            <div class="d-flex align-items-center gap-3">
                <form action="services.php" method="POST" class="d-flex align-items-center bg-white p-2 rounded shadow-sm border m-0" onchange="this.submit()">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="update_home_visibility" value="1">
                    <div class="form-check form-switch m-0 d-flex align-items-center pe-2">
                        <input class="form-check-input mt-0 me-2 fs-5" type="checkbox" id="showServicesHome" name="show_services_home" value="1" <?php echo $showServicesHome == '1' ? 'checked' : ''; ?> style="cursor: pointer;">
                        <label class="form-check-label fw-bold small text-dark" for="showServicesHome" style="cursor: pointer; padding-top: 2px;">Show on Home Page</label>
                    </div>
                </form>
                <a href="services_edit.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Service</a>
            </div>
        </div>
        
        <?php if (isset($_GET['msg'])): ?>
            <?php if ($_GET['msg'] == 'deleted'): ?>
                <div class="alert alert-success mt-3">Service deleted successfully.</div>
            <?php elseif ($_GET['msg'] == 'saved'): ?>
                <div class="alert alert-success mt-3">Service saved successfully.</div>
            <?php elseif ($_GET['msg'] == 'settings_updated'): ?>
                <div class="alert alert-success mt-3"><i class="fas fa-check-circle"></i> Service visibility settings updated.</div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th width="80">Image</th>
                                <th>Service Title</th>
                                <th>Added Date</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($services as $srv): ?>
                            <tr>
                                <td>
                                    <?php if(!empty($srv['image_path'])): ?>
                                        <img src="../<?php echo htmlspecialchars($srv['image_path']); ?>" alt="Service" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-secondary text-white d-flex align-items-center justify-content-center rounded" style="width: 60px; height: 60px;">
                                            <i class="fas fa-cogs"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <h6 class="mb-0 fw-bold text-primary"><?php echo htmlspecialchars($srv['title']); ?></h6>
                                </td>
                                <td><small class="text-muted"><?php echo date('Y-m-d', strtotime($srv['created_at'])); ?></small></td>
                                <td class="text-end">
                                    <a href="services_edit.php?id=<?php echo $srv['id']; ?>" class="btn btn-sm btn-outline-primary shadow-sm"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="services.php?delete=<?php echo $srv['id']; ?>" class="btn btn-sm btn-outline-danger shadow-sm ms-1" onclick="return confirm('Delete this service permanently?');"><i class="fas fa-trash"></i> Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (count($services) === 0): ?>
                            <tr><td colspan="4" class="text-center py-5 text-muted">No services added yet. Click "Add New Service" to get started.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

            </main>
        </div>
    </div>
</body>
</html>
