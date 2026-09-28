<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION["user_id"];
$course_id = isset($_GET["course_id"]) ? (int)$_GET["course_id"] : 0;
$lesson_id = isset($_GET["lesson_id"]) ? (int)$_GET["lesson_id"] : 0;

if ($course_id <= 0) {
    header("Location: my_courses.php");
    exit();
}

/* ---------- Check enrollment ---------- */
$stmt = $conn->prepare(
    "SELECT enrollment_id, progress, status
     FROM enrollments
     WHERE user_id = ? AND course_id = ?
     LIMIT 1"
);
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();
$enrollment = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$enrollment) {
    // Auto enroll if user opened from recommendations or direct link
    $stmt = $conn->prepare(
        "INSERT INTO enrollments (user_id, course_id, status, progress)
         VALUES (?, ?, 'Enrolled', 0)"
    );
    $stmt->bind_param("ii", $user_id, $course_id);
    $stmt->execute();
    $enrollment_id = $stmt->insert_id;
    $stmt->close();
    $enrollment = [
        "enrollment_id" => $enrollment_id,
        "progress" => 0,
        "status" => "Enrolled"
    ];
}

/* ---------- Fetch Course ---------- */
$stmt = $conn->prepare(
    "SELECT course_id, title, category, description, skills, level, duration, instructor
     FROM courses
     WHERE course_id = ?
     LIMIT 1"
);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$course) {
    header("Location: my_courses.php");
    exit();
}

