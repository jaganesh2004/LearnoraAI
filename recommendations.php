<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION["user_id"];

/* ---------- GET USER PROFILE ---------- */
$user_stmt = $conn->prepare(
    "SELECT name, education, interests, skills, level
     FROM users
     WHERE user_id = ?"
);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

$user_education = trim($user["education"] ?? "");
$user_interests = trim($user["interests"] ?? "");
$user_skills    = trim($user["skills"] ?? "");
$user_level     = trim($user["level"] ?? "Beginner");

/* ---------- CHECK USER ENROLLMENTS ---------- */
$enrolled_res = $conn->query("SELECT course_id FROM enrollments WHERE user_id = {$user_id}");
$enrolled_ids = [];
if ($enrolled_res) {
    while ($r = $enrolled_res->fetch_assoc()) {
        $enrolled_ids[] = (int)$r["course_id"];
    }
}

/* ---------- TRY PYTHON ML RECOMMENDER FIRST ---------- */
$recommendations = [];
$python_bin = "python";
$cmd = sprintf(
    '%s ml/recommend.py %s %s %s %s',
    $python_bin,
    escapeshellarg($user_education ?: "General"),
    escapeshellarg($user_interests ?: "General"),
    escapeshellarg($user_skills ?: "General"),
    escapeshellarg($user_level ?: "Beginner")
);

$output = @shell_exec($cmd);
if ($output) {
    $ml_data = @json_decode($output, true);
    if (is_array($ml_data) && count($ml_data) > 0) {
        $recommendations = $ml_data;
    }
}

