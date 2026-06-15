<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth_guard.php';

$user = current_user($conn);
$userId = current_user_id();
$transactionId = max(0, (int) ($_GET['id'] ?? ($_POST['id'] ?? 0)));

$transaction = db_fetch_one(
    $conn,
    "SELECT
        st.id,
        COALESCE(NULLIF(st.goalid, 0), st.saving_goal_id) AS goalid,
        st.amount,
        st.type,
        st.note,
        st.transaction_date,
        sg.name AS goal_name,
        sg.current_amount
     FROM saving_transactions st
     INNER JOIN saving_goals sg ON sg.id = COALESCE(NULLIF(st.goalid, 0), st.saving_goal_id) AND sg.userid = st.userid
     WHERE st.id = ? AND st.userid = ?
     LIMIT 1",
    'ii',
    [$transactionId, $userId]
);

if (!$transaction) {
    flash('error', 'Saving transaction not found.');
    redirect(app_url('savings/index.php'));
}

$goalId = (int) $transaction['goalid'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        flash('error', 'Your session expired. Please try again.');
        redirect(app_url('savings/delete_transaction.php?id=' . $transactionId));
    }

    mysqli_begin_transaction($conn);
    try {
        $lockedGoal = db_fetch_one(
            $conn,
            'SELECT id, current_amount, target_amount FROM saving_goals WHERE id = ? AND userid = ? FOR UPDATE',
            'ii',
            [$goalId, $userId]
        );

        if (!$lockedGoal) {
            throw new RuntimeException('Saving goal not found.');
        }

        $amount = (float) $transaction['amount'];
        $currentAmount = (float) $lockedGoal['current_amount'];
        $newAmount = $transaction['type'] === 'deposit' ? $currentAmount - $amount : $currentAmount + $amount;

        if ($newAmount < 0) {
            mysqli_rollback($conn);
            flash('error', 'This transaction cannot be deleted because it would make the goal amount negative.');
            redirect(app_url('savings/show.php?id=' . $goalId));
        }

        db_execute($conn, 'DELETE FROM saving_transactions WHERE id = ? AND userid = ?', 'ii', [$transactionId, $userId]);
        db_execute(
            $conn,
            "UPDATE saving_goals
             SET current_amount = ?, status = CASE WHEN ? >= target_amount THEN 'completed' ELSE 'active' END
             WHERE id = ? AND userid = ?",
            'ddii',
            [$newAmount, $newAmount, $goalId, $userId]
        );

        mysqli_commit($conn);
        flash('success', 'Saving transaction deleted.');
        redirect(app_url('savings/show.php?id=' . $goalId));
    } catch (Throwable $exception) {
        mysqli_rollback($conn);
        flash('error', 'Saving transaction could not be deleted.');
        redirect(app_url('savings/show.php?id=' . $goalId));
    }
}

$pageTitle = 'Delete Saving Transaction - Finote';
$activePage = 'savings';
$navUser = $user;

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="app-main">
    <div class="container">
        <div class="saving-card app-card p-4">
            <h1 class="page-title h3 mb-2">Delete Saving Transaction?</h1>
            <p class="text-muted">This will roll back its effect on the saving goal.</p>

            <div class="border rounded-3 p-3 mb-4">
                <strong><?php echo e($transaction['goal_name']); ?></strong><br>
                <span class="text-muted">
                    <?php echo e(ucfirst($transaction['type'])); ?> - <?php echo e(money($transaction['amount'])); ?> on <?php echo e(format_date($transaction['transaction_date'])); ?>
                </span>
            </div>

            <form method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="id" value="<?php echo e($transactionId); ?>">
                <button class="btn btn-danger" type="submit">Yes, Delete</button>
                <a class="btn btn-outline-secondary" href="<?php echo e(app_url('savings/show.php?id=' . $goalId)); ?>">Cancel</a>
            </form>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
