<?php
session_start();

if (!isset($_SESSION["employee_id"])) {
    header("Location: ../login.php");
    exit();
}

require_once "../db-connection.php";

$employee_id = $_SESSION["employee_id"];

$stmt = $conn->prepare("SELECT full_name FROM employees WHERE employee_id = ?");
$stmt->execute([$employee_id]);
$user = $stmt->fetch();

$total_stmt = $conn->prepare("SELECT COUNT(*) FROM fault_reports WHERE employee_id = ?");
$total_stmt->execute([$employee_id]);
$total_reports = $total_stmt->fetchColumn();

$pending_stmt = $conn->prepare("SELECT COUNT(*) FROM fault_reports WHERE employee_id = ? AND status = 'Pending'");
$pending_stmt->execute([$employee_id]);
$pending = $pending_stmt->fetchColumn();

$progress_stmt = $conn->prepare("SELECT COUNT(*) FROM fault_reports WHERE employee_id = ? AND status = 'In Progress'");
$progress_stmt->execute([$employee_id]);
$in_progress = $progress_stmt->fetchColumn();

$resolved_stmt = $conn->prepare("SELECT COUNT(*) FROM fault_reports WHERE employee_id = ? AND status = 'Resolved'");
$resolved_stmt->execute([$employee_id]);
$resolved = $resolved_stmt->fetchColumn();

$reports_stmt = $conn->prepare("
    SELECT * FROM fault_reports 
    WHERE employee_id = ?
    ORDER BY report_date DESC
    LIMIT 5
");
$reports_stmt->execute([$employee_id]);
$reports = $reports_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard - EFRS</title>
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
            <a href="submit_report.php">Submit Report</a>
            <a href="my_reports.php">My Reports</a>

            

            <a href="../logout.php" class="login-btn">Logout</a>
        </nav>

    </div>
</header>

<div class="dashboard-container">

    <h1>Employee Dashboard</h1>

    <div class="cards">

        <div class="card">
            <h3>Total Reports</h3>
            <p><?php echo $total_reports; ?></p>
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
        <a href="submit_report.php" class="btn primary">Submit New Report</a>
        <a href="my_reports.php" class="btn secondary">View My Reports</a>
    </div>

    <div class="recent-reports">
        <h2>Recent Reports</h2>

        <table>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Status</th>
                <th>Priority</th>
                <th>Date</th>
            </tr>

            <?php foreach ($reports as $report) { ?>
                <tr>
                    <td><?php echo $report["report_id"]; ?></td>
                    <td><?php echo $report["title"]; ?></td>
                    <td><?php echo $report["status"]; ?></td>
                    <td><?php echo $report["priority"]; ?></td>
                    <td><?php echo $report["report_date"]; ?></td>
                </tr>
            <?php } ?>

        </table>
    </div>

</div>

</body>
</html>