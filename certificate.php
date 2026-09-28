<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION["user_id"];
$course_id = isset($_GET["course_id"]) ? (int)$_GET["course_id"] : 0;

if ($course_id <= 0) {
    header("Location: my_courses.php");
    exit();
}

/* Course */
$stmt = $conn->prepare(
    "SELECT title, category, instructor
     FROM courses
     WHERE course_id = ?
     LIMIT 1"
);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$course) {
    die("Course not found.");
}

/* User */
$stmt = $conn->prepare(
    "SELECT name, email, education, interests, skills, level
     FROM users
     WHERE user_id = ?
     LIMIT 1"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    die("User not found.");
}

/* Completion check for this course */
$stmt = $conn->prepare(
    "SELECT
        (SELECT COUNT(*)
         FROM lessons
         WHERE course_id = ?) AS total_lessons,

        (SELECT COUNT(*)
         FROM lesson_progress lp
         INNER JOIN lessons l
            ON l.lesson_id = lp.lesson_id
         WHERE lp.user_id = ?
           AND l.course_id = ?
           AND lp.completed = 1) AS completed_lessons"
);
$stmt->bind_param("iii", $course_id, $user_id, $course_id);
$stmt->execute();
$completion = $stmt->get_result()->fetch_assoc();
$stmt->close();

$total = (int)$completion["total_lessons"];
$completed = (int)$completion["completed_lessons"];

