<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/_helpers.php';

$user = current_user($conn);
$userId = (int) $_SESSION['user_id'];
$transactionId = max(0, (int) ($_GET['id'] ?? ($_POST['id'] ?? 0)));
$transaction = find_transaction($conn, $userId, $transactionId);

if (!$transaction) {
    flash('error', 'Transaction not found.');
    redirect(app_url('transactions/index.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        flash('error', 'Your session expired. Please try again.');
        redirect(app_url('transactions/delete.php?id=' . $transactionId));
    }

    db_execute($conn, 'DELETE FROM transactions WHERE id = ? AND userid = ?', 'ii', [$transactionId, $userId]);
    flash('success', 'Transaction deleted.');
    redirect(app_url('transactions/index.php'));
}

$pageTitle = 'Delete Transaction - Finote';
$activePage = 'transactions';
$navUser = $user;

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="app-main">
    <div class="container">
        <div class="app-card p-4">
            <h1 class="page-title h3 mb-2">Delete Transaction?</h1>
            <p class="text-muted">This action cannot be undone. Please confirm before deleting.</p>

            <div class="border rounded-3 p-3 mb-4">
                <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                    <div>
                        <strong><?php echo e($transaction['description'] ?: 'No description'); ?></strong><br>
                        <span class="text-muted"><?php echo e(format_date($transaction['transaction_date'])); ?> · <?php echo e($transaction['account_name'] ?? '-'); ?> · <?php echo e($transaction['category_name'] ?? '-'); ?></span>
                    </div>
                    <div class="fw-bold"><?php echo e(money($transaction['amount'])); ?></div>
                </div>
            </div>

            <form method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="id" value="<?php echo e($transactionId); ?>">
                <button class="btn btn-danger" type="submit">Yes, Delete</button>
                <a class="btn btn-outline-secondary" href="<?php echo e(app_url('transactions/index.php')); ?>">Cancel</a>
            </form>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
