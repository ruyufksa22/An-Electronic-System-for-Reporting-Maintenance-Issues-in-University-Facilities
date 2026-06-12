<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit();
}

require_once "../db-connection.php";

$admin_id = $_SESSION["admin_id"];
$message = "";
$error_message = "";

$user_stmt = $conn->prepare("SELECT full_name FROM admins WHERE admin_id = ?");
$user_stmt->execute([$admin_id]);
$user = $user_stmt->fetch();

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    header("Location: all_reports.php");
    exit();
}

$report_id = $_GET["id"];

if (isset($_POST["update_priority"])) {
    $priority = trim($_POST["priority"]);

    if (empty($priority)) {
        $error_message = "Please select a priority.";
    } else {
        try {
            $update_stmt = $conn->prepare("UPDATE fault_reports SET priority = ? WHERE report_id = ?");
            $update_stmt->execute([$priority, $report_id]);
            $message = "Report priority updated successfully.";
        } catch (PDOException $e) {
            $error_message = "Failed to update report priority.";
        }
    }
}

$report_stmt = $conn->prepare("
    SELECT fr.*, e.full_name, e.email, e.department
    FROM fault_reports fr
    INNER JOIN employees e ON fr.employee_id = e.employee_id
    WHERE fr.report_id = ?
");
$report_stmt->execute([$report_id]);
$report = $report_stmt->fetch();

if (!$report) {
    header("Location: all_reports.php");
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
            <h2>Report Details</h2>
            <p>View full report information and assign the appropriate priority.</p>
        </div>

        <?php if (!empty($message)) { ?>
            <div class="success-message"><?php echo $message; ?></div>
        <?php } ?>

        <?php if (!empty($error_message)) { ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php } ?>

        <div class="details-page-card">

            <div class="details-grid">

                <div class="detail-item">
                    <span class="detail-label">Report ID</span>
                    <p><?php echo $report["report_id"]; ?></p>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Employee Name</span>
                    <p><?php echo htmlspecialchars($report["full_name"]); ?></p>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Employee Email</span>
                    <p><?php echo htmlspecialchars($report["email"]); ?></p>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Department</span>
                    <p><?php echo htmlspecialchars($report["department"]); ?></p>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Title</span>
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
                    <span class="detail-label">Report Date</span>
                    <p><?php echo $report["report_date"]; ?></p>
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
                    <span class="detail-label">Current Priority</span>
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

            </div>

            <?php if (!empty($report["image_path"])) { ?>
                <div class="report-image-box">
                    <h3>Attached Image</h3>
                    <img src="../<?php echo htmlspecialchars($report["image_path"]); ?>" alt="Report Image" class="report-image">
                </div>
            <?php } ?>

            <div class="manage-box" style="margin-bottom: 30px;">
                <h3>Assign Priority</h3>
                <form method="POST">
                    <div class="form-group">
                        <label for="priority">Select Priority</label>
                        <select name="priority" id="priority" required>
                            <option value="">Choose Priority</option>
                            <option value="Low" <?php if ($report["priority"] == "Low") echo "selected"; ?>>Low</option>
                            <option value="Medium" <?php if ($report["priority"] == "Medium") echo "selected"; ?>>Medium</option>
                            <option value="High" <?php if ($report["priority"] == "High") echo "selected"; ?>>High</option>
                        </select>
                    </div>

                    <button type="submit" name="update_priority" class="primary-btn">Update Priority</button>
                </form>
            </div>

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
                <a href="all_reports.php" class="secondary-btn">Back to All Reports</a>
            </div>

        </div>

    </div>
</section>

</body>
</html>