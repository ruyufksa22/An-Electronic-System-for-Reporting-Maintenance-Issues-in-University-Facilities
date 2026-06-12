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

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    header("Location: my_reports.php");
    exit();
}

$report_id = $_GET["id"];

$report_stmt = $conn->prepare("
    SELECT *
    FROM fault_reports
    WHERE report_id = ? AND employee_id = ?
");
$report_stmt->execute([$report_id, $employee_id]);
$report = $report_stmt->fetch();

if (!$report) {
    header("Location: my_reports.php");
    exit();
}

$notes_stmt = $conn->prepare("
    SELECT mn.*, ms.full_name AS maintenance_name
    FROM maintenance_notes mn
    INNER JOIN maintenance_staff ms ON mn.maintenance_id = ms.maintenance_id
    WHERE mn.report_id = ?
    ORDER BY mn.note_date DESC
");
$notes_stmt->execute([$report_id]);
$notes = $notes_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Details - EFRS</title>
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
            <h2>Report Details</h2>
            <p>View full information about your submitted fault report.</p>
        </div>

        <div class="details-page-card">

            <div class="details-grid">

                <div class="detail-item">
                    <span class="detail-label">Report ID</span>
                    <p><?php echo $report["report_id"]; ?></p>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Report Title</span>
                    <p><?php echo htmlspecialchars($report["title"]); ?></p>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Fault Type</span>
                    <p><?php echo htmlspecialchars($report["fault_type"]); ?></p>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Location</span>
                    <p><?php echo htmlspecialchars($report["location"]); ?></p>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Status</span>
                    <p>
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
                    </p>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Priority</span>
                    <p>
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
                    </p>
                </div>

                <div class="detail-item full-width">
                    <span class="detail-label">Description</span>
                    <p><?php echo nl2br(htmlspecialchars($report["description"])); ?></p>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Report Date</span>
                    <p><?php echo $report["report_date"]; ?></p>
                </div>

            </div>

            <?php if (!empty($report["image_path"])) { ?>
                <div class="report-image-box">
                    <h3>Attached Image</h3>
                    <img src="../<?php echo htmlspecialchars($report["image_path"]); ?>" alt="Report Image" class="report-image">
                </div>
            <?php } ?>

            <div class="notes-section">
                <h3>Maintenance Notes</h3>

                <?php if (count($notes) > 0) { ?>
                    <?php foreach ($notes as $note) { ?>
                        <div class="note-card">
                            <div class="note-header">
                                <strong><?php echo htmlspecialchars($note["maintenance_name"]); ?></strong>
                                <span><?php echo $note["note_date"]; ?></span>
                            </div>
                            <p><?php echo nl2br(htmlspecialchars($note["note_text"])); ?></p>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="empty-box small-empty">
                        <p>No maintenance notes available for this report yet.</p>
                    </div>
                <?php } ?>
            </div>

            <div class="details-buttons">
                <a href="my_reports.php" class="secondary-btn">Back to My Reports</a>
                <a href="submit_report.php" class="primary-btn">Submit New Report</a>
            </div>

        </div>
    </div>
</section>

</body>
</html>