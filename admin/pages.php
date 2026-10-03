<?php
require_once 'includes/auth.php';
require_once '../includes/db.php';

if (!hasMenuPermission('pages')) {
    header("Location: index.php");
    exit();
}

// Handle deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM pages WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: pages.php?msg=deleted");
    exit();
}

$stmt = $pdo->query("SELECT * FROM pages ORDER BY id DESC");
$pages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Pages - Gov Admin</title>
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
            <h2>Manage Pages</h2>
            <a href="pages_edit.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Page</a>
        </div>
        
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-success">Page deleted successfully.</div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Slug</th>
                            <th>Last Updated</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($pages as $page): ?>
                        <tr>
                            <td><?php echo $page['id']; ?></td>
                            <td><?php echo htmlspecialchars($page['title']); ?></td>
                            <td><code><?php echo htmlspecialchars($page['slug']); ?></code></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($page['last_updated'])); ?></td>
                            <td class="text-end">
                                <a href="pages_edit.php?id=<?php echo $page['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
                                <a href="pages.php?delete=<?php echo $page['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this page?');"><i class="fas fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (count($pages) === 0): ?>
                        <tr><td colspan="5" class="text-center py-4">No pages found.</td></tr>
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
