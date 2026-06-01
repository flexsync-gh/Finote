<?php

function start_secure_session()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

start_secure_session();

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function app_base_path()
{
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));

    if (preg_match('#/transactions$#', $dir)) {
        $dir = dirname($dir);
    }

    $dir = rtrim($dir, '/');
    return $dir === '' ? '' : $dir;
}

function app_url($path = '')
{
    $base = app_base_path();
    $path = ltrim((string) $path, '/');

    return $base . ($path === '' ? '/' : '/' . $path);
}

function redirect($path)
{
    header('Location: ' . $path);
    exit();
}

function is_logged_in()
{
    return !empty($_SESSION['user_id']);
}

function require_login()
{
    if (!is_logged_in()) {
        flash('error', 'Please login to continue.');
        redirect(app_url('login.php'));
    }
}

function redirect_if_logged_in()
{
    if (is_logged_in()) {
        redirect(app_url('dashboard.php'));
    }
}

function flash($type, $message)
{
    $_SESSION['flash'][$type][] = $message;
}

function get_flashes()
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf()
{
    $token = $_POST['csrf_token'] ?? '';
    return is_string($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function bind_params($stmt, $types, $params)
{
    if ($types === '') {
        return;
    }

    $refs = [];
    foreach ($params as $key => $value) {
        $refs[$key] = &$params[$key];
    }

    array_unshift($refs, $types);
    call_user_func_array([$stmt, 'bind_param'], $refs);
}

function db_fetch_all($conn, $sql, $types = '', $params = [])
{
    $stmt = mysqli_prepare($conn, $sql);
    bind_params($stmt, $types, $params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function db_fetch_one($conn, $sql, $types = '', $params = [])
{
    $rows = db_fetch_all($conn, $sql, $types, $params);
    return $rows[0] ?? null;
}

function db_execute($conn, $sql, $types = '', $params = [])
{
    $stmt = mysqli_prepare($conn, $sql);
    bind_params($stmt, $types, $params);
    return mysqli_stmt_execute($stmt);
}

function current_user($conn)
{
    if (!is_logged_in()) {
        return null;
    }

    return db_fetch_one(
        $conn,
        'SELECT id, name, email, phonenumber, profile_photo, created_at, updated_at FROM users WHERE id = ?',
        'i',
        [$_SESSION['user_id']]
    );
}

function money($amount)
{
    return 'Rp ' . number_format((float) $amount, 0, ',', '.');
}

function format_date($date)
{
    if (!$date) {
        return '-';
    }

    return date('d M Y', strtotime($date));
}

function pagination_url($page)
{
    $query = $_GET;
    $query['page'] = max(1, (int) $page);
    return '?' . http_build_query($query);
}

function highlight_keyword($text, $keyword)
{
    $safe = e($text);
    $keyword = trim((string) $keyword);

    if ($keyword === '') {
        return $safe;
    }

    return preg_replace('/(' . preg_quote(e($keyword), '/') . ')/i', '<mark>$1</mark>', $safe);
}

function ensure_user_starter_data($conn, $userId)
{
    $account = db_fetch_one($conn, 'SELECT id FROM accounts WHERE userid = ? LIMIT 1', 'i', [$userId]);
    if (!$account) {
        db_execute($conn, 'INSERT INTO accounts (userid, name, type, balance) VALUES (?, ?, ?, ?)', 'issd', [$userId, 'Dompet', 'cash', 0]);
    }

    $category = db_fetch_one($conn, 'SELECT id FROM categories WHERE userid = ? LIMIT 1', 'i', [$userId]);
    if (!$category) {
        db_execute($conn, 'INSERT INTO categories (userid, name, type) VALUES (?, ?, ?)', 'iss', [$userId, 'Income', 'income']);
        db_execute($conn, 'INSERT INTO categories (userid, name, type) VALUES (?, ?, ?)', 'iss', [$userId, 'General Expense', 'expense']);
    }
}

function profile_photo_url($user)
{
    if (!empty($user['profile_photo'])) {
        return app_url('uploads/profile/' . rawurlencode($user['profile_photo']));
    }

    return 'https://ui-avatars.com/api/?name=' . rawurlencode($user['name'] ?? 'Finote User') . '&background=5b3be0&color=fff&size=160';
}

function validate_profile_upload($file)
{
    if (empty($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [false, 'Please choose an image file.'];
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return [false, 'The upload failed. Please try again.'];
    }

    if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        return [false, 'Profile photo must be 2MB or smaller.'];
    }

    $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($extension, $allowed, true)) {
        return [false, 'Only JPG, PNG, and WEBP images are allowed.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowedMimes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
    ];

    if (($allowedMimes[$extension] ?? '') !== $mime) {
        return [false, 'The selected file is not a valid image.'];
    }

    return [true, $extension];
}

function delete_profile_photo($fileName)
{
    if (!$fileName) {
        return;
    }

    $path = dirname(__DIR__) . '/uploads/profile/' . basename($fileName);
    if (is_file($path)) {
        unlink($path);
    }
}
