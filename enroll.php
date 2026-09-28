<?php

session_start();

require_once "db.php";


// ==========================================
// USER LOGIN CHECK
// ==========================================

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit();

}


// ==========================================
// COURSE ID CHECK
// ==========================================

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {

    header("Location: courses.php");
    exit();

}


$user_id = $_SESSION["user_id"];
$course_id = intval($_GET["id"]);


// ==========================================
// CHECK COURSE EXISTS
// ==========================================

$course_stmt = $conn->prepare(
    "SELECT course_id, title
     FROM courses
     WHERE course_id = ?"
);

$course_stmt->bind_param(
    "i",
    $course_id
);

$course_stmt->execute();

$course_result = $course_stmt->get_result();


if ($course_result->num_rows == 0) {

    header("Location: courses.php");
    exit();

}


$course = $course_result->fetch_assoc();

$course_stmt->close();


// ==========================================
// CHECK ALREADY ENROLLED
// ==========================================

$check_stmt = $conn->prepare(
    "SELECT enrollment_id
     FROM enrollments
     WHERE user_id = ?
     AND course_id = ?"
);

$check_stmt->bind_param(
    "ii",
    $user_id,
    $course_id
);

$check_stmt->execute();

$check_result = $check_stmt->get_result();


if ($check_result->num_rows > 0) {

    $check_stmt->close();

    header(
        "Location: course_details.php?id="
        . $course_id
    );

    exit();

}

$check_stmt->close();


// ==========================================
// INSERT ENROLLMENT
// ==========================================

$insert_stmt = $conn->prepare(
    "INSERT INTO enrollments
    (user_id, course_id, status, progress)
    VALUES (?, ?, 'Enrolled', 0)"
);

$insert_stmt->bind_param(
    "ii",
    $user_id,
    $course_id
);


if ($insert_stmt->execute()) {

    $insert_stmt->close();
    log_user_activity($conn, $user_id, $course_id, 'enroll');

    header(
        "Location: my_courses.php?enrolled=success"
    );

    exit();

} else {

    $insert_stmt->close();

    die(
        "Enrollment failed. Please try again."
    );

}

?>