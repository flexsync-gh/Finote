<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/_helpers.php';

$user = current_user($conn);
$userId = (int) $_SESSION['user_id'];
$options = transaction_options($conn, $userId);

$q = trim($_GET['q'] ?? '');
$accountId = (int) ($_GET['account_id'] ?? 0);
$categoryId = (int) ($_GET['category_id'] ?? 0);
$type = $_GET['type'] ?? '';
$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo = trim($_GET['date_to'] ?? '');

$where = ['v.userid = ?'];
$types = 'i';
$params = [$userId];

if ($q !== '') {
    $where[] = 'v.description LIKE ?';
    $types .= 's';
    $params[] = '%' . $q . '%';
}

if ($accountId > 0) {
    $where[] = 't.accountid = ?';
    $types .= 'i';
    $params[] = $accountId;
}

if ($categoryId > 0) {
    $where[] = 't.categoryid = ?';
    $types .= 'i';
    $params[] = $categoryId;
}

if (in_array($type, ['income', 'expense'], true)) {
    $where[] = 'v.type = ?';
    $types .= 's';
    $params[] = $type;
}

if ($dateFrom !== '' && valid_transaction_date($dateFrom)) {
    $where[] = 'v.transaction_date >= ?';
    $types .= 's';
    $params[] = $dateFrom;
}

if ($dateTo !== '' && valid_transaction_date($dateTo)) {
    $where[] = 'v.transaction_date <= ?';
    $types .= 's';
    $params[] = $dateTo;
}

$whereSql = implode(' AND ', $where);
$perPage = 10;
$page = max(1, (int) ($_GET['page'] ?? 1));

// Procedure example: the SQL dump defines GetLaporanKeuanganUser(IN p_userid),
// so it cannot replace the filtered/paginated report query below.
// Prepared call example for an unfiltered report: db_fetch_all($conn, 'CALL GetLaporanKeuanganUser(?)', 'i', [$userId]);
$reportSource = 'v_laporan_transaksi v INNER JOIN transactions t ON t.id = v.id_transaksi';

