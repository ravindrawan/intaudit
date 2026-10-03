<?php
require_once 'includes/auth.php';
requireAdmin();
require_once '../includes/db.php';

if (!hasMenuPermission('services')) {
    header("Location: index.php");
    exit();
}

$error = '';
$isEdit = false;
$service = [
    'title' => '',
    'description' => '',
    'image_path' => '',
    'show_image' => '1'
];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
    $isEdit = true;
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$id]);
    $service = $stmt->fetch();
    if (!$service) {
        header("Location: services.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $title = trim($_POST['title']);
    // Sanitize rich-text content to prevent Stored XSS while allowing basic formatting
    $allowed_tags = '<p><br><b><i><strong><em><u><h1><h2><h3><h4><h5><h6><ul><ol><li><a><img><table><thead><tbody><tr><td><th><span><div><hr><strike><blockquote>';
    $description = strip_tags($_POST['description'], $allowed_tags);
    
    if (empty($title)) {
        $error = "Service Title is required.";
    } else {
        $imagePath = $isEdit ? $service['image_path'] : '';
        $showImage = isset($_POST['show_image']) ? 1 : 0;
        
        // Handle Featured Image Upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../assets/uploads/services/';
            if(!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
            
            $fileName = time() . '_' . basename($_FILES['image']['name']);
            // Add a simple check for valid image extensions
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $targetFile = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    // Delete old image if uploading a new one during edit
                    if ($isEdit && !empty($imagePath) && file_exists('../'.$imagePath)) {
                        @unlink('../'.$imagePath);
                    }
                    $imagePath = 'assets/uploads/services/' . $fileName;
                } else {
                    $error = "Failed to upload the image.";
                }
            } else {
                $error = "Invalid image file format. Only JPG, PNG, GIF, WEBP are allowed.";
            }
        }
        
        if (empty($error)) {
            if ($isEdit) {
                $stmt = $pdo->prepare("UPDATE services SET title = ?, description = ?, image_path = ?, show_image = ? WHERE id = ?");
                $stmt->execute([$title, $description, $imagePath, $showImage, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO services (title, description, image_path, show_image) VALUES (?, ?, ?, ?)");
                $stmt->execute([$title, $description, $imagePath, $showImage]);
            }
            header("Location: services.php?msg=saved");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $isEdit ? 'Edit' : 'Add'; ?> Service - Gov Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Summernote CSS for WYSIWYG -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    
        <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 px-md-4 py-4">

    <div class="container mt-4 mb-5">
        <h2><?php echo $isEdit ? 'Edit Official Service' : 'Add New Service'; ?></h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 mt-3">
            <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-4">
                                <label class="form-label fw-bold">Service Title</label>
                                <input type="text" class="form-control form-control-lg" name="title" required value="<?php echo htmlspecialchars($service['title']); ?>" placeholder="e.g., Certificate Issuance">
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">Service Detailed Description</label>
                                <textarea id="summernote" name="description"><?php echo htmlspecialchars($service['description']); ?></textarea>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card bg-light border">
                                <div class="card-body">
                                    <h6 class="card-title fw-bold border-bottom pb-2 mb-3"><i class="fas fa-image text-primary"></i> Featured Image</h6>
                                    
                                    <?php if($isEdit && !empty($service['image_path'])): ?>
                                        <div class="mb-3 text-center">
                                            <p class="mb-1 small text-muted">Current Image:</p>
                                            <img src="../<?php echo htmlspecialchars($service['image_path']); ?>" alt="Current Image" class="img-fluid img-thumbnail" style="max-height: 200px;">
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Upload <?php echo $isEdit && !empty($service['image_path']) ? 'New ' : ''; ?>Image</label>
                                        <input class="form-control form-control-sm" type="file" name="image" accept="image/*">
                                        <div class="form-text small">Recommended size: 800x600px</div>
                                    </div>
                                    <div class="form-check form-switch mb-3 border p-2 rounded bg-white shadow-sm">
                                        <input class="form-check-input ms-1 mt-2" type="checkbox" id="showImageToggle" name="show_image" value="1" <?php echo (!isset($service['show_image']) || $service['show_image'] == 1) ? 'checked' : ''; ?>>
                                        <label class="form-check-label ms-2 small fw-bold" for="showImageToggle">Display Featured Image</label>
                                        <div class="small text-muted ms-2 mt-1">If enabled, the image will appear on the services listing and home page.</div>
                                    </div>
                                    
                                    <hr>
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-save"></i> <?php echo $isEdit ? 'Update Service' : 'Publish Service'; ?></button>
                                        <a href="services.php" class="btn btn-outline-secondary">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#summernote').summernote({
                placeholder: 'Enter the service details here...',
                tabsize: 2,
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });
    </script>

            </main>
        </div>
    </div>
</body>
</html>
