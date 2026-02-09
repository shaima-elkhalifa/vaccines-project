<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

include 'db.php';

// Total users
$total_users = $conn->query("
    SELECT COUNT(*) AS total_users 
    FROM users 
    WHERE user_del = 0
")->fetch_assoc()['total_users'];

// Total children
$total_children = $conn->query("
    SELECT COUNT(*) AS total_children 
    FROM children 
    WHERE child_del = 0
")->fetch_assoc()['total_children'];

// Total vaccinated doses
$total_vaccinated = $conn->query("
    SELECT COUNT(*) AS total_vaccinated 
    FROM vaccinations 
    WHERE is_vaccinated = 1
")->fetch_assoc()['total_vaccinated'];

// Upcoming vaccinations
$total_upcoming = $conn->query("
    SELECT COUNT(*) AS total_upcoming 
    FROM vaccinations 
    WHERE vaccine_date >= CURDATE() 
      AND is_vaccinated = 0
")->fetch_assoc()['total_upcoming'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Child Vaccination</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background:#f4f0ff;
            font-family:"Segoe UI",sans-serif;
        }
        .topbar {
            background:linear-gradient(135deg,#5e2ca5,#a47cf3);
            color:#fff;
            padding:1.2rem 0;
            box-shadow:0 2px 10px rgba(0,0,0,0.15);
        }
        .topbar h1 {
            font-size:1.8rem;
            font-weight:600;
            margin:0;
        }
        .card-stat {
            border:none;
            border-radius:15px;
            box-shadow:0 6px 18px rgba(0,0,0,0.08);
            color:#fff;
            transition:0.3s;
        }
        .card-stat:hover {
            transform:translateY(-4px);
        }
        .card-purple-1 { background:#6f3cc3; }
        .card-purple-2 { background:#8754d8; }
        .card-purple-3 { background:#a56df0; }
        .card-purple-4 { background:#c29bf7; }

        .small-text {
            font-size:0.85rem;
            opacity:0.9;
        }
        .panel-card {
            border-radius:20px;
            border:none;
            box-shadow:0 6px 18px rgba(0,0,0,0.06);
        }
        .btn-outline-purple {
            border:2px solid #6f3cc3;
            color:#6f3cc3;
            border-radius:20px;
            font-weight:500;
        }
        .btn-outline-purple:hover {
            background:#6f3cc3;
            color:#fff;
        }
        .quick-btn {
            padding:0.6rem 1.4rem;
        }
    </style>
</head>
<body>

<div class="topbar">
    <div class="container d-flex justify-content-between align-items-center">
        <h1>Child Vaccination – Admin Panel</h1>
        <span class="small-text text-white">Welcome, Admin</span>
    </div>
</div>

<div class="container my-4">

    <div class="row g-3 mb-4">

        <div class="col-md-3 col-sm-6">
            <div class="card card-stat card-purple-1">
                <div class="card-body">
                    <h6 class="text-uppercase small mb-1">Total Users</h6>
                    <h2><?php echo $total_users; ?></h2>
                    <span class="small-text text-white">Parents, Doctors, Nurses</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card card-stat card-purple-2">
                <div class="card-body">
                    <h6 class="text-uppercase small mb-1">Total Children</h6>
                    <h2><?php echo $total_children; ?></h2>
                    <span class="small-text text-white">Registered children</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card card-stat card-purple-3">
                <div class="card-body">
                    <h6 class="text-uppercase small mb-1">Vaccinated Doses</h6>
                    <h2><?php echo $total_vaccinated; ?></h2>
                    <span class="small-text text-white">Completed doses</span>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card card-stat card-purple-4">
                <div class="card-body">
                    <h6 class="text-uppercase small mb-1">Upcoming Vaccines</h6>
                    <h2><?php echo $total_upcoming; ?></h2>
                    <span class="small-text text-white">Today & later</span>
                </div>
            </div>
        </div>

    </div>

    <div class="card panel-card mb-4">
        <div class="card-body">
            <h5 class="mb-2">Quick Actions</h5>
            <p class="small-text mb-3">
                Use the buttons below to manage users, children and vaccination reports.
            </p>
            <div class="d-flex flex-wrap gap-2">
                <a href="admin_dashboard.php" class="btn btn-outline-purple quick-btn">Users Control</a>
                <a href="dashboard.php" class="btn btn-outline-purple quick-btn">Users Report</a>
                <a href="children_report.php" class="btn btn-outline-purple quick-btn">Children Report</a>
                <a href="vaccinations_report.php" class="btn btn-outline-purple quick-btn">Vaccinations Report</a>
            </div>
        </div>
    </div>

</div>

</body>
</html>
