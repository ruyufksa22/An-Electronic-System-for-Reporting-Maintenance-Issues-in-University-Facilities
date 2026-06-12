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

$edit_mode = false;
$edit_maintenance = null;

if (isset($_POST["add_maintenance"])) {
    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $specialization = trim($_POST["specialization"]);

    if (empty($full_name) || empty($email) || empty($password) || empty($specialization)) {
        $error_message = "Please fill in all fields.";
    } else {
        try {
            $check_stmt = $conn->prepare("SELECT maintenance_id FROM maintenance_staff WHERE email = ?");
            $check_stmt->execute([$email]);

            if ($check_stmt->rowCount() > 0) {
                $error_message = "This email is already registered.";
            } else {
                $insert_stmt = $conn->prepare("
                    INSERT INTO maintenance_staff (full_name, email, password, specialization)
                    VALUES (?, ?, ?, ?)
                ");
                $insert_stmt->execute([$full_name, $email, $password, $specialization]);

                $message = "Maintenance staff added successfully.";
            }
        } catch (PDOException $e) {
            $error_message = "Failed to add maintenance staff.";
        }
    }
}


if (isset($_GET["edit"])) {
    $edit_id = $_GET["edit"];
    $edit_stmt = $conn->prepare("SELECT * FROM maintenance_staff WHERE maintenance_id = ?");
    $edit_stmt->execute([$edit_id]);
    $edit_maintenance = $edit_stmt->fetch();

    if ($edit_maintenance) {
        $edit_mode = true;
    }
}


if (isset($_POST["update_maintenance"])) {
    $maintenance_id = $_POST["maintenance_id"];
    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $specialization = trim($_POST["specialization"]);

    if (empty($full_name) || empty($email) || empty($specialization)) {
        $error_message = "Please fill in all fields.";
    } else {
        try {
            $check_stmt = $conn->prepare("SELECT maintenance_id FROM maintenance_staff WHERE email = ? AND maintenance_id != ?");
            $check_stmt->execute([$email, $maintenance_id]);

            if ($check_stmt->rowCount() > 0) {
                $error_message = "This email is already used by another maintenance staff member.";
            } else {
                $update_stmt = $conn->prepare("
                    UPDATE maintenance_staff
                    SET full_name = ?, email = ?, specialization = ?
                    WHERE maintenance_id = ?
                ");
                $update_stmt->execute([$full_name, $email, $specialization, $maintenance_id]);

                $message = "Maintenance staff updated successfully.";
            }
        } catch (PDOException $e) {
            $error_message = "Failed to update maintenance staff.";
        }
    }
}

if (isset($_GET["delete"])) {
    $delete_id = $_GET["delete"];

    try {
        $delete_stmt = $conn->prepare("DELETE FROM maintenance_staff WHERE maintenance_id = ?");
        $delete_stmt->execute([$delete_id]);

        header("Location: manage_maintenance.php");
        exit();
    } catch (PDOException $e) {
        $error_message = "Failed to delete maintenance staff.";
    }
}

$maintenance_stmt = $conn->prepare("SELECT * FROM maintenance_staff ORDER BY created_at DESC");
$maintenance_stmt->execute();
$maintenance_list = $maintenance_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Maintenance Staff - EFRS</title>
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
            <a href="manage_maintenance.php" class="active">Maintenance</a>
            <a href="reports_statistics.php">Statistics</a>
            <span class="user-name"><?php echo htmlspecialchars($user["full_name"]); ?></span>
            <a href="../logout.php" class="login-btn">Logout</a>
        </nav>
    </div>
</header>

<section class="page-section">
    <div class="container">

        <div class="page-header-box">
            <h2>Manage Maintenance Staff</h2>
            <p>Add, update, and remove maintenance team accounts.</p>
        </div>

        <?php if (!empty($message)) { ?>
            <div class="success-message"><?php echo $message; ?></div>
        <?php } ?>

        <?php if (!empty($error_message)) { ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php } ?>

        <div class="manage-page-grid">

            <div class="manage-form-card">
                <h3><?php echo $edit_mode ? "Update Maintenance Staff" : "Add New Maintenance Staff"; ?></h3>

                <form method="POST">
                    <?php if ($edit_mode) { ?>
                        <input type="hidden" name="maintenance_id" value="<?php echo $edit_maintenance["maintenance_id"]; ?>">
                    <?php } ?>

                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" value="<?php echo $edit_mode ? htmlspecialchars($edit_maintenance["full_name"]) : ""; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo $edit_mode ? htmlspecialchars($edit_maintenance["email"]) : ""; ?>" required>
                    </div>

                    <?php if (!$edit_mode) { ?>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="text" name="password" required>
                        </div>
                    <?php } ?>

                    <div class="form-group">
                        <label>Specialization</label>
                        <input type="text" name="specialization" value="<?php echo $edit_mode ? htmlspecialchars($edit_maintenance["specialization"]) : ""; ?>" required>
                    </div>

                    <?php if ($edit_mode) { ?>
                        <button type="submit" name="update_maintenance" class="primary-btn">Update Maintenance Staff</button>
                        <a href="manage_maintenance.php" class="secondary-btn">Cancel</a>
                    <?php } else { ?>
                        <button type="submit" name="add_maintenance" class="primary-btn">Add Maintenance Staff</button>
                    <?php } ?>
                </form>
            </div>

            <div class="manage-table-card">
                <h3>Maintenance Staff List</h3>

                <?php if (count($maintenance_list) > 0) { ?>
                    <div class="table-wrapper">
                        <table class="reports-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Specialization</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($maintenance_list as $maintenance) { ?>
                                    <tr>
                                        <td><?php echo $maintenance["maintenance_id"]; ?></td>
                                        <td><?php echo htmlspecialchars($maintenance["full_name"]); ?></td>
                                        <td><?php echo htmlspecialchars($maintenance["email"]); ?></td>
                                        <td><?php echo htmlspecialchars($maintenance["specialization"]); ?></td>
                                        <td><?php echo $maintenance["created_at"]; ?></td>
                                        <td>
                                            <a href="manage_maintenance.php?edit=<?php echo $maintenance["maintenance_id"]; ?>" class="table-action-btn">Edit</a>
                                            <a href="manage_maintenance.php?delete=<?php echo $maintenance["maintenance_id"]; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this maintenance staff member?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <div class="empty-box small-empty">
                        <p>No maintenance staff found.</p>
                    </div>
                <?php } ?>
            </div>

        </div>

    </div>
</section>

</body>
</html>