/* ---------- Fetch Lessons ---------- */
$stmt = $conn->prepare(
    "SELECT lesson_id, course_id, lesson_number, title, video_url, duration, description, document_title, document_content, document_url
     FROM lessons
     WHERE course_id = ?
     ORDER BY lesson_number ASC"
);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$lessons = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (!$lessons) {
    // Run DB init to ensure lessons exist
    if (function_exists('check_and_init_db')) {
        check_and_init_db($conn);
    }
    // Retry fetch
    $stmt = $conn->prepare(
        "SELECT lesson_id, course_id, lesson_number, title, video_url, duration, description, document_title, document_content, document_url
         FROM lessons
         WHERE course_id = ?
         ORDER BY lesson_number ASC"
    );
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $lessons = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

if (!$lessons) {
    die("No lessons available for this course yet.");
}

/* ---------- Current Lesson ---------- */
$current_lesson = null;
if ($lesson_id > 0) {
    foreach ($lessons as $l) {
        if ((int)$l["lesson_id"] === $lesson_id) {
            $current_lesson = $l;
            break;
        }
    }
}
if (!$current_lesson) {
    $current_lesson = $lessons[0];
}

/* ---------- Toggle / Complete Lesson POST ---------- */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["toggle_lesson"])) {
    $target_lesson_id = (int)$_POST["lesson_id"];
    
    // Check current state
    $stmt = $conn->prepare(
        "SELECT completed FROM lesson_progress WHERE user_id = ? AND lesson_id = ? LIMIT 1"
    );
    $stmt->bind_param("ii", $user_id, $target_lesson_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    $new_status = ($row && (int)$row["completed"] === 1) ? 0 : 1;
    
    $stmt = $conn->prepare(
        "INSERT INTO lesson_progress (user_id, lesson_id, completed, completed_at)
         VALUES (?, ?, ?, NOW())
         ON DUPLICATE KEY UPDATE
            completed = ?,
            completed_at = NOW()"
    );
    $stmt->bind_param("iiii", $user_id, $target_lesson_id, $new_status, $new_status);
    $stmt->execute();
    if ($new_status === 1) {
        log_user_activity($conn, $user_id, $course_id, 'like');
    }
    
    // Redirect back to keep lesson position
    header("Location: learning.php?course_id=" . $course_id . "&lesson_id=" . $target_lesson_id);
    exit();
}

/* ---------- Completed lessons list ---------- */
$stmt = $conn->prepare(
    "SELECT lp.lesson_id
     FROM lesson_progress lp
     INNER JOIN lessons l ON l.lesson_id = lp.lesson_id
     WHERE lp.user_id = ?
       AND l.course_id = ?
       AND lp.completed = 1"
);
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();
$res = $stmt->get_result();
$completed_lessons = [];
while ($r = $res->fetch_assoc()) {
    $completed_lessons[] = (int)$r["lesson_id"];
}
$stmt->close();

$total_lessons = count($lessons);
$completed_count = count($completed_lessons);
$progress = $total_lessons > 0 ? (int)round(($completed_count / $total_lessons) * 100) : 0;

/* ---------- Update enrollment status & progress ---------- */
$status = ($progress >= 100) ? "Completed" : "Enrolled";
$stmt = $conn->prepare(
    "UPDATE enrollments
     SET progress = ?, status = ?
     WHERE enrollment_id = ?"
);
$stmt->bind_param("isi", $progress, $status, $enrollment["enrollment_id"]);
$stmt->execute();
$stmt->close();

/* ---------- Prev & Next Lesson pointers ---------- */
$prev_lesson = null;
$next_lesson = null;
for ($i = 0; $i < count($lessons); $i++) {
    if ((int)$lessons[$i]["lesson_id"] === (int)$current_lesson["lesson_id"]) {
        if ($i > 0) $prev_lesson = $lessons[$i - 1];
        if ($i < count($lessons) - 1) $next_lesson = $lessons[$i + 1];
        break;
    }
}

/* ---------- YouTube Embed Helper ---------- */
function getYoutubeEmbedUrl($url) {
    $url = trim((string)$url);
    if ($url === "") return "";
    if (preg_match('~youtube\.com/embed/([A-Za-z0-9_-]{6,})~', $url, $m)) {
        return "https://www.youtube.com/embed/" . $m[1];
    }
    if (preg_match('~youtube\.com/watch\?[^#]*v=([A-Za-z0-9_-]{6,})~', $url, $m)) {
        return "https://www.youtube.com/embed/" . $m[1];
    }
    if (preg_match('~youtu\.be/([A-Za-z0-9_-]{6,})~', $url, $m)) {
        return "https://www.youtube.com/embed/" . $m[1];
    }
    return $url;
}

$embed_video_url = getYoutubeEmbedUrl($current_lesson["video_url"]);
$is_current_completed = in_array((int)$current_lesson["lesson_id"], $completed_lessons, true);

/* ---------- Markdown Document Helper ---------- */
function renderDocumentMarkdown($text) {
    if (empty($text)) {
        return "<p class='no-doc'>No document notes attached to this lesson.</p>";
    }
    $text = htmlspecialchars($text);
    // Code block ```lang ... ```
    $text = preg_replace_callback('/```([a-z]*)\n(.*?)```/s', function($matches) {
        return '<div class="code-block-wrapper"><pre><code>' . trim($matches[2]) . '</code></pre></div>';
    }, $text);
    // Inline code
    $text = preg_replace('/`([^`]+)`/', '<code class="inline-code">$1</code>', $text);
    // Headers
    $text = preg_replace('/### (.*?)\n/', '<h4 class="doc-h3">$1</h4>', $text);
    $text = preg_replace('/## (.*?)\n/', '<h3 class="doc-h2">$1</h3>', $text);
    $text = preg_replace('/# (.*?)\n/', '<h2 class="doc-h1">$1</h2>', $text);
    // Bold
    $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
    // Lists
    $text = preg_replace('/^- (.*?)$/m', '<li>$1</li>', $text);
    $text = preg_replace('/(<li>.*<\/li>)/s', '<ul class="doc-list">$1</ul>', $text);
    // Line breaks
    $text = nl2br($text);
    return $text;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($course["title"]); ?> - Learnora AI Classroom</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
:root {
    --primary-bg: #120404;
    --card-bg: #1e0707;
    --card-border: #4d1212;
    --accent-red: #dc2626;
    --accent-gold: #d4af37;
    --accent-green: #16a34a;
    --text-main: #f8fafc;
    --text-muted: #cbd5e1;
}

* { box-sizing: border-box; }
body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #0f0303;
    color: var(--text-main);
    line-height: 1.6;
}

/* Header */
.classroom-header {
    height: 70px;
    padding: 0 40px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #250606;
    border-bottom: 1px solid var(--card-border);
    position: sticky;
    top: 0;
    z-index: 100;
}
.brand-logo {
    font-size: 24px;
    font-weight: 800;
    color: #fff;
    text-decoration: none;
}
.brand-logo span { color: var(--accent-red); }
.header-actions {
    display: flex;
    align-items: center;
    gap: 15px;
}
.back-btn {
    color: #f87171;
    text-decoration: none;
    border: 1px solid #7f1d1d;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    transition: 0.3s;
}
.back-btn:hover {
    background: #7f1d1d;
    color: #fff;
}

/* Container */
.classroom-container {
    max-width: 1500px;
    margin: 25px auto;
    padding: 0 30px;
}

/* Course Banner Header */
.course-banner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #300808, #1c0404);
    border: 1px solid var(--card-border);
    padding: 22px 30px;
    border-radius: 14px;
    margin-bottom: 25px;
}
.course-banner h1 {
    margin: 0 0 6px;
    font-size: 26px;
    color: #fff;
}
.course-banner p {
    margin: 0;
    color: #fca5a5;
    font-size: 14px;
}