$countRow = db_fetch_one($conn, "SELECT COUNT(*) AS total FROM $reportSource WHERE $whereSql", $types, $params);
$totalRows = (int) ($countRow['total'] ?? 0);
$totalPages = max(1, (int) ceil($totalRows / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$listTypes = $types . 'ii';
$listParams = array_merge($params, [$perPage, $offset]);

$transactions = db_fetch_all(
    $conn,
    "SELECT
        v.id_transaksi AS id,
        v.amount,
        v.description,
        v.type,
        v.transaction_date,
        v.nama_akun AS account_name,
        v.nama_kategori AS category_name,
        v.status_transaksi
     FROM $reportSource
     WHERE $whereSql
     ORDER BY v.transaction_date DESC, v.id_transaksi DESC
     LIMIT ? OFFSET ?",
    $listTypes,
    $listParams
);

$showingFrom = $totalRows === 0 ? 0 : $offset + 1;
$showingTo = min($offset + count($transactions), $totalRows);

$pageTitle = 'Transactions - Finote';
$activePage = 'transactions';
$navUser = $user;

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="app-main">
    <div class="container">
        <?php require __DIR__ . '/../includes/flash.php'; ?>

        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
            <div>
                <h1 class="page-title mb-1">Transactions</h1>
                <p class="text-muted mb-0">Showing <?php echo e($showingFrom); ?>-<?php echo e($showingTo); ?> of <?php echo e($totalRows); ?> transactions.</p>
            </div>
            <a class="btn btn-primary align-self-lg-start" href="<?php echo e(app_url('transactions/create.php')); ?>">Add Transaction</a>
        </div>

        <div class="app-card p-4 mb-4">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="q">Search description</label>
                    <input id="q" class="form-control" name="q" value="<?php echo e($q); ?>" placeholder="Lunch, salary, transport">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="account_id">Account</label>
                    <select id="account_id" class="form-select" name="account_id">
                        <option value="0">All accounts</option>
                        <?php foreach ($options['accounts'] as $account) { ?>
                            <option value="<?php echo e($account['id']); ?>" <?php echo $accountId === (int)$account['id'] ? 'selected' : ''; ?>><?php echo e($account['name']); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="category_id">Category</label>
                    <select id="category_id" class="form-select" name="category_id">
                        <option value="0">All categories</option>
                        <?php foreach ($options['categories'] as $category) { ?>
                            <option value="<?php echo e($category['id']); ?>" <?php echo $categoryId === (int)$category['id'] ? 'selected' : ''; ?>><?php echo e($category['name']); ?> (<?php echo e($category['type']); ?>)</option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="type">Type</label>
                    <select id="type" class="form-select" name="type">
                        <option value="">All types</option>
                        <option value="income" <?php echo $type === 'income' ? 'selected' : ''; ?>>Income</option>
                        <option value="expense" <?php echo $type === 'expense' ? 'selected' : ''; ?>>Expense</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="date_from">From</label>
                    <input id="date_from" class="form-control" type="date" name="date_from" value="<?php echo e($dateFrom); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="date_to">To</label>
                    <input id="date_to" class="form-control" type="date" name="date_to" value="<?php echo e($dateTo); ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button class="btn btn-primary flex-fill" type="submit">Filter</button>
                    <a class="btn btn-outline-secondary" href="<?php echo e(app_url('transactions/index.php')); ?>">Reset</a>
                </div>
            </form>
        </div>

        <div class="app-card p-0 overflow-hidden">
            <?php if (empty($transactions)) { ?>
                <div class="empty-state">
                    <h2 class="h5">No matching transactions</h2>
                    <p>Try changing your filters or add a new transaction.</p>
                    <a class="btn btn-primary" href="<?php echo e(app_url('transactions/create.php')); ?>">Add Transaction</a>
                </div>
            <?php } else { ?>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Account</th>
                                <th>Category</th>
                                <th>Type</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction) { ?>
                                <tr>
                                    <td><?php echo e(format_date($transaction['transaction_date'])); ?></td>
                                    <td class="transaction-description"><?php echo highlight_keyword($transaction['description'] ?: 'No description', $q); ?></td>
                                    <td><?php echo e($transaction['account_name'] ?? '-'); ?></td>
                                    <td><?php echo e($transaction['category_name'] ?? '-'); ?></td>
                                    <td>
                                        <span class="badge <?php echo $transaction['type'] === 'income' ? 'badge-soft-income' : 'badge-soft-expense'; ?>">
                                            <?php echo e(ucfirst($transaction['type'])); ?>
                                        </span>
                                        <div class="small text-muted"><?php echo e($transaction['status_transaksi'] ?? '-'); ?></div>
                                    </td>
                                    <td class="text-end fw-semibold"><?php echo e(money($transaction['amount'])); ?></td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a class="btn btn-outline-primary" href="<?php echo e(app_url('transactions/show.php?id=' . $transaction['id'])); ?>">View</a>
                                            <a class="btn btn-outline-secondary" href="<?php echo e(app_url('transactions/edit.php?id=' . $transaction['id'])); ?>">Edit</a>
                                            <a class="btn btn-outline-danger" href="<?php echo e(app_url('transactions/delete.php?id=' . $transaction['id'])); ?>">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        </div>

        <?php if ($totalPages > 1) { ?>
            <nav class="mt-4" aria-label="Transaction pagination">
                <ul class="pagination justify-content-center flex-wrap">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>"><a class="page-link" href="<?php echo e(pagination_url(1)); ?>">First</a></li>
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>"><a class="page-link" href="<?php echo e(pagination_url($page - 1)); ?>">Previous</a></li>
                    <li class="page-item disabled"><span class="page-link">Page <?php echo e($page); ?> of <?php echo e($totalPages); ?></span></li>
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>"><a class="page-link" href="<?php echo e(pagination_url($page + 1)); ?>">Next</a></li>
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>"><a class="page-link" href="<?php echo e(pagination_url($totalPages)); ?>">Last</a></li>
                </ul>
            </nav>
        <?php } ?>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
