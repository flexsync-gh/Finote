<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth_guard.php';

$user = current_user($conn);
$userId = current_user_id();
$limit = category_limit();

$categories = db_fetch_all(
    $conn,
    "SELECT c.id, c.name, c.type,
        (SELECT COUNT(*) FROM transactions t WHERE t.userid = c.userid AND t.categoryid = c.id) AS transaction_count
     FROM categories c
     WHERE c.userid = ?
     ORDER BY c.type, c.name",
    'i',
    [$userId]
);

$categoryCount = count($categories);
$atLimit = $categoryCount >= $limit;

$pageTitle = 'Categories - Finote';
$activePage = 'categories';
$navUser = $user;

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="app-main">
    <div class="container">
        <?php require __DIR__ . '/../includes/flash.php'; ?>

        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
            <div>
                <h1 class="page-title mb-1">Categories</h1>
                <p class="text-muted mb-0"><?php echo e($categoryCount); ?> / <?php echo e($limit); ?> categories</p>
            </div>
            <?php if ($atLimit) { ?>
                <button class="btn btn-primary align-self-lg-start" type="button" disabled>Category Limit Reached</button>
            <?php } else { ?>
                <a class="btn btn-primary align-self-lg-start" href="<?php echo e(app_url('categories/create.php')); ?>">Add Category</a>
            <?php } ?>
        </div>

        <div class="app-card p-0 overflow-hidden">
            <?php if (empty($categories)) { ?>
                <div class="empty-state">
                    <h2 class="h5">No categories yet</h2>
                    <p>Add income and expense categories to organize transactions.</p>
                    <a class="btn btn-primary" href="<?php echo e(app_url('categories/create.php')); ?>">Add Category</a>
                </div>
            <?php } else { ?>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th class="text-end">Transactions</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category) { ?>
                                <tr>
                                    <td class="fw-semibold"><?php echo e($category['name']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $category['type'] === 'income' ? 'badge-soft-income' : 'badge-soft-expense'; ?>">
                                            <?php echo e(ucfirst($category['type'])); ?>
                                        </span>
                                    </td>
                                    <td class="text-end"><?php echo e($category['transaction_count']); ?></td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a class="btn btn-outline-secondary" href="<?php echo e(app_url('categories/edit.php?id=' . $category['id'])); ?>">Edit</a>
                                            <a class="btn btn-outline-danger" href="<?php echo e(app_url('categories/delete.php?id=' . $category['id'])); ?>">Delete</a>
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
