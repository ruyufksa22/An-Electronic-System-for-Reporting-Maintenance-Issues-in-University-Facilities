<?php
session_start();

if (!isset($_SESSION["employee_id"])) {
    header("Location: ../login.php");
    exit();
}

require_once "../db-connection.php";

$employee_id = $_SESSION["employee_id"];
$message = "";
$error_message = "";

$stmt = $conn->prepare("SELECT full_name FROM employees WHERE employee_id = ?");
$stmt->execute([$employee_id]);
$user = $stmt->fetch();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $fault_type = trim($_POST["fault_type"]);
    $location = trim($_POST["location"]);
    $description = trim($_POST["description"]);
    $image_path = "";

    if (empty($title) || empty($fault_type) || empty($location) || empty($description)) {
        $error_message = "Please fill in all required fields.";
    } else {

        if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
            $allowed_types = ["jpg", "jpeg", "png", "gif"];
            $file_name = $_FILES["image"]["name"];
            $file_tmp = $_FILES["image"]["tmp_name"];
            $file_size = $_FILES["image"]["size"];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if (!in_array($file_ext, $allowed_types)) {
                $error_message = "Only JPG, JPEG, PNG, and GIF files are allowed.";
            } elseif ($file_size > 5 * 1024 * 1024) {
                $error_message = "Image size must be less than 5 MB.";
            } else {
                $new_file_name = time() . "_" . rand(1000, 9999) . "." . $file_ext;
                $upload_path = "../uploads/" . $new_file_name;

                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $image_path = "uploads/" . $new_file_name;
                } else {
                    $error_message = "Failed to upload the image.";
                }
            }
        }

        if (empty($error_message)) {
            try {
                $insert_stmt = $conn->prepare("
                    INSERT INTO fault_reports (employee_id, title, fault_type, location, description, image_path, status, priority)
                    VALUES (?, ?, ?, ?, ?, ?, 'Pending', 'Medium')
                ");
                $insert_stmt->execute([$employee_id, $title, $fault_type, $location, $description, $image_path]);

                $message = "Fault report submitted successfully.";
            } catch (PDOException $e) {
                $error_message = "Something went wrong while submitting the report.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Report - EFRS</title>
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
            <a href="submit_report.php" class="active">Submit Report</a>
            <a href="my_reports.php">My Reports</a>
            <a href="../logout.php" class="login-btn">Logout</a>
        </nav>
    </div>
</header>

<section class="page-section">
    <div class="container">
        <div class="form-page-card">
            <div class="page-title-box">
                <h2>Submit New Fault Report</h2>
                <p>Enter the fault details clearly so the maintenance team can handle the issue efficiently.</p>
            </div>

            <?php if (!empty($message)) { ?>
                <div class="success-message"><?php echo $message; ?></div>
            <?php } ?>

            <?php if (!empty($error_message)) { ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php } ?>

            <form action="" method="POST" enctype="multipart/form-data" class="report-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="title">Report Title</label>
                        <input type="text" name="title" id="title" required>
                    </div>

                    <div class="form-group">
                        <label for="fault_type">Fault Type</label>
                        <select name="fault_type" id="fault_type" required>
                            <option value="">Select Fault Type</option>
                            <option value="Electrical">Electrical</option>
                            <option value="Technical">Technical</option>
                            <option value="Plumbing">Plumbing</option>
                            <option value="Furniture">Furniture</option>
                            <option value="Air Conditioning">Air Conditioning</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" name="location" id="location" placeholder="Example: Building A - Room 204" required>
                </div>

                <div class="form-group">
                    <label for="description">Fault Description</label>
                    <textarea name="description" id="description" rows="6" required></textarea>
                </div>

                <div class="form-group">
                    <label for="image">Upload Image (Optional)</label>
                    <input type="file" name="image" id="image" accept=".jpg,.jpeg,.png,.gif">
                </div>

                <div class="form-buttons">
                    <button type="submit" class="primary-btn">Submit Report</button>
                    <a href="dashboard.php" class="secondary-btn">Back to Dashboard</a>
                </div>
            </form>
        </div>
    </div>
</section>

</body>
</html>