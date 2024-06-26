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

// Fetch statistics
$totalPersons = getTotalPersons();
$totalAttendance = getTotalAttendance();
$personsWithMobile = getPersonsWithMobile();
$personsWithoutMobile = getPersonsWithoutMobile();

$persons = getAllPersonsWithLastSeen();

function getAllPersonsWithLastSeen() {
    global $conn;
    $stmt = $conn->prepare("SELECT p.id, p.name, p.mobile, p.status, (SELECT MAX(a.date) FROM attendance a WHERE a.person_id = p.id AND a.type = 'Checked') as last_seen FROM persons p");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
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
                    <h1 class="h3 mb-4 text-gray-800">Dashboard</h1>
                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Persons</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalPersons; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Attendance Records</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalAttendance; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Persons with Mobile Number</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $personsWithMobile; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-mobile-alt fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Persons without Mobile Number</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $personsWithoutMobile; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-phone-slash fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="dashboardTable" class="display">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Mobile</th>
                                    <th>Status</th>
                                    <th>Last Seen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($persons as $person): ?>
                                    <tr>
                                        <td><a href="person.php?id=<?php echo $person['id']; ?>"><?php echo $person['name']; ?></a></td>
                                        <td><?php echo $person['mobile']; ?></td>
                                        <td><?php echo $person['status']; ?></td>
                                        <td><?php echo $person['last_seen']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination-jump">
                        <input type="number" id="page-number" placeholder="Page number">
                        <button id="go-to-page">Go</button>
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
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#dashboardTable').DataTable({
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "pageLength": 10
            });

            $('#go-to-page').click(function() {
                var page = parseInt($('#page-number').val());
                if (!isNaN(page)) {
                    var pageIndex = page - 1;
                    table.page(pageIndex).draw(false);
                }
            });
        });
    </script>
</body>
</html>
