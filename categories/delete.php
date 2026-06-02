<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth_guard.php';

$user = current_user($conn);
$userId = current_user_id();
$categoryId = max(0, (int) ($_GET['id'] ?? ($_POST['id'] ?? 0)));
$category = find_category($conn, $userId, $categoryId);

if (!$category) {
    flash('error', 'Category not found.');
    redirect(app_url('categories/index.php'));
}

$transactionCount = category_transaction_count($conn, $userId, $categoryId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        flash('error', 'Your session expired. Please try again.');
        redirect(app_url('categories/delete.php?id=' . $categoryId));
    }

    if ($transactionCount > 0) {
        flash('error', 'This category is used by transactions, so it cannot be deleted.');
        redirect(app_url('categories/index.php'));
    }

    db_execute($conn, 'DELETE FROM categories WHERE id = ? AND userid = ?', 'ii', [$categoryId, $userId]);
    flash('success', 'Category deleted.');
    redirect(app_url('categories/index.php'));
}

$pageTitle = 'Delete Category - Finote';
$activePage = 'categories';
$navUser = $user;

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="app-main">
    <div class="container">
        <div class="app-card p-4">
            <h1 class="page-title h3 mb-2">Delete Category?</h1>
            <p class="text-muted">This action cannot be undone. Please confirm before deleting.</p>

            <div class="border rounded-3 p-3 mb-4">
                <strong><?php echo e($category['name']); ?></strong><br>
                <span class="badge <?php echo $category['type'] === 'income' ? 'badge-soft-income' : 'badge-soft-expense'; ?>">
                    <?php echo e(ucfirst($category['type'])); ?>
                </span>
                <span class="text-muted ms-2"><?php echo e($transactionCount); ?> related transactions</span>
            </div>

            <?php if ($transactionCount > 0) { ?>
                <div class="alert alert-warning">This category is already used by transactions, so it cannot be deleted.</div>
                <a class="btn btn-outline-secondary" href="<?php echo e(app_url('categories/index.php')); ?>">Back to Categories</a>
            <?php } else { ?>
                <form method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="id" value="<?php echo e($categoryId); ?>">
                    <button class="btn btn-danger" type="submit">Yes, Delete</button>
                    <a class="btn btn-outline-secondary" href="<?php echo e(app_url('categories/index.php')); ?>">Cancel</a>
                </form>
            <?php } ?>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
