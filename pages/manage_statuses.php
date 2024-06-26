<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

require '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_status'])) {
        $status = $_POST['status'];
        addStatus($status);
    } elseif (isset($_POST['edit_status'])) {
        $id = $_POST['status_id'];
        $status = $_POST['status'];
        editStatus($id, $status);
    } elseif (isset($_POST['delete_status'])) {
        $id = $_POST['status_id'];
        deleteStatus($id);
    }
}

$statuses = getStatuses();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Statuses</title>
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
                    <h1 class="h3 mb-4 text-gray-800">Manage Statuses</h1>
                    <form method="post">
                        <div class="form-group">
                            <label>Add Status</label>
                            <input type="text" name="status" class="form-control" required>
                            <button type="submit" name="add_status" class="btn btn-primary mt-2">Add</button>
                        </div>
                    </form>
                    <hr>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($statuses as $status): ?>
                                <tr>
                                    <td><?php echo $status['id']; ?></td>
                                    <td><?php echo $status['status']; ?></td>
                                    <td>
                                        <form method="post" style="display:inline-block;">
                                            <input type="hidden" name="status_id" value="<?php echo $status['id']; ?>">
                                            <input type="text" name="status" value="<?php echo $status['status']; ?>" required>
                                            <button type="submit" name="edit_status" class="btn btn-warning">Edit</button>
                                        </form>
                                        <form method="post" style="display:inline-block;">
                                            <input type="hidden" name="status_id" value="<?php echo $status['id']; ?>">
                                            <button type="submit" name="delete_status" class="btn btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
