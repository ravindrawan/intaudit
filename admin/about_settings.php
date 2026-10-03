<?php
require_once 'includes/auth.php';
requireAdmin();
require_once '../includes/db.php';

// Fetch base URL for processing image paths
$stmtBaseUrl = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'base_url'");
$stmtBaseUrl->execute();
$baseUrlSetting = $stmtBaseUrl->fetchColumn();
if (empty($baseUrlSetting)) {
    $baseUrlSetting = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');
}
$baseUrlSetting = rtrim($baseUrlSetting, '/');

if (!hasMenuPermission('about_settings')) {
    header("Location: index.php");
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    
    // Toggles for different sections
    $visionShow = isset($_POST['about_vision_show_home']) ? '1' : '0';
    $missionShow = isset($_POST['about_mission_show_home']) ? '1' : '0';
    $descShow = isset($_POST['about_desc_show_home']) ? '1' : '0';
    $chairmanShow = isset($_POST['about_chairman_msg_show_home']) ? '1' : '0';
    $secShow = isset($_POST['about_sec_msg_show_home']) ? '1' : '0';
    
    // New toggles for about us page specifically
    $chairmanShowAbout = isset($_POST['about_chairman_msg_show_about']) ? '1' : '0';
    $secShowAbout = isset($_POST['about_sec_msg_show_about']) ? '1' : '0';
    
    $about_desc = $_POST['about_desc'] ?? '';
    if (!empty($baseUrlSetting)) {
        $about_desc = str_replace('src="' . $baseUrlSetting . '/', 'src="', $about_desc);
    }
    
    $about_chairman_msg = $_POST['about_chairman_msg'] ?? '';
    if (!empty($baseUrlSetting)) {
        $about_chairman_msg = str_replace('src="' . $baseUrlSetting . '/', 'src="', $about_chairman_msg);
    }
    
    $about_sec_msg = $_POST['about_sec_msg'] ?? '';
    if (!empty($baseUrlSetting)) {
        $about_sec_msg = str_replace('src="' . $baseUrlSetting . '/', 'src="', $about_sec_msg);
    }

    $settingsToUpdate = [
        'about_vision' => $_POST['about_vision'] ?? '',
        'about_mission' => $_POST['about_mission'] ?? '',
        'about_desc' => $about_desc,
        'about_chairman_msg' => $about_chairman_msg,
        'about_sec_msg' => $about_sec_msg,
        'about_vision_show_home' => $visionShow,
        'about_mission_show_home' => $missionShow,
        'about_desc_show_home' => $descShow,
        'about_chairman_msg_show_home' => $chairmanShow,
        'about_sec_msg_show_home' => $secShow,
        'about_chairman_title' => trim($_POST['about_chairman_title'] ?? 'Message from the Chairman'),
        'about_sec_title' => trim($_POST['about_sec_title'] ?? 'Message from the Secretary'),
        'about_chairman_msg_show_about' => $chairmanShowAbout,
        'about_sec_msg_show_about' => $secShowAbout
    ];

    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    
    // Note: The settings table doesn't have a UNIQUE index on setting_key by default in our setup scripts, 
    // so let's use UPDATE first, then INSERT if not exists.
    $updateStmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = ?");
    $insertStmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");

    $pdo->beginTransaction();
    try {
        foreach ($settingsToUpdate as $key => $value) {
            $updateStmt->execute([$value, $key]);
            if ($updateStmt->rowCount() === 0) {
                // Double check if key exists, because rowCount is 0 if value is unchanged
                $checkStmt->execute([$key]);
                if ($checkStmt->fetchColumn() == 0) {
                    $insertStmt->execute([$key, $value]);
                }
            }
        }
        $pdo->commit();
        $success = 'About Us content updated successfully.';
    } catch(PDOException $e) {
        $pdo->rollBack();
        $error = 'Failed to update settings. ' . $e->getMessage();
    }
}

// Fetch current details
$stmt = $pdo->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'about_%'");
$stmt->execute();
$aboutData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Initialize array keys if empty
$defaults = [
    'about_vision', 'about_mission', 'about_desc', 'about_chairman_msg', 'about_sec_msg', 
    'about_vision_show_home', 'about_mission_show_home', 'about_desc_show_home', 'about_chairman_msg_show_home', 'about_sec_msg_show_home',
    'about_chairman_title', 'about_sec_title', 'about_chairman_msg_show_about', 'about_sec_msg_show_about'
];
foreach($defaults as $k) {
    if(!isset($aboutData[$k])) $aboutData[$k] = '';
}