/* Certificate Unlocked Banner */
.cert-unlocked-banner {
    background: linear-gradient(135deg, #785c13, #b8860b);
    color: #fff;
    padding: 20px 30px;
    border-radius: 14px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 10px 30px rgba(212, 175, 55, 0.25);
    animation: pulseCert 2s infinite alternate;
}
@keyframes pulseCert {
    from { box-shadow: 0 0 15px rgba(212,175,55,0.3); }
    to { box-shadow: 0 0 30px rgba(212,175,55,0.7); }
}
.cert-banner-text h3 { margin: 0 0 4px; font-size: 22px; color: #fff; }
.cert-banner-text p { margin: 0; color: #fef08a; font-size: 14px; }
.cert-banner-btn {
    background: #fff;
    color: #785c13;
    padding: 12px 24px;
    border-radius: 10px;
    font-weight: 800;
    text-decoration: none;
    font-size: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    transition: 0.3s;
}
.cert-banner-btn:hover {
    background: #fef08a;
    transform: translateY(-2px);
}

/* Grid Layout */
.classroom-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 380px;
    gap: 30px;
}

/* Main Content Card */
.main-card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 16px;
    overflow: hidden;
}

/* Content Tabs Header */
.content-tabs {
    display: flex;
    background: #170505;
    border-bottom: 1px solid var(--card-border);
}
.tab-btn {
    flex: 1;
    padding: 16px 20px;
    background: transparent;
    border: 0;
    color: #94a3b8;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: 0.3s;
    border-bottom: 3px solid transparent;
}
.tab-btn:hover {
    color: #fff;
    background: rgba(255,255,255,0.03);
}
.tab-btn.active {
    color: #ef4444;
    border-bottom-color: #ef4444;
    background: var(--card-bg);
}

/* Tab Panels */
.tab-panel {
    display: none;
}
.tab-panel.active {
    display: block;
}

/* Video Player */
.video-wrapper {
    position: relative;
    width: 100%;
    aspect-ratio: 16 / 9;
    background: #000;
}
.video-wrapper iframe {
    width: 100%;
    height: 100%;
    border: 0;
}
.no-video-box {
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
}

/* Document View Panel */
.document-view-container {
    padding: 30px;
    background: #160404;
    min-height: 450px;
}
.doc-header-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 20px;
    margin-bottom: 25px;
    border-bottom: 1px solid var(--card-border);
}
.doc-header-bar h3 {
    margin: 0;
    font-size: 22px;
    color: #fca5a5;
    display: flex;
    align-items: center;
    gap: 10px;
}
.download-doc-btn {
    background: #b91c1c;
    color: #fff;
    padding: 10px 18px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: 0.3s;
}
.download-doc-btn:hover {
    background: #dc2626;
}

