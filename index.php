<?php
require_once 'config.php';

// Get current user data
if (isLoggedIn()) {
    $user_id = getCurrentUserId();
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_result = $stmt->get_result();
    $current_user = $user_result->fetch_assoc();
    
    // Get user stats
    $stmt = $conn->prepare("
        SELECT 
            (SELECT COUNT(*) FROM tweets WHERE user_id = ?) as tweet_count,
            (SELECT COUNT(*) FROM follows WHERE follower_id = ?) as following_count,
            (SELECT COUNT(*) FROM follows WHERE following_id = ?) as followers_count
    ");
    $stmt->bind_param("iii", $user_id, $user_id, $user_id);
    $stmt->execute();
    $stats_result = $stmt->get_result();
    $stats = $stats_result->fetch_assoc();
    
    // Get tweets feed (tweets from users followed + own tweets)
    $page = $_GET['page'] ?? 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    $stmt = $conn->prepare("
        SELECT t.*, u.username, u.full_name, u.profile_pic,
               (SELECT COUNT(*) FROM likes WHERE tweet_id = t.id AND user_id = ?) as liked
        FROM tweets t
        JOIN users u ON t.user_id = u.id
        WHERE t.user_id = ? 
           OR t.user_id IN (SELECT following_id FROM follows WHERE follower_id = ?)
        ORDER BY t.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("iiiii", $user_id, $user_id, $user_id, $limit, $offset);
    $stmt->execute();
    $tweets_result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twitter Clone</title>
    <link rel="stylesheet" href="style.php">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <nav>
                <a href="index.php" class="logo">𝕏</a>
                <div class="nav-links">
                    <?php if (isLoggedIn()): ?>
                        <a href="index.php">Home</a>
                        <a href="profile.php">Profile</a>
                        <a href="logout.php">Logout</a>
                        <img src="<?php echo htmlspecialchars($current_user['profile_pic']); ?>" 
                             alt="Profile" class="profile-pic">
                    <?php else: ?>
                        <a href="login.php">Login</a>
                        <a href="register.php">Register</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <main class="container">
        <?php if (isLoggedIn()): ?>
            <!-- Left Sidebar -->
            <aside class="sidebar">
                <div class="user-card">
                    <div class="user-info">
                        <img src="<?php echo htmlspecialchars($current_user['profile_pic']); ?>" 
                             alt="<?php echo htmlspecialchars($current_user['full_name']); ?>">
                        <div>
                            <h3><?php echo htmlspecialchars($current_user['full_name']); ?></h3>
                            <p>@<?php echo htmlspecialchars($current_user['username']); ?></p>
                        </div>
                    </div>
                    <p class="bio"><?php echo htmlspecialchars($current_user['bio'] ?? 'No bio yet'); ?></p>
                    <div class="stats">
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
            </aside>

            <!-- Main Content -->
            <section class="main-content">
                <!-- Tweet Form -->
                <div class="tweet-form">
                    <form id="createTweetForm" onsubmit="return false;">
                        <textarea id="tweetContent" 
                                  placeholder="What's happening?" 
                                  maxlength="280"></textarea>
                        <div id="imagePreview"></div>
                        <div class="tweet-actions">
                            <div>
                                <input type="file" id="tweetImage" accept="image/*" 
                                       style="display: none;" onchange="previewImage(this)">
                                <button type="button" onclick="document.getElementById('tweetImage').click()"
                                        class="action-btn">📷</button>
                                <span id="charCount">0/280</span>
                            </div>
                            <button type="button" onclick="postTweet()" class="tweet-btn">Tweet</button>
                        </div>
                    </form>
                </div>

                <!-- Tweet Feed -->
                <div class="tweet-feed" id="tweetFeed">
                    <?php while ($tweet = $tweets_result->fetch_assoc()): ?>
                        <div class="tweet" id="tweet<?php echo $tweet['id']; ?>">
                            <div class="tweet-header">
                                <div class="tweet-user">
                                    <img src="<?php echo htmlspecialchars($tweet['profile_pic']); ?>" 
                                         alt="<?php echo htmlspecialchars($tweet['full_name']); ?>">
                                    <div class="tweet-info">
                                        <h4><?php echo htmlspecialchars($tweet['full_name']); ?></h4>
                                        <span>@<?php echo htmlspecialchars($tweet['username']); ?> · 
                                            <?php echo date('M j, Y', strtotime($tweet['created_at'])); ?></span>
                                    </div>
                                </div>
                                <?php if ($tweet['user_id'] == $user_id): ?>
                                    <div class="tweet-actions-dropdown">
                                        <button onclick="editTweet(<?php echo $tweet['id']; ?>)" 
                                                class="action-btn">✏️ Edit</button>
                                        <button onclick="deleteTweet(<?php echo $tweet['id']; ?>)" 
                                                class="action-btn">🗑️ Delete</button>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="tweet-content" id="tweetContent<?php echo $tweet['id']; ?>">
                                <?php echo nl2br(htmlspecialchars($tweet['content'])); ?>
                            </div>
                            <?php if ($tweet['image']): ?>
                                <img src="<?php echo htmlspecialchars($tweet['image']); ?>" 
                                     alt="Tweet image" class="tweet-image">
                            <?php endif; ?>
                            <div class="tweet-actions">
                                <button onclick="likeTweet(<?php echo $tweet['id']; ?>)" 
                                        class="action-btn" id="likeBtn<?php echo $tweet['id']; ?>">
                                    <?php echo $tweet['liked'] ? '❤️ Liked' : '🤍 Like'; ?>
                                    <span id="likeCount<?php echo $tweet['id']; ?>">
                                        <?php echo $tweet['likes_count']; ?>
                                    </span>
                                </button>
                                <button class="action-btn">🔄 
                                    <span><?php echo $tweet['retweets_count']; ?></span>
                                </button>
                                <?php if ($tweet['user_id'] != $user_id): ?>
                                    <button onclick="followUser(<?php echo $tweet['user_id']; ?>)" 
                                            class="action-btn" id="followBtn<?php echo $tweet['user_id']; ?>">
                                        Follow
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>

            <!-- Right Sidebar -->
            <aside class="right-sidebar">
                <div class="trending">
                    <h3>Who to follow</h3>
                    <?php
                    // Get users to follow (not following and not self)
                    $stmt = $conn->prepare("
                        SELECT u.id, u.username, u.full_name, u.profile_pic, u.bio
                        FROM users u
                        WHERE u.id != ? 
                          AND u.id NOT IN (SELECT following_id FROM follows WHERE follower_id = ?)
                        ORDER BY RAND()
                        LIMIT 5
                    ");
                    $stmt->bind_param("ii", $user_id, $user_id);
                    $stmt->execute();
                    $suggestions_result = $stmt->get_result();
                    
                    while ($user = $suggestions_result->fetch_assoc()):
                    ?>
                        <div class="trending-item">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" 
                                     alt="<?php echo htmlspecialchars($user['full_name']); ?>" 
                                     style="width: 40px; height: 40px; border-radius: 50%;">
                                <div>
                                    <strong><?php echo htmlspecialchars($user['full_name']); ?></strong><br>
                                    <span>@<?php echo htmlspecialchars($user['username']); ?></span>
                                </div>
                            </div>
                            <button onclick="followUser(<?php echo $user['id']; ?>)" 
                                    class="tweet-btn" style="width: 100%; padding: 8px;">
                                Follow
                            </button>
                        </div>
                    <?php endwhile; ?>
                </div>
            </aside>

        <?php else: ?>
            <!-- Landing page for non-logged in users -->
            <div style="text-align: center; padding: 100px 20px;">
                <h1 style="font-size: 48px; margin-bottom: 20px;">𝕏</h1>
                <h2 style="font-size: 36px; margin-bottom: 40px;">Happening now</h2>
                <div style="max-width: 300px; margin: 0 auto;">
                    <h3 style="margin-bottom: 20px;">Join today.</h3>
                    <a href="register.php" class="btn" style="display: block; margin-bottom: 15px;">Create account</a>
                    <p style="margin-bottom: 20px; color: #8899a6;">Already have an account?</p>
                    <a href="login.php" class="btn" style="background-color: transparent; border: 1px solid #1da1f2; color: #1da1f2;">Sign in</a>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- Edit Tweet Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Tweet</h3>
                <button onclick="closeModal()" class="close-btn">&times;</button>
            </div>
            <form id="editTweetForm" onsubmit="return false;">
                <input type="hidden" id="editTweetId">
                <div class="form-group">
                    <textarea id="editContent" maxlength="280"></textarea>
                    <span id="editCharCount">0/280</span>
                </div>
                <button type="button" onclick="submitEdit()" class="btn">Update Tweet</button>
            </form>
        </div>
    </div>

    <script src="script.php"></script>
    <script>
    function submitEdit() {
        const tweetId = document.getElementById('editTweetId').value;
        const content = document.getElementById('editContent').value;
        
        fetch('tweet_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=edit_tweet&tweet_id=${tweetId}&content=${encodeURIComponent(content)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeModal();
                location.reload();
            } else {
                alert(data.message || 'Error updating tweet');
            }
        });
    }
    
    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" style="max-width: 200px; border-radius: 10px; margin-top: 10px;">`;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>
</body>
</html>
