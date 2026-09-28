<?php
session_start();
$is_logged_in = isset($_SESSION["user_id"]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learnora AI - Personalized Learning Platform</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header>
    <a href="index.php" class="logo">
        Learnora <span>AI</span>
    </a>
    <nav>
        <a href="index.php" class="active">Home</a>
        <a href="courses.php">Explore Courses</a>
        <?php if ($is_logged_in): ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="recommendations.php">AI Recommendations</a>
            <a href="my_courses.php">My Courses</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>

<main>
    <section class="hero">
        <div class="hero-content">
            <span class="badge">AI-POWERED LEARNING PLATFORM</span>
            <h1>
                Learn Smarter.<br>
                Get Personalized.
            </h1>
            <p>
                Discover courses recommended specifically for your skills,
                interests, and learning goals powered by Learnora AI algorithms.
            </p>
            <div class="hero-buttons">
                <?php if ($is_logged_in): ?>
                    <a href="dashboard.php" class="btn">
                        Go to Dashboard
                    </a>
                    <a href="recommendations.php" class="btn-outline">
                        View AI Recommendations
                    </a>
                <?php else: ?>
                    <a href="register.php" class="btn">
                        Get Started
                    </a>
                    <a href="login.php" class="btn-outline">
                        Login to Account
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="features">
        <h2>Why Learnora AI?</h2>
        <p class="section-text">Empowering learners through smart recommendation algorithms and interactive course progress.</p>
        
        <div class="cards">
            <div class="card">
                <div class="icon">🤖</div>
                <h3>AI Recommendations</h3>
                <p>Get personalized course suggestions tailored to your individual skills, experience level, and career interests.</p>
            </div>
            
            <div class="card">
                <div class="icon">📚</div>
                <h3>Explore Anything</h3>
                <p>Explore a rich directory of courses in Programming, AI & ML, Web Development, Databases, and UI/UX Design.</p>
            </div>
            
            <div class="card">
                <div class="icon">📊</div>
                <h3>Track Learning</h3>
                <p>Monitor your progress across lessons in real-time and earn printable certificates upon course completion.</p>
            </div>
        </div>
    </section>
</main>

<footer>
    <p>&copy; <?php echo date("Y"); ?> Learnora AI. All rights reserved.</p>
    <div class="footer-links">
        <a href="index.php">Home</a>
        <a href="courses.php">Courses</a>
        <a href="verify_certificate.php">Verify Certificate</a>
    </div>
</footer>

</body>
</html>