<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth_guard.php';

$user = current_user($conn);
$userId = current_user_id();
$limit = account_limit();
$accountCount = account_count($conn, $userId);
$name = trim($_POST['name'] ?? '');
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Your session expired. Please try again.';
    }

    if ($accountCount >= $limit) {
        $errors[] = 'You have reached the 20 account limit.';
    }

    $errors = array_merge($errors, validate_account_name($conn, $userId, $name));

    if (empty($errors)) {
        db_execute(
            $conn,
            'INSERT INTO accounts (userid, name, type, balance) VALUES (?, ?, ?, ?)',
            'issd',
            [$userId, $name, 'cash', 0]
        );

        flash('success', 'Account added.');
        redirect(app_url('accounts/index.php'));
    }
}

$pageTitle = 'Add Account - Finote';
$activePage = 'accounts';
$navUser = $user;

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="app-main">
    <div class="container">
        <div class="app-card p-4">
            <h1 class="page-title h3 mb-1">Add Account</h1>
            <p class="text-muted mb-4"><?php echo e($accountCount); ?> / <?php echo e($limit); ?> accounts used.</p>

            <?php if (!empty($errors)) { ?>
                <div class="alert alert-danger">
                    <strong>Please fix these details:</strong>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error) { ?>
                            <li><?php echo e($error); ?></li>
                        <?php } ?>
                    </ul>
                </div>
            <?php } ?>

            <?php if ($accountCount >= $limit) { ?>
                <div class="alert alert-warning">You have reached the maximum number of accounts.</div>
                <a class="btn btn-outline-secondary" href="<?php echo e(app_url('accounts/index.php')); ?>">Back to Accounts</a>
            <?php } else { ?>
                <form method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="name">Account Name</label>
                        <input id="name" class="form-control" name="name" maxlength="100" value="<?php echo e($name); ?>" required autofocus>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button class="btn btn-primary" type="submit">Save Account</button>
                        <a class="btn btn-outline-secondary" href="<?php echo e(app_url('accounts/index.php')); ?>">Cancel</a>
                    </div>
                </form>
            <?php } ?>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
