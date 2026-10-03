<?php
require_once 'includes/auth.php';
require_once '../includes/db.php';

if (!hasMenuPermission('feedback')) {
    header("Location: index.php");
    exit();
}

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    header("Location: feedback.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM feedback WHERE id = ?");
$stmt->execute([$id]);
$feedback = $stmt->fetch();

if (!$feedback) {
    die("Feedback not found.");
}

// Mark as read if it was unread
if (!$feedback['is_read']) {
    $stmtUpdate = $pdo->prepare("UPDATE feedback SET is_read = 1 WHERE id = ?");
    $stmtUpdate->execute([$id]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Feedback #<?php echo $feedback['id']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .print-container { max-width: 800px; margin: 40px auto; padding: 20px; }
        .print-header { border-bottom: 2px solid #343a40; padding-bottom: 20px; margin-bottom: 30px; }
        .label { font-weight: bold; color: #6c757d; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .value { font-size: 1.1rem; margin-bottom: 20px; color: #212529; }
        .message-box { background-color: #f8f9fa; border: 1px solid #dee2e6; padding: 20px; border-radius: 5px; min-height: 200px; white-space: pre-wrap; font-size: 1.05rem; line-height: 1.6;}
        
        @media print {
            .no-print { display: none !important; }
            .print-container { margin: 0; max-width: 100%; border: none; box-shadow: none; }
            body { background-color: #fff; }
        }
    </style>
</head>
<body class="bg-light">

<div class="container print-container bg-white shadow-sm border rounded">
    
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <a href="feedback.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to List</a>
        <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Print Feedback</button>
    </div>

    <div class="print-header d-flex justify-content-between align-items-end">
        <div>
            <h2 class="mb-1 text-dark">Customer Feedback</h2>
            <div class="text-muted">ID: #<?php echo str_pad($feedback['id'], 5, '0', STR_PAD_LEFT); ?></div>
        </div>
        <div class="text-end">
            <div class="label mb-1">Received On</div>
            <div class="fw-bold fs-5 text-dark"><?php echo date('F j, Y, g:i a', strtotime($feedback['created_at'])); ?></div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="label">Customer Name</div>
            <div class="value"><?php echo htmlspecialchars($feedback['name']); ?></div>
        </div>
        <div class="col-md-6 text-md-end">
            <div class="label">Contact Email</div>
            <div class="value"><a href="mailto:<?php echo htmlspecialchars($feedback['email']); ?>" class="text-decoration-none"><?php echo htmlspecialchars($feedback['email']); ?></a></div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="label">Phone Number</div>
            <div class="value"><?php echo !empty($feedback['phone']) ? htmlspecialchars($feedback['phone']) : '<span class="text-muted fst-italic">Not provided</span>'; ?></div>
        </div>
    </div>

    <hr class="mb-4">

    <div class="mb-4">
        <div class="label fs-5 mb-2 text-dark">Subject / Title</div>
        <div class="value fw-bold fs-4"><?php echo htmlspecialchars($feedback['title']); ?></div>
    </div>

    <div class="mb-4">
        <div class="label fs-5 mb-3 text-dark">Message / Feedback</div>
        <div class="message-box"><?php echo htmlspecialchars($feedback['message']); ?></div>
    </div>
    
    <div class="text-center mt-5 pt-3 border-top text-muted small print-only d-none d-print-block">
        Printed on <?php echo date('Y-m-d H:i:s'); ?> from Gov Admin System
    </div>

</div>

</body>
</html>
