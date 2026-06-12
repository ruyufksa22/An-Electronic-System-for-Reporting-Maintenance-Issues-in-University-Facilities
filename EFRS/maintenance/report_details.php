<?php
session_start();

if (!isset($_SESSION["maintenance_id"])) {
    header("Location: ../login.php");
    exit();
}

require_once "../db-connection.php";

$maintenance_id = $_SESSION["maintenance_id"];
$message = "";
$error_message = "";

$user_stmt = $conn->prepare("SELECT full_name FROM maintenance_staff WHERE maintenance_id = ?");
$user_stmt->execute([$maintenance_id]);
$user = $user_stmt->fetch();

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    header("Location: assigned_reports.php");
    exit();
}

$report_id = $_GET["id"];

if (isset($_POST["update_status"])) {
    $new_status = trim($_POST["status"]);

    if (empty($new_status)) {
        $error_message = "Please select a status.";
    } else {
        try {
            $update_stmt = $conn->prepare("UPDATE fault_reports SET status = ? WHERE report_id = ?");
            $update_stmt->execute([$new_status, $report_id]);

            $message = "Report status updated successfully.";
        } catch (PDOException $e) {
            $error_message = "Failed to update report status.";
        }
    }
}

if (isset($_POST["add_note"])) {
    $note_text = trim($_POST["note_text"]);

    if (empty($note_text)) {
        $error_message = "Please enter the maintenance note.";
    } else {
        try {
            $insert_note_stmt = $conn->prepare("
                INSERT INTO maintenance_notes (report_id, maintenance_id, note_text)
                VALUES (?, ?, ?)
            ");
            $insert_note_stmt->execute([$report_id, $maintenance_id, $note_text]);

            $message = "Maintenance note added successfully.";
        } catch (PDOException $e) {
            $error_message = "Failed to add maintenance note.";
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
    header("Location: assigned_reports.php");
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
    <title>Manage Report - EFRS</title>
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
            <a href="assigned_reports.php" class="active">Reports</a>
            <a href="../logout.php" class="login-btn">Logout</a>
        </nav>
    </div>
</header>

<section class="page-section">
    <div class="container">

        <div class="page-header-box">
            <h2>Manage Report</h2>
            <p>Review the fault report details, update its status, and add maintenance notes.</p>
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
                    <span class="detail-label">Report Date</span>
                    <p><?php echo $report["report_date"]; ?></p>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Current Status</span>
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

            </div>

            <?php if (!empty($report["image_path"])) { ?>
                <div class="report-image-box">
                    <h3>Attached Image</h3>
                    <img src="../<?php echo htmlspecialchars($report["image_path"]); ?>" alt="Report Image" class="report-image">
                </div>
            <?php } ?>

            <div class="manage-sections">

                <div class="manage-box">
                    <h3>Update Report Status</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label for="status">Select New Status</label>
                            <select name="status" id="status" required>
                                <option value="">Choose Status</option>
                                <option value="Pending" <?php if ($report["status"] == "Pending") echo "selected"; ?>>Pending</option>
                                <option value="In Progress" <?php if ($report["status"] == "In Progress") echo "selected"; ?>>In Progress</option>
                                <option value="Resolved" <?php if ($report["status"] == "Resolved") echo "selected"; ?>>Resolved</option>
                                <option value="Closed" <?php if ($report["status"] == "Closed") echo "selected"; ?>>Closed</option>
                            </select>
                        </div>

                        <button type="submit" name="update_status" class="primary-btn">Update Status</button>
                    </form>
                </div>

                <div class="manage-box">
                    <h3>Add Maintenance Note</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label for="note_text">Maintenance Note</label>
                            <textarea name="note_text" id="note_text" rows="5" required></textarea>
                        </div>

                        <button type="submit" name="add_note" class="primary-btn">Add Note</button>
                    </form>
                </div>

            </div>

            <div class="notes-section">
                <h3>Previous Maintenance Notes</h3>

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
                        <p>No maintenance notes have been added yet.</p>
                    </div>
                <?php } ?>
            </div>

            <div class="details-buttons">
                <a href="assigned_reports.php" class="secondary-btn">Back to Reports</a>
            </div>

        </div>
    </div>
</section>

</body>
</html>