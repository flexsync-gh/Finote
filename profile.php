<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/auth_guard.php';

$user = current_user($conn);

function verify_profile_current_password($conn, $userId, $currentPassword, &$rehashedPassword = null)
{
    $rehashedPassword = null;
    $account = db_fetch_one($conn, 'SELECT password FROM users WHERE id = ? LIMIT 1', 'i', [$userId]);
    $storedPassword = $account['password'] ?? '';

    if ($storedPassword === '' || $currentPassword === '') {
        return false;
    }

    if (password_verify($currentPassword, $storedPassword)) {
        if (password_needs_rehash($storedPassword, PASSWORD_DEFAULT)) {
            $rehashedPassword = password_hash($currentPassword, PASSWORD_DEFAULT);
        }

        return true;
    }

    $passwordInfo = password_get_info($storedPassword);
    if (empty($passwordInfo['algo']) && hash_equals($storedPassword, $currentPassword)) {
        $rehashedPassword = password_hash($currentPassword, PASSWORD_DEFAULT);
        return true;
    }

    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        flash('error', 'Your session expired. Please try again.');
        redirect(app_url('profile.php'));
    }

    $action = $_POST['action'] ?? 'upload_photo';

    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phonenumber'] ?? '');
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $errors = [];

        if ($name === '') {
            $errors[] = 'Name is required.';
        } elseif (text_length($name) > 70) {
            $errors[] = 'Name must be 70 characters or fewer.';
        }

        if ($email === '') {
            $errors[] = 'Email is required.';
        } elseif (text_length($email) > 80) {
            $errors[] = 'Email must be 80 characters or fewer.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }

        if ($phone === '') {
            $errors[] = 'Phone number is required.';
        } elseif (text_length($phone) > 255) {
            $errors[] = 'Phone number must be 255 characters or fewer.';
        }

        if ($newPassword !== '' && text_length($newPassword) < 6) {
            $errors[] = 'New password must be at least 6 characters.';
        }

        if ($newPassword !== '' && $newPassword !== $confirmPassword) {
            $errors[] = 'Password confirmation does not match.';
        }

        if ($email !== '' && text_length($email) <= 80 && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $existingEmail = db_fetch_one(
                $conn,
                'SELECT id FROM users WHERE LOWER(email) = LOWER(?) AND id <> ? LIMIT 1',
                'si',
                [$email, $user['id']]
            );

            if ($existingEmail) {
                $errors[] = 'Email is already used by another account.';
            }
        }

        if ($phone !== '' && text_length($phone) <= 255) {
            $existingPhone = db_fetch_one(
                $conn,
                'SELECT id FROM users WHERE phonenumber = ? AND id <> ? LIMIT 1',
                'si',
                [$phone, $user['id']]
            );

            if ($existingPhone) {
                $errors[] = 'Phone number is already used by another account.';
            }
        }

        $emailChanged = $email !== ($user['email'] ?? '');
        $phoneChanged = $phone !== ($user['phonenumber'] ?? '');
        $passwordChanged = $newPassword !== '';
        $needsCurrentPassword = $emailChanged || $phoneChanged || $passwordChanged;
        $rehashedPassword = null;

        if ($needsCurrentPassword && !verify_profile_current_password($conn, (int) $user['id'], $currentPassword, $rehashedPassword)) {
            $errors[] = 'Current password is required and must be correct to change email, phone, or password.';
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                flash('error', $error);
            }

            redirect(app_url('profile.php'));
        }

        $passwordForUpdate = $passwordChanged ? password_hash($newPassword, PASSWORD_DEFAULT) : $rehashedPassword;

        if ($passwordForUpdate !== null) {
            db_execute(
                $conn,
                'UPDATE users SET name = ?, email = ?, phonenumber = ?, password = ?, updated_at = NOW() WHERE id = ?',
                'ssssi',
                [$name, $email, $phone, $passwordForUpdate, $user['id']]
            );
        } else {
            db_execute(
                $conn,
                'UPDATE users SET name = ?, email = ?, phonenumber = ?, updated_at = NOW() WHERE id = ?',
                'sssi',
                [$name, $email, $phone, $user['id']]
            );
        }

        $_SESSION['user'] = $name;
        flash('success', 'Profile information updated.');
        redirect(app_url('profile.php'));
    }

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

    if ($action !== 'upload_photo') {
        flash('error', 'Unsupported profile action.');
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

        <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
            <div>
                <h1 class="page-title mb-1">Profile</h1>
                <p class="text-muted mb-0">Manage your account information and profile photo.</p>
            </div>
            <a class="btn btn-outline-primary align-self-md-start" href="<?php echo e(app_url('dashboard.php')); ?>">Back to Dashboard</a>
        </div>

        <div class="row g-4 align-items-start">
            <div class="col-lg-4">
                <div class="app-card profile-photo-card p-4 text-center">
                    <img id="profilePreview" class="avatar-lg mb-3" src="<?php echo e(profile_photo_url($user)); ?>" alt="Profile photo">
                    <h2 class="h4 fw-bold mb-1"><?php echo e($user['name']); ?></h2>
                    <p class="text-muted mb-4">Finote member since <?php echo e(format_date($user['created_at'])); ?></p>

                    <form method="POST" enctype="multipart/form-data" class="text-start">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="upload_photo">
                        <label class="form-label fw-semibold" for="profilePhoto">Upload profile photo</label>
                        <input id="profilePhoto" class="form-control" type="file" name="profile_photo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required>
                        <div class="form-text">JPG, PNG, or WEBP. Maximum size: 2MB.</div>
                        <button class="btn btn-primary w-100 mt-3" type="submit">Save Photo</button>
                    </form>

                    <form method="POST" class="mt-3" onsubmit="return confirm('Remove your current profile photo and use the default avatar?');">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="remove_photo">
                        <button class="btn btn-outline-danger w-100" type="submit" <?php echo empty($user['profile_photo']) ? 'disabled' : ''; ?>>
                            Remove Photo
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="app-card p-4 mb-4">
                    <div class="profile-section-heading mb-4">
                        <h2 class="h4 fw-bold mb-1">Account Information</h2>
                        <p class="text-muted mb-0">Changing email or phone requires your current password.</p>
                    </div>

                    <form method="POST">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="update_profile">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="name">Name</label>
                                <input id="name" class="form-control" type="text" name="name" value="<?php echo e($user['name']); ?>" maxlength="70" required autocomplete="name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="email">Email</label>
                                <input id="email" class="form-control" type="email" name="email" value="<?php echo e($user['email']); ?>" maxlength="80" required autocomplete="email">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="phonenumber">Phone Number</label>
                                <input id="phonenumber" class="form-control" type="tel" name="phonenumber" value="<?php echo e($user['phonenumber']); ?>" maxlength="255" required autocomplete="tel">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="currentPasswordInfo">Current Password</label>
                                <input id="currentPasswordInfo" class="form-control" type="password" name="current_password" autocomplete="current-password">
                                <div class="form-text">Required only when email or phone changes.</div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button class="btn btn-primary" type="submit">Save Account</button>
                        </div>
                    </form>
                </div>

                <div class="app-card p-4">
                    <div class="profile-section-heading mb-4">
                        <h2 class="h4 fw-bold mb-1">Change Password</h2>
                        <p class="text-muted mb-0">Use at least 6 characters for your new password.</p>
                    </div>

                    <form method="POST">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="update_profile">
                        <input type="hidden" name="name" value="<?php echo e($user['name']); ?>">
                        <input type="hidden" name="email" value="<?php echo e($user['email']); ?>">
                        <input type="hidden" name="phonenumber" value="<?php echo e($user['phonenumber']); ?>">

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" for="currentPasswordChange">Current Password</label>
                                <input id="currentPasswordChange" class="form-control" type="password" name="current_password" required autocomplete="current-password">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" for="newPassword">New Password</label>
                                <input id="newPassword" class="form-control" type="password" name="new_password" minlength="6" required autocomplete="new-password">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" for="confirmPassword">Confirm Password</label>
                                <input id="confirmPassword" class="form-control" type="password" name="confirm_password" minlength="6" required autocomplete="new-password">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button class="btn btn-primary" type="submit">Update Password</button>
                        </div>
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
