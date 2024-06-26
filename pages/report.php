<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

require '../includes/db.php';

$statuses = getStatuses();
$selectedWeeks = isset($_POST['weeks']) ? (int)$_POST['weeks'] : 3;
$selectedStatus = isset($_POST['status']) ? $_POST['status'] : [];
$noStatusOnly = isset($_POST['no_status_only']) ? true : false;
$nonAttendees = getNonAttendees($selectedWeeks, $selectedStatus, $noStatusOnly);

function isSelected($value, $selected) {
    return in_array($value, $selected) ? 'selected' : '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports</title>
    <link href="../assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../assets/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../assets/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include 'sidebar.php'; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'topbar.php'; ?>
                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">Reports</h1>
                    <form method="post" class="form-inline mb-4">
                        <div class="form-group mr-2">
                            <label for="weeks" class="mr-2">Weeks</label>
                            <select name="weeks" id="weeks" class="form-control">
                                <option value="3" <?php echo $selectedWeeks == 3 ? 'selected' : ''; ?>>3 Weeks</option>
                                <option value="5" <?php echo $selectedWeeks == 5 ? 'selected' : ''; ?>>5 Weeks</option>
                                <option value="7" <?php echo $selectedWeeks == 7 ? 'selected' : ''; ?>>7 Weeks</option>
                            </select>
                        </div>
                        <div class="form-group mr-2">
                            <label for="status" class="mr-2">Status</label>
                            <select name="status[]" id="status" class="form-control" multiple>
                                <?php foreach ($statuses as $status): ?>
                                    <option value="<?php echo $status['status']; ?>" <?php echo isSelected($status['status'], $selectedStatus); ?>><?php echo $status['status']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group mr-2">
                            <label class="mr-2">No Status Only</label>
                            <input type="checkbox" name="no_status_only" <?php echo $noStatusOnly ? 'checked' : ''; ?>>
                        </div>
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </form>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Non-Attendees for <?php echo $selectedWeeks; ?> Weeks</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTableReport" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Mobile</th>
                                            <th>Status</th>
                                            <th>Last Seen</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($nonAttendees as $person): ?>
                                            <tr>
                                                <td><a href="person.php?id=<?php echo $person['id']; ?>"><?php echo $person['name']; ?></a></td>
                                                <td><?php echo $person['mobile']; ?></td>
                                                <td><?php echo $person['status']; ?></td>
                                                <td><?php echo $person['last_seen'] ? $person['last_seen'] : 'Never'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
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
    <script src="../assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../assets/vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.print.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#dataTableReport').DataTable({
                "order": [[ 3, "desc" ]],
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ]
            });
        });
    </script>
</body>
</html>
