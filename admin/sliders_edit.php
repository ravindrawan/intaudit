<?php
require_once 'includes/auth.php';
requireAdmin();
require_once '../includes/db.php';

if (!hasMenuPermission('sliders')) {
    header("Location: index.php");
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$sliderData = ['title' => '', 'subtitle' => '', 'image_path' => '', 'order_index' => 0];
$error = '';

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM sliders WHERE id = ?");
    $stmt->execute([$id]);
    $sliderData = $stmt->fetch();
    if (!$sliderData) {
        die("Slider image not found.");
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $title = trim($_POST['title']);
    $subtitle = trim($_POST['subtitle']);
    $order_index = (int)$_POST['order_index'];
    $imagePath = $sliderData['image_path']; 

    // Handle new file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/uploads/sliders/';
        if(!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
        
        $fileName = time() . '_' . basename($_FILES['image']['name']);
        
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp'])) {
            $targetFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $imagePath = 'assets/uploads/sliders/' . $fileName;
            } else {
                $error = "Failed to upload the image.";
            }
        } else {
            $error = "Invalid image file format. Only JPG, PNG, GIF, WEBP are allowed.";
        }
    }

    if(empty($imagePath)) {
        $error = "An image file is required.";
    }

    if (!$error) {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE sliders SET title=?, subtitle=?, order_index=?, image_path=? WHERE id=?");
            $stmt->execute([$title, $subtitle, $order_index, $imagePath, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO sliders (title, subtitle, order_index, image_path) VALUES (?, ?, ?, ?)");
            $stmt->execute([$title, $subtitle, $order_index, $imagePath]);
        }
        header("Location: sliders.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $id > 0 ? 'Edit' : 'Add New'; ?> Slider Image</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
        <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 px-md-4 py-4">

    <div class="container mt-4 mb-5" style="max-width: 600px;">
        <h2><?php echo $id > 0 ? 'Edit' : 'Add New'; ?> Slider Image</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <div class="mb-3">
                        <label class="form-label">Featured Background Image</label>
                        <input type="file" class="form-control" name="image" accept="image/*" <?php echo empty($sliderData['image_path']) ? 'required' : ''; ?>>
                        <small class="text-muted">Recommended resolution: 1920x600 pixels.</small>
                        <?php if(!empty($sliderData['image_path'])): ?>
                            <div class="mt-2">
                                <img src="../<?php echo htmlspecialchars($sliderData['image_path']); ?>" alt="Current Image" style="max-height: 120px; border-radius: 5px;">
                                <small class="text-muted d-block">Current background image</small>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Main Heading (Optional Text Overlay)</label>
                        <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($sliderData['title']); ?>" placeholder="e.g., Welcome to the Portal">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Sub-Title / Description (Optional)</label>
                        <input type="text" class="form-control" name="subtitle" value="<?php echo htmlspecialchars($sliderData['subtitle']); ?>" placeholder="e.g., Serving the Nation setup">
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Display Order Index</label>
                        <input type="number" class="form-control" name="order_index" value="<?php echo $sliderData['order_index']; ?>" required>
                        <small class="text-muted">Images with a lower number will display first.</small>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Slider Data</button>
                    <a href="sliders.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>

            </main>
        </div>
    </div>
</body>
</html>
