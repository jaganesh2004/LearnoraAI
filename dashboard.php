<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "db.php";
$user_id = $_SESSION["user_id"];

// Fetch user stats
$enrolled_count = 0;
$completed_count = 0;

$stats_stmt = $conn->prepare(
    "SELECT 
        COUNT(*) as total_enrolled,
        SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as total_completed
     FROM enrollments
     WHERE user_id = ?"
);
$stats_stmt->bind_param("i", $user_id);
$stats_stmt->execute();
$stats_res = $stats_stmt->get_result()->fetch_assoc();
if ($stats_res) {
    $enrolled_count = (int)$stats_res["total_enrolled"];
    $completed_count = (int)($stats_res["total_completed"] ?? 0);
}
$stats_stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Learnora AI</title>
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .dashboard {
            min-height: calc(100vh - 72px);
            padding: 50px 8%;
            background: #fff8f0;
        }

        .welcome {
            background: linear-gradient(135deg, #450a0a, #7f1d1d);
            color: white;
            padding: 40px 45px;
            border-radius: 18px;
            margin-bottom: 35px;
            box-shadow: 0 10px 30px rgba(69, 10, 10, 0.15);
        }

        .welcome h1 {
            margin-bottom: 10px;
            font-size: 32px;
        }

        .welcome p {
            color: #fee2e2;
            font-size: 16px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 35px;
        }

        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 14px;
            border: 1px solid #fecaca;
            box-shadow: 0 6px 20px rgba(127, 29, 29, 0.08);
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .stat-icon {
            font-size: 36px;
            width: 55px;
            height: 55px;
            border-radius: 12px;
            background: #fee2e2;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-info h4 {
            font-size: 26px;
            color: #450a0a;
            margin-bottom: 4px;
        }

        .stat-info span {
            color: #78716c;
            font-size: 13px;
        }

        .dashboard-cards {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
        }

        .dashboard-card {
            flex: 1;
            min-width: 260px;
            background: white;
            padding: 30px;
            border-radius: 14px;
            border: 1px solid #fecaca;
            box-shadow: 0 8px 25px rgba(127, 29, 29, 0.10);
            transition: 0.3s;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            border-color: #b91c1c;
        }

        .dashboard-card h3 {
            color: #991b1b;
            margin-bottom: 12px;
            font-size: 20px;
        }

        .dashboard-card p {
            color: #57534e;
            font-size: 14px;
            line-height: 1.6;
        }

        .dashboard-card a {
            display: inline-block;
            margin-top: 18px;
            padding: 11px 20px;
            background: #991b1b;
            color: white;
            text-decoration: none;
            border-radius: 7px;
            font-weight: bold;
            transition: 0.3s;
        }

        .dashboard-card a:hover {
            background: #7f1d1d;
        }
    </style>
</head>

<body>

<header>
    <a href="index.php" class="logo">
        Learnora <span>AI</span>
    </a>

    <nav>
        <a href="courses.php">Explore Courses</a>
        <a href="recommendations.php">AI Recommendations</a>
        <a href="my_courses.php">My Courses</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main class="dashboard">

    <div class="welcome">
        <h1>
            Welcome back, <?php echo htmlspecialchars($_SESSION["user_name"]); ?> 👋
        </h1>
        <p>
            Track your progress, explore new courses, and get AI-powered learning recommendations tailored for you.
        </p>
    </div>

    <!-- QUICK STATS -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📚</div>
            <div class="stat-info">
                <h4><?php echo $enrolled_count; ?></h4>
                <span>Enrolled Courses</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🎓</div>
            <div class="stat-info">
                <h4><?php echo $completed_count; ?></h4>
                <span>Completed Courses</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🤖</div>
            <div class="stat-info">
                <h4>AI</h4>
                <span>Recommendations</span>
            </div>
        </div>
    </div>

    <div class="dashboard-cards">

        <div class="dashboard-card">
            <h3>🤖 AI Recommendations</h3>
            <p>
                Find personalized course recommendations automatically calculated from your skills and interests.
            </p>
            <a href="recommendations.php">
                View Recommendations
            </a>
        </div>

        <div class="dashboard-card">
            <h3>📚 Explore Courses</h3>
            <p>
                Browse our complete course library across Programming, AI, Web Development, SQL, and UI/UX Design.
            </p>
            <a href="courses.php">
                Browse Courses
            </a>
        </div>

        <div class="dashboard-card">
            <h3>📊 My Learning</h3>
            <p>
                Continue watching lessons from your active courses and access your official completion certificates.
            </p>
            <a href="my_courses.php">
                My Courses
            </a>
        </div>

        <div class="dashboard-card">
            <h3>👤 My Profile</h3>
            <p>
                Update your skills, interests, and learning level to refine your personalized AI recommendations.
            </p>
            <a href="profile.php">
                Update Profile
            </a>
        </div>

    </div>

</main>

<footer>
    <p>&copy; <?php echo date("Y"); ?> Learnora AI. All rights reserved.</p>
</footer>

</body>
</html>