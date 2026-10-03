<?php
require_once 'includes/auth.php';
requireAdmin();
require_once '../includes/db.php';

if (!hasMenuPermission('menu')) {
    header("Location: index.php");
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$menuData = ['label' => '', 'url' => '', 'menu_location' => 'header_nav', 'order_index' => 0];
$error = '';

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
    $stmt->execute([$id]);
    $menuData = $stmt->fetch();
    if (!$menuData) {
        die("Menu item not found.");
    }
}

// Fetch all pages for easy linking
$pages = $pdo->query("SELECT title, slug FROM pages ORDER BY title ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $label = trim($_POST['label']);
    $url = trim($_POST['url']);
    $menu_location = $_POST['menu_location'];
    $order_index = (int)$_POST['order_index'];

    if (empty($label) || empty($url)) {
        $error = "Label and URL are required.";
    } else {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE menu_items SET label=?, url=?, menu_location=?, order_index=? WHERE id=?");
            $stmt->execute([$label, $url, $menu_location, $order_index, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO menu_items (label, url, menu_location, order_index) VALUES (?, ?, ?, ?)");
            $stmt->execute([$label, $url, $menu_location, $order_index]);
        }
        header("Location: menu.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $id > 0 ? 'Edit' : 'Add'; ?> Menu Item</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function fillPageUrl(selectElement) {
            const slug = selectElement.value;
            if(slug !== "") {
                document.getElementById('menuUrl').value = 'page.php?slug=' + slug;
            }
        }
    </script>
</head>
<body class="bg-light">
        <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 px-md-4 py-4">

    <div class="container mt-4 mb-5" style="max-width: 600px;">
        <h2><?php echo $id > 0 ? 'Edit' : 'Add New'; ?> Menu Item</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <div class="mb-3">
                        <label class="form-label">Menu Label</label>
                        <input type="text" class="form-control" name="label" value="<?php echo htmlspecialchars($menuData['label']); ?>" required placeholder="e.g., About Us">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Link to Internal Page (Optional Shortcut)</label>
                        <select class="form-select" onchange="fillPageUrl(this)">
                            <option value="">-- Select an existing page --</option>
                            <?php foreach($pages as $p): ?>
                                <option value="<?php echo htmlspecialchars($p['slug']); ?>"><?php echo htmlspecialchars($p['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Or type a custom URL below.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Menu Link (URL)</label>
                        <input type="text" class="form-control" id="menuUrl" name="url" value="<?php echo htmlspecialchars($menuData['url']); ?>" required placeholder="e.g., page.php?slug=about-us">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Menu Location</label>
                            <select class="form-select" name="menu_location" required>
                                <option value="header_nav" <?php echo $menuData['menu_location'] == 'header_nav' ? 'selected' : ''; ?>>Header Top Navigation</option>
                                <option value="footer_nav" <?php echo $menuData['menu_location'] == 'footer_nav' ? 'selected' : ''; ?>>Footer Quick Links</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" name="order_index" value="<?php echo $menuData['order_index']; ?>" required>
                            <small class="text-muted">Lowest number appears first.</small>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Menu</button>
                    <a href="menu.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>

            </main>
        </div>
    </div>
</body>
</html>