if ($total === 0 || $completed < $total) {
    die("
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <title>Certificate Locked - Learnora AI</title>
            <style>
                body { background: #120404; color: #fff; font-family: 'Segoe UI', Arial, sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
                .locked-card { background: #210808; border: 1px solid #701818; padding: 45px 55px; border-radius: 20px; text-align: center; max-width: 500px; box-shadow: 0 15px 40px rgba(0,0,0,0.5); }
                h1 { color: #fca5a5; font-size: 32px; margin-bottom: 10px; }
                p { color: #cbd5e1; font-size: 16px; line-height: 1.6; }
                .progress-badge { background: #450a0a; color: #f87171; padding: 8px 18px; border-radius: 20px; font-weight: 800; display: inline-block; margin: 20px 0; }
                .btn { display: inline-block; background: #b91c1c; color: #fff; text-decoration: none; padding: 12px 26px; border-radius: 10px; font-weight: 700; margin-top: 10px; transition: 0.3s; }
                .btn:hover { background: #dc2626; }
            </style>
        </head>
        <body>
            <div class='locked-card'>
                <div style='font-size: 60px; margin-bottom: 15px;'>🔒</div>
                <h1>Certificate Locked</h1>
                <p>You must complete all video & document lessons in <strong>" . htmlspecialchars($course["title"]) . "</strong> before downloading your certificate.</p>
                <div class='progress-badge'>Progress: {$completed} / {$total} lessons completed</div>
                <br>
                <a href='learning.php?course_id={$course_id}' class='btn'>Continue Learning →</a>
            </div>
        </body>
        </html>
    ");
}

/* Mark enrollment completed */
$stmt = $conn->prepare(
    "UPDATE enrollments
     SET progress = 100, status = 'Completed'
     WHERE user_id = ? AND course_id = ?"
);
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();
$stmt->close();

/* Create/reuse one certificate */
$stmt = $conn->prepare(
    "SELECT certificate_id, certificate_code, issued_at
     FROM certificates
     WHERE user_id = ? AND course_id = ?
     LIMIT 1"
);
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();
$certificate = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$certificate) {
    $certificate_code = "LRN-" . date("Y") . "-" . strtoupper(bin2hex(random_bytes(4)));

    $stmt = $conn->prepare(
        "INSERT INTO certificates (user_id, course_id, certificate_code)
         VALUES (?, ?, ?)"
    );
    $stmt->bind_param("iis", $user_id, $course_id, $certificate_code);
    $stmt->execute();
    $certificate_id = $stmt->insert_id;
    $stmt->close();

    $certificate = [
        "certificate_id" => $certificate_id,
        "certificate_code" => $certificate_code,
        "issued_at" => date("Y-m-d H:i:s")
    ];
}

log_user_activity($conn, $user_id, $course_id, 'complete');

$issued_date = date("F d, Y", strtotime($certificate["issued_at"]));

// Uppercase values for printing
$name_uppercase = mb_strtoupper($user["name"] ?? "");
$course_uppercase = mb_strtoupper($course["title"] ?? "");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Official Certificate of Completion - <?php echo htmlspecialchars($name_uppercase); ?></title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800&family=Great+Vibes&family=Montserrat:wght@400;600;700;800&display=swap');

* { box-sizing: border-box; }
body {
    margin: 0;
    background: #0f0303;
    font-family: 'Montserrat', sans-serif;
    color: #1e293b;
    padding: 30px 15px;
}

/* Page Control Toolbar */
.control-toolbar {
    max-width: 950px;
    margin: 0 auto 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}
.back-link {
    color: #fca5a5;
    text-decoration: none;
    font-weight: 700;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.btn-group {
    display: flex;
    gap: 12px;
}
.btn-print {
    background: #b91c1c;
    color: #fff;
    border: 0;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(185,28,28,0.4);
    transition: 0.3s;
}
.btn-print:hover { background: #dc2626; transform: translateY(-2px); }
.btn-verify {
    background: #d4af37;
    color: #1a0505;
    text-decoration: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 800;
    font-size: 14px;
    box-shadow: 0 4px 15px rgba(212,175,55,0.3);
    transition: 0.3s;
}
.btn-verify:hover { background: #fef08a; transform: translateY(-2px); }

/* Certificate Main Frame */
.certificate-frame {
    width: 950px;
    max-width: 100%;
    margin: 0 auto;
    background: #fffdf9;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.6);
    position: relative;
}

.certificate-inner {
    border: 8px solid #851818;
    padding: 40px 50px;
    position: relative;
    background: radial-gradient(circle at center, #ffffff 60%, #fffcf5 100%);
    text-align: center;
}
.certificate-inner::before {
    content: '';
    position: absolute;
    top: 5px; left: 5px; right: 5px; bottom: 5px;
    border: 2px solid #d4af37;
    pointer-events: none;
}

/* Header */
.cert-header {
    margin-bottom: 20px;
}
.cert-logo {
    font-family: 'Cinzel', serif;
    font-size: 32px;
    font-weight: 800;
    color: #580c0c;
    letter-spacing: 2px;
}
.cert-logo span { color: #dc2626; }
.cert-subtitle {
    font-size: 13px;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: #b8860b;
    font-weight: 700;
    margin-top: 5px;
}

/* Title */
.cert-title {
    font-family: 'Cinzel', serif;
    font-size: 40px;
    color: #7f1d1d;
    margin: 12px 0 8px;
    text-transform: uppercase;
    letter-spacing: 3px;
    font-weight: 700;
}
.cert-present {
    font-size: 14px;
    color: #64748b;
    font-style: italic;
    margin-bottom: 15px;
}

/* Student Name - STRICT UPPERCASE */
.student-name {
    font-family: 'Cinzel', 'Montserrat', serif;
    font-size: 46px;
    font-weight: 800;
    color: #1e1b4b;
    margin: 8px 0;
    border-bottom: 2px solid #d4af37;
    display: inline-block;
    padding: 0 40px 8px;
    text-transform: uppercase;
    letter-spacing: 2px;
}

/* Course Title - STRICT UPPERCASE */
.completion-text {
    font-size: 15px;
    color: #475569;
    margin-top: 15px;
}
.course-title {
    font-family: 'Cinzel', serif;
    font-size: 26px;
    font-weight: 700;
    color: #991b1b;
    margin: 10px 0 20px;
    text-transform: uppercase;
    letter-spacing: 1px;
}


/* Metadata Footer */
.cert-footer {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px dashed #cbd5e1;
}
.sig-box {
    text-align: center;
    width: 200px;
}
.sig-line {
    font-family: 'Great Vibes', cursive;
    font-size: 32px;
    color: #0f172a;
    border-bottom: 1px solid #94a3b8;
    padding-bottom: 5px;
    margin-bottom: 5px;
}
.sig-title {
    font-size: 12px;
    color: #64748b;
    text-transform: uppercase;
    font-weight: 600;
}

/* Seal */
.seal-box {
    width: 90px;
    height: 90px;
    background: radial-gradient(circle, #fef08a, #d4af37);
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 15px rgba(212,175,55,0.5);
    border: 3px double #785c13;
    margin: 0 auto;
}
.seal-icon { font-size: 26px; line-height: 1; }
.seal-text { font-size: 9px; font-weight: 800; color: #451a03; text-transform: uppercase; letter-spacing: 1px; }

.meta-box {
    text-align: right;
    font-size: 13px;
    color: #475569;
    line-height: 1.8;
}
.code-highlight {
    font-family: monospace;
    font-weight: bold;
    color: #991b1b;
    background: #fef2f2;
    padding: 3px 8px;
    border-radius: 4px;
    border: 1px solid #fecaca;
    text-transform: uppercase;
}

/* Print CSS */
@media print {
    body { background: #fff; padding: 0; }
    .control-toolbar { display: none !important; }
    .certificate-frame {
        box-shadow: none;
        width: 100%;
        max-width: none;
        padding: 0;
        margin: 0;
    }
}
</style>
</head>
<body>

<div class="control-toolbar">
    <a href="my_courses.php" class="back-link">← Back to My Courses</a>
    <div class="btn-group">
        <button onclick="window.print()" class="btn-print">🖨️ Download / Print Certificate (PDF)</button>
        <a href="verify_certificate.php?code=<?php echo urlencode($certificate["certificate_code"]); ?>" target="_blank" class="btn-verify">
            🔍 Verify Certificate
        </a>
    </div>
</div>

<div class="certificate-frame">
    <div class="certificate-inner">

        <div class="cert-header">
            <div class="cert-logo">LEARNORA <span>AI</span></div>
            <div class="cert-subtitle">GLOBAL AI EDUCATION PLATFORM</div>
        </div>

        <div class="cert-title">CERTIFICATE OF COMPLETION</div>
        <div class="cert-present">This official certificate is proudly presented to</div>

        <div class="student-name">
            <?php echo htmlspecialchars($name_uppercase); ?>
        </div>

        <div class="completion-text">for successfully completing all curriculum requirements and master lessons in</div>

        <div class="course-title">
            <?php echo htmlspecialchars($course_uppercase); ?>
        </div>

        <div class="cert-footer">
            <div class="sig-box">
                <div class="sig-line">Learnora Academic Director</div>
                <div class="sig-title">INSTRUCTOR SIGNATURE</div>
            </div>

            <div class="seal-box">
                <div class="seal-icon">🎓</div>
                <div class="seal-text">VERIFIED</div>
            </div>

            <div class="meta-box">
                <div><strong>ISSUED DATE:</strong> <?php echo htmlspecialchars(mb_strtoupper($issued_date)); ?></div>
                <div><strong>CERTIFICATE ID:</strong> <span class="code-highlight"><?php echo htmlspecialchars(mb_strtoupper($certificate["certificate_code"])); ?></span></div>
                <div style="font-size: 10px; color: #94a3b8; margin-top: 4px;">VERIFIED AT LEARNORA.AI/VERIFY</div>
            </div>
        </div>

    </div>
</div>

</body>
</html>
