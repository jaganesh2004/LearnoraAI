<?php

session_start();

require_once "db.php";


// ==========================================
// LOGIN CHECK
// ==========================================

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit();

}

$user_id = $_SESSION["user_id"];


// ==========================================
// GET ENROLLED COURSES
// ==========================================

$stmt = $conn->prepare(

    "SELECT
        e.enrollment_id,
        e.status,
        e.progress,
        e.enrolled_at,

        c.course_id,
        c.title,
        c.category,
        c.description,
        c.skills,
        c.level,
        c.duration,
        c.instructor

     FROM enrollments e

     INNER JOIN courses c
        ON e.course_id = c.course_id

     WHERE e.user_id = ?

     ORDER BY e.enrolled_at DESC"

);

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();

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
        My Courses - Learnora AI
    </title>


    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >


    <style>

        /* =====================================
           PAGE
        ===================================== */

        .my-courses-page {

            min-height:
                calc(100vh - 72px);

            padding:
                55px 7%;

            background:
                #fff8f0;

        }


        .page-heading {

            text-align:
                center;

            margin-bottom:
                40px;

        }


        .page-heading h1 {

            color:
                #450a0a;

            font-size:
                40px;

            margin-bottom:
                10px;

        }


        .page-heading p {

            color:
                #78716c;

        }


        /* =====================================
           SUCCESS MESSAGE
        ===================================== */

        .success-message {

            max-width:
                1100px;

            margin:
                0 auto 25px;

            padding:
                14px 18px;

            background:
                #dcfce7;

            color:
                #166534;

            border:
                1px solid #86efac;

            border-radius:
                9px;

            text-align:
                center;

            font-weight:
                bold;

        }


        /* =====================================
           COURSE GRID
        ===================================== */

        .my-course-grid {

            max-width:
                1200px;

            margin:
                auto;

            display:
                grid;

            grid-template-columns:
                repeat(
                    auto-fit,
                    minmax(
                        300px,
                        1fr
                    )
                );

            gap:
                25px;

        }


        /* =====================================
           COURSE CARD
        ===================================== */

        .my-course-card {

            background:
                white;

            border:
                1px solid #fecaca;

            border-radius:
                16px;

            padding:
                28px;

            box-shadow:
                0 8px 25px
                rgba(
                    127,
                    29,
                    29,
                    0.08
                );

            transition:
                0.3s;

        }


        .my-course-card:hover {

            transform:
                translateY(-5px);

            border-color:
                #b91c1c;

            box-shadow:
                0 15px 35px
                rgba(
                    127,
                    29,
                    29,
                    0.15
                );

        }


        .course-icon {

            width:
                60px;

            height:
                60px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            background:
                #fee2e2;

            border-radius:
                12px;

            font-size:
                28px;

            margin-bottom:
                18px;

        }


        .category {

            display:
                inline-block;

            padding:
                5px 10px;

            background:
                #fee2e2;

            color:
                #991b1b;

            border-radius:
                20px;

            font-size:
                12px;

            font-weight:
                bold;

            margin-bottom:
                12px;

        }


        .my-course-card h2 {

            color:
                #450a0a;

            font-size:
                21px;

            margin-bottom:
                10px;

        }


        .description {

            color:
                #57534e;

            font-size:
                14px;

            line-height:
                1.6;

            margin-bottom:
                18px;

        }


        /* =====================================
           COURSE INFO
        ===================================== */

        .course-info {

            display:
                grid;

            grid-template-columns:
                1fr 1fr;

            gap:
                10px;

            margin-bottom:
                20px;

        }


        .info {

            color:
                #78716c;

            font-size:
                13px;

        }


        .info strong {

            color:
                #450a0a;

        }


        /* =====================================
           PROGRESS
        ===================================== */

        .progress-header {

            display:
                flex;

            justify-content:
                space-between;

            margin-bottom:
                8px;

            font-size:
                13px;

        }


        .progress-title {

            color:
                #57534e;

        }


        .progress-value {

            color:
                #991b1b;

            font-weight:
                bold;

        }


        .progress-bar {

            width:
                100%;

            height:
                9px;

            background:
                #f5d0d0;

            border-radius:
                20px;

            overflow:
                hidden;

            margin-bottom:
                20px;

        }


        .progress-fill {

            height:
                100%;

            background:
                linear-gradient(
                    90deg,
                    #7f1d1d,
                    #dc2626
                );

            border-radius:
                20px;

        }


        /* =====================================
           STATUS
        ===================================== */

        .status {

            display:
                inline-block;

            padding:
                6px 11px;

            border-radius:
                20px;

            font-size:
                12px;

            font-weight:
                bold;

            margin-bottom:
                18px;

        }


        .status-enrolled {

            background:
                #fee2e2;

            color:
                #991b1b;

        }


        .status-completed {

            background:
                #dcfce7;

            color:
                #166534;

        }


        .status-dropped {

            background:
                #f5f5f4;

            color:
                #57534e;

        }


        /* =====================================
           BUTTON
        ===================================== */

        .continue-button {

            display:
                block;

            width:
                100%;

            text-align:
                center;

            padding:
                12px;

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


        .continue-button:hover {

            background:
                #7f1d1d;

        }


        /* =====================================
           EMPTY STATE
        ===================================== */

        .empty-courses {

            max-width:
                650px;

            margin:
                50px auto;

            text-align:
                center;

            background:
                white;

            padding:
                50px 30px;

            border:
                1px solid #fecaca;

            border-radius:
                18px;

            box-shadow:
                0 8px 25px
                rgba(
                    127,
                    29,
                    29,
                    0.08
                );

        }


        .empty-icon {

            font-size:
                55px;

            margin-bottom:
                20px;

        }


        .empty-courses h2 {

            color:
                #450a0a;

            margin-bottom:
                10px;

        }


        .empty-courses p {

            color:
                #78716c;

            margin-bottom:
                25px;

        }


        .browse-button {

            display:
                inline-block;

            padding:
                13px 25px;

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


        /* =====================================
           MOBILE
        ===================================== */

        @media
        (max-width: 600px) {

            .my-courses-page {

                padding:
                    40px 5%;

            }


            .page-heading h1 {

                font-size:
                    32px;

            }


            .course-info {

                grid-template-columns:
                    1fr;

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


<!-- =====================================
     PAGE
===================================== -->

<section class="my-courses-page">


    <div class="page-heading">

        <h1>
            My Courses
        </h1>

        <p>
            Continue your learning journey.
        </p>

    </div>


    <!-- SUCCESS MESSAGE -->

    <?php

    if (
        isset($_GET["enrolled"]) &&
        $_GET["enrolled"] == "success"
    ):

    ?>

        <div class="success-message">

            🎉 Successfully enrolled!
            Your course has been added to My Courses.

        </div>

    <?php endif; ?>


    <?php if ($result->num_rows > 0): ?>


        <div class="my-course-grid">


            <?php while (
                $course = $result->fetch_assoc()
            ): ?>


                <div class="my-course-card">


                    <div class="course-icon">

                        📚

                    </div>


                    <span class="category">

                        <?php

                        echo htmlspecialchars(
                            $course["category"]
                        );

                        ?>

                    </span>


                    <h2>

                        <?php

                        echo htmlspecialchars(
                            $course["title"]
                        );

                        ?>

                    </h2>


                    <p class="description">

                        <?php

                        echo htmlspecialchars(
                            $course["description"]
                        );

                        ?>

                    </p>


                    <!-- COURSE INFO -->

                    <div class="course-info">


                        <div class="info">

                            👨‍🏫

                            <strong>
                                Instructor:
                            </strong>

                            <br>

                            <?php

                            echo htmlspecialchars(
                                $course["instructor"]
                            );

                            ?>

                        </div>


                        <div class="info">

                            ⏱️

                            <strong>
                                Duration:
                            </strong>

                            <br>

                            <?php

                            echo htmlspecialchars(
                                $course["duration"]
                            );

                            ?>

                        </div>


                        <div class="info">

                            🎯

                            <strong>
                                Level:
                            </strong>

                            <br>

                            <?php

                            echo htmlspecialchars(
                                $course["level"]
                            );

                            ?>

                        </div>


                        <div class="info">

                            📅

                            <strong>
                                Enrolled:
                            </strong>

                            <br>

                            <?php

                            echo date(
                                "d M Y",
                                strtotime(
                                    $course["enrolled_at"]
                                )
                            );

                            ?>

                        </div>


                    </div>


                    <!-- STATUS -->

                    <?php

                    $status_class =
                        "status-enrolled";

                    if (
                        $course["status"]
                        == "Completed"
                    ) {

                        $status_class =
                            "status-completed";

                    } elseif (
                        $course["status"]
                        == "Dropped"
                    ) {

                        $status_class =
                            "status-dropped";

                    }

                    ?>


                    <span
                        class="status
                        <?php
                        echo $status_class;
                        ?>"
                    >

                        <?php

                        echo htmlspecialchars(
                            $course["status"]
                        );

                        ?>

                    </span>


                    <!-- PROGRESS -->

                    <div class="progress-header">

                        <span class="progress-title">

                            Learning Progress

                        </span>


                        <span class="progress-value">

                            <?php

                            echo intval(
                                $course["progress"]
                            );

                            ?>%

                        </span>

                    </div>


                    <div class="progress-bar">

                        <div
                            class="progress-fill"
                            style="width:
                            <?php
                            echo intval(
                                $course["progress"]
                            );
                            ?>%;"
                        >
                        </div>

                    </div>


                    <!-- CONTINUE -->

                 <a
    href="learning.php?course_id=<?php echo $course["course_id"]; ?>"
    class="continue-button"
>
    Continue Learning
</a>

<?php if (intval($course["progress"]) >= 100 || $course["status"] === 'Completed'): ?>
    <a
        href="certificate.php?course_id=<?php echo $course["course_id"]; ?>"
        class="continue-button"
        style="background: linear-gradient(135deg, #d4af37, #b8860b); color: #1a0505; margin-top: 10px; display: inline-block; width: 100%; text-align: center;"
    >
        🎓 Download Certificate
    </a>
<?php endif; ?>


                </div>


            <?php endwhile; ?>


        </div>


    <?php else: ?>


        <!-- =================================
             EMPTY STATE
        ================================== -->


        <div class="empty-courses">


            <div class="empty-icon">
                📚
            </div>


            <h2>
                No Courses Yet
            </h2>


            <p>

                You haven't enrolled in any
                courses yet. Explore our courses
                and start learning.

            </p>


            <a
                href="courses.php"
                class="browse-button"
            >

                Explore Courses

            </a>


        </div>


    <?php endif; ?>


</section>


</body>

</html>


<?php

$stmt->close();

?>