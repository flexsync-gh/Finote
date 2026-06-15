<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth_guard.php';

$user = current_user($conn);
$userId = current_user_id();
$goalId = max(0, (int) ($_GET['id'] ?? ($_POST['id'] ?? 0)));
$goal = find_saving_goal($conn, $userId, $goalId);

if (!$goal) {
    flash('error', 'Saving goal not found.');
    redirect(app_url('savings/index.php'));
}

$transactionRow = db_fetch_one($conn, 'SELECT COUNT(*) AS total FROM saving_transactions WHERE (goalid = ? OR saving_goal_id = ?) AND userid = ?', 'iii', [$goalId, $goalId, $userId]);
$transactionCount = (int) ($transactionRow['total'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        flash('error', 'Your session expired. Please try again.');
        redirect(app_url('savings/delete.php?id=' . $goalId));
    }

    mysqli_begin_transaction($conn);
    try {
        db_execute($conn, 'DELETE FROM saving_transactions WHERE (goalid = ? OR saving_goal_id = ?) AND userid = ?', 'iii', [$goalId, $goalId, $userId]);
        db_execute($conn, 'DELETE FROM saving_goals WHERE id = ? AND userid = ?', 'ii', [$goalId, $userId]);
        mysqli_commit($conn);
        flash('success', 'Saving goal deleted.');
        redirect(app_url('savings/index.php'));
    } catch (Throwable $exception) {
        mysqli_rollback($conn);
        flash('error', 'Saving goal could not be deleted.');
        redirect(app_url('savings/delete.php?id=' . $goalId));
    }
}

$pageTitle = 'Delete Saving Goal - Finote';
$activePage = 'savings';
$navUser = $user;

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="app-main">
    <div class="container">
        <div class="saving-card app-card p-4">
            <h1 class="page-title h3 mb-2">Delete Saving Goal?</h1>
            <p class="text-muted">This also removes its saving transaction history. Please confirm before deleting.</p>

            <div class="border rounded-3 p-3 mb-4">
                <strong><?php echo e($goal['name']); ?></strong><br>
                <span class="text-muted"><?php echo e(money($goal['current_amount'])); ?> saved from <?php echo e(money($goal['target_amount'])); ?> - <?php echo e($transactionCount); ?> transactions</span>
            </div>

            <form method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="id" value="<?php echo e($goalId); ?>">
                <button class="btn btn-danger" type="submit">Yes, Delete</button>
                <a class="btn btn-outline-secondary" href="<?php echo e(app_url('savings/show.php?id=' . $goalId)); ?>">Cancel</a>
            </form>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
