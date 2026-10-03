<?php
require_once 'includes/auth.php';
require_once '../includes/db.php';

// Fetch base URL for processing image and file paths
$stmtBaseUrl = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'base_url'");
$stmtBaseUrl->execute();
$baseUrlSetting = $stmtBaseUrl->fetchColumn();
if (empty($baseUrlSetting)) {
    $baseUrlSetting = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');
}
$baseUrlSetting = rtrim($baseUrlSetting, '/');

if (!hasMenuPermission('pages')) {
    header("Location: index.php");
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pageData = ['title' => '', 'slug' => '', 'content' => ''];
$error = '';

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE id = ?");
    $stmt->execute([$id]);
    $pageData = $stmt->fetch();
    if (!$pageData) {
        die("Page not found.");
    }
}

if (!empty($baseUrlSetting) && !empty($pageData['content'])) {
    $pageData['content'] = preg_replace('/(src|href)="(?!(http:\/\/|https:\/\/|mailto:|tel:|data:|\/))(.*?)"/i', '$1="' . $baseUrlSetting . '/$3"', $pageData['content']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $title = trim($_POST['title']);
    $slug = trim($_POST['slug']);
    // Sanitize rich-text content to prevent Stored XSS while allowing basic formatting
    $allowed_tags = '<p><br><b><i><strong><em><u><h1><h2><h3><h4><h5><h6><ul><ol><li><a><img><table><thead><tbody><tr><td><th><span><div><hr><strike><blockquote>';
    $content = strip_tags($_POST['content'] ?? '', $allowed_tags);

    // Replace absolute base URL with relative path for DB save
    if (!empty($baseUrlSetting)) {
        // Strip out base url from src attribute (for images)
        $content = str_replace('src="' . $baseUrlSetting . '/', 'src="', $content);
        // Strip out base url from href attribute (for file links)
        $content = str_replace('href="' . $baseUrlSetting . '/', 'href="', $content);
    }

    if (empty($title) || empty($slug)) {
        $error = "Title and Slug are required.";
    } else {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE pages SET title=?, slug=?, content=? WHERE id=?");
            $stmt->execute([$title, $slug, $content, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO pages (title, slug, content) VALUES (?, ?, ?)");
            $stmt->execute([$title, $slug, $content]);
        }
        header("Location: pages.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $id > 0 ? 'Edit' : 'Add'; ?> Page - Gov Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Summernote CSS for WYSIWYG Editor -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
</head>
<body class="bg-light">
        <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 px-md-4 py-4">

    <div class="container mt-4 mb-5">
        <h2><?php echo $id > 0 ? 'Edit' : 'Add New'; ?> Page</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <div class="mb-3">
                        <label class="form-label">Page Title</label>
                        <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($pageData['title']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">URL Slug (e.g., about-us)</label>
                        <input type="text" class="form-control" name="slug" value="<?php echo htmlspecialchars($pageData['slug']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Page Content</label>
                        <textarea id="summernote" name="content"><?php echo htmlspecialchars($pageData['content']); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Page</button>
                    <a href="pages.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <script>
      var UploadFileButton = function (context) {
        var ui = $.summernote.ui;
        // create button
        var button = ui.button({
          contents: '<i class="fas fa-file-upload"></i> Document',
          tooltip: 'Upload Document (PDF, Word, Excel, ZIP)',
          click: function () {
            $('#hiddenFileInput').click();
          }
        });
        return button.render();   // return button as jquery object
      }

      $('#summernote').summernote({
        placeholder: 'Enter page content here...',
        tabsize: 2,
        height: 400,
        toolbar: [
          ['style', ['style']],
          ['font', ['bold', 'italic', 'underline', 'clear']],
          ['color', ['color']],
          ['para', ['ul', 'ol', 'paragraph']],
          ['table', ['table']],
          ['insert', ['uploadFile', 'link', 'picture', 'video']],
          ['view', ['fullscreen', 'codeview', 'help']]
        ],
        buttons: {
          uploadFile: UploadFileButton
        },
        callbacks: {
            onImageUpload: function(files) {
                for(let i=0; i < files.length; i++) {
                    uploadImage(files[i], this);
                }
            }
        }
      });
      
      // Image upload via AJAX
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
              }
          });
      }
      
      // File upload via AJAX
      $(document).ready(function(){
          // Append a hidden file input to the body
          $('body').append('<input type="file" id="hiddenFileInput" style="display:none;" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.txt,.csv" />');
          
          $('#hiddenFileInput').change(function() {
              var files = $(this)[0].files;
              if(files.length > 0) {
                  var data = new FormData();
                  data.append("file", files[0]);
                  
                  // Show loading indicator
                  var currText = 'Uploading...';
                  var loadingNode = $('<span>').text(currText)[0];
                  $('#summernote').summernote('insertNode', loadingNode);
                  
                  $.ajax({
                      url: 'upload_file.php',
                      cache: false,
                      contentType: false,
                      processData: false,
                      data: data,
                      type: "post",
                      success: function(response) {
                          try {
                              var res = JSON.parse(response);
                              if (res.error) {
                                  alert(res.error);
                                  $(loadingNode).remove();
                              } else {
                                  // Create link object
                                  var link = $('<a>').attr('href', res.url).attr('target', '_blank').text(res.name);
                                  $(loadingNode).replaceWith(link[0]);
                              }
                          } catch(e) {
                              alert("Invalid response from server.");
                              $(loadingNode).remove();
                          }
                          $('#hiddenFileInput').val(''); // clear input
                      },
                      error: function(data) {
                          alert("File upload failed. Please ensure file type is allowed and size is within limits.");
                          $(loadingNode).remove();
                          $('#hiddenFileInput').val('');
                      }
                  });
              }
          });
      });
    </script>

            </main>
        </div>
    </div>
</body>
</html>
