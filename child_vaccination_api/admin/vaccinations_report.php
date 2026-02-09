<?php
include 'db.php';

$result = $conn->query("
    SELECT 
        ch.name AS child_name, 
        cv.vaccine_name, 
        cv.vaccine_date,
        IF(cv.is_vaccinated=1,'Done','Pending') AS status,
        cv.vaccined_by, 
        cv.vaccinated_date
    FROM vaccinations cv
    LEFT JOIN children ch ON cv.child_id = ch.id
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vaccinations Report | Child Vaccination</title>
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
        .badge-done {
            background:#6f3cc3;
            color:#fff;
        }
        .badge-pending {
            background:#f5c542;
            color:#3b2b00;
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
        <h1>Vaccinations Report</h1>
        <a href="index.php" class="btn btn-outline-light btn-sm btn-outline-light-custom">Back to Dashboard</a>
    </div>
</div>

<div class="container my-4">

    <div class="card mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="card-title mb-1">Vaccination Records</h5>
                <p class="text-muted small mb-0">
                    Monitor each child’s vaccines, status and who administered the dose.
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <input type="text" id="searchInput" class="form-control form-control-sm search-input" placeholder="Search by child name...">
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0" id="vaccinesTable">
                <thead>
                <tr>
                    <th>Child Name</th>
                    <th>Vaccine</th>
                    <th>Scheduled Date</th>
                    <th>Status</th>
                    <th>Vaccinated By</th>
                    <th>Vaccinated Date</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['child_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['vaccine_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['vaccine_date']); ?></td>
                        <td>
                            <?php if ($row['status'] === 'Done'): ?>
                                <span class="badge-status badge-done">Done</span>
                            <?php else: ?>
                                <span class="badge-status badge-pending">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['vaccined_by']); ?></td>
                        <td><?php echo htmlspecialchars($row['vaccinated_date']); ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    const searchInput = document.getElementById('searchInput');
    const table = document.getElementById('vaccinesTable');
    const rows = table.getElementsByTagName('tr');

    searchInput.addEventListener('keyup', function () {
        const filter = searchInput.value.toLowerCase();

        for (let i = 1; i < rows.length; i++) {
            const td = rows[i].getElementsByTagName('td')[0]; // Child Name column
            if (td) {
                const txtValue = td.textContent || td.innerText;
                rows[i].style.display = txtValue.toLowerCase().includes(filter) ? '' : 'none';
            }
        }
    });
</script>

</body>
</html>
