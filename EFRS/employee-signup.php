<?php
session_start();

require_once "db-connection.php";

$message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $department = trim($_POST["department"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    if (empty($full_name) || empty($email) || empty($department) || empty($password) || empty($confirm_password)) {
        $error_message = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Password and Confirm Password do not match.";
    } elseif (strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters long.";
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

                $message = "Employee account created successfully.";
            }
        } catch (PDOException $e) {
            $error_message = "Something went wrong. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Sign Up - EFRS</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <header class="main-header">
        <div class="container navbar">
            <div class="logo-box">
                <img src="logo.png" alt="EFRS Logo" class="site-logo">
                <div class="logo-text">
                    <h1>EFRS</h1>
                    <p>Electronic Fault Reporting System</p>
                </div>
            </div>

            <nav class="nav-links">
                <a href="index.php">Home</a>
                <a href="login.php">Login</a>
                <a href="employee-signup.php" class="active">Sign Up</a>
            </nav>
        </div>
    </header>

    <section class="auth-section">
        <div class="auth-container">
            <div class="auth-left">
                <img src="logo.png" alt="EFRS Logo" class="auth-logo">
                <h2>Create Employee Account</h2>
                <p>
                    Register as an employee to submit fault reports, track report status,
                    and support better maintenance services inside the university.
                </p>
            </div>

            <div class="auth-right">
                <form action="" method="POST" class="auth-form">
                    <h2>Employee Sign Up</h2>

                    <?php if (!empty($message)) { ?>
                        <div class="success-message"><?php echo $message; ?></div>
                    <?php } ?>

                    <?php if (!empty($error_message)) { ?>
                        <div class="error-message"><?php echo $error_message; ?></div>
                    <?php } ?>

                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" name="full_name" id="full_name" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" name="email" id="email" required>
                    </div>

                    <div class="form-group">
                        <label for="department">Department</label>
                        <input type="text" name="department" id="department" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" required>
                    </div>

                    <button type="submit" class="auth-btn">Create Account</button>

                    <p class="auth-link-text">
                        Already have an account?
                        <a href="login.php">Login here</a>
                    </p>
                </form>
            </div>
        </div>
    </section>

</body>
</html>