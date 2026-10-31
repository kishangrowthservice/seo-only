<?php
// add_blog.php: Handles blog post submissions and updates blogs.json

// Require an authenticated admin session (previously this endpoint accepted
// unauthenticated POSTs and wrote them straight into blogs.json).
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    exit('Forbidden: admin login required.');
}

// CSRF: every state-changing POST must carry the session token.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string) $token)) {
        http_response_code(403);
        exit('Forbidden: invalid CSRF token.');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $author = isset($_POST['author']) ? trim($_POST['author']) : '';
    $slug = isset($_POST['slug']) ? trim($_POST['slug']) : '';
    $metaDescription = isset($_POST['seo_description']) ? trim($_POST['seo_description']) : '';
    $metaKeywords = isset($_POST['seo_keywords']) ? trim($_POST['seo_keywords']) : '';
    $metaTitle = isset($_POST['seo_title']) ? trim($_POST['seo_title']) : '';
    $date = isset($_POST['date']) ? trim($_POST['date']) : '';

    // Validate required fields
    if ($title === '' || $content === '' || $author === '' || $slug === '') {
        die('All fields are required.');
    }
    // Validate slug format
    if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
        die('Slug must contain only lowercase letters, numbers, and hyphens.');
    }

    $blogFile = 'blogs.json';
    $blogs = [];

    // Read existing blogs
    if (file_exists($blogFile)) {
        $json = file_get_contents($blogFile);
        $blogs = json_decode($json, true);
        if (!is_array($blogs)) {
            $blogs = [];
        }
    }

    // Ensure slug is unique
    foreach ($blogs as $b) {
        if ($b['slug'] === $slug) {
            die('Slug must be unique.');
        }
    }

    // Handle image upload
    $imagePath = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageTmp = $_FILES['image']['tmp_name'];
        $imageName = basename($_FILES['image']['name']);
        $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($imageExt, $allowedExts)) {
            die('Invalid image type. Allowed: jpg, jpeg, png, gif, webp');
        }
        // Verify the actual file content is an image, not just its name.
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $imageTmp) : '';
        if ($finfo) {
            finfo_close($finfo);
        }
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($mime, $allowedMimes, true)) {
            die('Invalid image content. Allowed: jpg, jpeg, png, gif, webp');
        }
        // Use document root for proper file system path
        $targetDir = $_SERVER['DOCUMENT_ROOT'] . '/images/blogs/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        // Unique filename
        $newImageName = uniqid('blog_', true) . '.' . $imageExt;
        $targetFile = $targetDir . $newImageName;
        if (!move_uploaded_file($imageTmp, $targetFile)) {
            die('Failed to upload image.');
        }
        // Add leading slash for web path
        $imagePath = '/images/blogs/' . $newImageName;
    } else {
        die('Image upload failed or not provided.');
    }

    // Generate new blog post
    $newBlog = [
        'id' => count($blogs) > 0 ? end($blogs)['id'] + 1 : 1,
        'title' => $title,
        'content' => $content, // Store raw markdown
        'author' => $author,
        'slug' => $slug,
        'image' => $imagePath,
        'seoTitle' => $metaTitle,
        'seoKeywords' => $metaKeywords,
        'seoDescription' => $metaDescription,
        'date' => $date
    ];

    $blogs[] = $newBlog;

    // Back up the current blogs.json before overwriting it: the whole blog
    // lives in this one file, and a corrupt write would lose every post.
    // Backups sit in backups/, shielded by a deny-all .htaccess written once.
    if (file_exists($blogFile)) {
        $backupDir = __DIR__ . '/backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
            file_put_contents($backupDir . '/.htaccess', "Require all denied\nDeny from all\n");
            file_put_contents($backupDir . '/index.html', '');
        }
        copy($blogFile, $backupDir . '/blogs-' . date('Ymd-His') . '.json');
    }

    // Write back to JSON file with file locking
    $fp = fopen($blogFile, 'w');
    if (flock($fp, LOCK_EX)) {
        fwrite($fp, json_encode($blogs, JSON_PRETTY_PRINT));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        header('Location: blogs');
        exit;
    } else {
        fclose($fp);
        die('Could not lock the file for writing.');
    }
} else {
    die('Invalid request method.');
} 