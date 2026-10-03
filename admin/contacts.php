<?php
require_once 'includes/auth.php';
requireAdmin();
require_once '../includes/db.php';

if (!hasMenuPermission('contacts')) {
    header("Location: index.php");
    exit();
}

// Handle deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // fetch image to delete
    $stmt = $pdo->prepare("SELECT image_path FROM contacts WHERE id = ?");
    $stmt->execute([$id]);
    $contact = $stmt->fetch();
    
    if ($contact && !empty($contact['image_path'])) {
        $imgPath = '../' . $contact['image_path'];
        if (file_exists($imgPath)) {
            unlink($imgPath);
        }
    }

    $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['successMsg'] = "Contact deleted successfully.";
    header("Location: contacts.php");
    exit();
}

$stmt = $pdo->query("SELECT * FROM contacts ORDER BY order_index ASC, id DESC");
$contacts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Contacts - Gov Admin</title>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-address-book text-primary me-2"></i> Manage Personnel Contacts</h2>
            <a href="contacts_edit.php" class="btn btn-primary shadow-sm"><i class="fas fa-plus"></i> Add New Contact</a>
        </div>
        
        <?php if(isset($_SESSION['successMsg'])): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $_SESSION['successMsg']; unset($_SESSION['successMsg']); ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Image</th>
                            <th>Name</th>
                            <th>Designation</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($contacts as $c): ?>
                        <tr>
                            <td class="ps-4">
                                <?php if(!empty($c['image_path']) && file_exists('../'.$c['image_path'])): ?>
                                    <img src="../<?php echo htmlspecialchars($c['image_path']); ?>" alt="Profile" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #ddd;">
                                <?php else: ?>
                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; border: 2px solid #ddd;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold"><?php echo htmlspecialchars($c['name']); ?></td>
                            <td><?php echo htmlspecialchars($c['designation'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($c['phone'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($c['email'] ?? '-'); ?></td>
                            <td class="text-end pe-4">
                                <a href="contacts_edit.php?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i> Edit</a>
                                <a href="contacts.php?delete=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this contact?');"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(count($contacts) === 0): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No personnel contacts found. Click "Add New Contact" to create one.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
