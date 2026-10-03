<?php
require_once 'includes/auth.php';
require_once '../includes/db.php';

if (!hasMenuPermission('news')) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    // Optionally remove image file as well
    $stmt = $pdo->prepare("SELECT image_path FROM news WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $news = $stmt->fetch();
    if($news && !empty($news['image_path']) && file_exists('../'.$news['image_path'])){
        @unlink('../'.$news['image_path']);
    }
    
    $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: news.php?msg=deleted");
    exit();
}

$stmt = $pdo->query("SELECT * FROM news ORDER BY created_at DESC");
$newsItems = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage News - Gov Admin</title>
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
            <h2>Manage News & Posts</h2>
            <a href="news_edit.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add News</a>
        </div>
        
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-success">News deleted successfully.</div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Date Posted</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($newsItems as $news): ?>
                        <tr>
                            <td>
                                <?php if(!empty($news['image_path'])): ?>
                                <img src="../<?php echo htmlspecialchars($news['image_path']); ?>" alt="News img" style="height:40px; border-radius:4px;">
                                <?php else: ?>
                                <span class="text-muted">No Image</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($news['title']); ?></td>
                            <td><?php echo date('Y-m-d', strtotime($news['created_at'])); ?></td>
                            <td class="text-end">
                                <a href="news_edit.php?id=<?php echo $news['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                <a href="news.php?delete=<?php echo $news['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this news?');"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (count($newsItems) === 0): ?>
                        <tr><td colspan="4" class="text-center py-4">No news found.</td></tr>
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
