<?php
require_once 'includes/auth.php';
requireAdmin();
require_once '../includes/db.php';

if (!hasMenuPermission('contacts')) {
    header("Location: index.php");
    exit();
}

$error = '';
$contact = [
    'name' => '',
    'designation' => '',
    'phone' => '',
    'fax' => '',
    'email' => '',
    'image_path' => '',
    'order_index' => 0
];
$isEdit = false;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $isEdit = true;
    $stmt = $pdo->prepare("SELECT * FROM contacts WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $contact = $stmt->fetch();
    
    if (!$contact) {
        die("Contact not found.");
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $name = trim($_POST['name']);
    $designation = trim($_POST['designation'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $fax = trim($_POST['fax'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $orderIndex = (int)$_POST['order_index'];

    if (empty($name)) {
        $error = "Name is required.";
    } else {
        $imagePath = $contact['image_path'] ?? '';
        
        // Handle file upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $targetDir = "../assets/uploads/images/";
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
            $targetFilePath = $targetDir . $fileName;
            
            $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
            if (in_array($fileType, ['jpg', 'png', 'jpeg', 'gif', 'webp'])) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
                    // delete old file if exists
                    if (!empty($contact['image_path']) && file_exists('../' . $contact['image_path'])) {
                        unlink('../' . $contact['image_path']);
                    }
                    // Save relative path for DB
                    $imagePath = 'assets/uploads/images/' . $fileName;
                } else {
                    $error = "Error uploading image.";
                }
            } else {
                $error = "Sorry, only JPG, JPEG, PNG, WEBP & GIF files are allowed.";
            }
        }
        
        if (empty($error)) {
            if ($isEdit) {
                $stmt = $pdo->prepare("UPDATE contacts SET name=?, designation=?, phone=?, fax=?, email=?, image_path=?, order_index=? WHERE id=?");
                $stmt->execute([$name, $designation, $phone, $fax, $email, $imagePath, $orderIndex, $_GET['id']]);
                $_SESSION['successMsg'] = "Contact updated successfully.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO contacts (name, designation, phone, fax, email, image_path, order_index) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $designation, $phone, $fax, $email, $imagePath, $orderIndex]);
                $_SESSION['successMsg'] = "Contact created successfully.";
            }
            header("Location: contacts.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Edit Contact' : 'Add Contact'; ?> - Gov Admin</title>
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
                <h4 class="mb-0 fw-bold"><i class="fas <?php echo $isEdit ? 'fa-edit' : 'fa-plus-circle'; ?> text-primary me-2"></i> <?php echo $isEdit ? 'Edit Personnel Contact' : 'Add Personnel Contact'; ?></h4>
                <a href="contacts.php" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to List</a>
            </div>
            <div class="card-body p-4">
                
                <?php if($error): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?></div>
                <?php endif; ?>
                
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($contact['name']); ?>" required placeholder="e.g. Mr. John Doe">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Designation / Role Title</label>
                            <input type="text" class="form-control" name="designation" value="<?php echo htmlspecialchars($contact['designation']); ?>" placeholder="e.g. Director General">
                        </div>
                    </div>

                    <div class="row bg-light p-3 rounded mb-4 mt-2">
                        <h5 class="fw-bold mb-3 fs-6"><i class="fas fa-address-card text-muted me-2"></i> Contact Info</h5>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Phone Number</label>
                            <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($contact['phone']); ?>" placeholder="e.g. +94 11 234 5678">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Fax Number</label>
                            <input type="text" class="form-control" name="fax" value="<?php echo htmlspecialchars($contact['fax']); ?>" placeholder="e.g. +94 11 234 5679">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($contact['email']); ?>" placeholder="e.g. director@institute.gov.lk">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold">Profile Picture</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                            <?php if($isEdit && !empty($contact['image_path']) && file_exists('../'.$contact['image_path'])): ?>
                                <div class="mt-3">
                                    <p class="mb-1 text-muted small">Current Picture:</p>
                                    <img src="../<?php echo $contact['image_path']; ?>" alt="Current Image" class="img-thumbnail" style="max-height: 120px; object-fit: cover; border-radius: 50%;">
                                </div>
                            <?php endif; ?>
                            <small class="text-muted d-block mt-2">Recommended: Square format (e.g. 300x300 pixels).</small>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold">Display Order</label>
                            <input type="number" class="form-control" name="order_index" value="<?php echo (int)$contact['order_index']; ?>" min="0">
                            <small class="text-muted d-block mt-1">Lower numbers will display first in the list.</small>
                        </div>
                    </div>
                    
                    <hr>
                    <div class="text-end">
                        <a href="contacts.php" class="btn btn-light shadow-sm me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm"><i class="fas fa-save me-2"></i> <?php echo $isEdit ? 'Update Contact' : 'Save Contact'; ?></button>
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
