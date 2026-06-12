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

$total_stmt = $conn->prepare("SELECT COUNT(*) FROM fault_reports");
$total_stmt->execute();
$total_reports = $total_stmt->fetchColumn();

$status_stmt = $conn->prepare("
    SELECT status, COUNT(*) AS total
    FROM fault_reports
    GROUP BY status
");
$status_stmt->execute();
$status_data = $status_stmt->fetchAll();

$priority_stmt = $conn->prepare("
    SELECT priority, COUNT(*) AS total
    FROM fault_reports
    GROUP BY priority
");
$priority_stmt->execute();
$priority_data = $priority_stmt->fetchAll();

$fault_type_stmt = $conn->prepare("
    SELECT fault_type, COUNT(*) AS total
    FROM fault_reports
    GROUP BY fault_type
    ORDER BY total DESC
");
$fault_type_stmt->execute();
$fault_type_data = $fault_type_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Statistics - EFRS</title>
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
            <a href="all_reports.php">All Reports</a>
            <a href="manage_employees.php">Employees</a>
            <a href="manage_maintenance.php">Maintenance</a>
            <a href="reports_statistics.php" class="active">Statistics</a>
            <span class="user-name"><?php echo htmlspecialchars($user["full_name"]); ?></span>
            <a href="../logout.php" class="login-btn">Logout</a>
        </nav>
    </div>
</header>

<section class="page-section">
    <div class="container">

        <div class="page-header-box">
            <h2>Reports & Statistics</h2>
            <p>Monitor the overall performance of the fault reporting system through organized statistics.</p>
        </div>

        <div class="cards">
            <div class="card">
                <h3>Total Reports</h3>
                <p><?php echo $total_reports; ?></p>
            </div>
        </div>

        <div class="statistics-grid">

            <div class="stats-card">
                <h3>Reports by Status</h3>

                <?php if (count($status_data) > 0) { ?>
                    <div class="stats-list">
                        <?php foreach ($status_data as $row) { ?>
                            <div class="stats-item">
                                <span class="stats-label"><?php echo htmlspecialchars($row["status"]); ?></span>
                                <span class="stats-value"><?php echo $row["total"]; ?></span>
                            </div>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <p class="no-stats-text">No data available.</p>
                <?php } ?>
            </div>

            <div class="stats-card">
                <h3>Reports by Priority</h3>

                <?php if (count($priority_data) > 0) { ?>
                    <div class="stats-list">
                        <?php foreach ($priority_data as $row) { ?>
                            <div class="stats-item">
                                <span class="stats-label"><?php echo htmlspecialchars($row["priority"]); ?></span>
                                <span class="stats-value"><?php echo $row["total"]; ?></span>
                            </div>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <p class="no-stats-text">No data available.</p>
                <?php } ?>
            </div>

        </div>

        <div class="stats-card large-stats-card">
            <h3>Reports by Fault Type</h3>

            <?php if (count($fault_type_data) > 0) { ?>
                <div class="table-wrapper">
                    <table class="reports-table">
                        <thead>
                            <tr>
                                <th>Fault Type</th>
                                <th>Total Reports</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fault_type_data as $row) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row["fault_type"]); ?></td>
                                    <td><?php echo $row["total"]; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } else { ?>
                <div class="empty-box small-empty">
                    <p>No fault type statistics available.</p>
                </div>
            <?php } ?>
        </div>

    </div>
</section>

</body>
</html>