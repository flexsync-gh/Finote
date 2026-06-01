<?php include 'auth.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Finote Auth</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

:root{
    --brand:#5b3be0;
    --brand-2:#8c62ff;
    --ink:#17152b;
    --muted:#6f6a85;
    --card:#ffffff;
}

*{
    box-sizing:border-box;
}

body{
    min-height:100vh;
    margin:0;
    color:var(--ink);
    background:
        radial-gradient(circle at top left, rgba(140,98,255,0.28), transparent 32rem),
        radial-gradient(circle at bottom right, rgba(255,193,7,0.18), transparent 28rem),
        linear-gradient(135deg,#f7f4ff 0%,#ffffff 46%,#eef0ff 100%);
    transition:background 0.3s ease,color 0.3s ease;
}

body::before,
body::after{
    content:"";
    position:fixed;
    border-radius:999px;
    pointer-events:none;
    z-index:0;
}

body::before{
    width:310px;
    height:310px;
    left:-95px;
    bottom:8%;
    background:rgba(91,59,224,0.1);
    filter:blur(4px);
}

body::after{
    width:210px;
    height:210px;
    right:8%;
    top:12%;
    border:1px solid rgba(91,59,224,0.18);
}

.auth-page{
    position:relative;
    z-index:1;
    width:100%;
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:32px 18px;
}

.auth-shell{
    position:relative;
    width:100%;
    max-width:980px;
    display:grid;
    grid-template-columns:1fr 440px;
    overflow:hidden;
    border:1px solid rgba(255,255,255,0.72);
    border-radius:28px;
    background:rgba(255,255,255,0.72);
    box-shadow:0 24px 70px rgba(45,32,101,0.18);
    backdrop-filter:blur(22px);
}

.brand-panel{
    position:relative;
    min-height:620px;
    padding:46px;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
    color:white;
    background:
        radial-gradient(circle at 20% 20%, rgba(255,255,255,0.26), transparent 18rem),
        linear-gradient(145deg,var(--brand),var(--brand-2));
}

.brand-panel::after{
    content:"";
    position:absolute;
    inset:auto -70px -85px auto;
    width:260px;
    height:260px;
    border-radius:48px;
    background:rgba(255,255,255,0.13);
    transform:rotate(18deg);
}

.brand-logo{
    width:54px;
    height:54px;
    display:grid;
    place-items:center;
    border-radius:18px;
    background:rgba(255,255,255,0.18);
    box-shadow:inset 0 0 0 1px rgba(255,255,255,0.25);
    font-weight:800;
    font-size:1.35rem;
}

.brand-header{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:16px;
}

.brand-actions{
    display:flex;
    align-items:center;
    gap:12px;
}

.back-home{
    display:inline-flex;
    align-items:center;
    gap:8px;
    min-height:38px;
    padding:0 14px;
    border-radius:999px;
    color:white;
    text-decoration:none;
    font-weight:700;
    background:rgba(255,255,255,0.14);
    border:1px solid rgba(255,255,255,0.2);
    transition:background 0.2s ease,transform 0.2s ease;
}

.back-home:hover,
.back-home:focus{
    color:white;
    background:rgba(255,255,255,0.22);
    transform:translateY(-1px);
}

.brand-panel h1{
    max-width:360px;
    margin:28px 0 14px;
    font-size:2.45rem;
    line-height:1.08;
    font-weight:800;
}

.brand-panel p{
    max-width:380px;
    color:rgba(255,255,255,0.82);
}

.brand-points{
    display:grid;
    gap:14px;
    margin-top:34px;
}

.brand-point{
    display:flex;
    gap:12px;
    align-items:center;
    padding:13px 15px;
    border-radius:16px;
    background:rgba(255,255,255,0.13);
    border:1px solid rgba(255,255,255,0.16);
}

.brand-point span{
    width:28px;
    height:28px;
    display:grid;
    place-items:center;
    flex:0 0 auto;
    border-radius:50%;
    background:rgba(255,255,255,0.22);
    font-weight:700;
}

.auth-content{
    position:relative;
    padding:116px 42px 44px;
    background:var(--card);
}

.auth-box{
    width:100%;
}

.auth-box h2{
    font-weight:800;
    margin-bottom:8px !important;
}

.auth-subtitle{
    color:var(--muted);
    margin-bottom:28px;
    text-align:center;
}

.form-control{
    min-height:50px;
    border-radius:14px;
    border-color:#e1def0;
    background:#fbfaff;
}

.form-control:focus{
    border-color:var(--brand-2);
    box-shadow:0 0 0 0.22rem rgba(91,59,224,0.13);
}

.btn{
    min-height:48px;
    border-radius:14px;
    font-weight:700;
}

.btn-primary{
    border:0;
    background:linear-gradient(135deg,var(--brand),var(--brand-2));
    box-shadow:0 12px 24px rgba(91,59,224,0.22);
}

.btn-warning{
    border:0;
    background:linear-gradient(135deg,#ffb02e,#ff8f3d);
    box-shadow:0 12px 24px rgba(255,143,61,0.2);
}

.hidden{
    display:none;
}

.toggle-btn{
    cursor:pointer;
    color:var(--brand);
    font-weight:700;
}

.theme-toggle{
    width:76px;
    height:38px;
    position:absolute;
    top:44px;
    right:42px;
    z-index:3;
    display:inline-flex;
    align-items:center;
    justify-content:space-between;
    padding:0 11px;
    border:1px solid rgba(91,59,224,0.14);
    border-radius:999px;
    background:#f5f2ff;
    color:#8d84a8;
    box-shadow:0 10px 24px rgba(45,32,101,0.1);
}

.theme-toggle .toggle-icon{
    position:relative;
    z-index:1;
    font-size:14px;
}

.theme-toggle::after{
    content:"";
    position:absolute;
    top:4px;
    left:4px;
    width:28px;
    height:28px;
    border-radius:50%;
    background:linear-gradient(135deg,#ffd76a,#ff9f43);
    box-shadow:0 5px 12px rgba(255,159,67,0.35);
    transition:transform 0.25s ease,background 0.25s ease;
}

.dark-mode{
    color:#f6f3ff;
    background:
        radial-gradient(circle at top left, rgba(91,59,224,0.24), transparent 30rem),
        radial-gradient(circle at bottom right, rgba(140,98,255,0.18), transparent 27rem),
        linear-gradient(135deg,#12101f 0%,#19162c 55%,#0d0c16 100%);
}

.dark-mode .auth-shell{
    border-color:rgba(255,255,255,0.08);
    background:rgba(28,24,45,0.72);
    box-shadow:0 24px 70px rgba(0,0,0,0.34);
}

.dark-mode .auth-content{
    background:#1b1829;
}

.dark-mode .auth-subtitle,
.dark-mode .text-muted{
    color:#b8b1ca !important;
}

.dark-mode .form-control{
    border-color:#343049;
    background:#242033;
    color:#ffffff;
}

.dark-mode .form-control::placeholder{
    color:#938ca7;
}

.dark-mode .theme-toggle{
    border-color:rgba(255,255,255,0.1);
    background:#252138;
    color:#b9b2ce;
}

.dark-mode .theme-toggle::after{
    transform:translateX(38px);
    background:linear-gradient(135deg,#d9dcff,#8c62ff);
    box-shadow:0 5px 12px rgba(140,98,255,0.34);
}

@media (max-width: 860px){
    .auth-shell{
        max-width:480px;
        grid-template-columns:1fr;
    }

    .brand-panel{
        min-height:auto;
        padding:32px;
    }

    .brand-header{
        align-items:flex-start;
    }

    .brand-actions{
        padding-top:48px;
        align-items:flex-end;
    }

    .back-home{
        min-height:34px;
        padding:0 12px;
        font-size:0.92rem;
    }

    .theme-toggle{
        top:32px;
        right:32px;
        border-color:rgba(255,255,255,0.24);
        background:rgba(255,255,255,0.16);
        color:rgba(255,255,255,0.84);
        box-shadow:inset 0 0 0 1px rgba(255,255,255,0.08),0 10px 24px rgba(45,32,101,0.12);
    }

    .brand-panel h1{
        font-size:2rem;
    }

    .brand-points{
        display:none;
    }

    .auth-content{
        padding:30px 24px 34px;
    }
}

@media (max-width: 430px){
    .brand-panel{
        padding:26px 22px;
    }

    .brand-header{
        gap:12px;
    }

    .brand-actions{
        padding-top:46px;
    }

    .brand-logo{
        width:48px;
        height:48px;
        border-radius:16px;
    }

    .theme-toggle{
        top:26px;
        right:22px;
        width:70px;
        height:36px;
    }

    .theme-toggle::after{
        width:26px;
        height:26px;
    }

    .dark-mode .theme-toggle::after{
        transform:translateX(34px);
    }
}

</style>

</head>
<body>

<main class="auth-page">
<section class="auth-shell">
<aside class="brand-panel">
    <div>
        <div class="brand-header">
            <div class="brand-logo">Fi</div>
            <div class="brand-actions">
                <a class="back-home" href="index.php" aria-label="Back to Home">
                    <span aria-hidden="true">&larr;</span>
                    Back to Home
                </a>
            </div>
        </div>
        <h1>Money notes that feel calm and clear.</h1>
        <p>Track spending, budgets, and personal finance goals in one clean workspace.</p>

        <div class="brand-points">
            <div class="brand-point"><span>1</span>Quick daily expense tracking</div>
            <div class="brand-point"><span>2</span>Simple views for budgets and goals</div>
            <div class="brand-point"><span>3</span>Built for personal finance clarity</div>
        </div>
    </div>

    <small>Finote Personal Finance</small>
</aside>

<button id="darkToggle" class="theme-toggle" type="button" aria-label="Toggle dark mode">
    <span class="toggle-icon">&#9728;</span>
    <span class="toggle-icon">&#9790;</span>
</button>

<div class="auth-content">
<div class="auth-box">

    <h2 class="text-center mb-4">Finote</h2>
    <p class="auth-subtitle">Welcome back. Sign in or create an account to continue.</p>

    <?php if($message != "") { ?>

        <div class="alert alert-warning">
            <?php echo $message; ?>
        </div>

    <?php } ?>

    <form method="POST" id="loginForm">

        <input type="hidden" name="action" value="login">

        <div class="mb-3">
            <input type="email"
                   name="email"
                   class="form-control"
                   placeholder="Email"
                   required>
        </div>

        <div class="mb-3">
            <input type="password"
                   name="password"
                   class="form-control"
                   placeholder="Password"
                   required>
        </div>

        <button class="btn btn-primary w-100">
            Login
        </button>

        <p class="mt-3 text-center">
            Login with
            <span class="toggle-btn" onclick="showLoginPhone()">
                Phone Number
            </span>
            instead.
        </p>

        <p class="mt-3 text-center">
            Don't have account?
            <span class="toggle-btn" onclick="showRegister()">
                Register
            </span>
        </p>

    </form>

    <form method="POST" id="loginFormPhone" class="hidden">

        <input type="hidden" name="action" value="loginphone">

        <div class="mb-3">
            <input type="tel"
                   name="phone"
                   class="form-control"
                   placeholder="Phone Number"
                   required>
        </div>

        <div class="mb-3">
            <input type="password"
                   name="password"
                   class="form-control"
                   placeholder="Password"
                   required>
        </div>

        <button class="btn btn-primary w-100">
            Login
        </button>

        <p class="mt-3 text-center">
            Login with
            <span class="toggle-btn" onclick="showLogin()">
                Email Instead
            </span>
            instead.
        </p>

        <p class="mt-3 text-center">
            Don't have account?
            <span class="toggle-btn" onclick="showRegister()">
                Register
            </span>
        </p>

    </form>

    <form method="POST" id="registerForm" class="hidden">

        <input type="hidden" name="action" value="register">

        <div class="mb-3">
            <input type="text"
                   name="username"
                   class="form-control"
                   placeholder="Username"
                   required>
        </div>

        <div class="mb-3">
            <input type="tel"
                   name="phone"
                   class="form-control"
                   placeholder="Phone Number"
                   required>
        </div>

        <div class="mb-3">
            <input type="email"
                   name="email"
                   class="form-control"
                   placeholder="Email"
                   required>
        </div>

        <div class="mb-3">
            <input type="password"
                   name="password"
                   class="form-control"
                   placeholder="Password"
                   required>
        </div>

        <button class="btn btn-warning w-100 text-white">
            Register
        </button>

        <p class="mt-3 text-center">
            Already have account?
            <span class="toggle-btn" onclick="showLogin()">
                Login
            </span>
        </p>

    </form>

</div>
</div>
</section>
</main>

<script>

function showRegister(){
    document.getElementById("loginForm").classList.add("hidden");
    document.getElementById("loginFormPhone").classList.add("hidden");
    document.getElementById("registerForm").classList.remove("hidden");
}

function showLogin(){
    document.getElementById("registerForm").classList.add("hidden");
    document.getElementById("loginFormPhone").classList.add("hidden");
    document.getElementById("loginForm").classList.remove("hidden");
}

function showLoginPhone(){
    document.getElementById("registerForm").classList.add("hidden");
    document.getElementById("loginForm").classList.add("hidden");
    document.getElementById("loginFormPhone").classList.remove("hidden");
}

const darkBtn = document.getElementById("darkToggle");

if(localStorage.getItem("darkMode") === "enabled"){
    document.body.classList.add("dark-mode");
    darkBtn.setAttribute("aria-pressed","true");
}else{
    darkBtn.setAttribute("aria-pressed","false");
}

darkBtn.addEventListener("click", () => {

    document.body.classList.toggle("dark-mode");

    if(document.body.classList.contains("dark-mode")){
        localStorage.setItem("darkMode","enabled");
        darkBtn.setAttribute("aria-pressed","true");
    }else{
        localStorage.setItem("darkMode","disabled");
        darkBtn.setAttribute("aria-pressed","false");
    }

});

</script>

</body>
</html>
