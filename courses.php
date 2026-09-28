<?php

session_start();

require_once "db.php";

$is_logged_in = isset($_SESSION["user_id"]);
$enrolled_course_ids = [];

if ($is_logged_in) {
    $u_id = (int)$_SESSION["user_id"];
    $e_stmt = $conn->prepare("SELECT course_id FROM enrollments WHERE user_id = ?");
    $e_stmt->bind_param("i", $u_id);
    $e_stmt->execute();
    $e_res = $e_stmt->get_result();
    while ($row = $e_res->fetch_assoc()) {
        $enrolled_course_ids[] = (int)$row["course_id"];
    }
    $e_stmt->close();
}

// ===============================
// GET SEARCH AND FILTER VALUES
// ===============================

$search = isset($_GET["search"])
    ? trim($_GET["search"])
    : "";

$category = isset($_GET["category"])
    ? trim($_GET["category"])
    : "";

$level = isset($_GET["level"])
    ? trim($_GET["level"])
    : "";


// ===============================
// BUILD QUERY
// ===============================

$sql = "SELECT * FROM courses WHERE 1=1";

$params = [];
$types = "";


// SEARCH
if ($search != "") {

    $sql .= "
        AND (
            title LIKE ?
            OR category LIKE ?
            OR skills LIKE ?
            OR description LIKE ?
        )
    ";

    $search_value = "%" . $search . "%";

    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;

    $types .= "ssss";
}


// CATEGORY FILTER
if ($category != "") {

    $sql .= " AND category = ?";

    $params[] = $category;

    $types .= "s";
}


// LEVEL FILTER
if ($level != "") {

    $sql .= " AND level = ?";

    $params[] = $level;

    $types .= "s";
}


// ORDER
$sql .= " ORDER BY created_at DESC";


// ===============================
// PREPARE QUERY
// ===============================

$stmt = $conn->prepare($sql);


// Bind parameters if available
if (!empty($params)) {

    $stmt->bind_param(
        $types,
        ...$params
    );
}


$stmt->execute();

$result = $stmt->get_result();

if ($is_logged_in && ($search !== "" || $category !== "")) {
    $matched = $result->fetch_all(MYSQLI_ASSOC);
    foreach (array_slice($matched, 0, 3) as $m_course) {
        log_user_activity($conn, (int)$_SESSION["user_id"], (int)$m_course["course_id"], 'search');
    }
    $result->data_seek(0);
}


// ===============================
// GET CATEGORIES
// ===============================

