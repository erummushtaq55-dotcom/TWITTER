<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = getCurrentUserId();

// Get profile user data
$profile_user_id = $_GET['id'] ?? $user_id;
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $profile_user_id);
$stmt->execute();
$result = $stmt->get_result();
$profile_user = $result->fetch_assoc();

if (!$profile_user) {
    die("User not found");
}

// Get user stats
$stmt = $conn->prepare("
    SELECT 
        (SELECT COUNT(*) FROM tweets WHERE user_id = ?) as tweet_count,
        (SELECT COUNT(*) FROM follows WHERE follower_id = ?) as following_count,
        (SELECT COUNT(*) FROM follows WHERE following_id = ?) as followers_count,
        (SELECT COUNT(*) FROM follows WHERE follower_id = ? AND following_id = ?) as is_following
");
$stmt->bind_param("iiiii", $profile_user_id, $profile_user_id, $profile_user_id, $user_id, $profile_user_id);
$stmt->execute();
$stats_result = $stmt->get_result();
$stats = $stats_result->fetch_assoc();

// Get user's tweets
$stmt = $conn->prepare("
    SELECT t.*, u.username, u.full_name, u.profile_pic,
           (SELECT COUNT(*) FROM likes WHERE tweet_id = t.id AND user_id = ?) as liked
    FROM tweets t
    JOIN users u ON t.user_id = u.id
    WHERE t.user_id = ?
    ORDER BY t.created_at DESC
");
$stmt->bind_param("ii", $user_id, $profile_user_id);
$stmt->execute();
$tweets_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($profile_user['full_name']); ?> - Twitter Clone</title>
    <link rel="stylesheet" href="style.php">
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <a href="index.php" class="logo">𝕏</a>
                <div class="nav-links">
                    <a href="index.php">Home</a>
                    <a href="profile.php">Profile</a>
                    <a href="logout.php">Logout</a>
                    <img src="<?php echo htmlspecialchars($current_user['profile_pic']); ?>" 
                         alt="Profile" class="profile-pic">
                </div>
            </nav>
        </div>
    </header>

    <main class="container">
        <!-- Profile Header -->
        <section style="grid-column: 1 / -1; margin-bottom: 20px;">
            <div style="background-color: #1e2732; border-radius: 15px; padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; gap: 20px;">
                        <img src="<?php echo htmlspecialchars($profile_user['profile_pic']); ?>" 
                             alt="<?php echo htmlspecialchars($profile_user['full_name']); ?>"
                             style="width: 100px; height: 100px; border-radius: 50%;">
                        <div>
                            <h2><?php echo htmlspecialchars($profile_user['full_name']); ?></h2>
                            <p style="color: #8899a6;">@<?php echo htmlspecialchars($profile_user['username']); ?></p>
                            <p><?php echo htmlspecialchars($profile_user['bio'] ?? 'No bio yet'); ?></p>
                        </div>
                    </div>
                    
                    <?php if ($profile_user_id != $user_id): ?>
                        <button onclick="followUser(<?php echo $profile_user_id; ?>)" 
                                class="tweet-btn" id="followBtn<?php echo $profile_user_id; ?>"
                                style="width: 100px;">
                            <?php echo $stats['is_following'] ? 'Following' : 'Follow'; ?>
                        </button>
                    <?php endif; ?>
                </div>
                
                <div style="display: flex; gap: 30px;">
                    <div class="stat">
                        <div class="stat-number"><?php echo $stats['tweet_count']; ?></div>
                        <div class="stat-label">Tweets</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number"><?php echo $stats['following_count']; ?></div>
                        <div class="stat-label">Following</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number"><?php echo $stats['followers_count']; ?></div>
                        <div class="stat-label">Followers</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- User's Tweets -->
        <section class="main-content">
            <h3 style="margin-bottom: 20px;">Tweets</h3>
            <div class="tweet-feed">
                <?php while ($tweet = $tweets_result->fetch_assoc()): ?>
                    <div class="tweet">
                        <!-- Tweet structure same as index.php -->
                        <?php include 'tweet_template.php'; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>
    </main>

    <script src="script.php"></script>
</body>
</html>
