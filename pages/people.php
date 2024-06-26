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

$persons = getAllPersons();

function getAllPersons() {
    global $conn;
    $stmt = $conn->prepare("SELECT id, name, mobile, status, (SELECT MAX(date) FROM attendance WHERE person_id = persons.id) AS last_seen FROM persons");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['person_id']) && isset($_POST['mobile'])) {
        $id = $_POST['person_id'];
        $mobile = $_POST['mobile'];
        $stmt = $conn->prepare("UPDATE persons SET mobile = ? WHERE id = ?");
        $stmt->bind_param("si", $mobile, $id);
        $stmt->execute();
        echo "Mobile number updated successfully!";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>People</title>
    <link href="../assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../assets/css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include 'sidebar.php'; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'topbar.php'; ?>
                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">People</h1>
                    <div class="table-responsive">
                        <table id="peopleTable" class="display">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Mobile</th>
                                    <th>Status</th>
                                    <th>Last Seen</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($persons as $person): ?>
                                    <tr>
                                        <td><?php echo $person['name']; ?></td>
                                        <td><?php echo $person['mobile']; ?></td>
                                        <td><?php echo $person['status']; ?></td>
                                        <td><?php echo $person['last_seen']; ?></td>
                                        <td>
                                            <button class="btn btn-primary edit-btn" data-id="<?php echo $person['id']; ?>" data-name="<?php echo $person['name']; ?>" data-mobile="<?php echo $person['mobile']; ?>" data-status="<?php echo $person['status']; ?>">Edit</button>
                                        </td>
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

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Person</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" id="person_id" name="person_id">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name" disabled>
                        </div>
                        <div class="form-group">
                            <label for="mobile">Mobile</label>
                            <input type="text" class="form-control" id="mobile" name="mobile">
                        </div>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/vendor/jquery/jquery.min.js"></script>
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../assets/js/sb-admin-2.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#peopleTable').DataTable({
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "pageLength": 10
            });

            $('#peopleTable').on('click', '.edit-btn', function() {
                var id = $(this).data('id');
                var name = $(this).data('name');
                var mobile = $(this).data('mobile');
                var status = $(this).data('status');

                $('#editModal #person_id').val(id);
                $('#editModal #name').val(name);
                $('#editModal #mobile').val(mobile);
                $('#editModal').modal('show');
            });

            $('#editForm').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    url: 'people.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        alert('Mobile number updated successfully!');
                        location.reload();
                    }
                });
            });
        });
    </script>
</body>
</html>
