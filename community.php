<?php
session_start();
require_once 'db.php';

// Check login state
$isLoggedIn = isset($_SESSION['user_id']);
$pic = $isLoggedIn ? $_SESSION['profile_pic'] ?? 'uploads/default.jpeg' : 'uploads/default.jpeg';

$members = [];
$q = '';
$totalMembers = 0;

// Get total member count
$countResult = $conn->query("SELECT COUNT(*) as total FROM users");
if ($countResult) {
    $totalMembers = $countResult->fetch_assoc()['total'];
}

// Default query
$sql = "SELECT id, name, pronouns, profile_pic, created_at FROM users ORDER BY name ASC";

// Handle search query
if (isset($_GET['q'])) {
    $q = trim($_GET['q']);
    $q = mysqli_real_escape_string($conn, $q);

    $sql = "SELECT id, name, pronouns, profile_pic, created_at FROM users 
            WHERE name LIKE '%$q%' 
               OR pronouns LIKE '%$q%' 
               OR email LIKE '%$q%' 
            ORDER BY created_at DESC";
}

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $members[] = $row;
    }
}

// Function to get time ago format
function timeAgo($datetime) {
    if (!$datetime) return 'Never';
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'Just now';
    if ($time < 3600) return floor($time/60) . ' min ago';
    if ($time < 86400) return floor($time/3600) . ' hrs ago';
    if ($time < 604800) return floor($time/86400) . ' days ago';
    return date('M j, Y', strtotime($datetime));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WOMXN Community - Connect & Grow Together</title>
    <link rel="stylesheet" href="styles.css">
      <link href="https://fonts.googleapis.com/css2?family=Macondo&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
                 /* Global styles */
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
        .community-container {
            margin-left: 240px;
            padding: 2rem;
            flex: 1;
            max-width: calc(100% - 240px);
        }

          

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            margin-top: 55px;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .results-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2b2b2b;
        }

        .results-count {
            color: #6c757d;
            font-size: 0.95rem;
        }

        .sort-options {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .sort-btn {
            padding: 8px 12px;
            background: white;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            color: #6c757d;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }

        .sort-btn:hover, .sort-btn.active {
            background: #872657;
            color: white;
            border-color: #872657;
        }

        .members-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        }

        .member-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .member-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(135, 38, 87, 0.15);
        }

        .member-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(135deg, #872657 0%, #b8336a 100%);
        }

        .member-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .member-avatar {
            position: relative;
        }

        .member-card img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #f8f9fa;
        }

        .online-indicator {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 16px;
            height: 16px;
            background: #28a745;
            border: 2px solid white;
            border-radius: 50%;
        }

        .member-info h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2b2b2b;
            margin-bottom: 0.25rem;
        }

        .member-pronouns {
            background: rgba(135, 38, 87, 0.1);
            color: #872657;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .member-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            font-size: 0.85rem;
            color: #6c757d;
        }

        .join-date {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .last-active {
            font-weight: 500;
        }

        .no-results {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .no-results i {
            font-size: 3rem;
            color: #e1e5e9;
            margin-bottom: 1rem;
        }

        .no-results h3 {
            font-size: 1.2rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }

        .no-results p {
            color: #adb5bd;
        }

        .clear-search {
            display: inline-block;
            margin-top: 1rem;
            padding: 8px 16px;
            background: #872657;
            color: white;
            text-decoration: none;
            border-radius: 20px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .clear-search:hover {
            background: #68212f;
            transform: translateY(-1px);
        }

        .loading-skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 8px;
        }

        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        .loading-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
        }
        .loading-line {
            height: 12px;
            margin: 8px 0;
        }
        .loading-line.short {
            width: 30%;
        }
        .loading-line.medium {
            width: 60%;
        }
        .loading-line.long {
            width: 90%;
        }
        .search-bar {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .search-bar input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #e1e5e9;
            border-radius: 30px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        .search-bar input:focus {
            border-color: #872657;
            outline: none;
            box-shadow: 0 0 5px rgba(135, 38, 87, 0.2);
        }
        .search-bar button {
            background: #872657;
            color: white;
            border: none;
            padding: 0 20px;
            border-radius: 30px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s ease;
        }
        .search-bar button:hover {
            background: #68212f;
        }
        .search-bar button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        /* Responsive adjustments */
        @media (max-width: 600px) {

            main { padding: 1rem; margin-left: 0; }
            .community-container { margin-left: 0; max-width: 100%; }
            .results-header { flex-direction: column; align-items: flex-start; }
            .members-grid { grid-template-columns: 1fr; }
            .search-bar { flex-direction: column; }
            .search-bar input, .search-bar button { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?> 
        <?php include 'topbar.php'; ?>   

        <!-- Main content -->
        <div class="community-container">
            <!-- Header Section -->
            
            

            <!-- Results Header -->
            <div class="results-header">
                <div>
                    <?php if ($q): ?>
                        <h2 class="results-title">
                            <i class="fas fa-search"></i> Search Results
                        </h2>
                        <p class="results-count">Found <?= count($members) ?> result<?= count($members) !== 1 ? 's' : '' ?> for "<?= htmlspecialchars($q) ?>"</p>
                    <?php else: ?>
                        <h2 class="results-title">
                            <i class="fas fa-users"></i> Community Members
                        </h2>
                        <p class="results-count">Showing <?= count($members) ?> of <?= $totalMembers ?> members</p>
                    <?php endif; ?>
                </div>
                
                <?php if ($q): ?>
                    <a href="?" class="clear-search">
                        <i class="fas fa-times"></i> Clear Search
                    </a>
                <?php endif; ?>
            </div>

            <!-- Members Grid -->
            <?php if (!empty($members)): ?>
                <div class="members-grid">
                    <?php foreach ($members as $row): ?>
                        <a href="user_profile.php?id=<?= urlencode($row['id']) ?>" style="text-decoration:none; color:inherit;">
                            <div class="member-card">
                                <div class="member-header">
                                    <div class="member-avatar">
                                        <img 
  src="<?= htmlspecialchars($pic, ENT_QUOTES) ?>" 
  alt="Profile" 
  class="nav-profile-pic" 
  onerror="this.onerror=null;this.src='uploads/default.jpeg';">

                                        <?php if (isset($row['last_active']) && strtotime($row['last_active']) > strtotime('-30 minutes')): ?>
                                            <div class="online-indicator" title="Online"></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="member-info">
                                        <h3><?= htmlspecialchars($row['name']) ?></h3>
                                        <?php if (!empty($row['pronouns'])): ?>
                                            <span class="member-pronouns"><?= htmlspecialchars($row['pronouns']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="member-meta">
                                    <div class="join-date">
                                        <i class="fas fa-calendar-plus"></i>
                                        Joined <?= isset($row['created_at']) ? timeAgo($row['created_at']) : 'Recently' ?>
                                    </div>
                                    <?php if (isset($row['last_active'])): ?>
                                        <div class="last-active">
                                            Active <?= timeAgo($row['last_active']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <?php if ($q): ?>
                        <h3>No results found</h3>
                        <p>We couldn't find any members matching "<?= htmlspecialchars($q) ?>"</p>
                        <a href="?" class="clear-search">
                            <i class="fas fa-users"></i> View All Members
                        </a>
                    <?php else: ?>
                        <h3>No members found</h3>
                        <p>The community is just getting started. Be among the first to join!</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Enhanced search with real-time feedback
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[name="q"]');
            const searchForm = document.querySelector('.search-bar');
            
            // Auto-focus search on page load if there's no query
            if (!searchInput.value) {
                searchInput.focus();
            }
            
            // Add loading state to search button
            searchForm.addEventListener('submit', function() {
                const button = this.querySelector('button');
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
                button.disabled = true;
                
                // Re-enable after a short delay (in case of quick redirects)
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                }, 2000);
            });
            
            // Smooth scroll to results on search
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('q')) {
                document.querySelector('.results-header')?.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'start' 
                });
            }
            
            // Add keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Focus search with Ctrl/Cmd + K
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    searchInput.focus();
                    searchInput.select();
                }
                
                // Clear search with Escape (when search is focused)
                if (e.key === 'Escape' && document.activeElement === searchInput) {
                    if (searchInput.value) {
                        searchInput.value = '';
                    } else {
                        searchInput.blur();
                    }
                }
            });
            
            // Add hover effects for member cards
            document.querySelectorAll('.member-card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-4px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(-2px)';
                });
            });
        });
    </script>
</body>
</html>