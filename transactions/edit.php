<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/_helpers.php';

$user = current_user($conn);
$userId = (int) $_SESSION['user_id'];
$transactionId = max(0, (int) ($_GET['id'] ?? 0));
$transaction = find_transaction($conn, $userId, $transactionId);

if (!$transaction) {
    flash('error', 'Transaction not found.');
    redirect(app_url('transactions/index.php'));
}

$options = transaction_options($conn, $userId);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction = array_merge($transaction, [
        'type' => $_POST['type'] ?? '',
        'accountid' => $_POST['accountid'] ?? '',
        'categoryid' => $_POST['categoryid'] ?? '',
        'amount' => $_POST['amount'] ?? '',
        'description' => $_POST['description'] ?? '',
        'transaction_date' => $_POST['transaction_date'] ?? '',
    ]);

    if (!verify_csrf()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $errors = validate_transaction_input($conn, $userId, $_POST);
    }

    if (empty($errors)) {
        db_execute(
            $conn,
            'UPDATE transactions SET accountid = ?, categoryid = ?, amount = ?, description = ?, type = ?, transaction_date = ? WHERE id = ? AND userid = ?',
            'iidsssii',
            [
                (int) $_POST['accountid'],
                (int) $_POST['categoryid'],
                (float) $_POST['amount'],
                trim($_POST['description'] ?? ''),
                $_POST['type'],
                $_POST['transaction_date'],
                $transactionId,
                $userId,
            ]
        );

        flash('success', 'Transaction updated.');
        redirect(app_url('transactions/index.php'));
    }
}

$pageTitle = 'Edit Transaction - Finote';
$activePage = 'transactions';
$navUser = $user;

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="app-main">
    <div class="container">
        <div class="app-card p-4">
            <h1 class="page-title h3 mb-1">Edit Transaction</h1>
            <p class="text-muted mb-4">Update this transaction safely.</p>
            <?php transaction_form(app_url('transactions/edit.php?id=' . $transactionId), $transaction, $options['accounts'], $options['categories'], $errors); ?>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
