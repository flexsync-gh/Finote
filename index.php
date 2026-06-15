<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Finote - Personal Finance Tracker';
$previewImage = is_file(__DIR__ . '/Screenshot (2).png') ? app_url('Screenshot (2).png') : '';

require __DIR__ . '/includes/header.php';
?>

<nav class="navbar navbar-expand-lg app-navbar landing-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?php echo e(app_url('index.php')); ?>">Finote</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#landingNav" aria-controls="landingNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div id="landingNav" class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-3">
                <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                <li class="nav-item"><a class="nav-link" href="#preview">Preview</a></li>
                <li class="nav-item"><a class="nav-link" href="#start">Start</a></li>
                <li class="nav-item d-flex align-items-center">
                    <button id="darkToggle" class="app-theme-toggle" type="button" aria-label="Toggle dark mode" data-theme-toggle>
                        <span class="toggle-icon">&#9728;</span>
                        <span class="toggle-icon">&#9790;</span>
                    </button>
                </li>
                <li class="nav-item">
                    <a class="btn btn-warning text-white ms-lg-2" href="<?php echo e(app_url('login.php')); ?>">Login</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="landing-page">
    <section class="landing-hero">
        <div class="container">
            <div class="row align-items-center g-4 g-lg-5">
                <div class="col-lg-6">
                    <div class="landing-copy">
                        <span class="landing-kicker">Personal Finance Workspace</span>
                        <h1>Finote</h1>
                        <p>Track spending, organize accounts, plan budgets, and follow saving goals in one calm dashboard built for everyday money decisions.</p>
                        <div class="d-flex flex-wrap gap-3">
                            <a class="btn btn-primary btn-lg" href="<?php echo e(app_url('login.php')); ?>">Start Tracking</a>
                            <a class="btn btn-outline-primary btn-lg" href="#features">Explore Features</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" id="preview">
                    <div class="landing-preview">
                        <?php if ($previewImage !== '') { ?>
                            <img src="<?php echo e($previewImage); ?>" alt="Finote dashboard preview">
                        <?php } else { ?>
                            <div class="landing-preview-fallback">
                                <div class="landing-preview-line"></div>
                                <div class="landing-preview-grid">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                                <div class="landing-preview-chart"></div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="landing-section">
        <div class="container">
            <div class="landing-section-heading text-center">
                <h2 class="page-title h1 mb-2">Built for clear money habits</h2>
                <p class="text-muted mb-0">Everything important stays close without making the workspace feel crowded.</p>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-4">
                    <div class="landing-feature-card h-100">
                        <div class="landing-feature-icon">Rp</div>
                        <h3 class="h5 fw-bold">Expense Tracking</h3>
                        <p>Record income and expenses with accounts, categories, dates, and useful transaction notes.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="landing-feature-card h-100">
                        <div class="landing-feature-icon">%</div>
                        <h3 class="h5 fw-bold">Budget Control</h3>
                        <p>Compare monthly spending against category budgets so overspending is easier to catch early.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="landing-feature-card h-100">
                        <div class="landing-feature-icon">Go</div>
                        <h3 class="h5 fw-bold">Saving Goals</h3>
                        <p>Plan goals, log deposits, and keep progress visible alongside the rest of your finances.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="start" class="landing-cta">
        <div class="container">
            <div class="landing-card">
                <div>
                    <h2 class="h1 fw-bold mb-2">Ready to make your money easier to read?</h2>
                    <p class="mb-0">Open Finote and start building a cleaner record of your daily finances.</p>
                </div>
                <a class="btn btn-warning text-white btn-lg" href="<?php echo e(app_url('login.php')); ?>">Get Started</a>
            </div>
        </div>
    </section>
</main>

<footer class="landing-footer py-4">
    <div class="container d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <strong>Finote</strong>
            <div class="small">Personal Finance Website</div>
        </div>
        <div class="d-flex flex-wrap gap-3">
            <a href="<?php echo e(app_url('login.php')); ?>">Login</a>
            <a href="#features">Features</a>
            <a href="#start">Start</a>
        </div>
    </div>
</footer>

<?php require __DIR__ . '/includes/footer.php'; ?>
