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
$edit_employee = null;

if (isset($_POST["add_employee"])) {
    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $department = trim($_POST["department"]);

    if (empty($full_name) || empty($email) || empty($password) || empty($department)) {
        $error_message = "Please fill in all fields.";
    } else {
        try {
            $check_stmt = $conn->prepare("SELECT employee_id FROM employees WHERE email = ?");
            $check_stmt->execute([$email]);

            if ($check_stmt->rowCount() > 0) {
                $error_message = "This email is already registered.";
            } else {
                $insert_stmt = $conn->prepare("
                    INSERT INTO employees (full_name, email, password, department)
                    VALUES (?, ?, ?, ?)
                ");
                $insert_stmt->execute([$full_name, $email, $password, $department]);

                $message = "Employee added successfully.";
            }
        } catch (PDOException $e) {
            $error_message = "Failed to add employee.";
        }
    }
}

if (isset($_GET["edit"])) {
    $edit_id = $_GET["edit"];
    $edit_stmt = $conn->prepare("SELECT * FROM employees WHERE employee_id = ?");
    $edit_stmt->execute([$edit_id]);
    $edit_employee = $edit_stmt->fetch();

    if ($edit_employee) {
        $edit_mode = true;
    }
}

if (isset($_POST["update_employee"])) {
    $employee_id = $_POST["employee_id"];
    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $department = trim($_POST["department"]);

    if (empty($full_name) || empty($email) || empty($department)) {
        $error_message = "Please fill in all fields.";
    } else {
        try {
            $check_stmt = $conn->prepare("SELECT employee_id FROM employees WHERE email = ? AND employee_id != ?");
            $check_stmt->execute([$email, $employee_id]);

            if ($check_stmt->rowCount() > 0) {
                $error_message = "This email is already used by another employee.";
            } else {
                $update_stmt = $conn->prepare("
                    UPDATE employees
                    SET full_name = ?, email = ?, department = ?
                    WHERE employee_id = ?
                ");
                $update_stmt->execute([$full_name, $email, $department, $employee_id]);

                $message = "Employee updated successfully.";
            }
        } catch (PDOException $e) {
            $error_message = "Failed to update employee.";
        }
    }
}


if (isset($_GET["delete"])) {
    $delete_id = $_GET["delete"];

    try {
        $delete_stmt = $conn->prepare("DELETE FROM employees WHERE employee_id = ?");
        $delete_stmt->execute([$delete_id]);

        header("Location: manage_employees.php");
        exit();
    } catch (PDOException $e) {
        $error_message = "Failed to delete employee.";
    }
}


$employees_stmt = $conn->prepare("SELECT * FROM employees ORDER BY created_at DESC");
$employees_stmt->execute();
$employees = $employees_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Employees - EFRS</title>
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
            <a href="manage_employees.php" class="active">Employees</a>
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
            <h2>Manage Employees</h2>
            <p>Add, update, and remove employee accounts.</p>
        </div>

        <?php if (!empty($message)) { ?>
            <div class="success-message"><?php echo $message; ?></div>
        <?php } ?>

        <?php if (!empty($error_message)) { ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php } ?>

        <div class="manage-page-grid">

            <div class="manage-form-card">
                <h3><?php echo $edit_mode ? "Update Employee" : "Add New Employee"; ?></h3>

                <form method="POST">
                    <?php if ($edit_mode) { ?>
                        <input type="hidden" name="employee_id" value="<?php echo $edit_employee["employee_id"]; ?>">
                    <?php } ?>

                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" value="<?php echo $edit_mode ? htmlspecialchars($edit_employee["full_name"]) : ""; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo $edit_mode ? htmlspecialchars($edit_employee["email"]) : ""; ?>" required>
                    </div>

                    <?php if (!$edit_mode) { ?>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="text" name="password" required>
                        </div>
                    <?php } ?>

                    <div class="form-group">
                        <label>Department</label>
                        <input type="text" name="department" value="<?php echo $edit_mode ? htmlspecialchars($edit_employee["department"]) : ""; ?>" required>
                    </div>

                    <?php if ($edit_mode) { ?>
                        <button type="submit" name="update_employee" class="primary-btn">Update Employee</button>
                        <a href="manage_employees.php" class="secondary-btn">Cancel</a>
                    <?php } else { ?>
                        <button type="submit" name="add_employee" class="primary-btn">Add Employee</button>
                    <?php } ?>
                </form>
            </div>

            <div class="manage-table-card">
                <h3>Employees List</h3>

                <?php if (count($employees) > 0) { ?>
                    <div class="table-wrapper">
                        <table class="reports-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Department</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employees as $employee) { ?>
                                    <tr>
                                        <td><?php echo $employee["employee_id"]; ?></td>
                                        <td><?php echo htmlspecialchars($employee["full_name"]); ?></td>
                                        <td><?php echo htmlspecialchars($employee["email"]); ?></td>
                                        <td><?php echo htmlspecialchars($employee["department"]); ?></td>
                                        <td><?php echo $employee["created_at"]; ?></td>
                                        <td>
                                            <a href="manage_employees.php?edit=<?php echo $employee["employee_id"]; ?>" class="table-action-btn">Edit</a>
                                            <a href="manage_employees.php?delete=<?php echo $employee["employee_id"]; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this employee?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <div class="empty-box small-empty">
                        <p>No employees found.</p>
                    </div>
                <?php } ?>
            </div>

        </div>

    </div>
</section>

</body>
</html>