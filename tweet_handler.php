<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$user_id = getCurrentUserId();

switch ($action) {
    case 'create_tweet':
        $content = trim($_POST['content'] ?? '');
        
        if (empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Tweet content cannot be empty']);
            exit();
        }
        
        if (strlen($content) > 280) {
            echo json_encode(['success' => false, 'message' => 'Tweet is too long (max 280 characters)']);
            exit();
        }
        
        // Handle image upload
        $image_path = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = $_FILES['image']['type'];
            
            if (in_array($file_type, $allowed_types)) {
                $upload_dir = 'uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $file_extension;
                $destination = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                    $image_path = $destination;
                }
            }
        }
        
        $stmt = $conn->prepare("INSERT INTO tweets (user_id, content, image) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $content, $image_path);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Tweet posted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error posting tweet']);
        }
        break;
        
    case 'edit_tweet':
        $tweet_id = $_POST['tweet_id'] ?? 0;
        $content = trim($_POST['content'] ?? '');
        
        // Check if user owns the tweet
        $stmt = $conn->prepare("SELECT user_id FROM tweets WHERE id = ?");
        $stmt->bind_param("i", $tweet_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $tweet = $result->fetch_assoc();
        
        if ($tweet && $tweet['user_id'] == $user_id) {
            $stmt = $conn->prepare("UPDATE tweets SET content = ? WHERE id = ?");
            $stmt->bind_param("si", $content, $tweet_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Tweet updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating tweet']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        }
        break;
        
    case 'delete_tweet':
        $tweet_id = $_POST['tweet_id'] ?? 0;
        
        // Check if user owns the tweet
        $stmt = $conn->prepare("SELECT user_id FROM tweets WHERE id = ?");
        $stmt->bind_param("i", $tweet_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $tweet = $result->fetch_assoc();
        
        if ($tweet && $tweet['user_id'] == $user_id) {
            // Delete tweet image if exists
            $stmt = $conn->prepare("SELECT image FROM tweets WHERE id = ?");
            $stmt->bind_param("i", $tweet_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $tweet_data = $result->fetch_assoc();
            
            if ($tweet_data['image'] && file_exists($tweet_data['image'])) {
                unlink($tweet_data['image']);
            }
            
            $stmt = $conn->prepare("DELETE FROM tweets WHERE id = ?");
            $stmt->bind_param("i", $tweet_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Tweet deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error deleting tweet']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        }
        break;
        
    case 'like_tweet':
        $tweet_id = $_POST['tweet_id'] ?? 0;
        
        // Check if already liked
        $stmt = $conn->prepare("SELECT id FROM likes WHERE user_id = ? AND tweet_id = ?");
        $stmt->bind_param("ii", $user_id, $tweet_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Unlike
            $stmt = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND tweet_id = ?");
            $stmt->bind_param("ii", $user_id, $tweet_id);
            $stmt->execute();
            
            // Update likes count
            $stmt = $conn->prepare("UPDATE tweets SET likes_count = likes_count - 1 WHERE id = ?");
            $stmt->bind_param("i", $tweet_id);
            $stmt->execute();
            
            echo json_encode(['success' => true, 'liked' => false]);
        } else {
            // Like
            $stmt = $conn->prepare("INSERT INTO likes (user_id, tweet_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $user_id, $tweet_id);
            $stmt->execute();
            
            // Update likes count
            $stmt = $conn->prepare("UPDATE tweets SET likes_count = likes_count + 1 WHERE id = ?");
            $stmt->bind_param("i", $tweet_id);
            $stmt->execute();
            
            echo json_encode(['success' => true, 'liked' => true]);
        }
        break;
        
    case 'load_more':
        $page = $_GET['page'] ?? 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        // Get tweets from users the current user follows, plus their own tweets
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
        $result = $stmt->get_result();
        
        $tweets = [];
        while ($row = $result->fetch_assoc()) {
            $tweets[] = $row;
        }
        
        echo json_encode(['success' => true, 'tweets' => $tweets]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
