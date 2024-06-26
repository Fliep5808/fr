<?php
require 'config.php';

function getPersonByName($name) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM persons WHERE name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function createPerson($name, $mobile = null, $status = null, $notes = null) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO persons (name, mobile, status, notes) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $mobile, $status, $notes);
    $stmt->execute();
    return $conn->insert_id;
}

function insertAttendance($person_id, $date, $week, $type, $check_in_detail) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO attendance (person_id, date, week, type, check_in_detail) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $person_id, $date, $week, $type, $check_in_detail);
    $stmt->execute();
}

function insertAttendanceBatch($attendanceData) {
    global $conn;
    $conn->begin_transaction();
    $stmt = $conn->prepare("INSERT INTO attendance (person_id, date, week, type, check_in_detail) VALUES (?, ?, ?, ?, ?)");

    foreach ($attendanceData as $data) {
        $stmt->bind_param("issss", $data[0], $data[1], $data[2], $data[3], $data[4]);
        $stmt->execute();
    }

    $conn->commit();
}

function getPersons() {
    global $conn;
    $result = $conn->query("SELECT * FROM persons");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getPersonStats() {
    global $conn;
    $totalPersons = $conn->query("SELECT COUNT(*) AS count FROM persons")->fetch_assoc()['count'];
    $totalAttendance = $conn->query("SELECT COUNT(*) AS count FROM attendance")->fetch_assoc()['count'];
    $personsWithMobile = $conn->query("SELECT COUNT(*) AS count FROM persons WHERE mobile IS NOT NULL AND mobile != ''")->fetch_assoc()['count'];
    $personsWithoutMobile = $totalPersons - $personsWithMobile;
    return [
        'totalPersons' => $totalPersons,
        'totalAttendance' => $totalAttendance,
        'personsWithMobile' => $personsWithMobile,
        'personsWithoutMobile' => $personsWithoutMobile
    ];
}

function getTotalPersons() {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) FROM persons");
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    return $count;
}

function getTotalAttendance() {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) FROM attendance");
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    return $count;
}

function getPersonsWithMobile() {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) FROM persons WHERE mobile IS NOT NULL AND mobile != ''");
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    return $count;
}

function getPersonsWithoutMobile() {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) FROM persons WHERE mobile IS NULL OR mobile = ''");
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    return $count;
}

function updatePerson($id, $mobile, $status, $notes, $imagePath = null) {
    global $conn;
    // Debugging: Print the received parameters
    echo "<pre>";
    echo "Update Person:\n";
    echo "ID: $id\n";
    echo "Mobile: $mobile\n";
    echo "Status: $status\n";
    echo "Notes: $notes\n";
    echo "Image Path: $imagePath\n";
    echo "</pre>";
    if ($imagePath) {
        $stmt = $conn->prepare("UPDATE persons SET mobile = ?, status = ?, notes = ?, image_path = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $mobile, $status, $notes, $imagePath, $id);
    } else {
        $stmt = $conn->prepare("UPDATE persons SET mobile = ?, status = ?, notes = ? WHERE id = ?");
        $stmt->bind_param("sssi", $mobile, $status, $notes, $id);
    }
    $stmt->execute();
}

function deletePersonImage($personId) {
    global $conn;
    $stmt = $conn->prepare("SELECT image_path FROM persons WHERE id = ?");
    $stmt->bind_param("i", $personId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result && $result['image_path']) {
        $filePath = $result['image_path'];
        if (file_exists($filePath)) {
            if (unlink($filePath)) {
                echo "Image deleted successfully: " . $filePath;
            } else {
                echo "Failed to delete image: " . $filePath;
            }
        } else {
            echo "Image file does not exist: " . $filePath;
        }
    } else {
        echo "No image path found for person ID: " . $personId;
    }

    $stmt = $conn->prepare("UPDATE persons SET image_path = NULL WHERE id = ?");
    $stmt->bind_param("i", $personId);
    $stmt->execute();
}

function getPersonById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT p.*, MAX(a.date) as last_seen FROM persons p LEFT JOIN attendance a ON p.id = a.person_id AND a.type = 'Checked' WHERE p.id = ? GROUP BY p.id");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function attendanceRecordExists($person_id, $date, $type) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM attendance WHERE person_id = ? AND date = ? AND type = ?");
    $stmt->bind_param("iss", $person_id, $date, $type);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function getNonAttendees($weeks, $statuses, $noStatusOnly) {
    global $conn;

    $query = "
        SELECT p.id, p.name, p.mobile, p.status, MAX(a.date) as last_seen
        FROM persons p
        LEFT JOIN attendance a ON p.id = a.person_id AND a.type = 'Checked'
        WHERE 1=1";

    // Add status filter if any statuses are selected
    if (!empty($statuses)) {
        $statusPlaceholders = implode(',', array_fill(0, count($statuses), '?'));
        $query .= " AND p.status IN ($statusPlaceholders)";
    }

    // Filter for no status if selected
    if ($noStatusOnly) {
        $query .= " AND (p.status IS NULL OR p.status = '')";
    }

    $query .= "
        GROUP BY p.id
        HAVING MAX(a.date) IS NULL OR MAX(a.date) < DATE_SUB(CURDATE(), INTERVAL ? WEEK)";
    
    $stmt = $conn->prepare($query);

    // Bind parameters
    $types = str_repeat('s', count($statuses)) . 'i';
    $params = array_merge($statuses, [$weeks]);
    $stmt->bind_param($types, ...$params);
    
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Status functions
function getStatuses() {
    global $conn;
    $result = $conn->query("SELECT * FROM statuses");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function addStatus($status) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO statuses (status) VALUES (?)");
    $stmt->bind_param("s", $status);
    $stmt->execute();
}

function editStatus($id, $status) {
    global $conn;
    $stmt = $conn->prepare("UPDATE statuses SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
}

function deleteStatus($id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM statuses WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}
?>
