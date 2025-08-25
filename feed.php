<?php
session_start();
require 'db.php';

$isLoggedIn = isset($_SESSION['user_id']);
$pic = 'uploads/default.jpeg';

// Get user profile pic if logged in
if ($isLoggedIn) {
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $pic = !empty($row['profile_pic']) ? $row['profile_pic'] : $pic;
    }
    $stmt->close();
}

// Fetch posts with user info
$sql = "SELECT posts.*, users.name, users.profile_pic 
        FROM posts 
        JOIN users ON posts.user_id = users.id 
        ORDER BY posts.created_at DESC";
$result = $conn->query($sql);

$posts = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>WOMXN | Feed</title>
  <link rel="stylesheet" href="styles.css">
  <link href="https://fonts.googleapis.com/css2?family=Macondo&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <style>
        :root {
            --primary-color: #872657;
            --primary-hover: #68212f;
            --primary-light: #b8336a;
            --secondary-color: #ffe6f0;
            --accent-color: #ff6b9d;
            --text-dark: #2b2b2b;
            --text-muted: #6c757d;
            --text-light: #8e8e93;
            --bg-primary: #ffe6f0;
            --bg-secondary: #ffffff;
            --bg-card: #ffffff;
            --border-color: #e1e5e9;
            --border-light: #f0f2f5;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 20px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.12);
            --border-radius: 12px;
            --border-radius-lg: 16px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
                 /* Global styles */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Poppins', sans-serif;
  display: flex;
  background-color: #ffffffff;
  color: #2b2b2b;
}

.container {
  display: flex;
  width: 100%;
}

/* Main content */
main {
  margin-left: 240px;
  padding: 2rem;
  flex: 1;
  min-height: 100vh;
  margin-top: 60px;  /* space for topbar */
}

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Post Form */
        .post-form {
            background: var(--bg-card);
            border-radius: var(--border-radius-lg);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-light);
            transition: var(--transition);
            height: auto;
            position: relative;
            overflow: hidden;
        }

        .post-form:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }

        .post-form textarea {
            width: 100%;
            padding: 16px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            outline: none;
            font-size: 1rem;
            font-family: inherit;
            resize: vertical;
            min-height: 120px;
            transition: var(--transition);
            background: #fafbfc;
        }

        .post-form textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(135, 38, 87, 0.1);
            background: white;
        }

        .post-form textarea::placeholder {
            color: var(--text-light);
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            gap: 1rem;
        }

        .file-input-container {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .post-form input[type="file"] {
            display: none;
        }

        .file-label {
            display: inline-flex;
            align-items: center;
            max-width: 200px;
            gap: 8px;
            padding: 10px 16px;
            background: var(--border-light);
            color: var(--text-muted);
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.9rem;
        }

        .file-label:hover {
            background: var(--border-color);
            color: var(--text-dark);
        }

        .post-form button[type="submit"] {
            padding: 12px 24px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
        }

        .post-form button[type="submit"]:hover {
            background: linear-gradient(135deg, var(--primary-hover) 0%, #9a2a5a 100%);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        /* Posts */
        .post {
            background: var(--bg-card);
            border-radius: var(--border-radius-lg);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-light);
            transition: var(--transition);
            position: relative;
        }

        .post:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }

        .post .meta {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 1rem;
        }

        .post .meta img {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--border-light);
            transition: var(--transition);
        }

        .post .meta img:hover {
            border-color: var(--primary-color);
        }

        .post .meta .user-info {
            flex: 1;
        }

        .post .meta .user-name {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 2px;
        }

        .post .meta .post-time {
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 400;
        }

        .post-content {
            font-size: 1rem;
            line-height: 1.6;
            color: var(--text-dark);
            margin-bottom: 1rem;
        }

        .post img {
            width: 400px;
            max-width: 100%;
            height:400px;
            border-radius: var(--border-radius);
            margin-top: 1rem;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .post img:hover {
            box-shadow: var(--shadow-md);
            transform: scale(1.02);
        }

        /* Comments Section */
        .comments-section {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid var(--border-light);
        }

        .comments-header {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .comment {
            background: #fafbfc;
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            border-left: 4px solid var(--primary-color);
            transition: var(--transition);
        }

        .comment:hover {
            background: #f0f2f5;
            transform: translateX(4px);
        }

        .comment-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
        }

        .comment-author {
            font-weight: 600;
            color: var(--primary-color);
        }

        .comment-time {
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        .comment-text {
            color: var(--text-dark);
            line-height: 1.5;
        }

        .no-comments {
            color: var(--text-muted);
            font-style: italic;
            text-align: center;
            padding: 1rem;
        }

        /* Comment Form */
        .comment-form {
            margin-top: 1rem;
        }

        .comment-form textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            outline: none;
            font-size: 0.95rem;
            font-family: inherit;
            resize: vertical;
            min-height: 80px;
            transition: var(--transition);
            background: white;
        }

        .comment-form textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(135, 38, 87, 0.1);
        }

        .comment-form button {
            margin-top: 8px;
            background: var(--primary-color);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            font-size: 0.9rem;
        }

        .comment-form button:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }

        .login-prompt {
            color: var(--text-muted);
            font-style: italic;
            text-align: center;
            padding: 1rem;
            background: var(--border-light);
            border-radius: var(--border-radius);
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            main {
                margin-left: 280px;
            }
        }

        @media (max-width: 768px) {
            main {
                margin-left: 0;
                padding: 1rem;
            }

            .search-section {
                padding: 1.5rem;
            }

            .search-bar {
                flex-direction: column;
                gap: 1rem;
            }

            .search-bar button {
                align-self: center;
                min-width: 120px;
            }

            .post {
                padding: 1rem;
            }

            .form-actions {
                flex-direction: column;
                align-items: stretch;
            }
        }

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(135, 38, 87, 0.3);
            border-radius: 50%;
            border-top-color: var(--primary-color);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body>
