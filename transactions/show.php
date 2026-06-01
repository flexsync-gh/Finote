<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/_helpers.php';

$user = current_user($conn);
$userId = (int) $_SESSION['user_id'];
$transactionId = max(0, (int) ($_GET['id'] ?? 0));
$transaction = find_transaction($conn, $userId, $transactionId);

if (!$transaction) {
    flash('error', 'Transaction not found.');
    redirect(app_url('transactions/index.php'));
}

$pageTitle = 'Transaction Detail - Finote';
$activePage = 'transactions';
$navUser = $user;

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="app-main">
    <div class="container">
        <div class="app-card p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
                <div>
                    <h1 class="page-title h3 mb-1">Transaction Detail</h1>
                    <p class="text-muted mb-0">A closer look at this record.</p>
                </div>
                <div class="d-flex gap-2 align-self-md-start">
                    <a class="btn btn-outline-secondary" href="<?php echo e(app_url('transactions/index.php')); ?>">Back</a>
                    <a class="btn btn-primary" href="<?php echo e(app_url('transactions/edit.php?id=' . $transactionId)); ?>">Edit</a>
                </div>
            </div>

            <dl class="row">
                <dt class="col-sm-3">Date</dt>
                <dd class="col-sm-9"><?php echo e(format_date($transaction['transaction_date'])); ?></dd>
                <dt class="col-sm-3">Type</dt>
                <dd class="col-sm-9"><?php echo e(ucfirst($transaction['type'])); ?></dd>
                <dt class="col-sm-3">Amount</dt>
                <dd class="col-sm-9 fw-bold"><?php echo e(money($transaction['amount'])); ?></dd>
                <dt class="col-sm-3">Account</dt>
                <dd class="col-sm-9"><?php echo e($transaction['account_name'] ?? '-'); ?></dd>
                <dt class="col-sm-3">Category</dt>
                <dd class="col-sm-9"><?php echo e($transaction['category_name'] ?? '-'); ?></dd>
                <dt class="col-sm-3">Description</dt>
                <dd class="col-sm-9"><?php echo e($transaction['description'] ?: 'No description'); ?></dd>
            </dl>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