.document-body {
    background: #250808;
    border: 1px solid #571414;
    border-radius: 12px;
    padding: 30px;
    color: #e2e8f0;
    font-size: 15px;
    line-height: 1.8;
}
.doc-h1 { color: #f87171; border-bottom: 1px solid #4a1010; padding-bottom: 8px; margin-top: 25px; }
.doc-h2 { color: #fca5a5; margin-top: 20px; }
.doc-h3 { color: #fecaca; margin-top: 15px; }
.code-block-wrapper {
    background: #0f0303;
    border: 1px solid #641212;
    border-radius: 8px;
    padding: 16px;
    margin: 15px 0;
    overflow-x: auto;
}
.code-block-wrapper code {
    font-family: 'Consolas', 'Monaco', monospace;
    color: #86efac;
    font-size: 14px;
}
.inline-code {
    background: #3b0c0c;
    color: #fca5a5;
    padding: 2px 7px;
    border-radius: 5px;
    font-family: monospace;
}
.doc-list {
    padding-left: 20px;
    margin: 15px 0;
}
.doc-list li { margin-bottom: 8px; }

/* Lesson Details Section */
.lesson-meta-content {
    padding: 30px;
}
.lesson-badge {
    display: inline-block;
    padding: 5px 12px;
    background: #450a0a;
    color: #fca5a5;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 1px;
    margin-bottom: 12px;
}
.lesson-meta-content h2 {
    margin: 0 0 12px;
    font-size: 26px;
}
.lesson-desc {
    color: #cbd5e1;
    font-size: 15px;
    line-height: 1.7;
    margin-bottom: 25px;
}

/* Action Toolbar */
.action-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    padding-top: 20px;
    border-top: 1px solid var(--card-border);
    flex-wrap: wrap;
}
.complete-form button {
    padding: 13px 22px;
    border: 0;
    border-radius: 9px;
    font-weight: 800;
    font-size: 14px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: 0.3s;
}
.btn-complete {
    background: #166534;
    color: #fff;
}
.btn-complete:hover { background: #15803d; }
.btn-completed {
    background: #14532d;
    color: #86efac;
    border: 1px solid #22c55e;
}
.btn-nav {
    padding: 13px 22px;
    background: #3b0d0d;
    color: #fff;
    text-decoration: none;
    border-radius: 9px;
    font-weight: 700;
    font-size: 14px;
    transition: 0.3s;
}
.btn-nav:hover { background: #991b1b; }
.btn-cert-gold {
    background: linear-gradient(135deg, #d4af37, #aa7c11);
    color: #1a0505;
    padding: 13px 24px;
    border-radius: 9px;
    text-decoration: none;
    font-weight: 800;
    font-size: 15px;
    box-shadow: 0 4px 15px rgba(212,175,55,0.4);
    transition: 0.3s;
}
.btn-cert-gold:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(212,175,55,0.6);
}

/* Sidebar */
.sidebar-card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 16px;
    overflow: hidden;
}
.sidebar-header {
    padding: 22px;
    background: #170505;
    border-bottom: 1px solid var(--card-border);
}
.sidebar-header h3 {
    margin: 0 0 6px;
    font-size: 18px;
}
.sidebar-header p {
    margin: 0 0 15px;
    color: #94a3b8;
    font-size: 13px;
}
/* Progress Bar */
.progress-box {
    background: #0f0303;
    padding: 14px;
    border-radius: 10px;
    border: 1px solid #3b0d0d;
}
.progress-info {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
    margin-bottom: 8px;
}
.progress-bar-bg {
    height: 8px;
    background: #280707;
    border-radius: 10px;
    overflow: hidden;
}
.progress-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #991b1b, #ef4444);
    transition: width 0.5s ease;
}

/* Lessons List */
.lessons-scroll-list {
    padding: 12px;
    max-height: 520px;
    overflow-y: auto;
}
.lesson-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    margin-bottom: 8px;
    border-radius: 10px;
    text-decoration: none;
    color: #e2e8f0;
    background: #140404;
    border: 1px solid transparent;
    transition: 0.2s;
}
.lesson-item:hover {
    background: #2d0909;
    border-color: #571414;
}
.lesson-item.active {
    background: #4a0c0c;
    border-color: #ef4444;
}
.item-number {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: #250606;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 13px;
    color: #fca5a5;
    flex-shrink: 0;
}
.active .item-number {
    background: #b91c1c;
    color: #fff;
}
.item-info {
    flex: 1;
    min-width: 0;
}
.item-title {
    display: block;
    font-size: 14px;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.item-meta {
    font-size: 11px;
    color: #94a3b8;
    display: flex;
    gap: 8px;
    margin-top: 2px;
}
.item-check {
    color: #22c55e;
    font-weight: 900;
    font-size: 16px;
}

/* Responsive */
@media (max-width: 1024px) {
    .classroom-grid { grid-template-columns: 1fr; }
}
</style>
</head>
<body>

<!-- Header -->
<header class="classroom-header">
    <a href="index.php" class="brand-logo">
        Learnora <span>AI</span>
    </a>
    <div class="header-actions">
        <a href="my_courses.php" class="back-btn">← My Enrolled Courses</a>
    </div>
</header>

<!-- Main Container -->
<main class="classroom-container">

    <!-- Course Banner -->
    <div class="course-banner">
        <div>
            <h1><?php echo htmlspecialchars($course["title"]); ?></h1>
            <p>Instructor: <?php echo htmlspecialchars($course["instructor"] ?? "Learnora AI Expert"); ?> &bull; Category: <?php echo htmlspecialchars($course["category"]); ?></p>
        </div>
        <div>
            <span style="background: #450a0a; color:#fca5a5; padding:6px 14px; border-radius:20px; font-weight:700; font-size:13px;">
                🎯 <?php echo htmlspecialchars($course["level"]); ?> Level
            </span>
        </div>
    </div>

    <!-- Certificate Banner if 100% complete -->
    <?php if ($progress >= 100): ?>
        <div class="cert-unlocked-banner">
            <div class="cert-banner-text">
                <h3>🎉 Congratulations! Course Completed!</h3>
                <p>You have successfully finished all lessons in <?php echo htmlspecialchars($course["title"]); ?>. Your official certificate is ready!</p>
            </div>
            <a href="certificate.php?course_id=<?php echo $course_id; ?>" class="cert-banner-btn">
                🎓 Claim & Download Certificate
            </a>
        </div>
    <?php endif; ?>

    <div class="classroom-grid">

        <!-- Left Column: Video / Document Content -->
        <div class="main-card">
            
            <!-- Tabs -->
            <div class="content-tabs">
                <button type="button" class="tab-btn active" onclick="switchTab('video')">
                    🎥 Video Lesson
                </button>
                <button type="button" class="tab-btn" onclick="switchTab('document')">
                    📄 Document Notes & Materials
                </button>
            </div>

            <!-- Tab 1: Video -->
            <div id="tab-video" class="tab-panel active">
                <div class="video-wrapper">
                    <?php if ($embed_video_url): ?>
                        <iframe
                            src="<?php echo htmlspecialchars($embed_video_url); ?>"
                            title="<?php echo htmlspecialchars($current_lesson["title"]); ?>"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                        </iframe>
                    <?php else: ?>
                        <div class="no-video-box">
                            <div style="font-size: 50px;">🎥</div>
                            <h3>Video Lesson Stream</h3>
                            <p>No video player link specified for this lesson.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tab 2: Document / Notes -->
            <div id="tab-document" class="tab-panel">
                <div class="document-view-container">
                    <div class="doc-header-bar">
                        <h3>
                            📑 <?php echo htmlspecialchars($current_lesson["document_title"] ?? ($current_lesson["title"] . " Notes")); ?>
                        </h3>
                        <a href="download_notes.php?lesson_id=<?php echo (int)$current_lesson["lesson_id"]; ?>" class="download-doc-btn">
                            📥 Download Notes (.TXT)
                        </a>
                    </div>

                    <div class="document-body">
                        <?php echo renderDocumentMarkdown($current_lesson["document_content"]); ?>
                    </div>
                </div>
            </div>

            <!-- Lesson Meta & Control Buttons -->
            <div class="lesson-meta-content">
                <span class="lesson-badge">LESSON <?php echo (int)$current_lesson["lesson_number"]; ?> OF <?php echo $total_lessons; ?></span>
                <h2><?php echo htmlspecialchars($current_lesson["title"]); ?></h2>
                <p class="lesson-desc">
                    <?php echo htmlspecialchars($current_lesson["description"] ?? "Complete this lesson to advance your skills."); ?>
                </p>

                <!-- Actions Toolbar -->
                <div class="action-toolbar">
                    <form method="post" class="complete-form">
                        <input type="hidden" name="lesson_id" value="<?php echo (int)$current_lesson["lesson_id"]; ?>">
                        <?php if ($is_current_completed): ?>
                            <button type="submit" name="toggle_lesson" class="btn-completed">
                                ✓ Completed (Click to Toggle)
                            </button>
                        <?php else: ?>
                            <button type="submit" name="toggle_lesson" class="btn-complete">
                                ✓ Mark Lesson Complete
                            </button>
                        <?php endif; ?>
                    </form>

                    <div style="display:flex; gap:10px;">
                        <?php if ($prev_lesson): ?>
                            <a href="learning.php?course_id=<?php echo $course_id; ?>&lesson_id=<?php echo (int)$prev_lesson["lesson_id"]; ?>" class="btn-nav">
                                ← Previous
                            </a>
                        <?php endif; ?>

                        <?php if ($next_lesson): ?>
                            <a href="learning.php?course_id=<?php echo $course_id; ?>&lesson_id=<?php echo (int)$next_lesson["lesson_id"]; ?>" class="btn-nav">
                                Next Lesson →
                            </a>
                        <?php endif; ?>

                        <?php if ($progress >= 100): ?>
                            <a href="certificate.php?course_id=<?php echo $course_id; ?>" class="btn-cert-gold">
                                🎓 Download Certificate
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

        </div>

        <!-- Right Column: Sidebar Lessons List -->
        <aside class="sidebar-card">
            <div class="sidebar-header">
                <h3>Course Content</h3>
                <p><?php echo $completed_count; ?> of <?php echo $total_lessons; ?> lessons completed</p>

                <div class="progress-box">
                    <div class="progress-info">
                        <span>Progress</span>
                        <span style="color:#ef4444; font-weight:800;"><?php echo $progress; ?>%</span>
                    </div>
                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill" style="width: <?php echo $progress; ?>%;"></div>
                    </div>
                </div>
            </div>

            <div class="lessons-scroll-list">
                <?php foreach ($lessons as $l): ?>
                    <?php
                    $is_act = (int)$l["lesson_id"] === (int)$current_lesson["lesson_id"];
                    $is_done = in_array((int)$l["lesson_id"], $completed_lessons, true);
                    ?>
                    <a href="learning.php?course_id=<?php echo $course_id; ?>&lesson_id=<?php echo (int)$l["lesson_id"]; ?>"
                       class="lesson-item <?php echo $is_act ? 'active' : ''; ?>">
                        <div class="item-number"><?php echo (int)$l["lesson_number"]; ?></div>
                        <div class="item-info">
                            <span class="item-title"><?php echo htmlspecialchars($l["title"]); ?></span>
                            <div class="item-meta">
                                <span>⏱️ <?php echo htmlspecialchars($l["duration"] ?? "10 mins"); ?></span>
                                <span>📄 Notes Included</span>
                            </div>
                        </div>
                        <?php if ($is_done): ?>
                            <span class="item-check">✓</span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </aside>

    </div>

</main>

<script>
function switchTab(tabName) {
    const videoBtn = document.querySelectorAll('.tab-btn')[0];
    const docBtn = document.querySelectorAll('.tab-btn')[1];
    const videoPanel = document.getElementById('tab-video');
    const docPanel = document.getElementById('tab-document');

    if (tabName === 'video') {
        videoBtn.classList.add('active');
        docBtn.classList.remove('active');
        videoPanel.classList.add('active');
        docPanel.classList.remove('active');
    } else {
        docBtn.classList.add('active');
        videoBtn.classList.remove('active');
        docPanel.classList.add('active');
        videoPanel.classList.remove('active');
    }
}
</script>

</body>
</html>