<div class="container">
    <?php include 'sidebar.php'; ?>
    <?php include 'topbar.php'; ?>

    <main>
        <?php if ($isLoggedIn): ?>
            <div class="post-form">
                <form action="create_post.php" method="POST" enctype="multipart/form-data">
                    <textarea name="content" placeholder="What's on your mind? Share your thoughts with the community..." required></textarea>
                  <div id="media-preview" style="margin-top:16px;">
                    <!-- Media previews will be shown here -->
                  </div>
                    <div class="form-actions">
                        <div class="file-input-container">
                            <input type="file" name="image" accept="image/*" id="image-upload">
                            <label for="image-upload" class="file-label">
                                <i class="fas fa-image"></i> Add Photo
                            </label>
                        </div>

                        <div class="file-input-container">
                            <input type="file" name="video" accept="video/*" id="video-upload">
                            <label for="video-upload" class="file-label">
                                <i class="fas fa-video"></i> Add Video
                            </label>
                        </div>

                        <button type="submit">
                            <i class="fas fa-paper-plane"></i> Share Post
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <?php foreach ($posts as $post): ?>
            <div class="post">
                <div class="meta">
                    <img src="<?= htmlspecialchars($post['profile_pic'] ?: 'uploads/default.jpeg') ?>" 
                         alt="<?= htmlspecialchars($post['name']) ?>'s Profile"
                         onerror="this.src='uploads/default.jpeg'">
                    <div class="user-info">
                        <div class="user-name"><?= htmlspecialchars($post['name']) ?></div>
                        <div class="post-time"><?= htmlspecialchars($post['created_at']) ?></div>
                    </div>
                </div>

                <div class="post-content">
                    <?= nl2br(htmlspecialchars($post['content'])) ?>
                </div>

                <?php if (!empty($post['image'])): ?>
                    <img src="<?= htmlspecialchars($post['image']) ?>" alt="Post Image">
                <?php endif; ?>

                <?php if (!empty($post['video_path'])): ?>
                    <video width="400" controls>
                        <source src="<?= htmlspecialchars($post['video_path']) ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                <?php endif; ?>

                <?php
                // Fetch comments for this post
                $post_id = $post['id'];
                $stmt = $conn->prepare("SELECT comments.comment_text, comments.created_at, users.name 
                                        FROM comments 
                                        JOIN users ON comments.user_id = users.id 
                                        WHERE comments.post_id = ? 
                                        ORDER BY comments.created_at ASC");
                $stmt->bind_param("i", $post_id);
                $stmt->execute();
                $comments_result = $stmt->get_result();
                ?>

                <div class="comments-section">
                    <div class="comments-header">
                        <i class="fas fa-comments"></i>
                        Comments (<?= $comments_result->num_rows ?>)
                    </div>

                    <?php if ($comments_result->num_rows > 0): ?>
                        <?php while ($comment = $comments_result->fetch_assoc()): ?>
                            <div class="comment">
                                <div class="comment-header">
                                    <span class="comment-author"><?= htmlspecialchars($comment['name']) ?></span>
                                    <span class="comment-time"><?= htmlspecialchars($comment['created_at']) ?></span>
                                </div>
                                <div class="comment-text"><?= nl2br(htmlspecialchars($comment['comment_text'])) ?></div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-comments">
                            <i class="fas fa-comment-slash"></i>
                            No comments yet. Be the first to share your thoughts!
                        </div>
                    <?php endif; ?>

                    <?php if ($isLoggedIn): ?>
                        <form action="submit_comment.php" method="POST" class="comment-form">
                            <input type="hidden" name="post_id" value="<?= $post_id ?>">
                            <textarea name="comment_text" placeholder="Share your thoughts on this post..." required></textarea>
                            <button type="submit">
                                <i class="fas fa-reply"></i> Post Comment
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="login-prompt">
                            <i class="fas fa-sign-in-alt"></i>
                            Please log in to leave a comment and join the conversation.
                        </div>
                    <?php endif; ?>
                </div>

                <?php $stmt->close(); ?>
            </div>
        <?php endforeach; ?>
    </main>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const imageInput = document.getElementById('image-upload');
    const videoInput = document.getElementById('video-upload');
    const previewContainer = document.getElementById('media-preview');

    function clearPreview() {
        previewContainer.innerHTML = '';
    }

    function createImagePreview(file) {
        const img = document.createElement('img');
        img.style.maxWidth = '400px';
        img.style.maxHeight = '400px';
        img.style.borderRadius = '12px';
        img.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.06)';
        img.src = URL.createObjectURL(file);
        img.onload = () => URL.revokeObjectURL(img.src);
        return img;
    }

    function createVideoPreview(file) {
        const video = document.createElement('video');
        video.controls = true;
        video.style.maxWidth = '400px';
        video.style.maxHeight = '400px';
        video.style.borderRadius = '12px';
        video.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.06)';
        video.src = URL.createObjectURL(file);
        video.onload = () => URL.revokeObjectURL(video.src);
        return video;
    }

    function handleFileChange() {
        clearPreview();

        if (imageInput.files.length > 0) {
            // If an image is selected, show image preview
            const file = imageInput.files[0];
            if (file.type.startsWith('image/')) {
                previewContainer.appendChild(createImagePreview(file));
                // Clear video input to avoid conflict
                videoInput.value = '';
            }
        } else if (videoInput.files.length > 0) {
            // If video is selected, show video preview
            const file = videoInput.files[0];
            if (file.type.startsWith('video/')) {
                previewContainer.appendChild(createVideoPreview(file));
                // Clear image input to avoid conflict
                imageInput.value = '';
            }
        }
    }

    imageInput.addEventListener('change', handleFileChange);
    videoInput.addEventListener('change', handleFileChange);
});
</script>

</body>
</html>
