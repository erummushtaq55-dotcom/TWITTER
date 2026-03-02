<?php
header("Content-Type: application/javascript");
?>

// Tweet functionality
function postTweet() {
    const content = document.getElementById('tweetContent').value;
    const formData = new FormData();
    formData.append('content', content);
    formData.append('action', 'create_tweet');
    
    // Handle image upload if any
    const imageInput = document.getElementById('tweetImage');
    if (imageInput.files[0]) {
        formData.append('image', imageInput.files[0]);
    }
    
    fetch('tweet_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('tweetContent').value = '';
            document.getElementById('tweetImage').value = '';
            location.reload();
        } else {
            alert(data.message || 'Error posting tweet');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}

// Edit tweet
function editTweet(tweetId) {
    const modal = document.getElementById('editModal');
    const content = document.getElementById('tweetContent' + tweetId).innerText;
    
    document.getElementById('editTweetId').value = tweetId;
    document.getElementById('editContent').value = content;
    modal.style.display = 'block';
}

// Delete tweet
function deleteTweet(tweetId) {
    if (confirm('Are you sure you want to delete this tweet?')) {
        fetch('tweet_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=delete_tweet&tweet_id=${tweetId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error deleting tweet');
            }
        });
    }
}

// Like tweet
function likeTweet(tweetId) {
    fetch('tweet_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=like_tweet&tweet_id=${tweetId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const likeBtn = document.getElementById('likeBtn' + tweetId);
            const likeCount = document.getElementById('likeCount' + tweetId);
            
            if (data.liked) {
                likeBtn.innerHTML = '❤️ Liked';
                likeCount.textContent = parseInt(likeCount.textContent) + 1;
            } else {
                likeBtn.innerHTML = '🤍 Like';
                likeCount.textContent = parseInt(likeCount.textContent) - 1;
            }
        }
    });
}

// Follow user
function followUser(userId) {
    fetch('follow_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=follow&user_id=${userId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const followBtn = document.getElementById('followBtn' + userId);
            if (data.following) {
                followBtn.textContent = 'Following';
                followBtn.style.backgroundColor = '#38444d';
            } else {
                followBtn.textContent = 'Follow';
                followBtn.style.backgroundColor = '#1da1f2';
            }
        }
    });
}

// Close modal
function closeModal() {
    const modals = document.getElementsByClassName('modal');
    for (let modal of modals) {
        modal.style.display = 'none';
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modals = document.getElementsByClassName('modal');
    for (let modal of modals) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
}

// Update character count
function updateCharCount() {
    const textarea = document.getElementById('tweetContent');
    const charCount = document.getElementById('charCount');
    const maxLength = 280;
    const currentLength = textarea.value.length;
    
    charCount.textContent = `${currentLength}/${maxLength}`;
    
    if (currentLength > maxLength) {
        charCount.style.color = '#e0245e';
    } else if (currentLength > 260) {
        charCount.style.color = '#ffad1f';
    } else {
        charCount.style.color = '#8899a6';
    }
}

// Image preview for tweet
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" style="max-width: 200px; border-radius: 10px; margin-top: 10px;">`;
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.innerHTML = '';
    }
}

// Load more tweets
let page = 1;
function loadMoreTweets() {
    page++;
    fetch(`tweet_handler.php?action=load_more&page=${page}`)
    .then(response => response.json())
    .then(data => {
        if (data.tweets) {
            const feed = document.getElementById('tweetFeed');
            data.tweets.forEach(tweet => {
                feed.innerHTML += createTweetHTML(tweet);
            });
        }
    });
}

// Create tweet HTML (simplified version)
function createTweetHTML(tweet) {
    return `
        <div class="tweet" id="tweet${tweet.id}">
            <!-- Tweet content here -->
        </div>
    `;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Update tweet character count
    const tweetContent = document.getElementById('tweetContent');
    if (tweetContent) {
        tweetContent.addEventListener('input', updateCharCount);
        updateCharCount();
    }
    
    // Infinite scroll
    window.addEventListener('scroll', function() {
        if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 100) {
            loadMoreTweets();
        }
    });
});
