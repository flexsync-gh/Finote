<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth_guard.php';

$user = current_user($conn);
$userId = current_user_id();

db_execute(
    $conn,
    "UPDATE saving_goals
     SET status = CASE WHEN current_amount >= target_amount THEN 'completed' ELSE 'active' END
     WHERE userid = ?",
    'i',
    [$userId]
);

$goals = db_fetch_all(
    $conn,
    'SELECT id, name, target_amount, current_amount, target_date, status
     FROM saving_goals
     WHERE userid = ?
     ORDER BY status, target_date, id DESC',
    'i',
    [$userId]
);

$pageTitle = 'Savings - Finote';
$activePage = 'savings';
$navUser = $user;

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>

<main class="app-main">
    <div class="container">
        <?php require __DIR__ . '/../includes/flash.php'; ?>

        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
            <div>
                <h1 class="page-title mb-1">Savings</h1>
                <p class="text-muted mb-0">Plan goals, record deposits, and keep progress visible.</p>
            </div>
            <a class="btn btn-primary align-self-lg-start" href="<?php echo e(app_url('savings/create.php')); ?>">Add Goal</a>
        </div>

        <?php if (empty($goals)) { ?>
            <div class="saving-card app-card">
                <div class="empty-state">
                    <h2 class="h5">No saving goals yet</h2>
                    <p>Create a goal to start tracking progress toward something specific.</p>
                    <a class="btn btn-primary" href="<?php echo e(app_url('savings/create.php')); ?>">Add Goal</a>
                </div>
            </div>
        <?php } else { ?>
            <div class="row g-3">
                <?php foreach ($goals as $goal) {
                    $target = (float) $goal['target_amount'];
                    $current = (float) $goal['current_amount'];
                    $remaining = max(0, $target - $current);
                    $progress = $target > 0 ? min(100, ($current / $target) * 100) : 0;
                    $statusClass = $goal['status'] === 'completed' ? 'status-safe' : 'status-warning';
                ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="saving-card app-card p-4 h-100">
                            <div class="d-flex justify-content-between gap-3 mb-3">
                                <div>
                                    <h2 class="h5 fw-bold mb-1"><?php echo e($goal['name']); ?></h2>
                                    <p class="text-muted mb-0"><?php echo e(format_date($goal['target_date'])); ?></p>
                                </div>
                                <span class="status-badge <?php echo e($statusClass); ?>"><?php echo e(ucfirst($goal['status'])); ?></span>
                            </div>

                            <div class="d-flex justify-content-between gap-2 mb-2">
                                <span class="fw-semibold"><?php echo e(money($current)); ?></span>
                                <span class="text-muted"><?php echo e(money($target)); ?></span>
                            </div>
                            <div class="progress saving-progress mb-3" role="progressbar" aria-valuenow="<?php echo e((int) $progress); ?>" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar" style="width: <?php echo e($progress); ?>%"></div>
                            </div>

                            <div class="d-flex justify-content-between gap-2 mb-4">
                                <span class="text-muted">Remaining</span>
                                <strong><?php echo e(money($remaining)); ?></strong>
                            </div>

                            <div class="mobile-action-group d-flex flex-wrap gap-2">
                                <a class="btn btn-sm btn-primary" href="<?php echo e(app_url('savings/show.php?id=' . $goal['id'])); ?>">Detail</a>
                                <a class="btn btn-sm btn-outline-secondary" href="<?php echo e(app_url('savings/edit.php?id=' . $goal['id'])); ?>">Edit</a>
                                <a class="btn btn-sm btn-outline-danger" href="<?php echo e(app_url('savings/delete.php?id=' . $goal['id'])); ?>">Delete</a>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
