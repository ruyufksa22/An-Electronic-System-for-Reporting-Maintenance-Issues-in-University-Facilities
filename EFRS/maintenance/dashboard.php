<?php
session_start();

if (!isset($_SESSION["maintenance_id"])) {
    header("Location: ../login.php");
    exit();
}

require_once "../db-connection.php";

$maintenance_id = $_SESSION["maintenance_id"];

$user_stmt = $conn->prepare("SELECT full_name FROM maintenance_staff WHERE maintenance_id = ?");
$user_stmt->execute([$maintenance_id]);
$user = $user_stmt->fetch();

$total_stmt = $conn->prepare("SELECT COUNT(*) FROM fault_reports");
$total_stmt->execute();
$total = $total_stmt->fetchColumn();

$pending_stmt = $conn->prepare("SELECT COUNT(*) FROM fault_reports WHERE status = 'Pending'");
$pending_stmt->execute();
$pending = $pending_stmt->fetchColumn();

$progress_stmt = $conn->prepare("SELECT COUNT(*) FROM fault_reports WHERE status = 'In Progress'");
$progress_stmt->execute();
$in_progress = $progress_stmt->fetchColumn();

$resolved_stmt = $conn->prepare("SELECT COUNT(*) FROM fault_reports WHERE status = 'Resolved'");
$resolved_stmt->execute();
$resolved = $resolved_stmt->fetchColumn();

$reports_stmt = $conn->prepare("
    SELECT fr.*, e.full_name
    FROM fault_reports fr
    JOIN employees e ON fr.employee_id = e.employee_id
    ORDER BY fr.report_date DESC
    LIMIT 6
");
$reports_stmt->execute();
$reports = $reports_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Dashboard - EFRS</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>

<header class="main-header">
    <div class="container navbar">

        <div class="logo-box">
            <img src="../logo.png" alt="EFRS Logo" class="site-logo">
            <div class="logo-text">
                <h1>EFRS</h1>
                <p>Electronic Fault Reporting System</p>
            </div>
        </div>

        <nav class="nav-links">
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="assigned_reports.php">Reports</a>
            <a href="../logout.php" class="login-btn">Logout</a>
        </nav>

    </div>
</header>

<section class="page-section">
    <div class="container">

        <div class="page-header-box">
            <h2>Maintenance Dashboard</h2>
            <p>Manage and monitor all fault reports in the system.</p>
        </div>

        <div class="cards">

            <div class="card">
                <h3>Total Reports</h3>
                <p><?php echo $total; ?></p>
            </div>

            <div class="card pending">
                <h3>Pending</h3>
                <p><?php echo $pending; ?></p>
            </div>

            <div class="card progress">
                <h3>In Progress</h3>
                <p><?php echo $in_progress; ?></p>
            </div>

            <div class="card resolved">
                <h3>Resolved</h3>
                <p><?php echo $resolved; ?></p>
            </div>

        </div>

        <div class="actions">
            <a href="assigned_reports.php" class="btn primary">View All Reports</a>
        </div>

        <div class="reports-page-card">
            <h3 style="margin-bottom:15px;">Recent Reports</h3>

            <div class="table-wrapper">
                <table class="reports-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Employee</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($reports as $report) { ?>
                            <tr>
                                <td><?php echo $report["report_id"]; ?></td>
                                <td><?php echo $report["full_name"]; ?></td>
                                <td><?php echo $report["title"]; ?></td>

                                <td>
                                    <span class="status-badge
                                        <?php
                                            if ($report["status"] == "Pending") echo "status-pending";
                                            elseif ($report["status"] == "In Progress") echo "status-progress";
                                            elseif ($report["status"] == "Resolved") echo "status-resolved";
                                            else echo "status-closed";
                                        ?>">
                                        <?php echo $report["status"]; ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="priority-badge
                                        <?php
                                            if ($report["priority"] == "High") echo "priority-high";
                                            elseif ($report["priority"] == "Medium") echo "priority-medium";
                                            else echo "priority-low";
                                        ?>">
                                        <?php echo $report["priority"]; ?>
                                    </span>
                                </td>

                                <td><?php echo $report["report_date"]; ?></td>

                                <td>
                                    <a href="report_details.php?id=<?php echo $report["report_id"]; ?>" class="table-action-btn">
                                        Manage
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>

                </table>
            </div>

        </div>

    </div>
</section>

</body>
</html>