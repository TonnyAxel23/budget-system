<?php
require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function loginUser($user_id, $username) {
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['last_activity'] = time();
}

function logoutUser() {
    session_unset();
    session_destroy();
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}
?>