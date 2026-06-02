<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth_guard.php';

$user = current_user($conn);
$userId = current_user_id();
$categoryId = max(0, (int) ($_GET['id'] ?? 0));
$category = find_category($conn, $userId, $categoryId);

if (!$category) {
    flash('error', 'Category not found.');
    redirect(app_url('categories/index.php'));
}

$name = trim($_POST['name'] ?? $category['name']);
$type = $_POST['type'] ?? $category['type'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Your session expired. Please try again.';
    }

    $errors = array_merge($errors, validate_category_input($conn, $userId, $name, $type, $categoryId));

    if (empty($errors)) {
        db_execute(
            $conn,
            'UPDATE categories SET name = ?, type = ? WHERE id = ? AND userid = ?',
            'ssii',
            [$name, $type, $categoryId, $userId]
        );

        flash('success', 'Category updated.');
        redirect(app_url('categories/index.php'));
    }
}

$pageTitle = 'Edit Category - Finote';
$activePage = 'categories';
$navUser = $user;

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="app-main">
    <div class="container">
        <div class="app-card p-4">
            <h1 class="page-title h3 mb-1">Edit Category</h1>
            <p class="text-muted mb-4">Update this category.</p>

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
        </div>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
