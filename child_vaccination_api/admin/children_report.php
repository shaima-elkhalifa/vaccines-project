<?php
header("Access-Control-Allow-Origin: http://localhost:5661");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
include 'db.php';

$result = $conn->query("
    SELECT 
        c.id, 
        c.name AS child_name, 
        c.birth_date, 
        c.gender,
        u.name AS parent_name, 
        d.name AS doctor_name,
        IF(c.child_del=1,'Deleted','Active') AS status
    FROM children c
    LEFT JOIN users u ON c.user_id = u.id
    LEFT JOIN users d ON c.doctor_id = d.id
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Children Report | Child Vaccination</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background:#f4f0ff;
            font-family:"Segoe UI",sans-serif;
        }
        .topbar {
            background: linear-gradient(135deg,#5e2ca5,#a47cf3);
            color:#fff;
            padding:1rem 0;
            box-shadow:0 2px 10px rgba(0,0,0,0.15);
        }
        .topbar h1 { 
            font-size:1.5rem; 
            margin:0; 
            font-weight:600;
        }
        .card {
            border-radius:20px;
            border:none;
            box-shadow:0 6px 18px rgba(0,0,0,0.06);
        }
        .table thead {
            background:#e8ddff;
        }
        .table thead th {
            font-size:0.8rem;
            text-transform:uppercase;
            letter-spacing:0.05em;
        }
        .badge-status {
            padding:0.3rem 0.7rem;
            border-radius:999px;
            font-size:0.75rem;
        }
        .badge-active {
            background:#6f3cc3;
            color:#fff;
        }
        .badge-deleted {
            background:#b0b0b0;
            color:#fff;
        }
        .search-input {
            border-radius:999px;
            border:1px solid #6f3cc3;
        }
        .search-input:focus {
            box-shadow:0 0 0 0.15rem rgba(111,60,195,0.3);
        }
        .btn-outline-light-custom {
            border-radius:20px;
        }
    </style>
</head>
<body>

<div class="topbar">
    <div class="container d-flex justify-content-between align-items-center">
        <h1>Children Report</h1>
        <a href="index.php" class="btn btn-outline-light btn-sm btn-outline-light-custom">Back to Dashboard</a>
    </div>
</div>

<div class="container my-4">
    <div class="card mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="card-title mb-1">Children List</h5>
                <p class="text-muted small mb-0">
                    View all registered children, their parents and assigned doctors.
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <input type="text" id="searchInput" class="form-control form-control-sm search-input" placeholder="Search by child name...">
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0" id="childrenTable">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Child Name</th>
                    <th>Birth Date</th>
                    <th>Gender</th>
                    <th>Parent</th>
                    <th>Doctor</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['child_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['birth_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['gender']); ?></td>
                        <td><?php echo htmlspecialchars($row['parent_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                        <td>
                            <?php if ($row['status'] === 'Active'): ?>
                                <span class="badge-status badge-active">Active</span>
                            <?php else: ?>
                                <span class="badge-status badge-deleted">Deleted</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const searchInput = document.getElementById('searchInput');
    const table = document.getElementById('childrenTable');
    const rows = table.getElementsByTagName('tr');

    searchInput.addEventListener('keyup', function () {
        const filter = searchInput.value.toLowerCase();

        for (let i = 1; i < rows.length; i++) {
            const td = rows[i].getElementsByTagName('td')[1]; // Child Name column
            if (td) {
                const txtValue = td.textContent || td.innerText;
                rows[i].style.display = txtValue.toLowerCase().includes(filter) ? '' : 'none';
            }
        }
    });
</script>

</body>
</html>
