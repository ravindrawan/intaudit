<?php
require_once 'includes/auth.php';
require_once '../includes/db.php';

if (!hasMenuPermission('news')) {
    header("Location: index.php");
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$newsData = ['title' => '', 'content' => '', 'image_path' => ''];
$error = '';

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
    $stmt->execute([$id]);
    $newsData = $stmt->fetch();
    if (!$newsData) {
        die("News item not found.");
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $title = trim($_POST['title']);
    // Sanitize rich-text content to prevent Stored XSS while allowing basic formatting
    $allowed_tags = '<p><br><b><i><strong><em><u><h1><h2><h3><h4><h5><h6><ul><ol><li><a><img><table><thead><tbody><tr><td><th><span><div><hr><strike><blockquote>';
    $content = strip_tags($_POST['content'], $allowed_tags);
    $imagePath = $newsData['image_path']; // Default to existing

    if (empty($title)) {
        $error = "Title is required.";
    } else {
        // Handle file upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../assets/uploads/news/';
            if(!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
            $fileName = time() . '_' . basename($_FILES['image']['name']);
            
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp'])) {
                $targetFile = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    // If it's an update and there is an old image, we could delete it, but let's keep it simple
                    $imagePath = 'assets/uploads/news/' . $fileName;
                } else {
                    $error = "Failed to upload image.";
                }
            } else {
                $error = "Invalid image file format. Only JPG, PNG, GIF, WEBP are allowed.";
            }
        }

        if (!$error) {
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE news SET title=?, content=?, image_path=? WHERE id=?");
                $stmt->execute([$title, $content, $imagePath, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO news (title, content, image_path) VALUES (?, ?, ?)");
                $stmt->execute([$title, $content, $imagePath]);
            }
            header("Location: news.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $id > 0 ? 'Edit' : 'Add'; ?> News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
</head>
<body class="bg-light">
        <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 px-md-4 py-4">

    <div class="container mt-4 mb-5">
        <h2><?php echo $id > 0 ? 'Edit' : 'Add'; ?> News Article</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <div class="mb-3">
                        <label class="form-label">Article Title</label>
                        <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($newsData['title']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Featured Image</label>
                        <input type="file" class="form-control" name="image" accept="image/*">
                        <?php if(!empty($newsData['image_path'])): ?>
                            <div class="mt-2">
                                <img src="../<?php echo htmlspecialchars($newsData['image_path']); ?>" alt="Current Image" style="max-height: 100px; border-radius: 5px;">
                                <small class="text-muted d-block">Current featured image</small>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Article Content</label>
                        <textarea id="summernote" name="content"><?php echo htmlspecialchars($newsData['content']); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Article</button>
                    <a href="news.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <script>
      $('#summernote').summernote({
        placeholder: 'Write your news article here...',
        height: 300,
        toolbar: [
          ['style', ['style']],
          ['font', ['bold', 'underline', 'clear']],
          ['color', ['color']],
          ['para', ['ul', 'ol', 'paragraph']],
          ['table', ['table']],
          ['insert', ['link', 'picture', 'video']],
          ['view', ['fullscreen', 'codeview', 'help']]
        ]
      });
    </script>

            </main>
        </div>
    </div>
</body>
</html>
