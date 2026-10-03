<?php
require_once 'includes/auth.php';
requireAdmin();
require_once '../includes/db.php';

if (!hasMenuPermission('download_tags')) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

// Handle Tag Creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $tagName = trim($_POST['tag_name']);
    if (empty($tagName)) {
        $error = "Tag name cannot be empty.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO download_tags (tag_name) VALUES (?)");
            $stmt->execute([$tagName]);
            $success = "Tag added successfully.";
        } catch(PDOException $e) {
            $error = "Error adding tag. It might already exist.";
        }
    }
}

// Handle Tag Deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM download_tags WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: download_tags.php?msg=deleted");
    exit();
}

$stmt = $pdo->query("SELECT * FROM download_tags ORDER BY tag_name ASC");
$tags = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Download Tags - Gov Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    
        <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 px-md-4 py-4">

    <div class="container mt-4 mb-5" style="max-width: 800px;">
        <h2>Manage File Categories (Tags)</h2>
        <p class="text-muted">Create tags to organize the files available for public download.</p>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-success">Tag deleted successfully.</div>
        <?php endif; ?>

        <!-- Add Tag Form -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form action="" method="POST" class="d-flex w-100 align-items-center">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="action" value="add">
                    <input type="text" class="form-control me-2" name="tag_name" required placeholder="Enter new tag name (e.g., Reports, Circulars)">
                    <button type="submit" class="btn btn-primary" style="white-space: nowrap;"><i class="fas fa-plus"></i> Add Tag</button>
                </form>
            </div>
        </div>

        <!-- Tag List -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Tag Category Name</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($tags as $tag): ?>
                        <tr>
                            <td><?php echo $tag['id']; ?></td>
                            <td class="fw-bold text-primary"><?php echo htmlspecialchars($tag['tag_name']); ?></td>
                            <td class="text-end">
                                <a href="download_tags.php?delete=<?php echo $tag['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this tag? Files using this tag will become uncategorized.');"><i class="fas fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(count($tags) === 0): ?>
                        <tr><td colspan="3" class="text-center py-4">No tags created yet.</td></tr>
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
