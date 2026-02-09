<?php
header("Access-Control-Allow-Origin: http://localhost:5661");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}include './../db_connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name  = $_POST['name'] ?? '';
  $email = $_POST['email'] ?? '';
  $pass  = md5($_POST['password'] ?? '');
  $role  = $_POST['role'] ?? 'parent';

  $stmt = $conn->prepare(
    "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)"
  );
  $stmt->bind_param("ssss", $name, $email, $pass, $role);
  $stmt->execute();

  header("Location: index.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add User</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --primary-purple: #6f42c1;
      --primary-purple-dark: #59339a;
    }

    body {
      background-color: #f5f3ff;
    }

    .navbar {
      background-color: var(--primary-purple);
    }

    .navbar-brand,
    .navbar-nav .nav-link {
      color: #ffffff !important;
    }

    .card {
      border-radius: 1rem;
      box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.05);
    }

    .card-header {
      background-color: #f1e9ff;
      border-bottom: none;
    }

    .btn-primary,
    .btn-success {
      background-color: var(--primary-purple);
      border-color: var(--primary-purple);
    }

    .btn-primary:hover,
    .btn-success:hover {
      background-color: var(--primary-purple-dark);
      border-color: var(--primary-purple-dark);
    }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">Child Vaccination Admin</a>
  </div>
</nav>

<div class="container mb-5">
  <div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">Add User</h5>
        </div>
        <div class="card-body">
          <form method="post" autocomplete="off">
            <div class="mb-3">
              <label class="form-label">Name</label>
              <input
                type="text"
                name="name"
                class="form-control"
                required
              >
            </div>

            <div class="mb-3">
              <label class="form-label">Email</label>
              <input
                type="email"
                name="email"
                class="form-control"
                required
              >
            </div>

            <div class="mb-3">
              <label class="form-label">Password</label>
              <input
                type="password"
                name="password"
                class="form-control"
                required
              >
            </div>

            <div class="mb-3">
              <label class="form-label">Role</label>
              <select name="role" class="form-select" required>
                <option value="parent">Parent</option>
                <option value="doctor">Doctor</option>
                <option value="nurse">Nurse</option>
                <option value="admin">Admin</option>
              </select>
            </div>

            <div class="d-flex justify-content-between">
              <a href="index.php" class="btn btn-secondary">
                Cancel
              </a>
              <button type="submit" class="btn btn-primary">
                Add
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
