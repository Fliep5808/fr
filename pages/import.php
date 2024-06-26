<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

require '../includes/db.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ob_start(); // Start output buffering

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    if (($handle = fopen($file, "r")) !== FALSE) {
        fgetcsv($handle); // Skip the header row
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (isset($data[0]) && isset($data[1]) && isset($data[2]) && isset($data[3]) && isset($data[4])) {
                $name = $data[0];
                
                // Check if the date is valid
                $dateObj = DateTime::createFromFormat('m/d/Y', $data[1]);
                if ($dateObj !== false) {
                    $date = $dateObj->format('Y-m-d');
                } else {
                    echo "Invalid date format for: " . $data[1];
                    continue; // Skip this record
                }

                $week = $data[2];
                $type = $data[3];
                $check_in_detail = $data[4];

                // Check if the person exists
                $stmt = $conn->prepare("SELECT id FROM persons WHERE name = ?");
                $stmt->bind_param("s", $name);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $person_id = $row['id'];
                } else {
                    // Insert the new person
                    $stmt = $conn->prepare("INSERT INTO persons (name) VALUES (?)");
                    $stmt->bind_param("s", $name);
                    $stmt->execute();
                    $person_id = $stmt->insert_id;
                }

                // Check if the attendance record already exists
                $stmt = $conn->prepare("SELECT id FROM attendance WHERE person_id = ? AND date = ? AND week = ? AND type = ? AND check_in_detail = ?");
                $stmt->bind_param("issss", $person_id, $date, $week, $type, $check_in_detail);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows == 0) {
                    // Insert the attendance record
                    $stmt = $conn->prepare("INSERT INTO attendance (person_id, date, week, type, check_in_detail) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("issss", $person_id, $date, $week, $type, $check_in_detail);
                    $stmt->execute();
                }
            } else {
                echo "Invalid data format in CSV row: " . implode(", ", $data);
            }
        }
        fclose($handle);
    }
    header("Location: import.php?success=1");
    ob_end_flush(); // End output buffering and flush output
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Import CSV</title>
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
                    <h1 class="h3 mb-4 text-gray-800">Import CSV</h1>
                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success">CSV imported successfully!</div>
                    <?php endif; ?>
                    <form method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>CSV File</label>
                            <input type="file" name="csv_file" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">Import</button>
                    </form>
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
