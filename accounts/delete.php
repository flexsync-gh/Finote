<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth_guard.php';

$user = current_user($conn);
$userId = current_user_id();
$accountId = max(0, (int) ($_GET['id'] ?? ($_POST['id'] ?? 0)));
$account = find_account($conn, $userId, $accountId);

if (!$account) {
    flash('error', 'Account not found.');
    redirect(app_url('accounts/index.php'));
}

$transactionCount = account_transaction_count($conn, $userId, $accountId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        flash('error', 'Your session expired. Please try again.');
        redirect(app_url('accounts/delete.php?id=' . $accountId));
    }

    if ($transactionCount > 0) {
        flash('error', 'This account is used by transactions, so it cannot be deleted.');
        redirect(app_url('accounts/index.php'));
    }

    db_execute($conn, 'DELETE FROM accounts WHERE id = ? AND userid = ?', 'ii', [$accountId, $userId]);
    flash('success', 'Account deleted.');
    redirect(app_url('accounts/index.php'));
}

$pageTitle = 'Delete Account - Finote';
$activePage = 'accounts';
$navUser = $user;

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="app-main">
    <div class="container">
        <div class="app-card p-4">
            <h1 class="page-title h3 mb-2">Delete Account?</h1>
            <p class="text-muted">This action cannot be undone. Please confirm before deleting.</p>

            <div class="border rounded-3 p-3 mb-4">
                <strong><?php echo e($account['name']); ?></strong><br>
                <span class="text-muted"><?php echo e($transactionCount); ?> related transactions</span>
            </div>

            <?php if ($transactionCount > 0) { ?>
                <div class="alert alert-warning">This account is already used by transactions, so it cannot be deleted.</div>
                <a class="btn btn-outline-secondary" href="<?php echo e(app_url('accounts/index.php')); ?>">Back to Accounts</a>
            <?php } else { ?>
                <form method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="id" value="<?php echo e($accountId); ?>">
                    <button class="btn btn-danger" type="submit">Yes, Delete</button>
                    <a class="btn btn-outline-secondary" href="<?php echo e(app_url('accounts/index.php')); ?>">Cancel</a>
                </form>
            <?php } ?>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
