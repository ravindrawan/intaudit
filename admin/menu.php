<?php
require_once 'includes/auth.php';
requireAdmin(); // Only admin manages menus
require_once '../includes/db.php';

if (!hasMenuPermission('menu')) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: menu.php?msg=deleted");
    exit();
}

$stmt = $pdo->query("SELECT menu_location, id, label, url, order_index FROM menu_items ORDER BY menu_location, order_index ASC");
$menus = $stmt->fetchAll(PDO::FETCH_GROUP);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Menus - Gov Admin</title>
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
            <h2>Manage Navigation Menus</h2>
            <a href="menu_edit.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Menu Item</a>
        </div>
        
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-success">Menu item deleted successfully.</div>
        <?php endif; ?>

        <div class="row">
            <!-- Header Menu -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white">Header Navigation Items</div>
                    <ul class="list-group list-group-flush">
                        <?php if (isset($menus['header_nav'])): ?>
                            <?php foreach($menus['header_nav'] as $item): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>
                                        <strong><?php echo htmlspecialchars($item['label']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($item['url']); ?> (Order: <?php echo $item['order_index']; ?>)</small>
                                    </span>
                                    <span>
                                        <a href="menu_edit.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                        <a href="menu.php?delete=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this menu item?');"><i class="fas fa-trash"></i></a>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-center text-muted py-3">No items in header menu.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Footer Menu -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-secondary text-white">Footer Navigation Items</div>
                    <ul class="list-group list-group-flush">
                        <?php if (isset($menus['footer_nav'])): ?>
                            <?php foreach($menus['footer_nav'] as $item): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>
                                        <strong><?php echo htmlspecialchars($item['label']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($item['url']); ?> (Order: <?php echo $item['order_index']; ?>)</small>
                                    </span>
                                    <span>
                                        <a href="menu_edit.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                        <a href="menu.php?delete=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this menu item?');"><i class="fas fa-trash"></i></a>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-center text-muted py-3">No items in footer menu.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

            </main>
        </div>
    </div>
</body>
</html>
