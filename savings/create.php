<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth_guard.php';

$user = current_user($conn);
$userId = current_user_id();
$goal = [
    'name' => trim($_POST['name'] ?? ''),
    'target_amount' => trim($_POST['target_amount'] ?? ''),
    'current_amount' => trim($_POST['current_amount'] ?? '0'),
    'target_date' => $_POST['target_date'] ?? date('Y-m-d', strtotime('+6 months')),
    'status' => $_POST['status'] ?? 'active',
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Your session expired. Please try again.';
    }

    $errors = array_merge($errors, validate_saving_goal_input($goal));

    if (empty($errors)) {
        $targetAmount = normalize_decimal($goal['target_amount']);
        $currentAmount = normalize_decimal($goal['current_amount']);
        $status = $currentAmount >= $targetAmount ? 'completed' : 'active';

        db_execute(
            $conn,
            'INSERT INTO saving_goals (userid, name, target_amount, current_amount, target_date, status) VALUES (?, ?, ?, ?, ?, ?)',
            'isddss',
            [$userId, $goal['name'], $targetAmount, $currentAmount, $goal['target_date'], $status]
        );

        flash('success', 'Saving goal added.');
        redirect(app_url('savings/index.php'));
    }
}

$pageTitle = 'Add Saving Goal - Finote';
$activePage = 'savings';
$navUser = $user;

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="app-main">
    <div class="container">
        <div class="saving-card app-card p-4">
            <h1 class="page-title h3 mb-1">Add Saving Goal</h1>
            <p class="text-muted mb-4">Create a target and track progress over time.</p>

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
                        <label class="form-label fw-semibold" for="name">Goal Name</label>
                        <input id="name" class="form-control" name="name" maxlength="100" value="<?php echo e($goal['name']); ?>" required autofocus>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="target_date">Target Date</label>
                        <input id="target_date" class="form-control" name="target_date" type="date" value="<?php echo e($goal['target_date']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="target_amount">Target Amount</label>
                        <input id="target_amount" class="form-control" name="target_amount" type="number" min="0.01" step="0.01" value="<?php echo e($goal['target_amount']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="current_amount">Current Amount</label>
                        <input id="current_amount" class="form-control" name="current_amount" type="number" min="0" step="0.01" value="<?php echo e($goal['current_amount']); ?>" required>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button class="btn btn-primary" type="submit">Save Goal</button>
                    <a class="btn btn-outline-secondary" href="<?php echo e(app_url('savings/index.php')); ?>">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
