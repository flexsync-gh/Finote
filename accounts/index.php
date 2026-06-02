<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth_guard.php';

$user = current_user($conn);
$userId = current_user_id();
$limit = account_limit();

$accounts = db_fetch_all(
    $conn,
    "SELECT a.id, a.name, a.type, a.balance,
        (SELECT COUNT(*) FROM transactions t WHERE t.userid = a.userid AND t.accountid = a.id) AS transaction_count
     FROM accounts a
     WHERE a.userid = ?
     ORDER BY a.name",
    'i',
    [$userId]
);

$accountCount = count($accounts);
$atLimit = $accountCount >= $limit;

$pageTitle = 'Accounts - Finote';
$activePage = 'accounts';
$navUser = $user;

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="app-main">
    <div class="container">
        <?php require __DIR__ . '/../includes/flash.php'; ?>

        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
            <div>
                <h1 class="page-title mb-1">Accounts</h1>
                <p class="text-muted mb-0"><?php echo e($accountCount); ?> / <?php echo e($limit); ?> accounts</p>
            </div>
            <?php if ($atLimit) { ?>
                <button class="btn btn-primary align-self-lg-start" type="button" disabled>Account Limit Reached</button>
            <?php } else { ?>
                <a class="btn btn-primary align-self-lg-start" href="<?php echo e(app_url('accounts/create.php')); ?>">Add Account</a>
            <?php } ?>
        </div>

        <div class="app-card p-0 overflow-hidden">
            <?php if (empty($accounts)) { ?>
                <div class="empty-state">
                    <h2 class="h5">No accounts yet</h2>
                    <p>Add an account to start recording transactions.</p>
                    <a class="btn btn-primary" href="<?php echo e(app_url('accounts/create.php')); ?>">Add Account</a>
                </div>
            <?php } else { ?>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th class="text-end">Transactions</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($accounts as $account) { ?>
                                <tr>
                                    <td class="fw-semibold"><?php echo e($account['name']); ?></td>
                                    <td><?php echo e(ucfirst($account['type'])); ?></td>
                                    <td class="text-end"><?php echo e($account['transaction_count']); ?></td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a class="btn btn-outline-secondary" href="<?php echo e(app_url('accounts/edit.php?id=' . $account['id'])); ?>">Edit</a>
                                            <a class="btn btn-outline-danger" href="<?php echo e(app_url('accounts/delete.php?id=' . $account['id'])); ?>">Delete</a>
                                        </div>
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
