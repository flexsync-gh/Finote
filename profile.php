<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/auth_guard.php';

$user = current_user($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        flash('error', 'Your session expired. Please try again.');
        redirect(app_url('profile.php'));
    }

    $action = $_POST['action'] ?? 'upload_photo';

    if ($action === 'remove_photo') {
        if (empty($user['profile_photo'])) {
            flash('error', 'You are already using the default avatar.');
            redirect(app_url('profile.php'));
        }

        $oldPhoto = $user['profile_photo'];
        db_execute($conn, 'UPDATE users SET profile_photo = NULL WHERE id = ?', 'i', [$user['id']]);
        delete_profile_photo($oldPhoto);

        flash('success', 'Profile photo removed. Your default avatar is back.');
        redirect(app_url('profile.php'));
    }

    [$isValid, $result] = validate_profile_upload($_FILES['profile_photo'] ?? null);

    if (!$isValid) {
        flash('error', $result);
        redirect(app_url('profile.php'));
    }

    $extension = $result;
    $fileName = 'user_' . $user['id'] . '_' . bin2hex(random_bytes(12)) . '.' . $extension;
    $uploadDir = __DIR__ . '/uploads/profile/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (!move_uploaded_file($_FILES['profile_photo']['tmp_name'], $uploadDir . $fileName)) {
        flash('error', 'Could not save the uploaded image.');
        redirect(app_url('profile.php'));
    }

    $oldPhoto = $user['profile_photo'] ?? null;
    db_execute($conn, 'UPDATE users SET profile_photo = ? WHERE id = ?', 'si', [$fileName, $user['id']]);
    delete_profile_photo($oldPhoto);

    flash('success', 'Profile photo updated.');
    redirect(app_url('profile.php'));
}

$pageTitle = 'Profile - Finote';
$activePage = 'profile';
$navUser = $user;

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/navbar.php';
?>

<main class="app-main">
    <div class="container">
        <?php require __DIR__ . '/includes/flash.php'; ?>

        <div class="row g-4 align-items-start">
            <div class="col-lg-4">
                <div class="app-card p-4 text-center">
                    <img id="profilePreview" class="avatar-lg mb-3" src="<?php echo e(profile_photo_url($user)); ?>" alt="Profile photo">
                    <h1 class="h4 fw-bold mb-1"><?php echo e($user['name']); ?></h1>
                    <p class="text-muted mb-0">Finote member since <?php echo e(format_date($user['created_at'])); ?></p>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="app-card p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
                        <div>
                            <h2 class="page-title h3 mb-1">Profile</h2>
                            <p class="text-muted mb-0">Manage your identity and profile photo.</p>
                        </div>
                        <a class="btn btn-outline-primary align-self-md-start" href="<?php echo e(app_url('dashboard.php')); ?>">Back to Dashboard</a>
                    </div>

                    <dl class="row mb-4">
                        <dt class="col-sm-3">Name</dt>
                        <dd class="col-sm-9"><?php echo e($user['name']); ?></dd>
                        <dt class="col-sm-3">Email</dt>
                        <dd class="col-sm-9"><?php echo e($user['email']); ?></dd>
                        <dt class="col-sm-3">Phone</dt>
                        <dd class="col-sm-9"><?php echo e($user['phonenumber']); ?></dd>
                    </dl>

                    <form method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="upload_photo">
                        <label class="form-label fw-semibold" for="profilePhoto">Upload profile photo</label>
                        <input id="profilePhoto" class="form-control" type="file" name="profile_photo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required>
                        <div class="form-text">JPG, PNG, or WEBP. Maximum size: 2MB.</div>
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <button class="btn btn-primary" type="submit">Save Photo</button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <form method="POST" onsubmit="return confirm('Remove your current profile photo and use the default avatar?');">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="remove_photo">
                        <h3 class="h5 fw-bold mb-1">Reset profile photo</h3>
                        <p class="text-muted mb-3">Remove your uploaded image and return to the default Finote avatar.</p>
                        <button class="btn btn-outline-danger" type="submit" <?php echo empty($user['profile_photo']) ? 'disabled' : ''; ?>>
                            Remove Photo
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.getElementById('profilePhoto').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;

    const preview = document.getElementById('profilePreview');
    preview.src = URL.createObjectURL(file);
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
