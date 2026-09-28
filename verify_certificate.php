<?php
session_start();
require_once "db.php";

$raw_code = trim($_GET["code"] ?? "");
$certificate = null;
$searched = false;

if ($raw_code !== "") {
    $searched = true;
    // Normalize code for matching
    $clean_input = strtoupper(str_replace(['-', ' '], '', $raw_code));
    $exact_input = strtoupper($raw_code);

    $stmt = $conn->prepare(
        "SELECT
            cert.certificate_code,
            cert.issued_at,
            u.name AS student_name,
            u.email AS student_email,
            u.education AS student_education,
            u.interests AS student_interests,
            u.skills AS student_skills,
            c.title AS course_title,
            c.category AS course_category,
            c.instructor AS course_instructor
         FROM certificates cert
         INNER JOIN users u
            ON u.user_id = cert.user_id
         INNER JOIN courses c
            ON c.course_id = cert.course_id
         WHERE UPPER(REPLACE(cert.certificate_code, '-', '')) = ?
            OR UPPER(cert.certificate_code) = ?
         LIMIT 1"
    );
    $stmt->bind_param("ss", $clean_input, $exact_input);
    $stmt->execute();
    $certificate = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verify Certificate - Learnora AI</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
:root {
    --bg-dark: #120404;
    --card-dark: #1f0707;
    --border-red: #5e1515;
    --accent-red: #dc2626;
    --accent-gold: #d4af37;
    --accent-green: #22c55e;
    --text-light: #f8fafc;
}

body {
    margin: 0;
    font-family: 'Segoe UI', Arial, sans-serif;
    background: var(--bg-dark);
    color: var(--text-light);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* Header */
header {
    height: 70px;
    padding: 0 40px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #250606;
    border-bottom: 1px solid var(--border-red);
}
header a { text-decoration: none; }
.logo { font-size: 24px; font-weight: 800; color: #fff; }
.logo span { color: var(--accent-red); }
nav a { color: #fca5a5; text-decoration: none; font-weight: 600; font-size: 14px; margin-left: 20px; transition: 0.3s; }
nav a:hover { color: #fff; }

/* Main Container */
.verify-container {
    max-width: 800px;
    width: 90%;
    margin: 50px auto;
    flex: 1;
}

.search-box {
    background: var(--card-dark);
    border: 1px solid var(--border-red);
    border-radius: 16px;
    padding: 35px;
    text-align: center;
    box-shadow: 0 15px 35px rgba(0,0,0,0.5);
    margin-bottom: 30px;
}
.search-box h1 {
    margin: 0 0 10px;
    font-size: 28px;
    color: #fff;
}
.search-box p {
    color: #cbd5e1;
    font-size: 15px;
    margin-bottom: 25px;
}

.verify-form {
    display: flex;
    gap: 12px;
    max-width: 550px;
    margin: 0 auto;
}
.verify-form input {
    flex: 1;
    padding: 14px 18px;
    border-radius: 10px;
    border: 1px solid #7f1d1d;
    background: #0f0303;
    color: #fff;
    font-size: 15px;
    outline: none;
    font-family: monospace;
    text-transform: uppercase;
}
.verify-form input:focus {
    border-color: #ef4444;
}
.verify-form button {
    padding: 14px 26px;
    border: 0;
    border-radius: 10px;
    background: #b91c1c;
    color: #fff;
    font-weight: 700;
    font-size: 15px;
    cursor: pointer;
    transition: 0.3s;
}
.verify-form button:hover {
    background: #dc2626;
}

/* Result Card */
.result-card {
    background: var(--card-dark);
    border-radius: 16px;
    padding: 35px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.5);
    text-align: center;
    animation: fadeIn 0.4s ease;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Valid Result */
.valid-card {
    border: 2px solid #16a34a;
    background: linear-gradient(135deg, #170707, #061e0d);
}
.badge-valid {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #14532d;
    color: #86efac;
    border: 1px solid #22c55e;
    padding: 8px 20px;
    border-radius: 20px;
    font-weight: 800;
    font-size: 14px;
    margin-bottom: 20px;
}

/* Invalid Result */
.invalid-card {
    border: 2px solid #b91c1c;
    background: linear-gradient(135deg, #1e0707, #2d0909);
}
.badge-invalid {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #450a0a;
    color: #fca5a5;
    border: 1px solid #991b1b;
    padding: 8px 20px;
    border-radius: 20px;
    font-weight: 800;
    font-size: 14px;
    margin-bottom: 20px;
}

/* Certificate Data Display */
.cert-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    text-align: left;
    margin-top: 25px;
    background: rgba(0,0,0,0.3);
    padding: 25px;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.08);
}
.detail-item label {
    display: block;
    font-size: 11px;
    color: #94a3b8;
    text-transform: uppercase;
    font-weight: 800;
    letter-spacing: 1px;
    margin-bottom: 4px;
}
.detail-item .value {
    font-size: 15px;
    font-weight: 700;
    color: #fff;
    text-transform: uppercase;
}
.detail-item .value-student {
    font-size: 24px;
    color: var(--accent-gold);
    text-transform: uppercase;
    font-weight: 900;
}
.detail-item .code-value {
    font-family: monospace;
    color: #fca5a5;
    background: #450a0a;
    padding: 4px 10px;
    border-radius: 5px;
    display: inline-block;
    text-transform: uppercase;
}

/* Footer */
footer {
    text-align: center;
    padding: 25px;
    background: #170404;
    border-top: 1px solid var(--border-red);
    font-size: 14px;
    color: #94a3b8;
}
</style>
</head>
<body>

<header>
    <a href="index.php" class="logo">
        Learnora <span>AI</span>
    </a>
    <nav>
        <a href="index.php">Home</a>
        <a href="courses.php">Courses</a>
        <?php if (isset($_SESSION["user_id"])): ?>
            <a href="my_courses.php">My Courses</a>
            <a href="dashboard.php">Dashboard</a>
        <?php else: ?>
            <a href="login.php">Login</a>
        <?php endif; ?>
    </nav>
</header>

<main class="verify-container">

    <div class="search-box">
        <h1>🔍 CERTIFICATE VERIFICATION</h1>
        <p>Enter a Learnora AI Certificate ID below to verify authenticity and view official recipient credentials.</p>

        <form method="get" class="verify-form">
            <input
                type="text"
                name="code"
                placeholder="e.g. LRN-2026-7E1EAD6A"
                value="<?php echo htmlspecialchars(mb_strtoupper($raw_code)); ?>"
                required
            >
            <button type="submit">VERIFY NOW</button>
        </form>
    </div>

    <?php if ($searched): ?>
        <?php if ($certificate): ?>
            <!-- Valid Result -->
            <div class="result-card valid-card">
                <div class="badge-valid">
                    ✓ OFFICIAL VERIFIED CERTIFICATE
                </div>

                <h2 style="margin: 0 0 10px; color: #fff; font-size: 24px; text-transform: uppercase;">
                    AUTHENTIC CERTIFICATE ISSUED BY LEARNORA AI
                </h2>
                <p style="color: #cbd5e1; margin: 0 0 20px; font-size: 14px;">
                    This certificate is official, valid, and registered in our central system.
                </p>

                <div class="cert-details-grid">
                    <div class="detail-item" style="grid-column: 1 / -1;">
                        <label>STUDENT NAME (RECIPIENT)</label>
                        <div class="value value-student">
                            🎓 <?php echo htmlspecialchars(mb_strtoupper($certificate["student_name"])); ?>
                        </div>
                    </div>

                    <div class="detail-item" style="grid-column: 1 / -1;">
                        <label>COURSE TITLE</label>
                        <div class="value" style="font-size: 18px; color: #86efac; text-transform: uppercase;">
                            📚 <?php echo htmlspecialchars(mb_strtoupper($certificate["course_title"])); ?>
                        </div>
                    </div>

                    <div class="detail-item">
                        <label>EDUCATION / DEGREE</label>
                        <div class="value">
                            <?php echo htmlspecialchars(mb_strtoupper($certificate["student_education"] ?? "N/A")); ?>
                        </div>
                    </div>

                    <div class="detail-item">
                        <label>PRIMARY INTEREST</label>
                        <div class="value">
                            <?php echo htmlspecialchars(mb_strtoupper($certificate["student_interests"] ?? "N/A")); ?>
                        </div>
                    </div>

                    <div class="detail-item">
                        <label>EXISTING SKILLS</label>
                        <div class="value">
                            <?php echo htmlspecialchars(mb_strtoupper($certificate["student_skills"] ?? "N/A")); ?>
                        </div>
                    </div>

                    <div class="detail-item">
                        <label>INSTRUCTOR</label>
                        <div class="value">
                            <?php echo htmlspecialchars(mb_strtoupper($certificate["course_instructor"] ?? "LEARNORA AI")); ?>
                        </div>
                    </div>

                    <div class="detail-item">
                        <label>ISSUED DATE</label>
                        <div class="value">
                            🗓️ <?php echo htmlspecialchars(mb_strtoupper(date("F d, Y", strtotime($certificate["issued_at"])))); ?>
                        </div>
                    </div>

                    <div class="detail-item">
                        <label>CERTIFICATE CODE</label>
                        <div class="value">
                            <span class="code-value"><?php echo htmlspecialchars(mb_strtoupper($certificate["certificate_code"])); ?></span>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- Invalid Result -->
            <div class="result-card invalid-card">
                <div class="badge-invalid">
                    ❌ CERTIFICATE NOT FOUND
                </div>

                <h2 style="margin: 0 0 10px; color: #fca5a5; font-size: 22px; text-transform: uppercase;">
                    NO RECORD MATCHES CODE "<?php echo htmlspecialchars(mb_strtoupper($raw_code)); ?>"
                </h2>
                <p style="color: #cbd5e1; font-size: 14px; line-height: 1.6; max-width: 500px; margin: 0 auto;">
                    We could not verify any official Learnora AI certificate matching the code provided. Please double-check for typos or contact support.
                </p>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</main>

<footer>
    <p>&copy; <?php echo date("Y"); ?> Learnora AI. All rights reserved.</p>
</footer>

</body>
</html>