// Convert relative image paths to absolute for the Summernote editor
if (!empty($baseUrlSetting)) {
    $aboutData['about_desc'] = preg_replace('/src="(?!(http:\/\/|https:\/\/|data:|\/))(.*?)"/i', 'src="' . $baseUrlSetting . '/$2"', $aboutData['about_desc']);
    $aboutData['about_chairman_msg'] = preg_replace('/src="(?!(http:\/\/|https:\/\/|data:|\/))(.*?)"/i', 'src="' . $baseUrlSetting . '/$2"', $aboutData['about_chairman_msg']);
    $aboutData['about_sec_msg'] = preg_replace('/src="(?!(http:\/\/|https:\/\/|data:|\/))(.*?)"/i', 'src="' . $baseUrlSetting . '/$2"', $aboutData['about_sec_msg']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage About Us - Gov Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    
        <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 px-md-4 py-4">

    <div class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Manage About Us Content</h2>
            <a href="../about-us.php" class="btn btn-outline-info" target="_blank"><i class="fas fa-external-link-alt"></i> View Page</a>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-white fw-bold py-3"><i class="fas fa-bullseye text-danger me-2"></i> Core Identity</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-bold mb-0">Our Vision</label>
                                <div class="form-check form-switch fw-normal">
                                    <input class="form-check-input" type="checkbox" role="switch" id="visionShowHome" name="about_vision_show_home" value="1" <?php echo $aboutData['about_vision_show_home'] === '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label text-muted small" for="visionShowHome">Show on Home Page</label>
                                </div>
                            </div>
                            <textarea class="form-control" name="about_vision" rows="4" placeholder="Enter the institute vision here..."><?php echo htmlspecialchars($aboutData['about_vision']); ?></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-bold mb-0">Our Mission</label>
                                <div class="form-check form-switch fw-normal">
                                    <input class="form-check-input" type="checkbox" role="switch" id="missionShowHome" name="about_mission_show_home" value="1" <?php echo $aboutData['about_mission_show_home'] === '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label text-muted small" for="missionShowHome">Show on Home Page</label>
                                </div>
                            </div>
                            <textarea class="form-control" name="about_mission" rows="4" placeholder="Enter the institute mission here..."><?php echo htmlspecialchars($aboutData['about_mission']); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between align-items-center">
                    <div><i class="fas fa-info-circle text-info me-2"></i> Main Description</div>
                    <div class="form-check form-switch fw-normal">
                        <input class="form-check-input" type="checkbox" role="switch" id="descShowHome" name="about_desc_show_home" value="1" <?php echo $aboutData['about_desc_show_home'] === '1' ? 'checked' : ''; ?>>
                        <label class="form-check-label text-muted small" for="descShowHome">Display on Home Page</label>
                    </div>
                </div>
                <div class="card-body">
                    <textarea class="summernote" name="about_desc"><?php echo htmlspecialchars($aboutData['about_desc']); ?></textarea>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold py-3"><i class="fas fa-comment-dots text-warning me-2"></i> Messages from Leadership</div>
                <div class="card-body">
                    <div class="mb-5 pb-4 border-bottom">
                        <div class="row align-items-end mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label fw-bold">Chairman / Governor Title</label>
                                <input type="text" class="form-control" name="about_chairman_title" value="<?php echo htmlspecialchars($aboutData['about_chairman_title'] ?: 'Message from the Chairman'); ?>">
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex flex-wrap gap-3">
                                    <div class="form-check form-switch fw-normal bg-light px-4 py-2 rounded">
                                        <input class="form-check-input" type="checkbox" role="switch" id="chairShowHome" name="about_chairman_msg_show_home" value="1" <?php echo $aboutData['about_chairman_msg_show_home'] === '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label text-muted small ms-2" for="chairShowHome">Show on Home Page</label>
                                    </div>
                                    <div class="form-check form-switch fw-normal bg-light px-4 py-2 rounded">
                                        <input class="form-check-input" type="checkbox" role="switch" id="chairShowAbout" name="about_chairman_msg_show_about" value="1" <?php echo ($aboutData['about_chairman_msg_show_about'] === '1' || $aboutData['about_chairman_msg_show_about'] === '') ? 'checked' : ''; ?>>
                                        <label class="form-check-label text-muted small ms-2" for="chairShowAbout">Show on About Us Page</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <textarea class="summernote" name="about_chairman_msg"><?php echo htmlspecialchars($aboutData['about_chairman_msg']); ?></textarea>
                    </div>
                    <div>
                        <div class="row align-items-end mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label fw-bold">Secretary Title</label>
                                <input type="text" class="form-control" name="about_sec_title" value="<?php echo htmlspecialchars($aboutData['about_sec_title'] ?: 'Message from the Secretary'); ?>">
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex flex-wrap gap-3">
                                    <div class="form-check form-switch fw-normal bg-light px-4 py-2 rounded">
                                        <input class="form-check-input" type="checkbox" role="switch" id="secShowHome" name="about_sec_msg_show_home" value="1" <?php echo $aboutData['about_sec_msg_show_home'] === '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label text-muted small ms-2" for="secShowHome">Show on Home Page</label>
                                    </div>
                                    <div class="form-check form-switch fw-normal bg-light px-4 py-2 rounded">
                                        <input class="form-check-input" type="checkbox" role="switch" id="secShowAbout" name="about_sec_msg_show_about" value="1" <?php echo ($aboutData['about_sec_msg_show_about'] === '1' || $aboutData['about_sec_msg_show_about'] === '') ? 'checked' : ''; ?>>
                                        <label class="form-check-label text-muted small ms-2" for="secShowAbout">Show on About Us Page</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <textarea class="summernote" name="about_sec_msg"><?php echo htmlspecialchars($aboutData['about_sec_msg']); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="sticky-bottom bg-light py-3 border-top mt-4 text-end">
                <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm"><i class="fas fa-save me-2"></i> Save All Changes</button>
            </div>
            
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.summernote').summernote({
                height: 250,
                tabsize: 2,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture']],
                    ['view', ['fullscreen', 'codeview']]
                ],
                callbacks: {
                    onImageUpload: function(files) {
                        for(let i=0; i < files.length; i++) {
                            uploadImage(files[i], this);
                        }
                    }
                }
            });

            function uploadImage(file, editor) {
                var data = new FormData();
                data.append("file", file);
                
                $.ajax({
                    url: 'upload_image.php',
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: data,
                    type: "post",
                    success: function(url) {
                        var image = $('<img>').attr('src', url);
                        $(editor).summernote("insertNode", image[0]);
                    },
                    error: function(data) {
                        alert("Image upload failed. Please try again.");
                        console.log(data);
                    }
                });
            }
        });
    </script>

            </main>
        </div>
    </div>
</body>
</html>
