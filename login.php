<?php include 'auth.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Finote Auth</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background:linear-gradient(135deg,#5b3be0,#8c62ff);
    transition:0.3s;
}

.auth-box{
    width:100%;
    max-width:420px;
    background:white;
    padding:35px;
    border-radius:20px;
    box-shadow:0 10px 30px rgba(0,0,0,0.2);
}

.dark-mode{
    background:#121212;
}

.dark-mode .auth-box{
    background:#1f1f1f;
    color:white;
}

.hidden{
    display:none;
}

.toggle-btn{
    cursor:pointer;
    color:#5b3be0;
    font-weight:bold;
}

.dark-toggle{
    position:absolute;
    top:20px;
    right:20px;
}

</style>

</head>
<body>

<button id="darkToggle" class="btn btn-light dark-toggle">
🌙
</button>

<div class="auth-box">

    <h2 class="text-center mb-4">Finote</h2>

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
            <span class="toggle-btn" onclick="showRegister()">
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

<script>

function showRegister(){
    document.getElementById("loginForm").classList.add("hidden");
    document.getElementById("registerForm").classList.remove("hidden");
}

function showLogin(){
    document.getElementById("registerForm").classList.add("hidden");
    document.getElementById("loginForm").classList.remove("hidden");
}

const darkBtn = document.getElementById("darkToggle");

if(localStorage.getItem("darkMode") === "enabled"){
    document.body.classList.add("dark-mode");
}

darkBtn.addEventListener("click", () => {

    document.body.classList.toggle("dark-mode");

    if(document.body.classList.contains("dark-mode")){
        localStorage.setItem("darkMode","enabled");
    }else{
        localStorage.setItem("darkMode","disabled");
    }

});

</script>

</body>
</html>