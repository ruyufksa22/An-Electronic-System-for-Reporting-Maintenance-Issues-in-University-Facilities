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

$status_filter = isset($_GET["status"]) ? trim($_GET["status"]) : "";
$priority_filter = isset($_GET["priority"]) ? trim($_GET["priority"]) : "";

$where_conditions = [];
$params = [];


if (!empty($status_filter)) {
    $where_conditions[] = "fr.status = ?";
    $params[] = $status_filter;
}

if (!empty($priority_filter)) {
    $where_conditions[] = "fr.priority = ?";
    $params[] = $priority_filter;
}

$where_sql = "";
if (!empty($where_conditions)) {
    $where_sql = "WHERE " . implode(" AND ", $where_conditions);
}
$reports_stmt = $conn->prepare("
    SELECT fr.*, e.full_name, e.department
    FROM fault_reports fr
    INNER JOIN employees e ON fr.employee_id = e.employee_id
    $where_sql
    ORDER BY fr.report_date DESC
");
$reports_stmt->execute($params);
$reports = $reports_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Reports - EFRS</title>
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
            <a href="all_reports.php" class="active">All Reports</a>
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
            <h2>All Reports</h2>
            <p>View all submitted fault reports and manage their details and priorities.</p>
        </div>

        <div class="reports-page-card">

            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label for="status">Filter by Status</label>
                    <select name="status" id="status">
                        <option value="">All Statuses</option>
                        <option value="Pending" <?php if ($status_filter == "Pending") echo "selected"; ?>>Pending</option>
                        <option value="In Progress" <?php if ($status_filter == "In Progress") echo "selected"; ?>>In Progress</option>
                        <option value="Resolved" <?php if ($status_filter == "Resolved") echo "selected"; ?>>Resolved</option>
                        <option value="Closed" <?php if ($status_filter == "Closed") echo "selected"; ?>>Closed</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="priority">Filter by Priority</label>
                    <select name="priority" id="priority">
                        <option value="">All Priorities</option>
                        <option value="Low" <?php if ($priority_filter == "Low") echo "selected"; ?>>Low</option>
                        <option value="Medium" <?php if ($priority_filter == "Medium") echo "selected"; ?>>Medium</option>
                        <option value="High" <?php if ($priority_filter == "High") echo "selected"; ?>>High</option>
                    </select>
                </div>

                <div class="filter-buttons">
                    <button type="submit" class="primary-btn">Apply Filter</button>
                    <a href="all_reports.php" class="secondary-btn">Reset</a>
                </div>
            </form>

            <?php if (count($reports) > 0) { ?>
                <div class="table-wrapper">
                    <table class="reports-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Employee</th>
                                <th>Department</th>
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
                                    <td><?php echo htmlspecialchars($report["full_name"]); ?></td>
                                    <td><?php echo htmlspecialchars($report["department"]); ?></td>
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
                    <p>There are no reports matching the selected filters.</p>
                </div>
            <?php } ?>

        </div>

    </div>
</section>

</body>
</html>