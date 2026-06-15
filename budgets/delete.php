<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth_guard.php';

$user = current_user($conn);
$userId = current_user_id();
$budgetId = max(0, (int) ($_GET['id'] ?? ($_POST['id'] ?? 0)));
$budget = find_budget($conn, $userId, $budgetId);

if (!$budget) {
    flash('error', 'Budget not found.');
    redirect(app_url('budgets/index.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        flash('error', 'Your session expired. Please try again.');
        redirect(app_url('budgets/delete.php?id=' . $budgetId));
    }

    db_execute($conn, 'DELETE FROM budgets WHERE id = ? AND userid = ?', 'ii', [$budgetId, $userId]);
    flash('success', 'Budget deleted.');
    redirect(app_url('budgets/index.php?month=' . $budget['month'] . '&year=' . $budget['year']));
}

$pageTitle = 'Delete Budget - Finote';
$activePage = 'budgets';
$navUser = $user;

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="app-main">
    <div class="container">
        <div class="budget-card app-card p-4">
            <h1 class="page-title h3 mb-2">Delete Budget?</h1>
            <p class="text-muted">This action cannot be undone. Please confirm before deleting.</p>

            <div class="border rounded-3 p-3 mb-4">
                <strong><?php echo e($budget['category_name']); ?></strong><br>
                <span class="text-muted"><?php echo e(month_options()[(int) $budget['month']] . ' ' . $budget['year']); ?> - <?php echo e(money($budget['amount'])); ?></span>
            </div>

            <form method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="id" value="<?php echo e($budgetId); ?>">
                <button class="btn btn-danger" type="submit">Yes, Delete</button>
                <a class="btn btn-outline-secondary" href="<?php echo e(app_url('budgets/index.php?month=' . $budget['month'] . '&year=' . $budget['year'])); ?>">Cancel</a>
            </form>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
