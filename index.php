<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Landing Page Finote</title>
    <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
      body.dark-mode {
        background-color: #121212;
        color: #ffffff;
      }

      body.dark-mode .bg-white {
        background-color: #1e1e1e !important;
      }

      body.dark-mode .text-muted {
        color: #bbbbbb !important;
      }

      body.dark-mode .navbar {
        background: #1a1a2e !important;
      }

      body.dark-mode footer {
        background: #111 !important;
      }

      body.dark-mode .card,
      body.dark-mode .p-4 {
        background-color: #333333 !important;
      }

      body.dark-mode footer {
        background: #0a0a0a !important;
        border-top: 1px solid #222;
      }

      .cta-finote {
        background: linear-gradient(135deg,#5b3be0,#8c62ff);
      }

      body.dark-mode .cta-finote {
        background: linear-gradient(135deg,#1a1a2e,#252541);
      }

      .prev-img{
        background:linear-gradient(135deg,#6a3df0,#9a6bff);
      }

      .nav-link{
        color: #ffffff;
      }

      body.dark-mode .prev-img{
        background: linear-gradient(135deg,#1a1a2e,#252541);
      }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark" style="background:#5b2be0;">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="#">
      <!-- <img src="FINOTE.png" height="32"> -->
      Finote
    </a>

    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div id="nav" class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-3">
        <li class="nav-item"><a class="nav-link" href="#">Features</a></li>
        <li class="nav-item"><a class="nav-link" href="#">About</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Pricing</a></li>
        <li class="nav-item">
          <a class="btn btn-warning text-white" href="login.php">Try Now</a>
        </li>
        <li class="nav-item">
          <button id="darkToggle" class="btn btn-outline-light btn-sm">
          🌙
          </button>
        </li>
      </ul>
    </div>
  </div>
</nav>

<section class="py-5">
  <div class="container">
    <div class="row align-items-center g-5">

      <div class="col-md-6 text-center text-md-start">
        <h1 class="fw-bold">Manage Your Money<br>the Neat Way</h1>
        <p class="text-muted">
          Finote helps you track your daily expenses, saving goals, budgeting,
          and organize your personal finances in one simple place.
        </p>

        <div class="d-flex gap-3 justify-content-center justify-content-md-start">
          <a href="login.php" class="btn text-white" style="background:#5b2be5;">Start Tracking</a>
          <a href="login.php" class="btn btn-warning text-white">Try Demo</a>
        </div>
      </div>

      <div class="col-md-6 text-center">
        <div class="prev-img p-3 rounded">
          <img src="Screenshot (2).png" class="img-fluid rounded">
        </div>
      </div>

    </div>
  </div>
</section>

<section class="py-5 bg-white text-center">
  <div class="container">
    <h2 class="mb-5">Why Finote?</h2>

    <div class="row g-4 justify-content-scenter">

      <div class="col-md-4">
        <div class="p-4 rounded" style="background:#f3f0ff;">
          <div class="fs-2">💰</div>
          <h5>Expense Tracking</h5>
          <p>Record and track all of your spending accurately.</p>
        </div>
      </div>

      <div class="col-md-4">
        <div class="p-4 rounded" style="background:#f3f0ff;">
          <div class="fs-2">📊</div>
          <h5>Simple Analytics</h5>
          <p>Visualization with clean charts and graphics.</p>
        </div>
      </div>

      <div class="col-md-4">
        <div class="p-4 rounded" style="background:#f3f0ff;">
          <div class="fs-2">🗒️</div>
          <h5>Finance Notes</h5>
          <p>Help you manage your financial goals and budgets.</p>
        </div>
      </div>

    </div>
  </div>
</section>

<section class="cta-finote py-5 text-center text-white">
  <div class="container">
    <h2>Track and control your money today</h2>
    <p>Start managing your personal finances with Finote.</p>
    <a href="login.php" class="btn btn-warning text-white">Get Started</a>
  </div>
</section>

<footer class="py-4 text-white" style="background:#2c1b66;">
  <div class="container d-flex flex-column flex-md-row justify-content-between">
    <div>
      <strong>Finote</strong><br>
      <small>Personal Finance Website</small>
    </div>

    <div class="d-flex gap-3 mt-3 mt-md-0">
      <a href="#" class="text-white text-decoration-none">Privacy</a>
      <a href="#" class="text-white text-decoration-none">Contact</a>
      <a href="#" class="text-white text-decoration-none">ToS & ToU</a>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
  const toggleBtn = document.getElementById("darkToggle");

  if (localStorage.getItem("darkMode") === "enabled") {
    document.body.classList.add("dark-mode");
    toggleBtn.textContent = "🌙";
  }

  toggleBtn.addEventListener("click", () => {
    document.body.classList.toggle("dark-mode");

    if (document.body.classList.contains("dark-mode")) {
      localStorage.setItem("darkMode", "enabled");
      toggleBtn.textContent = "🌙";
    } else {
      localStorage.setItem("darkMode", "disabled");
      toggleBtn.textContent = "☀️";
    }
  });
</script>
</body>
</html>