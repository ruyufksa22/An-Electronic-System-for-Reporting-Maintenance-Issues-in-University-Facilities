<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit();
}

require_once "../db-connection.php";

$admin_id = $_SESSION["admin_id"];

$user_stmt = $conn->prepare("SELECT full_name FROM admins WHERE admin_id = ?");
$user_stmt->execute([$admin_id]);
$user = $user_stmt->fetch();

$total_reports_stmt = $conn->prepare("SELECT COUNT(*) FROM fault_reports");
$total_reports_stmt->execute();
$total_reports = $total_reports_stmt->fetchColumn();

$pending_stmt = $conn->prepare("SELECT COUNT(*) FROM fault_reports WHERE status = 'Pending'");
$pending_stmt->execute();
$pending_reports = $pending_stmt->fetchColumn();

$progress_stmt = $conn->prepare("SELECT COUNT(*) FROM fault_reports WHERE status = 'In Progress'");
$progress_stmt->execute();
$in_progress_reports = $progress_stmt->fetchColumn();

$resolved_stmt = $conn->prepare("SELECT COUNT(*) FROM fault_reports WHERE status = 'Resolved'");
$resolved_stmt->execute();
$resolved_reports = $resolved_stmt->fetchColumn();

$closed_stmt = $conn->prepare("SELECT COUNT(*) FROM fault_reports WHERE status = 'Closed'");
$closed_stmt->execute();
$closed_reports = $closed_stmt->fetchColumn();

$employees_stmt = $conn->prepare("SELECT COUNT(*) FROM employees");
$employees_stmt->execute();
$total_employees = $employees_stmt->fetchColumn();

$maintenance_stmt = $conn->prepare("SELECT COUNT(*) FROM maintenance_staff");
$maintenance_stmt->execute();
$total_maintenance = $maintenance_stmt->fetchColumn();

$reports_stmt = $conn->prepare("
    SELECT fr.*, e.full_name
    FROM fault_reports fr
    INNER JOIN employees e ON fr.employee_id = e.employee_id
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
    <title>Admin Dashboard - EFRS</title>
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
            <a href="all_reports.php">All Reports</a>
            <a href="manage_employees.php">Employees</a>
            <a href="manage_maintenance.php">Maintenance</a>
            <a href="reports_statistics.php">Statistics</a>
            <span class="user-name"><?php echo htmlspecialchars($user["full_name"]); ?></span>
            <a href="../logout.php" class="login-btn">Logout</a>
        </nav>

    </div>
</header>

<section class="page-section">
    <div class="container">

        <div class="page-header-box">
            <h2>Admin Dashboard</h2>
            <p>Monitor the system, manage users, and track fault reporting performance.</p>
        </div>

        
        <div class="cards">

            <div class="card">
                <h3>Total Reports</h3>
                <p><?php echo $total_reports; ?></p>
            </div>

            <div class="card pending">
                <h3>Pending</h3>
                <p><?php echo $pending_reports; ?></p>
            </div>

            <div class="card progress">
                <h3>In Progress</h3>
                <p><?php echo $in_progress_reports; ?></p>
            </div>

            <div class="card resolved">
                <h3>Resolved</h3>
                <p><?php echo $resolved_reports; ?></p>
            </div>

            <div class="card closed-card">
                <h3>Closed</h3>
                <p><?php echo $closed_reports; ?></p>
            </div>

            <div class="card employee-card">
                <h3>Employees</h3>
                <p><?php echo $total_employees; ?></p>
            </div>

            <div class="card maintenance-card">
                <h3>Maintenance Staff</h3>
                <p><?php echo $total_maintenance; ?></p>
            </div>

        </div>

        
        <div class="quick-links-grid">
            <a href="all_reports.php" class="quick-link-card">
                <h3>All Reports</h3>
                <p>View all submitted fault reports and manage priorities.</p>
            </a>

            <a href="manage_employees.php" class="quick-link-card">
                <h3>Manage Employees</h3>
                <p>Add, view, update, and remove employee accounts.</p>
            </a>

            <a href="manage_maintenance.php" class="quick-link-card">
                <h3>Manage Maintenance Staff</h3>
                <p>Control maintenance team accounts and related details.</p>
            </a>

            <a href="reports_statistics.php" class="quick-link-card">
                <h3>Reports & Statistics</h3>
                <p>Review system analytics and maintenance performance.</p>
            </a>
        </div>

       
        <div class="reports-page-card">
            <h3 class="section-subtitle">Recent Reports</h3>

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
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $report) { ?>
                            <tr>
                                <td><?php echo $report["report_id"]; ?></td>
                                <td><?php echo htmlspecialchars($report["full_name"]); ?></td>
                                <td><?php echo htmlspecialchars($report["title"]); ?></td>
                                <td>
                                    <span class="status-badge
                                        <?php
                                            if ($report["status"] == "Pending") {
                                                echo "status-pending";
                                            } elseif ($report["status"] == "In Progress") {
                                                echo "status-progress";
                                            } elseif ($report["status"] == "Resolved") {
                                                echo "status-resolved";
                                            } else {
                                                echo "status-closed";
                                            }
                                        ?>">
                                        <?php echo htmlspecialchars($report["status"]); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="priority-badge
                                        <?php
                                            if ($report["priority"] == "High") {
                                                echo "priority-high";
                                            } elseif ($report["priority"] == "Medium") {
                                                echo "priority-medium";
                                            } else {
                                                echo "priority-low";
                                            }
                                        ?>">
                                        <?php echo htmlspecialchars($report["priority"]); ?>
                                    </span>
                                </td>
                                <td><?php echo $report["report_date"]; ?></td>
                                <td>
                                    <a href="report_details.php?id=<?php echo $report["report_id"]; ?>" class="table-action-btn">
                                        View
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