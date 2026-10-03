<?php
require_once 'includes/auth.php';
requireAdmin();
require_once '../includes/db.php';

$error = '';
$success = '';
$isEdit = false;
$officer = [
    'section_name' => '',
    'name' => '',
    'designation' => '',
    'photo_path' => '',
    'order_index' => 0
];

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $isEdit = true;
    $stmt = $pdo->prepare("SELECT * FROM officers WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $officer = $stmt->fetch();
    
    if (!$officer) {
        die("Officer not found.");
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    
    $section_name = trim($_POST['section_name']);
    $name = trim($_POST['name']);
    $designation = trim($_POST['designation']);
    $order_index = (int)($_POST['order_index'] ?? 0);
    $photo_path = $officer['photo_path'];

    if (empty($section_name) || empty($name) || empty($designation)) {
        $error = "Section Name, Name, and Designation are required.";
    } else {
        // Handle photo upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../assets/uploads/officers/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['photo']['name']);
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp'])) {
                $targetFile = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
                    // Delete old photo if it exists
                    if ($isEdit && !empty($photo_path) && file_exists('../' . $photo_path)) {
                        unlink('../' . $photo_path);
                    }
                    $photo_path = 'assets/uploads/officers/' . $fileName;
                } else {
                    $error = "Failed to upload photo.";
                }
            } else {
                $error = "Invalid photo format. Only JPG, PNG, and WEBP are allowed.";
            }
        }
    }

    if (!$error) {
        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE officers SET section_name=?, name=?, designation=?, photo_path=?, order_index=? WHERE id=?");
            $stmt->execute([$section_name, $name, $designation, $photo_path, $order_index, $_GET['id']]);
            $_SESSION['successMsg'] = "Officer updated successfully.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO officers (section_name, name, designation, photo_path, order_index) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$section_name, $name, $designation, $photo_path, $order_index]);
            $_SESSION['successMsg'] = "Officer added successfully.";
        }
        header("Location: officers.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Edit Officer' : 'Add Officer'; ?> - Gov Admin</title>
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
        <div class="card shadow-sm border-0 border-top border-primary border-3 w-75 mx-auto">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h4 class="mb-0 fw-bold"><i class="fas <?php echo $isEdit ? 'fa-user-edit' : 'fa-user-plus'; ?> text-primary me-2"></i> <?php echo $isEdit ? 'Edit Officer' : 'Add New Officer'; ?></h4>
                <a href="officers.php" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to List</a>
            </div>
            <div class="card-body p-4">
                
                <?php if($error): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($officer['name']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Designation (Thanathura) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="designation" value="<?php echo htmlspecialchars($officer['designation']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Section (Anshaya) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="section_name" value="<?php echo htmlspecialchars($officer['section_name']); ?>" required placeholder="e.g. Administration, Finance">
                                <small class="text-muted d-block mt-1">Officers with the exact same section name will be grouped together.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Order Index</label>
                                <input type="number" class="form-control" name="order_index" value="<?php echo (int)($officer['order_index']); ?>">
                                <small class="text-muted d-block mt-1">Lower numbers appear first within their section.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3 text-center">
                                <label class="form-label fw-bold d-block">Photo</label>
                                <?php if(!empty($officer['photo_path']) && file_exists('../' . $officer['photo_path'])): ?>
                                    <img src="../<?php echo htmlspecialchars($officer['photo_path']); ?>" alt="Current Photo" class="img-thumbnail rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white mx-auto mb-3" style="width: 150px; height: 150px; font-size: 3rem;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control form-control-sm" name="photo" accept="image/png, image/jpeg, image/webp">
                                <small class="text-muted d-block mt-2">Recommended: Square image (min 300x300px)</small>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    <div class="text-end">
                        <a href="officers.php" class="btn btn-light shadow-sm me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm"><i class="fas fa-save me-2"></i> <?php echo $isEdit ? 'Update Officer' : 'Save Officer'; ?></button>
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
