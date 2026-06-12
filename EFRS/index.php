<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EFRS - Electronic Fault Reporting System</title>
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
                <a href="index.php" class="active">Home</a>
                <a href="#about">About</a>
                <a href="#services">Services</a>
                <a href="#how-it-works">How It Works</a>
                <a href="login.php" class="login-btn">Login</a>
            </nav>
        </div>
    </header>

    <section class="hero-section">
        <div class="container hero-content">
            <div class="hero-text">
                <h2>Smart Fault Reporting for a Better University Environment</h2>
                <p>
                    The Electronic Fault Reporting System (EFRS) helps employees report faults inside university facilities
                    quickly and accurately. It improves communication with maintenance teams, reduces response time,
                    and supports efficient management of university resources.
                </p>

                <div class="hero-buttons">
                    <a href="login.php" class="primary-btn">Get Started</a>
                    <a href="#about" class="secondary-btn">Learn More</a>
                </div>
            </div>

            <div class="hero-image-card">
                <img src="logo.png" alt="EFRS Logo Preview" class="hero-logo">
            </div>
        </div>
    </section>

    <section class="about-section" id="about">
        <div class="container">
            <div class="section-title">
                <h2>About the System</h2>
                <p>
                    EFRS is designed to simplify the process of reporting and managing faults inside university facilities,
                    including classrooms, laboratories, restrooms, and technical equipment.
                </p>
            </div>

            <div class="about-grid">
                <div class="about-card">
                    <h3>Easy Reporting</h3>
                    <p>
                        Employees can submit a fault report through a simple interface by entering the fault type,
                        location, description, and optional image.
                    </p>
                </div>

                <div class="about-card">
                    <h3>Fast Maintenance Follow-up</h3>
                    <p>
                        Maintenance staff can review assigned reports, update statuses, and add notes to ensure
                        organized and effective fault handling.
                    </p>
                </div>

                <div class="about-card">
                    <h3>Administrative Control</h3>
                    <p>
                        Administrators can monitor all reports, assign priorities, manage users, and analyze
                        maintenance performance through a structured dashboard.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="services-section" id="services">
        <div class="container">
            <div class="section-title">
                <h2>System Services</h2>
                <p>Key features provided by the Electronic Fault Reporting System.</p>
            </div>

            <div class="services-grid">
                <div class="service-box">
                    <h3>Submit Fault Reports</h3>
                    <p>Report faults in university facilities with accurate details and optional image attachments.</p>
                </div>

                <div class="service-box">
                    <h3>Track Report Status</h3>
                    <p>Follow the progress of submitted reports from pending to resolved or closed.</p>
                </div>

                <div class="service-box">
                    <h3>Manage Priorities</h3>
                    <p>Administrators can assign priorities to urgent reports for faster action.</p>
                </div>

                <div class="service-box">
                    <h3>Maintenance Notes</h3>
                    <p>Maintenance staff can document their actions and keep clear repair records.</p>
                </div>

                <div class="service-box">
                    <h3>Organized Dashboard</h3>
                    <p>Each user role has a dedicated dashboard to perform tasks efficiently.</p>
                </div>

                <div class="service-box">
                    <h3>Statistics and Monitoring</h3>
                    <p>Generate useful reports and statistics to improve maintenance quality and response time.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="steps-section" id="how-it-works">
        <div class="container">
            <div class="section-title">
                <h2>How It Works</h2>
                <p>A simple process that helps the university handle faults efficiently.</p>
            </div>

            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">01</div>
                    <h3>Login</h3>
                    <p>The employee logs into the system using their account credentials.</p>
                </div>

                <div class="step-card">
                    <div class="step-number">02</div>
                    <h3>Submit Report</h3>
                    <p>The employee enters the fault details, location, and image if needed.</p>
                </div>

                <div class="step-card">
                    <div class="step-number">03</div>
                    <h3>Maintenance Review</h3>
                    <p>The maintenance team receives the report, reviews it, and updates the status.</p>
                </div>

                <div class="step-card">
                    <div class="step-number">04</div>
                    <h3>Track and Resolve</h3>
                    <p>The report is monitored until the issue is resolved and properly documented.</p>
                </div>
            </div>
        </div>
    </section>

    
    <section class="cta-section">
        <div class="container cta-box">
            <h2>Start Using EFRS Today</h2>
            <p>
                Access the system and help improve the quality, safety, and efficiency of university facilities.
            </p>
            <a href="login.php" class="primary-btn">Go to Login</a>
        </div>
    </section>

    
    <footer class="main-footer">
        <div class="container footer-content">
            <p>&copy; <?php echo date("Y"); ?> EFRS - Electronic Fault Reporting System. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>