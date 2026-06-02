<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/auth_guard.php';

$user = current_user($conn);
$userId = (int) $_SESSION['user_id'];

$summary = db_fetch_one(
    $conn,
    "SELECT *
     FROM v_ringkasan_keuangan_user
     WHERE userid = ?",
    'i',
    [$userId]
);

$recentTransactions = db_fetch_all(
    $conn,
    "SELECT id_transaksi AS id, amount, type, description, transaction_date, nama_akun AS account_name, nama_kategori AS category_name, status_transaksi
     FROM v_laporan_transaksi
     WHERE userid = ?
     ORDER BY transaction_date DESC, id_transaksi DESC
     LIMIT 5",
    'i',
    [$userId]
);

$totalIncome = (float) ($summary['total_income'] ?? 0);
$totalExpense = (float) ($summary['total_expense'] ?? 0);
$balance = (float) ($summary['saldo_bersih'] ?? 0);

$pageTitle = 'Dashboard - Finote';
$activePage = 'dashboard';
$navUser = $user;

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/navbar.php';
?>

<main class="app-main">
    <div class="container">
        <?php require __DIR__ . '/includes/flash.php'; ?>

        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
            <div>
                <h1 class="page-title mb-1">Welcome, <?php echo e($user['name']); ?></h1>
                <p class="text-muted mb-0">Here is your personal finance snapshot.</p>
            </div>
            <div class="d-flex flex-wrap gap-2 align-self-lg-start">
                <a class="btn btn-primary" href="<?php echo e(app_url('transactions/create.php')); ?>">Add Transaction</a>
                <a class="btn btn-outline-primary" href="<?php echo e(app_url('transactions/index.php')); ?>">View Transactions</a>
                <a class="btn btn-warning text-white" href="<?php echo e(app_url('profile.php')); ?>">Profile</a>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="summary-card summary-income p-4">
                    <p class="mb-1 opacity-75">Total Income</p>
                    <h2 class="h3 fw-bold mb-0"><?php echo e(money($totalIncome)); ?></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-card summary-expense p-4">
                    <p class="mb-1 opacity-75">Total Expense</p>
                    <h2 class="h3 fw-bold mb-0"><?php echo e(money($totalExpense)); ?></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-card summary-balance p-4">
                    <p class="mb-1 opacity-75">Current Balance</p>
                    <h2 class="h3 fw-bold mb-0"><?php echo e(money($balance)); ?></h2>
                </div>
            </div>
        </div>

        <div class="app-card p-4">
            <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                <div>
                    <h2 class="h4 fw-bold mb-1">Recent Transactions</h2>
                    <p class="text-muted mb-0">Your latest money movement.</p>
                </div>
                <a class="btn btn-sm btn-outline-primary" href="<?php echo e(app_url('transactions/index.php')); ?>">See All</a>
            </div>

            <?php if (empty($recentTransactions)) { ?>
                <div class="empty-state">
                    <h3 class="h5">No transactions yet</h3>
                    <p>Add your first transaction to start seeing dashboard insights.</p>
                    <a class="btn btn-primary" href="<?php echo e(app_url('transactions/create.php')); ?>">Add Transaction</a>
                </div>
            <?php } else { ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Account</th>
                                <th>Category</th>
                                <th>Type</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentTransactions as $transaction) { ?>
                                <tr>
                                    <td><?php echo e(format_date($transaction['transaction_date'])); ?></td>
                                    <td><?php echo e($transaction['description'] ?: 'No description'); ?></td>
                                    <td><?php echo e($transaction['account_name'] ?? '-'); ?></td>
                                    <td><?php echo e($transaction['category_name'] ?? '-'); ?></td>
                                    <td>
                                        <span class="badge <?php echo $transaction['type'] === 'income' ? 'badge-soft-income' : 'badge-soft-expense'; ?>">
                                            <?php echo e(ucfirst($transaction['type'])); ?>
                                        </span>
                                    </td>
                                    <td class="text-end fw-semibold"><?php echo e(money($transaction['amount'])); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        </div>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
