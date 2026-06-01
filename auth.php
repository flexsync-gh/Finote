<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/functions.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirect_if_logged_in();
    return;
}

if (!verify_csrf()) {
    $message = "Your session expired. Please try again.";
    return;
}

$action = $_POST['action'] ?? '';

if ($action === "register") {
    $username = trim($_POST['username'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $phone === '' || $email === '' || $password === '') {
        $message = "Please fill in all fields.";
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        return;
    }

    if (strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
        return;
    }

    $existing = db_fetch_one(
        $conn,
        "SELECT id FROM users WHERE email = ? OR name = ? OR phonenumber = ? LIMIT 1",
        "sss",
        [$email, $username, $phone]
    );

    if ($existing) {
        $message = "Account already exists.";
        return;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    db_execute(
        $conn,
        "INSERT INTO users (name, email, password, phonenumber) VALUES (?, ?, ?, ?)",
        "ssss",
        [$username, $email, $hashedPassword, $phone]
    );

    $userId = mysqli_insert_id($conn);
    ensure_user_starter_data($conn, $userId);

    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['user'] = $username;
    flash('success', 'Welcome to Finote.');
    redirect(app_url('dashboard.php'));
}

if ($action === "login" || $action === "loginphone") {
    $identifier = trim($action === "login" ? ($_POST['email'] ?? '') : ($_POST['phone'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($identifier === '' || $password === '') {
        $message = "Please enter your login details.";
        return;
    }

    if ($action === "login") {
        $user = db_fetch_one($conn, "SELECT * FROM users WHERE email = ? LIMIT 1", "s", [$identifier]);
    } else {
        $user = db_fetch_one($conn, "SELECT * FROM users WHERE phonenumber = ? LIMIT 1", "s", [$identifier]);
    }

    if (!$user) {
        $message = "Invalid login details.";
        return;
    }

    $storedPassword = $user['password'];
    $isValidPassword = password_verify($password, $storedPassword);

    $passwordInfo = password_get_info($storedPassword);
    if (!$isValidPassword && empty($passwordInfo['algo']) && hash_equals($storedPassword, $password)) {
        $isValidPassword = true;
        db_execute(
            $conn,
            "UPDATE users SET password = ? WHERE id = ?",
            "si",
            [password_hash($password, PASSWORD_DEFAULT), $user['id']]
        );
    }

    if (!$isValidPassword) {
        $message = "Invalid login details.";
        return;
    }

    ensure_user_starter_data($conn, (int) $user['id']);

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user'] = $user['name'];
    flash('success', 'Welcome back, ' . $user['name'] . '.');
    redirect(app_url('dashboard.php'));
}

$message = "Unsupported action.";
