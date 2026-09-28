<?php

session_start();

require_once "db.php";


// ==========================================
// GET COURSE ID
// ==========================================

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {

    header("Location: courses.php");
    exit();

}

$course_id = intval($_GET["id"]);


// ==========================================
// GET COURSE DETAILS
// ==========================================

$stmt = $conn->prepare(
    "SELECT *
     FROM courses
     WHERE course_id = ?"
);

$stmt->bind_param("i", $course_id);

$stmt->execute();

$result = $stmt->get_result();


// Course not found
if ($result->num_rows == 0) {

    header("Location: courses.php");
    exit();

}

$course = $result->fetch_assoc();


// ==========================================
// CHECK LOGIN / ENROLLMENT
// ==========================================

$is_logged_in = isset($_SESSION["user_id"]);

$is_enrolled = false;

if ($is_logged_in) {

    $user_id = $_SESSION["user_id"];
    log_user_activity($conn, $user_id, $course_id, 'view');

    $enroll_check = $conn->prepare(
        "SELECT enrollment_id
         FROM enrollments
         WHERE user_id = ?
         AND course_id = ?"
    );

    $enroll_check->bind_param(
        "ii",
        $user_id,
        $course_id
    );

    $enroll_check->execute();

    $enroll_result = $enroll_check->get_result();

    if ($enroll_result->num_rows > 0) {

        $is_enrolled = true;

    }

    $enroll_check->close();
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

        <?php
        echo htmlspecialchars(
            $course["title"]
        );
        ?>

        - Learnora AI

    </title>


    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >


    <style>

        /* =====================================
           COURSE DETAILS
        ===================================== */

        .details-page {

            min-height:
                calc(100vh - 72px);

            padding:
                60px 8%;

            background:
                #fff8f0;

        }


        .back-link {

            display:
                inline-block;

            margin-bottom:
                25px;

            color:
                #991b1b;

            text-decoration:
                none;

            font-weight:
                bold;

        }


        .course-details {

            max-width:
                1100px;

            margin:
                auto;

            background:
                white;

            border:
                1px solid #fecaca;

            border-radius:
                20px;

            overflow:
                hidden;

            box-shadow:
                0 15px 40px
                rgba(
                    127,
                    29,
                    29,
                    0.12
                );

        }


        /* =====================================
           TOP BANNER
        ===================================== */

        .details-banner {

            min-height:
                250px;

            display:
                flex;

            align-items:
                center;

            padding:
                50px;

            background:

                radial-gradient(
                    circle at 85% 30%,
                    #b91c1c,
                    transparent 35%
                ),

                linear-gradient(
                    135deg,
                    #1c0505,
                    #450a0a,
                    #7f1d1d
                );

            color:
                white;

        }


        .details-banner-content {

            max-width:
                750px;

        }


        .category-badge {

            display:
                inline-block;

            padding:
                7px 14px;

            border:
                1px solid #c9a227;

            border-radius:
                20px;

            color:
                #c9a227;

            font-size:
                13px;

            font-weight:
                bold;

            margin-bottom:
                15px;

        }


        .details-banner h1 {

            font-size:
                42px;

            line-height:
                1.2;

            margin-bottom:
                15px;

        }


        .details-banner p {

            color:
                #fee2e2;

            line-height:
                1.6;

        }


        /* =====================================
           CONTENT
        ===================================== */

        .details-content {

            padding:
                40px 50px;

        }


        .info-grid {

            display:
                grid;

            grid-template-columns:
                repeat(
                    3,
                    1fr
                );

            gap:
                18px;

            margin-bottom:
                35px;

        }


        .info-box {

            padding:
                20px;

            background:
                #fff8f0;

            border:
                1px solid #fecaca;

            border-radius:
                12px;

        }


        .info-box span {

            display:
                block;

            color:
                #78716c;

            font-size:
                13px;

            margin-bottom:
                7px;

        }


        .info-box strong {

            color:
                #450a0a;

            font-size:
                16px;

        }


        .details-section {

            margin-bottom:
                30px;

        }


        .details-section h2 {

            color:
                #450a0a;

            margin-bottom:
                12px;

        }


        .details-section p {

            color:
                #57534e;

            line-height:
                1.8;

        }


        .skills {

            display:
                flex;

            gap:
                10px;

            flex-wrap:
                wrap;

            margin-top:
                15px;

        }


        .skill {

            padding:
                8px 13px;

            background:
                #fee2e2;

            color:
                #991b1b;

            border-radius:
                20px;

            font-size:
                13px;

            font-weight:
                bold;

        }


        /* =====================================
           ENROLL AREA
        ===================================== */

        .enroll-area {

            margin-top:
                35px;

            padding-top:
                30px;

            border-top:
                1px solid #e7e5e4;

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            gap:
                20px;

            flex-wrap:
                wrap;

        }


        .enroll-text h3 {

            color:
                #450a0a;

            margin-bottom:
                6px;

        }


        .enroll-text p {

            color:
                #78716c;

        }


        .enroll-button {

            display:
                inline-block;

            padding:
                14px 30px;

            background:
                #991b1b;

            color:
                white;

            text-decoration:
                none;

            border-radius:
                8px;

            font-weight:
                bold;

        }


        .enroll-button:hover {

            background:
                #7f1d1d;

        }


        .login-button {

            background:
                #450a0a;

        }


        .already-enrolled {

            display:
                inline-block;

            padding:
                14px 25px;

            background:
                #dcfce7;

            color:
                #166534;

            border:
                1px solid #86efac;

            border-radius:
                8px;

            font-weight:
                bold;

        }


        /* =====================================
           MOBILE
        ===================================== */

        @media
        (max-width: 700px) {

            .details-page {

                padding:
                    35px 5%;

            }


            .details-banner {

                padding:
                    35px 25px;

            }


            .details-banner h1 {

                font-size:
                    30px;

            }


            .details-content {

                padding:
                    30px 25px;

            }


            .info-grid {

                grid-template-columns:
                    1fr;

            }


            .enroll-area {

                align-items:
                    flex-start;

                flex-direction:
                    column;

            }

        }

    </style>

</head>


<body>


<!-- =====================================
     HEADER
===================================== -->

<header>

    <div class="logo">
        Learnora AI
    </div>


    <nav>

        <a href="index.php">
            Home
        </a>

        <a href="courses.php">
            Courses
        </a>


        <?php if ($is_logged_in): ?>

            <a href="dashboard.php">
                Dashboard
            </a>

            <a href="logout.php">
                Logout
            </a>

        <?php else: ?>

            <a href="login.php">
                Login
            </a>

            <a href="register.php">
                Register
            </a>

        <?php endif; ?>


    </nav>

</header>


<!-- =====================================
     PAGE
===================================== -->

<section class="details-page">


    <a
        href="courses.php"
        class="back-link"
    >

        ← Back to Courses

    </a>


    <div class="course-details">


        <!-- =================================
             BANNER
        ================================== -->


        <div class="details-banner">


            <div class="details-banner-content">


                <span class="category-badge">

                    <?php

                    echo htmlspecialchars(
                        $course["category"]
                    );

                    ?>

                </span>


                <h1>

                    <?php

                    echo htmlspecialchars(
                        $course["title"]
                    );

                    ?>

                </h1>


                <p>

                    Learn with Learnora AI
                    and build your skills.

                </p>


            </div>


        </div>


        <!-- =================================
             CONTENT
        ================================== -->


        <div class="details-content">


            <!-- COURSE INFO -->


            <div class="info-grid">


                <div class="info-box">

                    <span>
                        👨‍🏫 Instructor
                    </span>

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $course["instructor"]
                        );

                        ?>

                    </strong>

                </div>


                <div class="info-box">

                    <span>
                        ⏱️ Duration
                    </span>

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $course["duration"]
                        );

                        ?>

                    </strong>

                </div>


                <div class="info-box">

                    <span>
                        🎯 Level
                    </span>

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $course["level"]
                        );

                        ?>

                    </strong>

                </div>


            </div>


            <!-- DESCRIPTION -->


            <div class="details-section">


                <h2>
                    About This Course
                </h2>


                <p>

                    <?php

                    echo nl2br(
                        htmlspecialchars(
                            $course["description"]
                        )
                    );

                    ?>

                </p>


            </div>


            <!-- SKILLS -->


            <div class="details-section">


                <h2>
                    Skills You Will Learn
                </h2>


                <div class="skills">


                    <?php

                    $skills = explode(
                        ",",
                        $course["skills"]
                    );


                    foreach ($skills as $skill):

                        $skill = trim($skill);

                    ?>


                        <span class="skill">

                            <?php

                            echo htmlspecialchars(
                                $skill
                            );

                            ?>

                        </span>


                    <?php endforeach; ?>


                </div>


            </div>


            <!-- ENROLL -->


            <div class="enroll-area">


                <div class="enroll-text">


                    <?php if ($is_enrolled): ?>


                        <h3>
                            You're already enrolled 🎉
                        </h3>

                        <p>
                            You can find this course
                            in your My Courses section.
                        </p>


                    <?php elseif ($is_logged_in): ?>


                        <h3>
                            Ready to start learning?
                        </h3>

                        <p>
                            Enroll now and start
                            your learning journey.
                        </p>


                    <?php else: ?>


                        <h3>
                            Start your learning journey
                        </h3>

                        <p>
                            Login to enroll in this course.
                        </p>


                    <?php endif; ?>


                </div>


                <div>


                    <?php if ($is_enrolled): ?>

                        <a
                            href="learning.php?course_id=<?php echo $course_id; ?>"
                            class="enroll-button"
                            style="background: #166534;"
                        >
                            ▶ Continue Learning
                        </a>

                    <?php elseif ($is_logged_in): ?>


                        <a
                            href="enroll.php?id=<?php
                                echo $course_id;
                            ?>"
                            class="enroll-button"
                        >

                            Enroll Now

                        </a>


                    <?php else: ?>


                        <a
                            href="login.php"
                            class="enroll-button login-button"
                        >

                            Login to Enroll

                        </a>


                    <?php endif; ?>


                </div>


            </div>


        </div>


    </div>


</section>


</body>

</html>

<?php

$stmt->close();

?>