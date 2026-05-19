<?php
session_start();
include 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $action = $_POST['action'];

    if ($action == "register") {

        $username = trim($_POST['username']);
        $phone = trim($_POST['phone']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $check = mysqli_query($conn,
            "SELECT * FROM users WHERE email='$email' OR name='$username' OR phonenumber='$phone'"
        );

        if (mysqli_num_rows($check) > 0) {

            $message = "Account already exists.";

        } else {

            $sql = "INSERT INTO users(name,email,password, phonenumber)
                    VALUES('$username','$email','$hashedPassword','$phone')";

            if (mysqli_query($conn, $sql)) {

                $_SESSION['user'] = $username;

                header("Location: dashboard.php");
                exit();

            } else {

                $message = "Registration failed.";

            }
        }
    }

    if ($action == "login") {

        $email = trim($_POST['email']);
        $password = $_POST['password'];

        $sql = "SELECT * FROM users WHERE email='$email'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {

            $user = mysqli_fetch_assoc($result);

            if (password_verify($password, $user['password'])) {

                $_SESSION['user'] = $user['name'];

                header("Location: dashboard.php");
                exit();

            } else {

                $message = "Wrong password.";

            }

        } else {

            $message = "Account not found.";

        }
    }

    if ($action == "loginphone") {

        $phone = trim($_POST['phone']);
        $password = $_POST['password'];

        $sql = "SELECT * FROM users WHERE phonenumber='$phone'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {

            $user = mysqli_fetch_assoc($result);

            if (password_verify($password, $user['password'])) {

                $_SESSION['user'] = $user['name'];

                header("Location: dashboard.php");
                exit();

            } else {

                $message = "Wrong password.";

            }

        } else {

            $message = "Account not found.";

        }
    }
}
?>