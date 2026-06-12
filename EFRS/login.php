<?php
session_start();

require_once "db-connection.php";

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $user_type = $_POST["user_type"];

    if (empty($email) || empty($password)) {
        $error_message = "Please fill in all fields.";
    } else {

        try {

            if ($user_type == "employee") {
                $stmt = $conn->prepare("SELECT * FROM employees WHERE email = ? AND password = ?");
                $stmt->execute([$email, $password]);
                $user = $stmt->fetch();

                if ($user) {
                    $_SESSION["employee_id"] = $user["employee_id"];
                    $_SESSION["user_type"] = "employee";

                    header("Location: employee/dashboard.php");
                    exit();
                }

            } elseif ($user_type == "maintenance") {
                $stmt = $conn->prepare("SELECT * FROM maintenance_staff WHERE email = ? AND password = ?");
                $stmt->execute([$email, $password]);
                $user = $stmt->fetch();

                if ($user) {
                    $_SESSION["maintenance_id"] = $user["maintenance_id"];
                    $_SESSION["user_type"] = "maintenance";

                    header("Location: maintenance/dashboard.php");
                    exit();
                }

            } elseif ($user_type == "admin") {
                $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ? AND password = ?");
                $stmt->execute([$email, $password]);
                $user = $stmt->fetch();

                if ($user) {
                    $_SESSION["admin_id"] = $user["admin_id"];
                    $_SESSION["user_type"] = "admin";

                    header("Location: admin/dashboard.php");
                    exit();
                }
            }

            $error_message = "Invalid email or password.";

        } catch (PDOException $e) {
            $error_message = "Login error. Try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EFRS</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    
    <header class="main-header">
        <div class="container navbar">
            <div class="logo-box">
                <img src="logo.png" class="site-logo">
                <div class="logo-text">
                    <h1>EFRS</h1>
                    <p>Electronic Fault Reporting System</p>
                </div>
            </div>

            <nav class="nav-links">
                <a href="index.php">Home</a>
                <a href="employee-signup.php">Sign Up</a>
            </nav>
        </div>
    </header>

    <section class="auth-section">
        <div class="auth-container">

            <div class="auth-left">
                <img src="logo.png" class="auth-logo">
                <h2>Welcome Back</h2>
                <p>
                    Login to access the system and manage fault reports efficiently.
                </p>
            </div>

            <div class="auth-right">
                <form method="POST" class="auth-form">
                    <h2>Login</h2>

                    <?php if (!empty($error_message)) { ?>
                        <div class="error-message"><?php echo $error_message; ?></div>
                    <?php } ?>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>

                    <div class="form-group">
                        <label>User Type</label>
                        <select name="user_type" required>
                            <option value="employee">Employee</option>
                            <option value="maintenance">Maintenance Staff</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <button type="submit" class="auth-btn">Login</button>

                    <p class="auth-link-text">
                        Don't have an account?
                        <a href="employee-signup.php">Sign up</a>
                    </p>
                </form>
            </div>

        </div>
    </section>

</body>
</html>