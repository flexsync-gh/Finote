<?php

session_start();

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white d-flex justify-content-center align-items-center vh-100">

<div class="text-center">

    <h1>Welcome, <?php echo $_SESSION['user']; ?> 👋</h1>

    <p>Future Finote Dashboard Here</p>

    <a href="logout.php" class="btn btn-warning">
        Logout
    </a>

</div>

</body>
</html>