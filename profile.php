<?php

session_start();

require_once "db.php";

// Login check
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$message = "";
$message_type = "";

// Get current user data
$stmt = $conn->prepare(
    "SELECT user_id, name, email, education, interests, skills, level
     FROM users
     WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();


// Update profile
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = mb_strtoupper(trim($_POST["name"] ?? ""));
    $education = mb_strtoupper(trim($_POST["education"] ?? ""));
    $interests = mb_strtoupper(trim($_POST["interests"] ?? ""));
    $skills = mb_strtoupper(trim($_POST["skills"] ?? ""));
    $level = trim($_POST["level"] ?? "Beginner");

    if (
        empty($name) ||
        empty($interests) ||
        empty($skills) ||
        empty($level)
    ) {

        $message = "Please fill all the required fields.";
        $message_type = "error";

    } else {

        $update = $conn->prepare(
            "UPDATE users
             SET name = ?,
                 education = ?,
                 interests = ?,
                 skills = ?,
                 level = ?
             WHERE user_id = ?"
        );

        $update->bind_param(
            "sssssi",
            $name,
            $education,
            $interests,
            $skills,
            $level,
            $user_id
        );

        if ($update->execute()) {

            $_SESSION["user_name"] = $name;

            $message = "Profile updated successfully!";
            $message_type = "success";

            // Refresh values
            $user["name"] = $name;
            $user["education"] = $education;
            $user["interests"] = $interests;
            $user["skills"] = $skills;
            $user["level"] = $level;

        } else {

            $message = "Something went wrong. Please try again.";
            $message_type = "error";
        }

        $update->close();
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        My Profile - Learnora AI
    </title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

    <style>

        * {
            box-sizing: border-box;
        }

        .profile-page {

            min-height:
                calc(100vh - 72px);

            padding:
                55px 20px;

            background:
                #fff8f0;

        }

        .profile-container {

            width: 100%;

            max-width: 650px;

            margin: auto;

            background: white;

            padding: 40px;

            border-radius: 20px;

            border:
                1px solid #fecaca;

            box-shadow:
                0 15px 40px
                rgba(127, 29, 29, 0.12);

        }

        .profile-heading {

            text-align: center;

            margin-bottom: 30px;

        }

        .profile-icon {

            width: 75px;

            height: 75px;

            margin: 0 auto 15px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 50%;

            background:
                #fee2e2;

            font-size: 35px;

        }

        .profile-heading h1 {

            color:
                #450a0a;

            margin-bottom: 8px;

        }

        .profile-heading p {

            color:
                #78716c;

        }

        .message {

            padding: 13px;

            border-radius: 8px;

            text-align: center;

            margin-bottom: 20px;

            font-weight: bold;

        }

        .success {

            background:
                #dcfce7;

            color:
                #166534;

            border:
                1px solid #86efac;

        }

        .error {

            background:
                #fee2e2;

            color:
                #991b1b;

            border:
                1px solid #fca5a5;

        }

        .form-group {

            margin-bottom: 20px;

        }

        .form-group label {

            display: block;

            margin-bottom: 7px;

            color:
                #450a0a;

            font-weight: bold;

        }

        .form-group input,
        .form-group select {

            width: 100%;

            padding: 13px;

            border:
                1px solid #d6d3d1;

            border-radius: 8px;

            outline: none;

            font-size: 15px;

            background: white;

        }

        .form-group input:focus,
        .form-group select:focus {

            border-color:
                #991b1b;

            box-shadow:
                0 0 0 3px #fee2e2;

        }

        .email-box {

            background:
                #f5f5f4;

            color:
                #78716c;

            cursor: not-allowed;

        }

        .hint {

            display: block;

            margin-top: 6px;

            color:
                #a8a29e;

            font-size: 12px;

        }

        .save-button {

            width: 100%;

            padding: 14px;

            border: none;

            border-radius: 8px;

            background:
                #991b1b;

            color: white;

            font-size: 16px;

            font-weight: bold;

            cursor: pointer;

        }

        .save-button:hover {

            background:
                #7f1d1d;

        }

        .back-link {

            display: block;

            text-align: center;

            margin-top: 20px;

            color:
                #991b1b;

            text-decoration: none;

            font-weight: bold;

        }

        @media (max-width: 600px) {

            .profile-container {

                padding: 28px 22px;

            }

        }

    </style>

</head>


<body>


<header>

    <div class="logo">
        Learnora AI
    </div>

    <nav>

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="courses.php">
            Courses
        </a>

        <a href="my_courses.php">
            My Courses
        </a>

        <a href="logout.php">
            Logout
        </a>

    </nav>

</header>


<section class="profile-page">


    <div class="profile-container">


        <div class="profile-heading">

            <div class="profile-icon">
                👤
            </div>

            <h1>
                My Profile
            </h1>

            <p>
                Tell Learnora AI about your
                learning goals.
            </p>

        </div>


        <?php if (!empty($message)): ?>

            <div
                class="message
                <?php echo $message_type; ?>"
            >

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>


        <form method="POST">


            <!-- NAME -->

            <div class="form-group">

                <label>
                    Full Name
                </label>

                <input
                    type="text"
                    name="name"
                    value="<?php
                        echo htmlspecialchars(
                            $user["name"] ?? ""
                        );
                    ?>"
                    required
                >

            </div>


            <!-- EMAIL -->

            <div class="form-group">

                <label>
                    Email Address
                </label>

                <input
                    type="email"
                    class="email-box"
                    value="<?php
                        echo htmlspecialchars(
                            $user["email"] ?? ""
                        );
                    ?>"
                    readonly
                >

                <span class="hint">
                    Email cannot be changed here.
                </span>

            </div>


            <!-- EDUCATION -->

            <div class="form-group">

                <label>
                    Education / Background
                </label>

                <input
                    type="text"
                    name="education"
                    placeholder="Example: Computer Science, Information Technology, Data Science"
                    value="<?php
                        echo htmlspecialchars(
                            $user["education"] ?? ""
                        );
                    ?>"
                >

                <span class="hint">
                    Your degree or area of study (used by Learnora AI for smart recommendations).
                </span>

            </div>


            <!-- INTERESTS -->

            <div class="form-group">

                <label>
                    Learning Interest
                </label>

                <input
                    type="text"
                    name="interests"
                    placeholder="Example: AI, Data Science"
                    value="<?php
                        echo htmlspecialchars(
                            $user["interests"] ?? ""
                        );
                    ?>"
                    required
                >

                <span class="hint">
                    You can enter multiple interests.
                </span>

            </div>


            <!-- SKILLS -->

            <div class="form-group">

                <label>
                    Your Skills
                </label>

                <input
                    type="text"
                    name="skills"
                    placeholder="Example: Python, SQL, HTML"
                    value="<?php
                        echo htmlspecialchars(
                            $user["skills"] ?? ""
                        );
                    ?>"
                    required
                >

                <span class="hint">
                    Separate multiple skills with commas.
                </span>

            </div>


            <!-- LEVEL -->

            <div class="form-group">

                <label>
                    Learning Level
                </label>

                <select
                    name="level"
                    required
                >

                    <option
                        value="Beginner"
                        <?php
                        if (
                            ($user["level"] ?? "")
                            == "Beginner"
                        ) {
                            echo "selected";
                        }
                        ?>
                    >
                        Beginner
                    </option>

                    <option
                        value="Intermediate"
                        <?php
                        if (
                            ($user["level"] ?? "")
                            == "Intermediate"
                        ) {
                            echo "selected";
                        }
                        ?>
                    >
                        Intermediate
                    </option>

                    <option
                        value="Advanced"
                        <?php
                        if (
                            ($user["level"] ?? "")
                            == "Advanced"
                        ) {
                            echo "selected";
                        }
                        ?>
                    >
                        Advanced
                    </option>

                </select>

            </div>


            <button
                type="submit"
                class="save-button"
            >

                Save Profile

            </button>


        </form>


        <a
            href="dashboard.php"
            class="back-link"
        >

            ← Back to Dashboard

        </a>


    </div>


</section>


</body>

</html>