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

    $name = mb_strtoupper(trim($_POST["name"] ?? ""));
    $email = strtolower(trim($_POST["email"] ?? ""));
    $password = $_POST["password"] ?? "";
    $education = mb_strtoupper(trim($_POST["education"] ?? ""));
    $skills = mb_strtoupper(trim($_POST["skills"] ?? ""));
    $interests = mb_strtoupper(trim($_POST["interests"] ?? ""));
    $level = $_POST["level"] ?? "Beginner";

    // Check empty fields
    if (
        empty($name) ||
        empty($email) ||
        empty($password) ||
        empty($education) ||
        empty($skills) ||
        empty($interests) ||
        empty($level)
    ) {

        $message = "Please fill all fields.";
        $message_type = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "error";

    } elseif (strlen($password) < 6) {

        $message = "Password must contain at least 6 characters.";
        $message_type = "error";

    } else {

        // Check whether email already exists
        $check = $conn->prepare(
            "SELECT user_id FROM users WHERE email = ?"
        );

        $check->bind_param("s", $email);

        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "This email is already registered.";
            $message_type = "error";

        } else {

            // Secure password
            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Insert user
            $stmt = $conn->prepare(
                "INSERT INTO users
                (name, email, password, education, skills, interests, level)
                VALUES (?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "sssssss",
                $name,
                $email,
                $hashed_password,
                $education,
                $skills,
                $interests,
                $level
            );

            if ($stmt->execute()) {

                $message =
                    "Registration successful! You can now login.";

                $message_type = "success";

                // Clear form values
                $_POST = [];

            } else {

                $message =
                    "Registration failed. Please try again.";

                $message_type = "error";
            }

            $stmt->close();
        }

        $check->close();
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Register - Learnora AI</title>

    <link rel="stylesheet"
          href="assets/css/style.css">

    <style>

        .register-section {
            min-height: calc(100vh - 72px);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 50px 20px;
            background:
                linear-gradient(
                    135deg,
                    #fff8f0,
                    #fee2e2
                );
        }

        .register-box {
            width: 100%;
            max-width: 650px;
            background: white;
            padding: 40px;
            border-radius: 18px;
            box-shadow:
                0 15px 40px
                rgba(127, 29, 29, 0.15);
            border: 1px solid #fecaca;
        }

        .register-box h1 {
            text-align: center;
            color: #450a0a;
            margin-bottom: 8px;
        }

        .register-subtitle {
            text-align: center;
            color: #78716c;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            color: #450a0a;
            font-weight: bold;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 13px;
            border: 1px solid #d6d3d1;
            border-radius: 8px;
            outline: none;
            font-size: 15px;
            background: #fff;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: #b91c1c;
            box-shadow:
                0 0 0 3px #fee2e2;
        }

        .form-group textarea {
            min-height: 85px;
            resize: vertical;
        }

        .register-button {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            background: #991b1b;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .register-button:hover {
            background: #7f1d1d;
        }

        .message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        .success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #57534e;
        }

        .login-link a {
            color: #991b1b;
            font-weight: bold;
            text-decoration: none;
        }

        @media (max-width: 600px) {

            .register-box {
                padding: 25px;
            }

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

        <a href="login.php">
            Login
        </a>

        <a href="register.php" class="active">
            Register
        </a>

    </nav>

</header>


<section class="register-section">


    <div class="register-box">


        <h1>
            Create Your Account
        </h1>


        <p class="register-subtitle">
            Tell us about yourself and
            we'll personalize your learning.
        </p>


        <?php if (!empty($message)): ?>

            <div class="message <?php echo $message_type; ?>">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>


        <form method="POST"
              action="">


            <div class="form-group">

                <label>
                    Full Name
                </label>

                <input
                    type="text"
                    name="name"
                    placeholder="Enter your name"
                    value="<?php
                        echo htmlspecialchars(
                            $_POST["name"] ?? ""
                        );
                    ?>"
                    required
                >

            </div>


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
                    placeholder="Minimum 6 characters"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Education
                </label>

                <input
                    type="text"
                    name="education"
                    placeholder="Example: Diploma in Computer Technology"
                    value="<?php
                        echo htmlspecialchars(
                            $_POST["education"] ?? ""
                        );
                    ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Your Skills
                </label>

                <textarea
                    name="skills"
                    placeholder="Example: Python, HTML, CSS, Java"
                    required
                ><?php
                    echo htmlspecialchars(
                        $_POST["skills"] ?? ""
                    );
                ?></textarea>

            </div>


            <div class="form-group">

                <label>
                    Your Interests
                </label>

                <textarea
                    name="interests"
                    placeholder="Example: AI, Machine Learning, Web Development"
                    required
                ><?php
                    echo htmlspecialchars(
                        $_POST["interests"] ?? ""
                    );
                ?></textarea>

            </div>


            <div class="form-group">

                <label>
                    Learning Level
                </label>

                <select
                    name="level"
                    required
                >

                    <option value="Beginner" <?php if (($_POST["level"] ?? "") == "Beginner") echo "selected"; ?>>
                        Beginner
                    </option>

                    <option value="Intermediate" <?php if (($_POST["level"] ?? "") == "Intermediate") echo "selected"; ?>>
                        Intermediate
                    </option>

                    <option value="Advanced" <?php if (($_POST["level"] ?? "") == "Advanced") echo "selected"; ?>>
                        Advanced
                    </option>

                </select>

            </div>


            <button
                type="submit"
                class="register-button"
            >

                Create Account

            </button>


        </form>


        <div class="login-link">

            Already have an account?

            <a href="login.php">
                Login here
            </a>

        </div>


    </div>


</section>


</body>

</html>