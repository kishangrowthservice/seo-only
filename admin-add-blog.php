<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit();
}

$currentDate = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add Blog Post (Admin)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/easymde/dist/easymde.min.css" />
    <script src="https://unpkg.com/easymde/dist/easymde.min.js"></script>
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --light-bg: #f7f8fa;
            --card-bg: #fff;
            --text: #222;
            --text-light: #555;
            --border: #d1d5db;
            --success: #10b981;
            --error: #ef4444;
            --warning: #f59e0b;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            background: var(--light-bg);
            font-family: "Inter", Arial, sans-serif;
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
            padding-bottom: 40px;
        }
        
        .admin-header {
            background: var(--card-bg);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .admin-title {
            font-size: 1.25rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .admin-title i {
            color: var(--primary);
        }
        
        .logout-link {
            font-size: 1rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            background: #f1f5ff;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .logout-link:hover {
            background: #e0e7ff;
            color: var(--primary-hover);
        }
        
        .container {
            max-width: 900px;
            margin: 40px auto;
            background: var(--card-bg);
            border-radius: 14px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            padding: 2.5rem;
            position: relative;
        }
        
        h1 {
            text-align: center;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 2rem;
            color: var(--text);
            position: relative;
            padding-bottom: 1rem;
        }
        
        h1:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--primary);
            border-radius: 2px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text);
            font-size: 0.95rem;
        }
        
        .field-description {
            font-size: 0.85rem;
            color: var(--text-light);
            margin-top: 0.25rem;
            font-weight: 400;
        }
        
        input[type="text"],
        input[type="date"],
        input[type="file"],
        textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            background: #fafbfc;
            transition: all 0.2s ease;
        }
        
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        input[type="text"]:focus,
        input[type="date"]:focus,
        textarea:focus,
        input[type="file"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
            background: #fff;
        }
        
        .editor-container {
            margin-top: 0.5rem;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        button[type="submit"] {
            width: 100%;
            background: var(--primary);
            color: #fff;
            font-weight: 600;
            font-size: 1.1rem;
            border: none;
            border-radius: 8px;
            padding: 0.9rem 0;
            margin-top: 1.5rem;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.15);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        button[type="submit"]:hover {
            background: var(--primary-hover);
            box-shadow: 0 4px 16px rgba(99, 102, 241, 0.2);
        }
        
        .info-badge {
            background: #e0f2fe;
            color: #0369a1;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .form-section {
            background: #f9fafb;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid #e5e7eb;
        }
        
        .form-section-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .editor-status {
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            color: var(--text-light);
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 1rem;
                padding: 1.5rem;
            }
            
            h1 {
                font-size: 1.5rem;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 480px) {
            .admin-header {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem;
            }
            
            .logout-link {
                width: 100%;
                justify-content: center;
            }
        }
        
        /* Fix for editor icons */
        .editor-toolbar button {
            font-family: 'Font Awesome 6 Free' !important;
            font-weight: 900 !important;
        }
        
        .editor-toolbar button:before {
            font-family: 'Font Awesome 6 Free' !important;
            font-weight: 900 !important;
        }
        
        .cm-s-easymde .cm-header-1 { font-size: 1.6em; color: var(--primary); }
        .cm-s-easymde .cm-header-2 { font-size: 1.4em; color: #4f46e5; }
        .cm-s-easymde .cm-header-3 { font-size: 1.2em; color: #6366f1; }
    </style>
  <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=AW-937335270">
</script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'AW-937335270');
</script>
</head>
<body>
    <header class="admin-header">
        <div class="admin-title">
            <i class="fas fa-tachometer-alt"></i>
            Admin Dashboard
        </div>
        <a href="logout.php" class="logout-link">
            <i class="fas fa-sign-out-alt"></i>
            Logout
        </a>
    </header>
    
    <div class="container">
        
        <h1>Add a New Blog Post</h1>
        
        <form id="blogForm" action="add_blog.php" method="POST" enctype="multipart/form-data">
            <div class="form-section">
                <h2 class="form-section-title">
                    <i class="fas fa-file-alt"></i>
                    Blog Content
                </h2>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" required placeholder="Enter blog title">
                    </div>
                    
                    <div class="form-group">
                        <label for="author">Author</label>
                        <input type="text" id="author" name="author" required placeholder="Author name">
                    </div>
                    
                    <div class="form-group">
                        <label for="slug">Slug</label>
                        <input type="text" id="slug" name="slug" pattern="[a-z0-9-]+" required placeholder="e.g. my-first-blog">
                        <p class="field-description">Lowercase letters, numbers, and hyphens only</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="date">Publish Date</label>
                        <input type="date" id="date" name="date" value="<?php echo $currentDate; ?>" required>
                        <p class="field-description">Date when the blog will be published</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Featured Image</label>
                        <input type="file" id="image" name="image" accept="image/*" required>
                        <p class="field-description">Recommended size: 1200x630 pixels</p>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="content">Content</label>
                    <div class="editor-container">
                        <textarea id="content" name="content" rows="10" cols="50" placeholder="Write your blog content here..."></textarea>
                    </div>
                    <div class="editor-status">
                        <span id="word-count">0 words</span>
                        <span id="char-count">0 characters</span>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h2 class="form-section-title">
                    <i class="fas fa-search"></i>
                    SEO Settings
                </h2>
                
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="seo_title">SEO Title</label>
                        <input type="text" id="seo_title" name="seo_title" placeholder="Optimized title for search engines">
                        <p class="field-description">Appears in search results (max 60 characters)</p>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="seo_description">SEO Description</label>
                        <textarea id="seo_description" name="seo_description" placeholder="Brief description of the blog content"></textarea>
                        <p class="field-description">Appears in search results (max 160 characters)</p>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="seo_keywords">SEO Keywords</label>
                        <input type="text" id="seo_keywords" name="seo_keywords" placeholder="keyword1, keyword2, keyword3">
                        <p class="field-description">Comma-separated list of relevant keywords</p>
                    </div>
                </div>
            </div>
            
            <button type="submit">
                <i class="fas fa-plus-circle"></i>
                Add Blog Post
            </button>
        </form>
    </div>
    
    <script>
      var easyMDE = new EasyMDE({
        element: document.getElementById("content"),
      });
      document
        .getElementById("blogForm")
        .addEventListener("submit", function (e) {
          if (!easyMDE.value().trim()) {
            alert("Content is required.");
            easyMDE.codemirror.focus();
            e.preventDefault();
          }
        });
    </script>
</body>
</html>