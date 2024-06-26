<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

// Adjust the path to the db.php file as needed
require '../includes/db.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_GET['person_id'])) {
    header("Location: dashboard.php");
    exit;
}

$person_id = $_GET['person_id'];
$attendance_records = getAttendanceByPersonId($person_id);

function getAttendanceByPersonId($person_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM attendance WHERE person_id = ? ORDER BY date DESC");
    $stmt->bind_param("i", $person_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$person = getPersonById($person_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Records for <?php echo $person['name']; ?></title>
    <link href="../assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../assets/css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include 'sidebar.php'; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'topbar.php'; ?>
                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">Attendance Records for <?php echo $person['name']; ?></h1>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Week</th>
                                    <th>Type</th>
                                    <th>Check In Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendance_records as $record): ?>
                                    <tr>
                                        <td><?php echo $record['date']; ?></td>
                                        <td><?php echo $record['week']; ?></td>
                                        <td><?php echo $record['type']; ?></td>
                                        <td><?php echo $record['check_in_detail']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php include 'footer.php'; ?>
        </div>
    </div>
    <script src="../assets/vendor/jquery/jquery.min.js"></script>
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../assets/js/sb-admin-2.min.js"></script>
</body>
</html>
