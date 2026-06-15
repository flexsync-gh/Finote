<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth_guard.php';

$user = current_user($conn);
$userId = current_user_id();
$budgetId = max(0, (int) ($_GET['id'] ?? 0));
$budget = find_budget($conn, $userId, $budgetId);

if (!$budget) {
    flash('error', 'Budget not found.');
    redirect(app_url('budgets/index.php'));
}

$categories = expense_categories($conn, $userId);
$categoryId = (int) ($_POST['categoryid'] ?? $budget['categoryid']);
$amount = trim($_POST['amount'] ?? $budget['amount']);
$month = (int) ($_POST['month'] ?? $budget['month']);
$year = (int) ($_POST['year'] ?? $budget['year']);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Your session expired. Please try again.';
    }

    $errors = array_merge($errors, validate_budget_input($conn, $userId, $categoryId, $amount, $month, $year, $budgetId));

    if (empty($errors)) {
        db_execute(
            $conn,
            'UPDATE budgets SET categoryid = ?, amount = ?, month = ?, year = ? WHERE id = ? AND userid = ?',
            'idiiii',
            [$categoryId, normalize_decimal($amount), $month, $year, $budgetId, $userId]
        );

        flash('success', 'Budget updated.');
        redirect(app_url('budgets/index.php?month=' . $month . '&year=' . $year));
    }
}

$pageTitle = 'Edit Budget - Finote';
$activePage = 'budgets';
$navUser = $user;

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="app-main">
    <div class="container">
        <div class="budget-card app-card p-4">
            <h1 class="page-title h3 mb-1">Edit Budget</h1>
            <p class="text-muted mb-4">Update the monthly limit for this category.</p>

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

            <form method="POST">
                <?php echo csrf_field(); ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="categoryid">Expense Category</label>
                        <select id="categoryid" class="form-select" name="categoryid" required autofocus>
                            <?php foreach ($categories as $category) { ?>
                                <option value="<?php echo e($category['id']); ?>" <?php echo $categoryId === (int) $category['id'] ? 'selected' : ''; ?>><?php echo e($category['name']); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="amount">Budget Amount</label>
                        <input id="amount" class="form-control" name="amount" type="number" min="0.01" step="0.01" value="<?php echo e($amount); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="month">Month</label>
                        <select id="month" class="form-select" name="month" required>
                            <?php foreach (month_options() as $value => $label) { ?>
                                <option value="<?php echo e($value); ?>" <?php echo $month === $value ? 'selected' : ''; ?>><?php echo e($label); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="year">Year</label>
                        <input id="year" class="form-control" name="year" type="number" min="2000" max="2100" value="<?php echo e($year); ?>" required>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button class="btn btn-primary" type="submit">Save Budget</button>
                    <a class="btn btn-outline-secondary" href="<?php echo e(app_url('budgets/index.php?month=' . $budget['month'] . '&year=' . $budget['year'])); ?>">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
