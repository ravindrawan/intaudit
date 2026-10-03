<?php
require_once 'includes/auth.php';
require_once '../includes/db.php';

if (!hasMenuPermission('downloads')) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $title = trim($_POST['title']);
    $tagId = !empty($_POST['tag_id']) ? (int)$_POST['tag_id'] : null;

    if (empty($title)) {
        $error = "File Title is required.";
    } elseif (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $error = "Please select a valid file to upload.";
    } else {
        $uploadDir = '../assets/uploads/downloads/';
        if(!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
        
        $fileName = time() . '_' . basename($_FILES['file']['name']);
        // Sanitize filename spaces
        $fileName = str_replace(' ', '_', $fileName);
        
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar', 'txt', 'csv'];
        if (!in_array($ext, $allowed)) {
            $error = "Invalid file type. Only standard document formats are allowed.";
        } else {
            $targetFile = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
                $filePath = 'assets/uploads/downloads/' . $fileName;
                
                $stmt = $pdo->prepare("INSERT INTO downloads (title, file_path, tag_id) VALUES (?, ?, ?)");
                $stmt->execute([$title, $filePath, $tagId]);
                
                header("Location: downloads.php");
                exit();
            } else {
                $error = "Failed to upload file to the server.";
            }
        }
    }
}

// Fetch all tags for the dropdown
$stmtTags = $pdo->query("SELECT * FROM download_tags ORDER BY tag_name ASC");
$allTags = $stmtTags->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload File - Gov Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
        <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 px-md-4 py-4">

    <div class="container mt-4 mb-5">
        <h2>Upload New File</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <div class="row">
                        <div class="col-md-7 mb-3">
                            <label class="form-label">File Title / Description</label>
                            <input type="text" class="form-control" name="title" required placeholder="e.g., Annual Report 2025">
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label">File Category (Tag)</label>
                            <select class="form-select" name="tag_id">
                                <option value="">-- No Category (General) --</option>
                                <?php foreach($allTags as $tag): ?>
                                    <option value="<?php echo $tag['id']; ?>"><?php echo htmlspecialchars($tag['tag_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Select File (PDF, DOCX, ZIP, etc.)</label>
                        <input type="file" class="form-control" name="file" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Upload File</button>
                    <a href="downloads.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>

            </main>
        </div>
    </div>
</body>
</html>
