<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth_guard.php';

$user = current_user($conn);
$userId = current_user_id();
$month = (int) ($_GET['month'] ?? date('n'));
$year = (int) ($_GET['year'] ?? date('Y'));

if ($month < 1 || $month > 12) {
    $month = (int) date('n');
}

if ($year < 2000 || $year > 2100) {
    $year = (int) date('Y');
}

$budgets = db_fetch_all(
    $conn,
    "SELECT
        b.id,
        b.amount,
        b.month,
        b.year,
        c.name AS category_name,
        COALESCE((
            SELECT SUM(t.amount)
            FROM transactions t
            WHERE t.userid = b.userid
              AND t.categoryid = b.categoryid
              AND t.type = 'expense'
              AND MONTH(t.transaction_date) = b.month
              AND YEAR(t.transaction_date) = b.year
        ), 0) AS spent
     FROM budgets b
     INNER JOIN categories c ON c.id = b.categoryid AND c.userid = b.userid
     WHERE b.userid = ? AND b.month = ? AND b.year = ?
     ORDER BY c.name",
    'iii',
    [$userId, $month, $year]
);

$totalBudget = 0;
$totalSpent = 0;
$overBudgetCount = 0;

foreach ($budgets as $budget) {
    $totalBudget += (float) $budget['amount'];
    $totalSpent += (float) $budget['spent'];
    if ((float) $budget['spent'] >= (float) $budget['amount']) {
        $overBudgetCount++;
    }
}

$totalRemaining = $totalBudget - $totalSpent;

$pageTitle = 'Budgets - Finote';
$activePage = 'budgets';
$navUser = $user;

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="app-main">
    <div class="container">
        <?php require __DIR__ . '/../includes/flash.php'; ?>

        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
            <div>
                <h1 class="page-title mb-1">Budgets</h1>
                <p class="text-muted mb-0">Track monthly limits for your expense categories.</p>
            </div>
            <a class="btn btn-primary align-self-lg-start" href="<?php echo e(app_url('budgets/create.php')); ?>">Add Budget</a>
        </div>

        <div class="app-card p-4 mb-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold" for="month">Month</label>
                    <select id="month" name="month" class="form-select">
                        <?php foreach (month_options() as $value => $label) { ?>
                            <option value="<?php echo e($value); ?>" <?php echo $month === $value ? 'selected' : ''; ?>><?php echo e($label); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="year">Year</label>
                    <input id="year" class="form-control" name="year" type="number" min="2000" max="2100" value="<?php echo e($year); ?>">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary flex-fill" type="submit">Filter</button>
                    <a class="btn btn-outline-secondary" href="<?php echo e(app_url('budgets/index.php')); ?>">Reset</a>
                </div>
            </form>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="summary-card summary-budget p-4 h-100">
                    <p class="mb-1 opacity-75">Total Budget</p>
                    <div class="h4 fw-bold mb-0"><?php echo e(money($totalBudget)); ?></div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="summary-card summary-expense p-4 h-100">
                    <p class="mb-1 opacity-75">Total Spent</p>
                    <div class="h4 fw-bold mb-0"><?php echo e(money($totalSpent)); ?></div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="summary-card summary-balance p-4 h-100">
                    <p class="mb-1 opacity-75">Total Remaining</p>
                    <div class="h4 fw-bold mb-0"><?php echo e(money($totalRemaining)); ?></div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="summary-card summary-warning p-4 h-100">
                    <p class="mb-1 opacity-75">Over Budget Count</p>
                    <div class="h4 fw-bold mb-0"><?php echo e($overBudgetCount); ?></div>
                </div>
            </div>
        </div>

        <div class="budget-card app-card p-0 overflow-hidden">
            <?php if (empty($budgets)) { ?>
                <div class="empty-state">
                    <h2 class="h5">No budgets yet</h2>
                    <p>Create a monthly budget for an expense category to compare planned vs spent.</p>
                    <a class="btn btn-primary" href="<?php echo e(app_url('budgets/create.php')); ?>">Add Budget</a>
                </div>
            <?php } else { ?>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th class="text-end">Budget</th>
                                <th class="text-end">Spent</th>
                                <th class="text-end">Remaining</th>
                                <th>Usage</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($budgets as $budget) {
                                $amount = (float) $budget['amount'];
                                $spent = (float) $budget['spent'];
                                $remaining = $amount - $spent;
                                $usage = $amount > 0 ? ($spent / $amount) * 100 : 0;
                                $barWidth = min(100, $usage);
                                $status = 'Safe';
                                $statusClass = 'status-safe';
                                if ($usage >= 100) {
                                    $status = 'Over Budget';
                                    $statusClass = 'status-over';
                                } elseif ($usage >= 80) {
                                    $status = 'Warning';
                                    $statusClass = 'status-warning';
                                }
                            ?>
                                <tr>
                                    <td class="fw-semibold"><?php echo e($budget['category_name']); ?></td>
                                    <td class="text-end"><?php echo e(money($amount)); ?></td>
                                    <td class="text-end"><?php echo e(money($spent)); ?></td>
                                    <td class="text-end fw-semibold"><?php echo e(money($remaining)); ?></td>
                                    <td style="min-width: 180px;">
                                        <div class="d-flex justify-content-between small text-muted mb-1">
                                            <span><?php echo e(number_format($usage, 0)); ?>%</span>
                                            <span><?php echo e(month_options()[$budget['month']] . ' ' . $budget['year']); ?></span>
                                        </div>
                                        <div class="progress budget-progress" role="progressbar" aria-valuenow="<?php echo e((int) $barWidth); ?>" aria-valuemin="0" aria-valuemax="100">
                                            <div class="progress-bar <?php echo e($statusClass); ?>" style="width: <?php echo e($barWidth); ?>%"></div>
                                        </div>
                                    </td>
                                    <td><span class="status-badge <?php echo e($statusClass); ?>"><?php echo e($status); ?></span></td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm mobile-action-group">
                                            <a class="btn btn-outline-secondary" href="<?php echo e(app_url('budgets/edit.php?id=' . $budget['id'])); ?>">Edit</a>
                                            <a class="btn btn-outline-danger" href="<?php echo e(app_url('budgets/delete.php?id=' . $budget['id'])); ?>">Delete</a>
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
