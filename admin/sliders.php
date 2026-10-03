<?php
require_once 'includes/auth.php';
requireAdmin(); // Only admin manages sliders
require_once '../includes/db.php';

if (!hasMenuPermission('sliders')) {
    header("Location: index.php");
    exit();
}

// Handle image deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("SELECT image_path FROM sliders WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $slider = $stmt->fetch();
    if($slider && !empty($slider['image_path']) && file_exists('../'.$slider['image_path'])){
        @unlink('../'.$slider['image_path']);
    }
    
    $stmt = $pdo->prepare("DELETE FROM sliders WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: sliders.php?msg=deleted");
    exit();
}

$stmt = $pdo->query("SELECT * FROM sliders ORDER BY order_index ASC");
$sliders = $stmt->fetchAll();

// Fetch settings
$stmtSet = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('slider_animation_type', 'slider_animation_speed')");
$settings = $stmtSet->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Sliders - Gov Admin</title>
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
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Manage Home Page Slider</h2>
            <a href="sliders_edit.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Image</a>
        </div>
        
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-success">Slider image deleted successfully.</div>
        <?php endif; ?>

        <!-- Animation Settings Panel -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-secondary text-white fw-bold">
                <i class="fas fa-cogs"></i> Global Animation Settings
            </div>
            <div class="card-body">
                <form action="sliders_settings.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <div class="row align-items-end">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label">Animation Type</label>
                            <select name="slider_animation_type" class="form-select" required>
                                <option value="slide" <?php echo ($settings['slider_animation_type'] ?? '') == 'slide' ? 'selected' : ''; ?>>Sliding (Left to Right)</option>
                                <option value="fade" <?php echo ($settings['slider_animation_type'] ?? '') == 'fade' ? 'selected' : ''; ?>>Cross-Fade</option>
                            </select>
                        </div>
                        <div class="col-md-5 mb-3 mb-md-0">
                            <label class="form-label">Animation Speed (Auto-play interval in ms)</label>
                            <input type="number" class="form-control" name="slider_animation_speed" value="<?php echo htmlspecialchars($settings['slider_animation_speed'] ?? '3000'); ?>" required placeholder="e.g. 5000">
                            <small class="text-muted">1000ms = 1 second. Put 0 to disable auto-play.</small>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-success w-100"><i class="fas fa-save"></i> Save Settings</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm flex-fill">
            <div class="card-body p-0">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 15%">Image</th>
                            <th>Display Title</th>
                            <th>Display Subtitle</th>
                            <th>Order</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($sliders as $slide): ?>
                        <tr>
                            <td>
                                <img src="../<?php echo htmlspecialchars($slide['image_path']); ?>" alt="Slider Image" class="img-thumbnail" style="max-height: 80px; object-fit: cover;">
                            </td>
                            <td><?php echo htmlspecialchars($slide['title']) ?: '<span class="text-muted">None</span>'; ?></td>
                            <td><?php echo htmlspecialchars($slide['subtitle']) ?: '<span class="text-muted">None</span>'; ?></td>
                            <td><?php echo $slide['order_index']; ?></td>
                            <td class="text-end">
                                <a href="sliders_edit.php?id=<?php echo $slide['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
                                <a href="sliders.php?delete=<?php echo $slide['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this slider image?');"><i class="fas fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (count($sliders) === 0): ?>
                        <tr><td colspan="5" class="text-center py-4">No slider images uploaded. The slider will be hidden.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

            </main>
        </div>
    </div>
</body>
</html>
