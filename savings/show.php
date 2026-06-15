<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth_guard.php';

$user = current_user($conn);
$userId = current_user_id();
$goalId = max(0, (int) ($_GET['id'] ?? 0));

sync_saving_goal_status($conn, $userId, $goalId);
$goal = find_saving_goal($conn, $userId, $goalId);

if (!$goal) {
    flash('error', 'Saving goal not found.');
    redirect(app_url('savings/index.php'));
}

$transactions = db_fetch_all(
    $conn,
    'SELECT id, amount, type, note, transaction_date, created_at
     FROM saving_transactions
     WHERE (goalid = ? OR saving_goal_id = ?) AND userid = ?
     ORDER BY transaction_date DESC, id DESC',
    'iii',
    [$goalId, $goalId, $userId]
);

$target = (float) $goal['target_amount'];
$current = (float) $goal['current_amount'];
$remaining = max(0, $target - $current);
$progress = $target > 0 ? min(100, ($current / $target) * 100) : 0;
$statusClass = $goal['status'] === 'completed' ? 'status-safe' : 'status-warning';

$pageTitle = 'Saving Detail - Finote';
$activePage = 'savings';
$navUser = $user;

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="app-main">
    <div class="container">
        <?php require __DIR__ . '/../includes/flash.php'; ?>

        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
            <div>
                <h1 class="page-title mb-1"><?php echo e($goal['name']); ?></h1>
                <p class="text-muted mb-0">Target date: <?php echo e(format_date($goal['target_date'])); ?></p>
            </div>
            <div class="d-flex flex-wrap gap-2 align-self-lg-start">
                <a class="btn btn-primary" href="<?php echo e(app_url('savings/add_transaction.php?id=' . $goalId)); ?>">Add Transaction</a>
                <a class="btn btn-outline-secondary" href="<?php echo e(app_url('savings/edit.php?id=' . $goalId)); ?>">Edit</a>
                <a class="btn btn-outline-danger" href="<?php echo e(app_url('savings/delete.php?id=' . $goalId)); ?>">Delete</a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-8">
                <div class="saving-card app-card p-4 h-100">
                    <div class="d-flex justify-content-between gap-3 mb-3">
                        <div>
                            <p class="text-muted mb-1">Progress</p>
                            <h2 class="h3 fw-bold mb-0"><?php echo e(money($current)); ?> / <?php echo e(money($target)); ?></h2>
                        </div>
                        <span class="status-badge <?php echo e($statusClass); ?>"><?php echo e(ucfirst($goal['status'])); ?></span>
                    </div>
                    <div class="progress saving-progress mb-3" role="progressbar" aria-valuenow="<?php echo e((int) $progress); ?>" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar" style="width: <?php echo e($progress); ?>%"></div>
                    </div>
                    <div class="d-flex flex-wrap justify-content-between gap-3">
                        <div>
                            <div class="text-muted">Remaining</div>
                            <strong><?php echo e(money($remaining)); ?></strong>
                        </div>
                        <div>
                            <div class="text-muted">Progress</div>
                            <strong><?php echo e(number_format($progress, 0)); ?>%</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="summary-card summary-savings p-4 h-100">
                    <p class="mb-1 opacity-75">Transactions</p>
                    <div class="h3 fw-bold mb-0"><?php echo e(count($transactions)); ?></div>
                </div>
            </div>
        </div>

        <div class="saving-card app-card p-0 overflow-hidden">
            <div class="p-4 border-bottom">
                <h2 class="h4 fw-bold mb-1">Saving History</h2>
                <p class="text-muted mb-0">Deposits and withdrawals for this goal.</p>
            </div>

            <?php if (empty($transactions)) { ?>
                <div class="empty-state">
                    <h3 class="h5">No saving transactions yet</h3>
                    <p>Add a deposit or withdrawal to start the history.</p>
                    <a class="btn btn-primary" href="<?php echo e(app_url('savings/add_transaction.php?id=' . $goalId)); ?>">Add Transaction</a>
                </div>
            <?php } else { ?>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Note</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction) { ?>
                                <tr>
                                    <td><?php echo e(format_date($transaction['transaction_date'])); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $transaction['type'] === 'deposit' ? 'status-safe' : 'status-over'; ?>">
                                            <?php echo e(ucfirst($transaction['type'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo e($transaction['note'] ?: '-'); ?></td>
                                    <td class="text-end fw-semibold"><?php echo e(money($transaction['amount'])); ?></td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-danger" href="<?php echo e(app_url('savings/delete_transaction.php?id=' . $transaction['id'])); ?>">Delete</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
