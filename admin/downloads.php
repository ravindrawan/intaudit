<?php
require_once 'includes/auth.php';
require_once '../includes/db.php';

if (!hasMenuPermission('downloads')) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("SELECT file_path FROM downloads WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $dl = $stmt->fetch();
    if($dl && !empty($dl['file_path']) && file_exists('../'.$dl['file_path'])){
        @unlink('../'.$dl['file_path']);
    }
    
    $stmt = $pdo->prepare("DELETE FROM downloads WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: downloads.php?msg=deleted");
    exit();
}

$stmt = $pdo->query("SELECT d.*, t.tag_name FROM downloads d LEFT JOIN download_tags t ON d.tag_id = t.id ORDER BY d.uploaded_at DESC");
$downloads = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Downloads - Gov Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    
        <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 px-md-4 py-4">

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Manage Downloads</h2>
            <a href="downloads_edit.php" class="btn btn-primary"><i class="fas fa-upload"></i> Upload File</a>
        </div>
        
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-success">File deleted successfully.</div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>File</th>
                            <th>Date Uploaded</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($downloads as $file): ?>
                        <tr>
                            <td><?php echo $file['id']; ?></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($file['title']); ?></td>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($file['tag_name'] ?? 'General'); ?></span></td>
                            <td><a href="../<?php echo htmlspecialchars($file['file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i> View File</a></td>
                            <td><?php echo date('Y-m-d', strtotime($file['uploaded_at'])); ?></td>
                            <td class="text-end">
                                <a href="downloads.php?delete=<?php echo $file['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this file permanently?');"><i class="fas fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (count($downloads) === 0): ?>
                        <tr><td colspan="5" class="text-center py-4">No files uploaded yet.</td></tr>
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
