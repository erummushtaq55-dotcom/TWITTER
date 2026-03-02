<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$action = $_POST['action'] ?? '';
$current_user_id = getCurrentUserId();
$target_user_id = $_POST['user_id'] ?? 0;

switch ($action) {
    case 'follow':
        // Check if not trying to follow self
        if ($current_user_id == $target_user_id) {
            echo json_encode(['success' => false, 'message' => 'Cannot follow yourself']);
            exit();
        }
        
        // Check if already following
        $stmt = $conn->prepare("SELECT id FROM follows WHERE follower_id = ? AND following_id = ?");
        $stmt->bind_param("ii", $current_user_id, $target_user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Unfollow
            $stmt = $conn->prepare("DELETE FROM follows WHERE follower_id = ? AND following_id = ?");
            $stmt->bind_param("ii", $current_user_id, $target_user_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'following' => false, 'message' => 'Unfollowed successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error unfollowing']);
            }
        } else {
            // Follow
            $stmt = $conn->prepare("INSERT INTO follows (follower_id, following_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $current_user_id, $target_user_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'following' => true, 'message' => 'Followed successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error following']);
            }
        }
        break;
        
    case 'get_followers':
        $user_id = $_GET['user_id'] ?? $current_user_id;
        
        $stmt = $conn->prepare("
            SELECT u.id, u.username, u.full_name, u.profile_pic,
                   (SELECT COUNT(*) FROM follows WHERE follower_id = ? AND following_id = u.id) as is_following
            FROM follows f
            JOIN users u ON f.follower_id = u.id
            WHERE f.following_id = ?
            ORDER BY f.created_at DESC
        ");
        $stmt->bind_param("ii", $current_user_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $followers = [];
        while ($row = $result->fetch_assoc()) {
            $followers[] = $row;
        }
        
        echo json_encode(['success' => true, 'followers' => $followers]);
        break;
        
    case 'get_following':
        $user_id = $_GET['user_id'] ?? $current_user_id;
        
        $stmt = $conn->prepare("
            SELECT u.id, u.username, u.full_name, u.profile_pic,
                   (SELECT COUNT(*) FROM follows WHERE follower_id = ? AND following_id = u.id) as is_following
            FROM follows f
            JOIN users u ON f.following_id = u.id
            WHERE f.follower_id = ?
            ORDER BY f.created_at DESC
        ");
        $stmt->bind_param("ii", $current_user_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $following = [];
        while ($row = $result->fetch_assoc()) {
            $following[] = $row;
        }
        
        echo json_encode(['success' => true, 'following' => $following]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
