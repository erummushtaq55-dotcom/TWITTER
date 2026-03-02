<?php
header("Content-Type: text/css");
?>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
    background-color: #15202b;
    color: #ffffff;
    line-height: 1.6;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;
    display: grid;
    grid-template-columns: 1fr 2fr 1fr;
    gap: 20px;
}

/* Header */
header {
    background-color: #1e2732;
    border-bottom: 1px solid #38444d;
    position: sticky;
    top: 0;
    z-index: 100;
}

nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
}

.logo {
    font-size: 24px;
    font-weight: bold;
    color: #1da1f2;
    text-decoration: none;
}

.nav-links {
    display: flex;
    gap: 20px;
    align-items: center;
}

.nav-links a {
    color: #ffffff;
    text-decoration: none;
    padding: 8px 16px;
    border-radius: 20px;
    transition: background-color 0.3s;
}

.nav-links a:hover {
    background-color: #1da1f2;
}

.profile-pic {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

/* Sidebar */
.sidebar {
    position: sticky;
    top: 80px;
    height: fit-content;
}

.user-card {
    background-color: #1e2732;
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 20px;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}

.user-info img {
    width: 60px;
    height: 60px;
    border-radius: 50%;
}

.stats {
    display: flex;
    gap: 20px;
    margin-top: 15px;
}

.stat {
    text-align: center;
}

.stat-number {
    font-weight: bold;
    font-size: 18px;
}

.stat-label {
    font-size: 14px;
    color: #8899a6;
}

/* Tweet Form */
.tweet-form {
    background-color: #1e2732;
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 20px;
}

.tweet-form textarea {
    width: 100%;
    background-color: transparent;
    border: none;
    color: #ffffff;
    font-size: 20px;
    resize: none;
    min-height: 100px;
    margin-bottom: 15px;
}

.tweet-form textarea:focus {
    outline: none;
}

.tweet-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.tweet-btn {
    background-color: #1da1f2;
    color: white;
    border: none;
    padding: 10px 24px;
    border-radius: 20px;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s;
}

.tweet-btn:hover {
    background-color: #0d8bd9;
}

/* Tweet Feed */
.tweet-feed {
    background-color: #1e2732;
    border-radius: 15px;
    overflow: hidden;
}

.tweet {
    padding: 20px;
    border-bottom: 1px solid #38444d;
    transition: background-color 0.3s;
}

.tweet:hover {
    background-color: #192734;
}

.tweet-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.tweet-user {
    display: flex;
    align-items: center;
    gap: 10px;
}

.tweet-user img {
    width: 48px;
    height: 48px;
    border-radius: 50%;
}

.tweet-info h4 {
    margin: 0;
}

.tweet-info span {
    color: #8899a6;
    font-size: 14px;
}

.tweet-content {
    margin: 15px 0;
    font-size: 18px;
}

.tweet-image {
    width: 100%;
    max-height: 400px;
    object-fit: cover;
    border-radius: 15px;
    margin-top: 15px;
}

.tweet-actions {
    display: flex;
    gap: 80px;
    margin-top: 15px;
}

.action-btn {
    background: none;
    border: none;
    color: #8899a6;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: color 0.3s;
}

.action-btn:hover {
    color: #1da1f2;
}

/* Right Sidebar */
.trending {
    background-color: #1e2732;
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 20px;
}

.trending h3 {
    margin-bottom: 15px;
}

.trending-item {
    padding: 10px 0;
    border-bottom: 1px solid #38444d;
}

.trending-item:last-child {
    border-bottom: none;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
    z-index: 1000;
}

.modal-content {
    background-color: #1e2732;
    margin: 50px auto;
    padding: 30px;
    border-radius: 15px;
    width: 90%;
    max-width: 500px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.close-btn {
    background: none;
    border: none;
    color: #8899a6;
    font-size: 24px;
    cursor: pointer;
}

/* Forms */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    color: #8899a6;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 10px;
    background-color: #38444d;
    border: 1px solid #38444d;
    border-radius: 5px;
    color: #ffffff;
}

.form-group textarea {
    min-height: 100px;
    resize: vertical;
}

.btn {
    background-color: #1da1f2;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 20px;
    font-weight: bold;
    cursor: pointer;
    width: 100%;
    transition: background-color 0.3s;
}

.btn:hover {
    background-color: #0d8bd9;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .container {
        grid-template-columns: 1fr 2fr;
    }
    
    .right-sidebar {
        display: none;
    }
}

@media (max-width: 768px) {
    .container {
        grid-template-columns: 1fr;
    }
    
    .sidebar {
        display: none;
    }
    
    .nav-links {
        flex-direction: column;
        gap: 10px;
    }
    
    .tweet-actions {
        gap: 40px;
    }
}

@media (max-width: 480px) {
    .tweet-actions {
        gap: 20px;
        flex-wrap: wrap;
    }
    
    .modal-content {
        margin: 20px auto;
        padding: 20px;
    }
}

/* Loading spinner */
.spinner {
    border: 3px solid #38444d;
    border-top: 3px solid #1da1f2;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin: 20px auto;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Success/Error messages */
.alert {
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.alert-success {
    background-color: #0c3;
    color: white;
}

.alert-error {
    background-color: #e0245e;
    color: white;
}
