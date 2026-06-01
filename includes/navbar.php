<?php
$activePage = $activePage ?? '';
$navUser = $navUser ?? null;
?>
<nav class="navbar navbar-expand-lg app-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?php echo e(app_url('dashboard.php')); ?>">Finote</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#appNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div id="appNav" class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item">
                    <a class="nav-link <?php echo $activePage === 'dashboard' ? 'active' : ''; ?>" href="<?php echo e(app_url('dashboard.php')); ?>">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $activePage === 'transactions' ? 'active' : ''; ?>" href="<?php echo e(app_url('transactions/index.php')); ?>">Transactions</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $activePage === 'profile' ? 'active' : ''; ?>" href="<?php echo e(app_url('profile.php')); ?>">Profile</a>
                </li>
                <li class="nav-item d-flex align-items-center">
                    <button id="appDarkToggle" class="app-theme-toggle" type="button" aria-label="Toggle dark mode">
                        <span class="toggle-icon">&#9728;</span>
                        <span class="toggle-icon">&#9790;</span>
                    </button>
                </li>
                <li class="nav-item">
                    <a class="btn btn-warning text-white ms-lg-2" href="<?php echo e(app_url('logout.php')); ?>">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
