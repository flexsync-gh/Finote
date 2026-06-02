<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth_guard.php';

$user = current_user($conn);
$userId = current_user_id();
$limit = category_limit();
$categoryCount = category_count($conn, $userId);
$name = trim($_POST['name'] ?? '');
$type = $_POST['type'] ?? 'expense';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Your session expired. Please try again.';
    }

    if ($categoryCount >= $limit) {
        $errors[] = 'You have reached the 50 category limit.';
    }

    $errors = array_merge($errors, validate_category_input($conn, $userId, $name, $type));

    if (empty($errors)) {
        db_execute(
            $conn,
            'INSERT INTO categories (userid, name, type) VALUES (?, ?, ?)',
            'iss',
            [$userId, $name, $type]
        );

        flash('success', 'Category added.');
        redirect(app_url('categories/index.php'));
    }
}

$pageTitle = 'Add Category - Finote';
$activePage = 'categories';
$navUser = $user;

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="app-main">
    <div class="container">
        <div class="app-card p-4">
            <h1 class="page-title h3 mb-1">Add Category</h1>
            <p class="text-muted mb-4"><?php echo e($categoryCount); ?> / <?php echo e($limit); ?> categories used.</p>

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

            <?php if ($categoryCount >= $limit) { ?>
                <div class="alert alert-warning">You have reached the maximum number of categories.</div>
                <a class="btn btn-outline-secondary" href="<?php echo e(app_url('categories/index.php')); ?>">Back to Categories</a>
            <?php } else { ?>
                <form method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label fw-semibold" for="name">Category Name</label>
                            <input id="name" class="form-control" name="name" maxlength="100" value="<?php echo e($name); ?>" required autofocus>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold" for="type">Type</label>
                            <select id="type" name="type" class="form-select" required>
                                <option value="expense" <?php echo $type === 'expense' ? 'selected' : ''; ?>>Expense</option>
                                <option value="income" <?php echo $type === 'income' ? 'selected' : ''; ?>>Income</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button class="btn btn-primary" type="submit">Save Category</button>
                        <a class="btn btn-outline-secondary" href="<?php echo e(app_url('categories/index.php')); ?>">Cancel</a>
                    </div>
                </form>
            <?php } ?>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
