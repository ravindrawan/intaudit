<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

// Handle Filtering
$whereClauses = [];
$params = [];

if (!empty($_GET['category'])) {
    if ($_GET['category'] == 'uncategorized') {
        $whereClauses[] = "d.tag_id IS NULL";
    } else {
        $whereClauses[] = "d.tag_id = ?";
        $params[] = $_GET['category'];
    }
}

if (!empty($_GET['upload_date'])) {
    $whereClauses[] = "DATE(d.uploaded_at) = ?";
    $params[] = $_GET['upload_date'];
}

if (!empty($_GET['title'])) {
    $whereClauses[] = "d.title LIKE ?";
    $params[] = '%' . $_GET['title'] . '%';
}

$whereSql = '';
if (count($whereClauses) > 0) {
    $whereSql = " WHERE " . implode(" AND ", $whereClauses);
}

// Fetch all categories (tags) that have files, plus a special pseudo-tag for Uncategorized (tag_id IS NULL)
$stmt = $pdo->prepare("
    SELECT d.*, IFNULL(t.tag_name, 'General Files') as category_name 
    FROM downloads d 
    LEFT JOIN download_tags t ON d.tag_id = t.id 
    $whereSql
    ORDER BY category_name ASC, d.uploaded_at DESC
");
$stmt->execute($params);
$allDownloads = $stmt->fetchAll();

// Fetch categories for the dropdown filter
$stmtCats = $pdo->query("SELECT id, tag_name FROM download_tags ORDER BY tag_name ASC");
$categories = $stmtCats->fetchAll();

$isFiltered = !empty($_GET['title']) || !empty($_GET['category']) || !empty($_GET['upload_date']);

// Group files by category in PHP for easier display
$groupedDownloads = [];
foreach($allDownloads as $file) {
    $cat = $file['category_name'];
    if (!isset($groupedDownloads[$cat])) {
        $groupedDownloads[$cat] = [];
    }
    $groupedDownloads[$cat][] = $file;
}
?>

<div class="row mt-4 mb-5 justify-content-center">
    <div class="col-lg-10">
        <h2 class="section-title mb-4">Official Forms & Downloads</h2>
        <p class="lead text-muted mb-5">Access all the publicly available documents, forms, and reports below. Click download to retrieve the file.</p>
        
        <div class="card shadow-sm border-0 mb-4 bg-light">
            <div class="card-body">
                <form action="downloads.php" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Search Title</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="title" class="form-control border-start-0 ps-0" placeholder="Enter document name..." value="<?php echo htmlspecialchars($_GET['title'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Category</label>
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            <option value="uncategorized" <?php echo (isset($_GET['category']) && $_GET['category'] == 'uncategorized') ? 'selected' : ''; ?>>General Files</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['tag_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Uploaded Date</label>
                        <input type="date" name="upload_date" class="form-control" value="<?php echo htmlspecialchars($_GET['upload_date'] ?? ''); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">Filter</button>
                    </div>
                </form>
                <?php if($isFiltered): ?>
                    <div class="mt-2 text-end">
                        <a href="downloads.php" class="text-danger small text-decoration-none fw-bold"><i class="fas fa-times me-1"></i> Clear all filters</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if(empty($groupedDownloads)): ?>
            <div class="text-center py-5 text-muted border rounded bg-white shadow-sm">
                <i class="fas fa-search fa-3x mb-3 text-secondary"></i>
                <h5>No files matching your search criteria.</h5>
                <p class="mb-0">Try adjusting your filters or clearing them to see all files.</p>
            </div>
        <?php else: ?>
            
            <div class="accordion shadow-sm" id="downloadsAccordion">
                <?php 
                $i = 0;
                foreach($groupedDownloads as $category => $files): 
                    $collapseId = "collapseCat" . $i;
                ?>
                <div class="accordion-item border-0 mb-3 rounded overflow-hidden">
                    <h2 class="accordion-header">
                        <button class="accordion-button <?php echo (!$isFiltered) ? 'collapsed' : ''; ?> fw-bold fs-5 bg-white text-dark border-bottom" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>" aria-expanded="<?php echo (!$isFiltered) ? 'false' : 'true'; ?>">
                            <i class="fas fa-tags text-primary me-3"></i> <?php echo htmlspecialchars($category); ?> 
                            <span class="badge bg-secondary ms-auto rounded-pill"><?php echo count($files); ?> File(s)</span>
                        </button>
                    </h2>
                    <div id="<?php echo $collapseId; ?>" class="accordion-collapse collapse <?php echo (!$isFiltered) ? '' : 'show'; ?>" data-bs-parent="#downloadsAccordion">
                        <div class="accordion-body p-0">
                            <div class="table-responsive m-0">
                                <table class="table table-hover align-middle mb-0 border-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="py-3 px-4 border-bottom-0">Document Title</th>
                                            <th class="py-3 px-4 border-bottom-0 text-center">Date Uploaded</th>
                                            <th class="py-3 px-4 border-bottom-0 text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($files as $file): ?>
                                        <tr>
                                            <td class="py-3 px-4 border-bottom">
                                                <h6 class="mb-1 text-dark fw-bold"><i class="fas fa-file-alt text-secondary me-2"></i> <?php echo htmlspecialchars($file['title']); ?></h6>
                                            </td>
                                            <td class="py-3 px-4 text-center text-muted border-bottom" style="font-size: 0.9rem;">
                                                <?php echo date('M d, Y', strtotime($file['uploaded_at'])); ?>
                                            </td>
                                            <td class="py-3 px-4 text-end border-bottom">
                                                <a href="<?php echo htmlspecialchars($file['file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-success px-3 rounded-pill">
                                                    <i class="fas fa-cloud-download-alt me-1"></i> Download
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
                $i++;
                endforeach; 
                ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