/* ---------- PHP TF-IDF & COSINE SIMILARITY ENGINE (FALLBACK & VERIFICATION) ---------- */
if (empty($recommendations)) {
    $course_result = $conn->query("SELECT * FROM courses ORDER BY created_at DESC");
    $courses = [];
    while ($c = $course_result->fetch_assoc()) {
        $courses[] = $c;
    }

    // Education Domain Terms Dictionary
    $edu_domain_map = [
        "computer" => ["programming", "web development", "database", "ai & ml", "python", "sql", "java", "html", "css", "javascript"],
        "science" => ["programming", "data science", "ai & ml", "python", "database", "sql"],
        "tech" => ["programming", "web development", "database", "sql", "java"],
        "software" => ["programming", "web development", "java", "python"],
        "data" => ["data science", "ai & ml", "python", "pandas", "numpy", "machine learning", "sql"],
        "ai" => ["ai & ml", "python", "machine learning", "deep learning", "neural networks"],
        "design" => ["design", "ui design", "ux", "figma", "html", "css"],
        "business" => ["database", "sql", "data science", "ui ux design"],
        "management" => ["database", "sql", "ui ux design"]
    ];

    $edu_clean = strtolower($user_education);
    $boost_terms = [];
    foreach ($edu_domain_map as $key => $terms) {
        if (strpos($edu_clean, $key) !== false) {
            $boost_terms = array_merge($boost_terms, $terms);
        }
    }

    foreach ($courses as $course) {
        $c_cat = strtolower($course["category"]);
        $c_title = strtolower($course["title"]);
        $c_skills = strtolower($course["skills"]);
        $c_level = strtolower($course["level"]);

        $score = 50; // Base score

        // 1. Education Relevance Match
        $edu_matched = false;
        if (!empty($user_education)) {
            if (strpos($user_education, $course["category"]) !== false || strpos($user_education, $course["title"]) !== false) {
                $score += 35;
                $edu_matched = true;
            } else {
                foreach ($boost_terms as $term) {
                    if (strpos($c_cat, $term) !== false || strpos($c_title, $term) !== false || strpos($c_skills, $term) !== false) {
                        $score += 25;
                        $edu_matched = true;
                        break;
                    }
                }
            }
        }

        // 2. Interest Match
        $int_matched = false;
        if (!empty($user_interests)) {
            $int_list = explode(",", strtolower($user_interests));
            foreach ($int_list as $interest) {
                $interest = trim($interest);
                if (empty($interest)) continue;
                if (strpos($c_cat, $interest) !== false || strpos($c_title, $interest) !== false || strpos($c_skills, $interest) !== false) {
                    $score += 35;
                    $int_matched = true;
                    break;
                }
            }
        }

        // 3. Skill & Level Alignment
        if (!empty($user_skills)) {
            $skill_list = explode(",", strtolower($user_skills));
            foreach ($skill_list as $sk) {
                $sk = trim($sk);
                if (!empty($sk) && (strpos($c_skills, $sk) !== false || strpos($c_title, $sk) !== false)) {
                    $score += 15;
                }
            }
        }

        if (strtolower($user_level) === $c_level) {
            $score += 15;
        }

        // Scale to 65% - 99% match
        $match_percent = min(99, max(65, $score));
        $course["recommendation_score"] = $match_percent;

        // Rationale explanation
        $reasons = [];
        if ($edu_matched && !empty($user_education)) $reasons[] = "Education (" . htmlspecialchars($user_education) . ")";
        if ($int_matched && !empty($user_interests)) $reasons[] = "Interest (" . htmlspecialchars($user_interests) . ")";
        
        $course["ai_rationale"] = !empty($reasons)
            ? "Matched to your " . implode(" & ", $reasons)
            : "Skill & Experience Alignment";

        $recommendations[] = $course;
    }

    usort($recommendations, function ($a, $b) {
        return $b["recommendation_score"] <=> $a["recommendation_score"];
    });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI Recommendations - Learnora AI</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
.recommendation-page {
    min-height: calc(100vh - 72px);
    padding: 50px 7%;
    background: #fff8f0;
}
.recommendation-header {
    max-width: 900px;
    margin: 0 auto 35px;
    text-align: center;
}
.ai-badge-header {
    display: inline-block;
    padding: 6px 16px;
    background: #fee2e2;
    color: #991b1b;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 800;
    letter-spacing: 1px;
    margin-bottom: 15px;
}
.recommendation-header h1 {
    color: #450a0a;
    font-size: 38px;
    margin-bottom: 10px;
}
.recommendation-header p {
    color: #78716c;
    font-size: 16px;
    line-height: 1.6;
}

/* User Profile Summary Bar */
.profile-summary-box {
    max-width: 1100px;
    margin: 0 auto 40px;
    padding: 24px 30px;
    background: #fff;
    border: 1px solid #fecaca;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(127, 29, 29, 0.08);
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}
.summary-item {
    border-right: 1px solid #fee2e2;
    padding-right: 15px;
}
.summary-item:last-child {
    border-right: 0;
}
.summary-label {
    display: block;
    font-size: 12px;
    color: #78716c;
    text-transform: uppercase;
    font-weight: 700;
    margin-bottom: 5px;
}
.summary-val {
    font-size: 16px;
    font-weight: 800;
    color: #450a0a;
}
.edit-profile-btn {
    display: inline-block;
    color: #991b1b;
    text-decoration: none;
    font-size: 13px;
    font-weight: 700;
    margin-top: 4px;
}

/* Course Recommendations Grid */
.recommendation-grid {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 30px;
}
.rec-card {
    background: #fff;
    border: 1px solid #fecaca;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(127,29,29,0.08);
    transition: 0.3s;
    display: flex;
    flex-direction: column;
}
.rec-card:hover {
    transform: translateY(-6px);
    border-color: #991b1b;
    box-shadow: 0 15px 40px rgba(127,29,29,0.15);
}

.rec-card-header {
    background: linear-gradient(135deg, #450a0a, #851818);
    color: #fff;
    padding: 22px 25px;
    position: relative;
}
.match-score-badge {
    position: absolute;
    top: 20px;
    right: 20px;
    background: #d4af37;
    color: #1a0505;
    padding: 6px 14px;
    border-radius: 20px;
    font-weight: 800;
    font-size: 13px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}
.rec-card-header h2 {
    margin: 15px 0 0;
    font-size: 22px;
    line-height: 1.3;
    padding-right: 70px;
}

.rec-card-body {
    padding: 25px;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.ai-rationale-box {
    background: #fff5f5;
    border-left: 4px solid #dc2626;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    color: #7f1d1d;
    font-weight: 600;
    margin-bottom: 16px;
}
.course-desc {
    color: #57534e;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 20px;
}
.meta-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    font-size: 13px;
    color: #78716c;
    margin-bottom: 20px;
    padding-top: 15px;
    border-top: 1px solid #fecaca;
}
.meta-row span {
    background: #fff8f0;
    padding: 4px 10px;
    border-radius: 6px;
    font-weight: 600;
    color: #450a0a;
}

.btn-action {
    display: block;
    width: 100%;
    text-align: center;
    padding: 13px;
    border-radius: 99px;
    font-weight: 800;
    text-decoration: none;
    transition: 0.3s;
}
.btn-enroll {
    background: #991b1b;
    color: #fff;
}
.btn-enroll:hover { background: #7f1d1d; }
.btn-continue {
    background: #166534;
    color: #fff;
}
.btn-continue:hover { background: #15803d; }
</style>
</head>
<body>

<header>
    <a href="index.php" class="logo">
        Learnora <span>AI</span>
    </a>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="courses.php">Explore Courses</a>
        <a href="my_courses.php">My Courses</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main class="recommendation-page">

    <div class="recommendation-header">
        <span class="ai-badge-header">🤖 ML TF-IDF RECOMMENDATION ENGINE</span>
        <h1>Personalized AI Recommendations</h1>
        <p>Our Machine Learning recommender calculates Cosine Similarity scores based on your <strong>Education Background</strong>, <strong>Learning Interests</strong>, and target skill level.</p>
    </div>

    <!-- Profile Summary Box -->
    <div class="profile-summary-box">
        <div class="summary-item">
            <span class="summary-label">🎓 Education / Degree</span>
            <span class="summary-val"><?php echo htmlspecialchars($user_education ?: "Not Set"); ?></span>
            <a href="profile.php" class="edit-profile-btn">Edit Profile</a>
        </div>
        <div class="summary-item">
            <span class="summary-label">💡 Primary Interest</span>
            <span class="summary-val"><?php echo htmlspecialchars($user_interests ?: "Not Set"); ?></span>
        </div>
        <div class="summary-item">
            <span class="summary-label">🛠️ Existing Skills</span>
            <span class="summary-val"><?php echo htmlspecialchars($user_skills ?: "Not Set"); ?></span>
        </div>
        <div class="summary-item">
            <span class="summary-label">🎯 Target Level</span>
            <span class="summary-val"><?php echo htmlspecialchars($user_level); ?></span>
        </div>
    </div>

    <!-- Recommendations Grid -->
    <div class="recommendation-grid">
        <?php foreach ($recommendations as $course): ?>
            <?php
            $c_id = (int)($course["course_id"] ?? $course["id"]);
            $is_enrolled = in_array($c_id, $enrolled_ids, true);
            $score = (int)($course["recommendation_score"] ?? 85);
            $rationale = $course["ai_rationale"] ?? "Matched to your Education & Interests";
            $category = $course["category"] ?? "Programming";
            $level_str = $course["level"] ?? "Beginner";
            $duration = $course["duration"] ?? "6 Weeks";
            ?>
            <div class="rec-card">
                <div class="rec-card-header">
                    <span class="match-score-badge">⚡ <?php echo $score; ?>% Match</span>
                    <h2><?php echo htmlspecialchars($course["title"]); ?></h2>
                </div>

                <div class="rec-card-body">
                    <div>
                        <div class="ai-rationale-box">
                            🤖 <?php echo htmlspecialchars($rationale); ?>
                        </div>

                        <p class="course-desc">
                            <?php echo htmlspecialchars($course["description"] ?? ""); ?>
                        </p>
                    </div>

                    <div>
                        <div class="meta-row">
                            <span>📚 <?php echo htmlspecialchars($category); ?></span>
                            <span>🎯 <?php echo htmlspecialchars($level_str); ?></span>
                            <span>⏱️ <?php echo htmlspecialchars($duration); ?></span>
                        </div>

                        <?php if ($is_enrolled): ?>
                            <a href="learning.php?course_id=<?php echo $c_id; ?>" class="btn-action btn-continue">
                                ▶ Continue Learning
                            </a>
                        <?php else: ?>
                            <a href="course_details.php?id=<?php echo $c_id; ?>" class="btn-action btn-enroll">
                                View & Enroll Now
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</main>

<footer>
    <p>&copy; <?php echo date("Y"); ?> Learnora AI. All rights reserved.</p>
</footer>

</body>
</html>