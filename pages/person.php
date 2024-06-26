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

$saved = false;
$imageDeleted = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete_image']) && isset($_POST['person_id'])) {
        $id = $_POST['person_id'];
        deletePersonImage($id);
        $imageDeleted = true;
        header("Location: person.php?id=$id&image_deleted=1");
        exit;
    } elseif (isset($_POST['save_person'])) {
        $id = $_POST['person_id'];
        $mobile = $_POST['mobile'];
        $status = $_POST['status'];
        $notes = $_POST['notes'];

        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $targetDir = '../uploads/';
            $imagePath = $targetDir . basename($_FILES['image']['name']);
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                $imagePath = null; // reset if upload failed
            }
        }

        if (isset($_POST['pasted_image']) && !empty($_POST['pasted_image'])) {
            $data = $_POST['pasted_image'];
            list($type, $data) = explode(';', $data);
            list(, $data) = explode(',', $data);
            $data = base64_decode($data);
            $imagePath = '../uploads/' . uniqid() . '.png';
            file_put_contents($imagePath, $data);
        }

        updatePerson($id, $mobile, $status, $notes, $imagePath);
        $saved = true;
        header("Location: person.php?id=$id&saved=1");
        exit;
    }
}

if (isset($_GET['id'])) {
    $person = getPersonById($_GET['id']);
    $statuses = getStatuses();
} else {
    header("Location: dashboard.php");
    exit;
}

$saved = isset($_GET['saved']);
$imageDeleted = isset($_GET['image_deleted']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Person Details</title>
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
                    <h1 class="h3 mb-4 text-gray-800">Person Details - <?php echo $person['name']; ?>
                        <?php if ($person['last_seen']): ?>
                            (<a href="attendance.php?person_id=<?php echo $person['id']; ?>" class="text-decoration-none">Last seen: <?php echo $person['last_seen']; ?></a>)
                        <?php endif; ?>
                    </h1>
                    <?php if ($saved): ?>
                        <div class="alert alert-success">Details saved successfully!</div>
                    <?php endif; ?>
                    <?php if ($imageDeleted): ?>
                        <div class="alert alert-success">Image deleted successfully!</div>
                    <?php endif; ?>
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="person_id" value="<?php echo $person['id']; ?>">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" class="form-control" value="<?php echo $person['name']; ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label>Mobile</label>
                            <input type="text" name="mobile" class="form-control" value="<?php echo $person['mobile']; ?>">
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <?php foreach ($statuses as $status): ?>
                                    <option value="<?php echo $status['status']; ?>" <?php echo ($person['status'] == $status['status']) ? 'selected' : ''; ?>>
                                        <?php echo $status['status']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control"><?php echo $person['notes']; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Image</label>
                            <input type="file" name="image" class="form-control">
                            <input type="hidden" name="pasted_image" id="pasted_image">
                            <?php if ($person['image_path']): ?>
                                <img src="<?php echo $person['image_path']; ?>" alt="Person Image" class="img-fluid mt-2" onerror="this.onerror=null; this.src='path/to/default-image.png'">
                                <button type="submit" name="delete_image" class="btn btn-danger mt-2">Delete Image</button>
                            <?php endif; ?>
                            <div id="pasted_image_preview" class="mt-2"></div>
                        </div>
                        <button type="submit" name="save_person" class="btn btn-primary">Save</button>
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
    <script>
        // Ensure no JavaScript interference
        // Comment out the JavaScript to ensure it doesn't interfere
        /*
        document.addEventListener('paste', function (e) {
            var clipboardData = e.clipboardData || window.clipboardData;
            var items = clipboardData.items;

            for (var i = 0; i < items.length; i++) {
                if (items[i].type.indexOf("image") !== -1) {
                    var file = items[i].getAsFile();
                    var reader = new FileReader();
                    reader.onload = function (event) {
                        var dataUrl = event.target.result;
                        document.getElementById('pasted_image').value = dataUrl;
                        document.getElementById('pasted_image_preview').innerHTML = '<img src="' + dataUrl + '" class="img-fluid mt-2">';
                    };
                    reader.readAsDataURL(file);
                }
            }
        });
        */
    </script>
</body>
</html>
