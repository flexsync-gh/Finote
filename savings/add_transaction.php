<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth_guard.php';

$user = current_user($conn);
$userId = current_user_id();
$goalId = max(0, (int) ($_GET['id'] ?? ($_POST['goalid'] ?? 0)));
$goal = find_saving_goal($conn, $userId, $goalId);

if (!$goal) {
    flash('error', 'Saving goal not found.');
    redirect(app_url('savings/index.php'));
}

$amount = trim($_POST['amount'] ?? '');
$type = $_POST['type'] ?? 'deposit';
$note = trim($_POST['note'] ?? '');
$transactionDate = $_POST['transaction_date'] ?? date('Y-m-d');
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Your session expired. Please try again.';
    }

    $errors = array_merge($errors, validate_saving_transaction_input($amount, $type, $transactionDate));

    if (empty($errors)) {
        $amountValue = normalize_decimal($amount);

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

            $currentAmount = (float) $lockedGoal['current_amount'];
            $newAmount = $type === 'deposit' ? $currentAmount + $amountValue : $currentAmount - $amountValue;

            if ($newAmount < 0) {
                $errors[] = 'Withdraw amount cannot make current amount negative.';
                mysqli_rollback($conn);
            } else {
                db_execute(
                    $conn,
                    'INSERT INTO saving_transactions (goalid, saving_goal_id, userid, amount, type, note, transaction_date) VALUES (?, ?, ?, ?, ?, ?, ?)',
                    'iiidsss',
                    [$goalId, $goalId, $userId, $amountValue, $type, $note, $transactionDate]
                );

                db_execute(
                    $conn,
                    "UPDATE saving_goals
                     SET current_amount = ?, status = CASE WHEN ? >= target_amount THEN 'completed' ELSE 'active' END
                     WHERE id = ? AND userid = ?",
                    'ddii',
                    [$newAmount, $newAmount, $goalId, $userId]
                );

                mysqli_commit($conn);
                flash('success', 'Saving transaction added.');
                redirect(app_url('savings/show.php?id=' . $goalId));
            }
        } catch (Throwable $exception) {
            mysqli_rollback($conn);
            $errors[] = 'Saving transaction could not be saved.';
        }
    }
}

$pageTitle = 'Add Saving Transaction - Finote';
$activePage = 'savings';
$navUser = $user;

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="app-main">
    <div class="container">
        <div class="saving-card app-card p-4">
            <h1 class="page-title h3 mb-1">Add Saving Transaction</h1>
            <p class="text-muted mb-4"><?php echo e($goal['name']); ?> currently has <?php echo e(money($goal['current_amount'])); ?>.</p>

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
                <input type="hidden" name="goalid" value="<?php echo e($goalId); ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="type">Type</label>
                        <select id="type" class="form-select" name="type" required autofocus>
                            <option value="deposit" <?php echo $type === 'deposit' ? 'selected' : ''; ?>>Deposit</option>
                            <option value="withdraw" <?php echo $type === 'withdraw' ? 'selected' : ''; ?>>Withdraw</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="amount">Amount</label>
                        <input id="amount" class="form-control" name="amount" type="number" min="0.01" step="0.01" value="<?php echo e($amount); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="transaction_date">Transaction Date</label>
                        <input id="transaction_date" class="form-control" name="transaction_date" type="date" value="<?php echo e($transactionDate); ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold" for="note">Note</label>
                        <textarea id="note" class="form-control" name="note" rows="4" maxlength="1000" placeholder="Optional note"><?php echo e($note); ?></textarea>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button class="btn btn-primary" type="submit">Save Transaction</button>
                    <a class="btn btn-outline-secondary" href="<?php echo e(app_url('savings/show.php?id=' . $goalId)); ?>">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
