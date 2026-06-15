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

    if (preg_match('#/(transactions|accounts|categories|budgets|savings)$#', $dir)) {
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

function current_user_id()
{
    return (int) ($_SESSION['user_id'] ?? 0);
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

function account_types()
{
    return [
        'cash' => 'Cash',
        'bank' => 'Bank',
        'ewallet' => 'E-Wallet',
    ];
}

function account_type_label($type)
{
    $types = account_types();
    return $types[$type] ?? ucfirst((string) $type);
}

function normalize_decimal($value)
{
    return (float) str_replace(',', '.', trim((string) $value));
}

function format_date($date)
{
    if (!$date) {
        return '-';
    }

    return date('d M Y', strtotime($date));
}

function valid_app_date($date)
{
    $parsed = DateTime::createFromFormat('Y-m-d', (string) $date);
    return $parsed && $parsed->format('Y-m-d') === $date;
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

function account_limit()
{
    return 20;
}

function category_limit()
{
    return 50;
}

function text_length($value)
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

function account_count($conn, $userId)
{
    $row = db_fetch_one($conn, 'SELECT COUNT(*) AS total FROM accounts WHERE userid = ?', 'i', [$userId]);
    return (int) ($row['total'] ?? 0);
}

function category_count($conn, $userId)
{
    $row = db_fetch_one($conn, 'SELECT COUNT(*) AS total FROM categories WHERE userid = ?', 'i', [$userId]);
    return (int) ($row['total'] ?? 0);
}

function find_account($conn, $userId, $accountId)
{
    return db_fetch_one(
        $conn,
        'SELECT id, userid, name, type, balance FROM accounts WHERE id = ? AND userid = ? LIMIT 1',
        'ii',
        [$accountId, $userId]
    );
}

function find_category($conn, $userId, $categoryId)
{
    return db_fetch_one(
        $conn,
        'SELECT id, userid, name, type FROM categories WHERE id = ? AND userid = ? LIMIT 1',
        'ii',
        [$categoryId, $userId]
    );
}

function account_name_exists($conn, $userId, $name, $excludeId = 0)
{
    $sql = 'SELECT id FROM accounts WHERE userid = ? AND LOWER(name) = LOWER(?)';
    $types = 'is';
    $params = [$userId, $name];

    if ($excludeId > 0) {
        $sql .= ' AND id <> ?';
        $types .= 'i';
        $params[] = $excludeId;
    }

    $sql .= ' LIMIT 1';
    return (bool) db_fetch_one($conn, $sql, $types, $params);
}

function category_name_exists($conn, $userId, $name, $type, $excludeId = 0)
{
    $sql = 'SELECT id FROM categories WHERE userid = ? AND type = ? AND LOWER(name) = LOWER(?)';
    $types = 'iss';
    $params = [$userId, $type, $name];

    if ($excludeId > 0) {
        $sql .= ' AND id <> ?';
        $types .= 'i';
        $params[] = $excludeId;
    }

    $sql .= ' LIMIT 1';
    return (bool) db_fetch_one($conn, $sql, $types, $params);
}

function validate_account_name($conn, $userId, $name, $excludeId = 0)
{
    $errors = [];

    if ($name === '') {
        $errors[] = 'Account name is required.';
    } elseif (text_length($name) > 100) {
        $errors[] = 'Account name must be 100 characters or fewer.';
    } elseif (account_name_exists($conn, $userId, $name, $excludeId)) {
        $errors[] = 'You already have an account with that name.';
    }

    return $errors;
}

function validate_account_input($conn, $userId, $name, $type, $balance, $excludeId = 0)
{
    $errors = validate_account_name($conn, $userId, $name, $excludeId);

    if (!array_key_exists($type, account_types())) {
        $errors[] = 'Account type must be cash, bank, or ewallet.';
    }

    $balanceText = trim((string) $balance);
    $normalizedBalance = str_replace(',', '.', $balanceText);

    if ($balanceText === '') {
        $errors[] = 'Account balance is required.';
    } elseif (!is_numeric($normalizedBalance)) {
        $errors[] = 'Account balance must be numeric.';
    } elseif ((float) $normalizedBalance < 0) {
        $errors[] = 'Account balance must be at least 0.';
    }

    return $errors;
}

function validate_category_input($conn, $userId, $name, $type, $excludeId = 0)
{
    $errors = [];

    if ($name === '') {
        $errors[] = 'Category name is required.';
    } elseif (text_length($name) > 100) {
        $errors[] = 'Category name must be 100 characters or fewer.';
    }

    if (!in_array($type, ['income', 'expense'], true)) {
        $errors[] = 'Category type must be income or expense.';
    } elseif ($name !== '' && text_length($name) <= 100 && category_name_exists($conn, $userId, $name, $type, $excludeId)) {
        $errors[] = 'You already have a category with that name and type.';
    }

    return $errors;
}

function account_transaction_count($conn, $userId, $accountId)
{
    $row = db_fetch_one(
        $conn,
        'SELECT COUNT(*) AS total FROM transactions WHERE userid = ? AND accountid = ?',
        'ii',
        [$userId, $accountId]
    );

    return (int) ($row['total'] ?? 0);
}

function category_transaction_count($conn, $userId, $categoryId)
{
    $row = db_fetch_one(
        $conn,
        'SELECT COUNT(*) AS total FROM transactions WHERE userid = ? AND categoryid = ?',
        'ii',
        [$userId, $categoryId]
    );

    return (int) ($row['total'] ?? 0);
}

function expense_categories($conn, $userId)
{
    return db_fetch_all($conn, 'SELECT id, name FROM categories WHERE userid = ? AND type = ? ORDER BY name', 'is', [$userId, 'expense']);
}

function find_budget($conn, $userId, $budgetId)
{
    return db_fetch_one(
        $conn,
        'SELECT b.*, c.name AS category_name
         FROM budgets b
         INNER JOIN categories c ON c.id = b.categoryid AND c.userid = b.userid
         WHERE b.id = ? AND b.userid = ?
         LIMIT 1',
        'ii',
        [$budgetId, $userId]
    );
}

function budget_exists($conn, $userId, $categoryId, $month, $year, $excludeId = 0)
{
    $sql = 'SELECT id FROM budgets WHERE userid = ? AND categoryid = ? AND month = ? AND year = ?';
    $types = 'iiii';
    $params = [$userId, $categoryId, $month, $year];

    if ($excludeId > 0) {
        $sql .= ' AND id <> ?';
        $types .= 'i';
        $params[] = $excludeId;
    }

    $sql .= ' LIMIT 1';
    return (bool) db_fetch_one($conn, $sql, $types, $params);
}

function validate_budget_input($conn, $userId, $categoryId, $amount, $month, $year, $excludeId = 0)
{
    $errors = [];
    $amountText = trim((string) $amount);
    $normalizedAmount = str_replace(',', '.', $amountText);
    $month = (int) $month;
    $year = (int) $year;

    $category = db_fetch_one($conn, 'SELECT id FROM categories WHERE id = ? AND userid = ? AND type = ?', 'iis', [(int) $categoryId, $userId, 'expense']);
    if (!$category) {
        $errors[] = 'Choose a valid expense category.';
    }

    if ($amountText === '') {
        $errors[] = 'Budget amount is required.';
    } elseif (!is_numeric($normalizedAmount) || (float) $normalizedAmount <= 0) {
        $errors[] = 'Budget amount must be numeric and greater than 0.';
    }

    if ($month < 1 || $month > 12) {
        $errors[] = 'Choose a valid month.';
    }

    if ($year < 2000 || $year > 2100) {
        $errors[] = 'Choose a valid year.';
    }

    if (empty($errors) && budget_exists($conn, $userId, (int) $categoryId, $month, $year, $excludeId)) {
        $errors[] = 'You already have a budget for this category, month, and year.';
    }

    return $errors;
}

function month_options()
{
    return [
        1 => 'January',
        2 => 'February',
        3 => 'March',
        4 => 'April',
        5 => 'May',
        6 => 'June',
        7 => 'July',
        8 => 'August',
        9 => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December',
    ];
}

function find_saving_goal($conn, $userId, $goalId)
{
    return db_fetch_one(
        $conn,
        'SELECT id, userid, name, target_amount, current_amount, target_date, status, created_at, updated_at
         FROM saving_goals
         WHERE id = ? AND userid = ?
         LIMIT 1',
        'ii',
        [$goalId, $userId]
    );
}

function sync_saving_goal_status($conn, $userId, $goalId)
{
    db_execute(
        $conn,
        "UPDATE saving_goals
         SET status = CASE WHEN current_amount >= target_amount THEN 'completed' ELSE 'active' END
         WHERE id = ? AND userid = ?",
        'ii',
        [$goalId, $userId]
    );
}

function validate_saving_goal_input($data)
{
    $errors = [];
    $name = trim($data['name'] ?? '');
    $targetAmount = str_replace(',', '.', trim((string) ($data['target_amount'] ?? '')));
    $currentAmount = str_replace(',', '.', trim((string) ($data['current_amount'] ?? '0')));
    $targetDate = trim($data['target_date'] ?? '');
    $status = $data['status'] ?? 'active';

    if ($name === '') {
        $errors[] = 'Goal name is required.';
    } elseif (text_length($name) > 100) {
        $errors[] = 'Goal name must be 100 characters or fewer.';
    }

    if ($targetAmount === '' || !is_numeric($targetAmount) || (float) $targetAmount <= 0) {
        $errors[] = 'Target amount must be numeric and greater than 0.';
    }

    if ($currentAmount === '' || !is_numeric($currentAmount) || (float) $currentAmount < 0) {
        $errors[] = 'Current amount must be numeric and at least 0.';
    }

    if (!valid_app_date($targetDate)) {
        $errors[] = 'Choose a valid target date.';
    }

    if (!in_array($status, ['active', 'completed'], true)) {
        $errors[] = 'Choose a valid status.';
    }

    return $errors;
}

function validate_saving_transaction_input($amount, $type, $transactionDate)
{
    $errors = [];
    $amountText = str_replace(',', '.', trim((string) $amount));

    if ($amountText === '' || !is_numeric($amountText) || (float) $amountText <= 0) {
        $errors[] = 'Amount must be numeric and greater than 0.';
    }

    if (!in_array($type, ['deposit', 'withdraw'], true)) {
        $errors[] = 'Choose deposit or withdraw.';
    }

    if (!valid_app_date($transactionDate)) {
        $errors[] = 'Choose a valid transaction date.';
    }

    return $errors;
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
