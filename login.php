<?php

session_start();

if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit();
}

require_once "db.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (empty($email) || empty($password)) {

        $message = "Please enter email and password.";
        $message_type = "error";

    } else {

        $stmt = $conn->prepare(
            "SELECT user_id, name, email, password
             FROM users
             WHERE email = ?"
        );

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows == 1) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user["password"])) {

                $_SESSION["user_id"] = $user["user_id"];
                $_SESSION["user_name"] = $user["name"];
                $_SESSION["user_email"] = $user["email"];

                header("Location: dashboard.php");
                exit();

            } else {

                $message = "Incorrect password.";
                $message_type = "error";
            }

        } else {

            $message = "No account found with this email.";
            $message_type = "error";
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login - Learnora AI</title>

    <link rel="stylesheet"
          href="assets/css/style.css">

    <style>

        .login-section {
            min-height: calc(100vh - 72px);

            display: flex;

            justify-content: center;

            align-items: center;

            padding: 40px 20px;

            background:
                linear-gradient(
                    135deg,
                    #fff8f0,
                    #fee2e2
                );
        }

        .login-box {
            width: 100%;
            max-width: 450px;

            background: white;

            padding: 40px;

            border-radius: 18px;

            box-shadow:
                0 15px 40px
                rgba(127, 29, 29, 0.15);

            border:
                1px solid #fecaca;
        }

        .login-box h1 {
            text-align: center;

            color: #450a0a;

            margin-bottom: 8px;
        }

        .login-subtitle {
            text-align: center;

            color: #78716c;

            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;

            margin-bottom: 7px;

            color: #450a0a;

            font-weight: bold;
        }

        .form-group input {
            width: 100%;

            padding: 13px;

            border:
                1px solid #d6d3d1;

            border-radius: 8px;

            outline: none;

            font-size: 15px;
        }

        .form-group input:focus {
            border-color: #b91c1c;

            box-shadow:
                0 0 0 3px #fee2e2;
        }

        .login-button {
            width: 100%;

            padding: 14px;

            border: none;

            border-radius: 8px;

            background: #991b1b;

            color: white;

            font-size: 16px;

            font-weight: bold;

            cursor: pointer;
        }

        .login-button:hover {
            background: #7f1d1d;
        }

        .message {
            padding: 12px;

            border-radius: 8px;

            margin-bottom: 20px;

            text-align: center;
        }

        .error {
            background: #fee2e2;

            color: #991b1b;

            border:
                1px solid #fca5a5;
        }

        .register-link {
            text-align: center;

            margin-top: 20px;

            color: #57534e;
        }

        .register-link a {
            color: #991b1b;

            font-weight: bold;

            text-decoration: none;
        }

    </style>

</head>


<body>


<header>

    <a href="index.php" class="logo">
        Learnora <span>AI</span>
    </a>

    <nav>

        <a href="index.php">
            Home
        </a>

        <a href="courses.php">
            Explore Courses
        </a>

        <a href="login.php" class="active">
            Login
        </a>

        <a href="register.php">
            Register
        </a>

    </nav>

</header>


<section class="login-section">


    <div class="login-box">


        <h1>
            Welcome Back
        </h1>


        <p class="login-subtitle">
            Login to continue your learning journey.
        </p>


        <?php if (!empty($message)): ?>

            <div class="message <?php echo $message_type; ?>">

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>


        <form method="POST">


            <div class="form-group">

                <label>
                    Email Address
                </label>

                <input
                    type="email"
                    name="email"
                    placeholder="Enter your email"
                    value="<?php
                        echo htmlspecialchars(
                            $_POST["email"] ?? ""
                        );
                    ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                >

            </div>


            <button
                type="submit"
                class="login-button"
            >

                Login

            </button>


        </form>


        <div class="register-link">

            Don't have an account?

            <a href="register.php">
                Create Account
            </a>

        </div>


    </div>


</section>


</body>

</html>