$category_query = $conn->query(
    "SELECT DISTINCT category
     FROM courses
     ORDER BY category"
);

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
        Courses - Learnora AI
    </title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >


    <style>

        /* ==========================
           COURSES PAGE
        ========================== */

        .courses-page {

            min-height:
                calc(100vh - 72px);

            padding:
                55px 7%;

            background:
                #fff8f0;

        }


        .courses-heading {

            text-align:
                center;

            margin-bottom:
                35px;

        }


        .courses-heading h1 {

            font-size:
                40px;

            color:
                #450a0a;

            margin-bottom:
                10px;

        }


        .courses-heading p {

            color:
                #78716c;

            font-size:
                16px;

        }


        /* ==========================
           SEARCH BOX
        ========================== */

        .search-area {

            max-width:
                1000px;

            margin:
                0 auto 40px;

            background:
                white;

            padding:
                25px;

            border-radius:
                15px;

            border:
                1px solid #fecaca;

            box-shadow:
                0 8px 25px
                rgba(
                    127,
                    29,
                    29,
                    0.08
                );

        }


        .search-form {

            display:
                flex;

            gap:
                12px;

            flex-wrap:
                wrap;

        }


        .search-input {

            flex:
                1;

            min-width:
                220px;

            padding:
                13px 15px;

            border:
                1px solid #d6d3d1;

            border-radius:
                8px;

            outline:
                none;

            font-size:
                15px;

        }


        .search-input:focus {

            border-color:
                #991b1b;

            box-shadow:
                0 0 0 3px
                #fee2e2;

        }


        .filter-select {

            min-width:
                180px;

            padding:
                13px 15px;

            border:
                1px solid #d6d3d1;

            border-radius:
                8px;

            outline:
                none;

            background:
                white;

            color:
                #450a0a;

            font-size:
                15px;

        }


        .search-button {

            padding:
                13px 24px;

            border:
                none;

            border-radius:
                8px;

            background:
                #991b1b;

            color:
                white;

            font-weight:
                bold;

            cursor:
                pointer;

        }


        .search-button:hover {

            background:
                #7f1d1d;

        }


        .clear-button {

            display:
                inline-flex;

            align-items:
                center;

            justify-content:
                center;

            padding:
                13px 20px;

            border-radius:
                8px;

            background:
                #f5f5f4;

            color:
                #450a0a;

            text-decoration:
                none;

            font-weight:
                bold;

        }


        .clear-button:hover {

            background:
                #fee2e2;

        }


        /* ==========================
           COURSE COUNT
        ========================== */

        .course-count {

            max-width:
                1200px;

            margin:
                0 auto 20px;

            color:
                #57534e;

            font-size:
                15px;

        }


        .course-count strong {

            color:
                #991b1b;

        }


        /* ==========================
           COURSE GRID
        ========================== */

        .course-grid {

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
                        280px,
                        1fr
                    )
                );

            gap:
                25px;

        }


        /* ==========================
           COURSE CARD
        ========================== */

        .course-card {

            background:
                white;

            border:
                1px solid #fecaca;

            border-radius:
                16px;

            overflow:
                hidden;

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


        .course-card:hover {

            transform:
                translateY(-6px);

            border-color:
                #b91c1c;

            box-shadow:
                0 15px 35px
                rgba(
                    127,
                    29,
                    29,
                    0.16
                );

        }


        /* ==========================
           COURSE TOP
        ========================== */

        .course-top {

            height:
                135px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            background:
                linear-gradient(
                    135deg,
                    #450a0a,
                    #991b1b
                );

            color:
                white;

            font-size:
                45px;

        }


        .course-content {

            padding:
                25px;

        }


        .course-category {

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


        .course-content h2 {

            color:
                #450a0a;

            font-size:
                21px;

            margin-bottom:
                12px;

        }


        .course-description {

            color:
                #57534e;

            font-size:
                14px;

            line-height:
                1.6;

            min-height:
                68px;

            margin-bottom:
                15px;

        }


        .course-meta {

            display:
                flex;

            justify-content:
                space-between;

            gap:
                8px;

            margin-bottom:
                15px;

            flex-wrap:
                wrap;

        }


        .meta-item {

            font-size:
                13px;

            color:
                #78716c;

        }


        .course-level {

            color:
                #991b1b;

            font-weight:
                bold;

        }


        .course-skills {

            color:
                #78716c;

            font-size:
                13px;

            margin-bottom:
                20px;

        }


        .view-button {

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


        .view-button:hover {

            background:
                #7f1d1d;

        }


        /* ==========================
           NO COURSES
        ========================== */

        .no-courses {

            grid-column:
                1 / -1;

            text-align:
                center;

            background:
                white;

            padding:
                50px;

            border-radius:
                15px;

            border:
                1px solid #fecaca;

        }


        .no-courses h2 {

            color:
                #450a0a;

            margin-bottom:
                10px;

        }


        .no-courses p {

            color:
                #78716c;

        }


        /* ==========================
           MOBILE
        ========================== */

        @media
        (max-width: 700px) {

            .courses-page {

                padding:
                    40px 5%;

            }


            .courses-heading h1 {

                font-size:
                    32px;

            }


            .search-form {

                flex-direction:
                    column;

            }


            .search-input,
            .filter-select,
            .search-button,
            .clear-button {

                width:
                    100%;

            }

        }

    </style>

</head>


<body>


<!-- ==========================
     HEADER
========================== -->


<header>

    <a href="index.php" class="logo">
        Learnora <span>AI</span>
    </a>

    <nav>

        <a href="index.php">
            Home
        </a>

        <a href="courses.php" class="active">
            Explore Courses
        </a>

        <?php if ($is_logged_in): ?>
            <a href="dashboard.php">
                Dashboard
            </a>

            <a href="recommendations.php">
                AI Recommendations
            </a>

            <a href="my_courses.php">
                My Courses
            </a>

            <a href="profile.php">
                Profile
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



<!-- ==========================
     COURSES PAGE
========================== -->


<section class="courses-page">


    <div class="courses-heading">

        <h1>
            Explore Courses
        </h1>

        <p>
            Discover courses that match
            your learning goals.
        </p>

    </div>



    <!-- ==========================
         SEARCH + FILTER
    =========================== -->


    <div class="search-area">


        <form
            method="GET"
            action="courses.php"
            class="search-form"
        >


            <!-- SEARCH -->

            <input
                type="text"
                name="search"
                class="search-input"
                placeholder="Search courses, skills..."
                value="<?php
                    echo htmlspecialchars($search);
                ?>"
            >


            <!-- CATEGORY -->

            <select
                name="category"
                class="filter-select"
            >

                <option value="">
                    All Categories
                </option>


                <?php while (
                    $cat = $category_query->fetch_assoc()
                ): ?>

                    <option
                        value="<?php
                            echo htmlspecialchars(
                                $cat["category"]
                            );
                        ?>"
                        <?php
                        if (
                            $category ==
                            $cat["category"]
                        ) {
                            echo "selected";
                        }
                        ?>
                    >

                        <?php
                        echo htmlspecialchars(
                            $cat["category"]
                        );
                        ?>

                    </option>

                <?php endwhile; ?>

            </select>


            <!-- LEVEL -->

            <select
                name="level"
                class="filter-select"
            >

                <option value="">
                    All Levels
                </option>

                <option
                    value="Beginner"
                    <?php
                    if ($level == "Beginner") {
                        echo "selected";
                    }
                    ?>
                >
                    Beginner
                </option>

                <option
                    value="Intermediate"
                    <?php
                    if ($level == "Intermediate") {
                        echo "selected";
                    }
                    ?>
                >
                    Intermediate
                </option>

                <option
                    value="Advanced"
                    <?php
                    if ($level == "Advanced") {
                        echo "selected";
                    }
                    ?>
                >
                    Advanced
                </option>

            </select>


            <button
                type="submit"
                class="search-button"
            >
                Search
            </button>


            <a
                href="courses.php"
                class="clear-button"
            >
                Clear
            </a>


        </form>


    </div>



    <!-- ==========================
         COURSE COUNT
    =========================== -->


    <div class="course-count">

        <?php

        $course_count = $result->num_rows;

        ?>

        Showing

        <strong>
            <?php echo $course_count; ?>
        </strong>

        course(s)

    </div>



    <!-- ==========================
         COURSE GRID
    =========================== -->


    <div class="course-grid">


        <?php if ($result->num_rows > 0): ?>


            <?php while (
                $course = $result->fetch_assoc()
            ): ?>


                <div class="course-card">


                    <div class="course-top">

                        📚

                    </div>


                    <div class="course-content">


                        <span class="course-category">

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


                        <p class="course-description">

                            <?php
                            echo htmlspecialchars(
                                $course["description"]
                            );
                            ?>

                        </p>


                        <div class="course-meta">


                            <span class="meta-item">

                                ⏱️

                                <?php
                                echo htmlspecialchars(
                                    $course["duration"]
                                );
                                ?>

                            </span>


                            <span class="meta-item course-level">

                                <?php
                                echo htmlspecialchars(
                                    $course["level"]
                                );
                                ?>

                            </span>


                        </div>


                        <p class="course-skills">

                            <strong>
                                Skills:
                            </strong>

                            <?php
                            echo htmlspecialchars(
                                $course["skills"]
                            );
                            ?>

                        </p>


                        <a
                            href="course_details.php?id=<?php
                                echo $course["course_id"];
                            ?>"
                            class="view-button"
                        >

                            View Course

                        </a>


                    </div>


                </div>


            <?php endwhile; ?>


        <?php else: ?>


            <div class="no-courses">

                <h2>
                    No Courses Found
                </h2>

                <p>
                    Try a different search
                    or filter.
                </p>

            </div>


        <?php endif; ?>


    </div>


</section>

<footer>
    <p>&copy; <?php echo date("Y"); ?> Learnora AI. All rights reserved.</p>
</footer>

</body>

</html>


<?php

$stmt->close();

?>