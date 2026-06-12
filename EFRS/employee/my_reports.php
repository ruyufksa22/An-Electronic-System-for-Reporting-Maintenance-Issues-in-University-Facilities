<?php
session_start();

if (!isset($_SESSION["employee_id"])) {
    header("Location: ../login.php");
    exit();
}

require_once "../db-connection.php";

$employee_id = $_SESSION["employee_id"];

$user_stmt = $conn->prepare("SELECT full_name FROM employees WHERE employee_id = ?");
$user_stmt->execute([$employee_id]);
$user = $user_stmt->fetch();

$reports_stmt = $conn->prepare("
    SELECT *
    FROM fault_reports
    WHERE employee_id = ?
    ORDER BY report_date DESC
");
$reports_stmt->execute([$employee_id]);
$reports = $reports_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reports - EFRS</title>
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
            <a href="dashboard.php">Dashboard</a>
            <a href="submit_report.php">Submit Report</a>
            <a href="my_reports.php" class="active">My Reports</a>
            <a href="../logout.php" class="login-btn">Logout</a>
        </nav>
    </div>
</header>

<section class="page-section">
    <div class="container">

        <div class="page-header-box">
            <h2>My Reports</h2>
            <p>View all submitted fault reports and track their current status.</p>
        </div>

        <div class="reports-page-card">

            <?php if (count($reports) > 0) { ?>
                <div class="table-wrapper">
                    <table class="reports-table">
                        <thead>
                            <tr>
                                <th>Report ID</th>
                                <th>Title</th>
                                <th>Fault Type</th>
                                <th>Location</th>
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
                                    <td><?php echo htmlspecialchars($report["title"]); ?></td>
                                    <td><?php echo htmlspecialchars($report["fault_type"]); ?></td>
                                    <td><?php echo htmlspecialchars($report["location"]); ?></td>
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
            <?php } else { ?>
                <div class="empty-box">
                    <h3>No Reports Found</h3>
                    <p>You have not submitted any fault reports yet.</p>
                    <a href="submit_report.php" class="primary-btn">Submit Your First Report</a>
                </div>
            <?php } ?>

        </div>
    </div>
</section>

</body>
</html>