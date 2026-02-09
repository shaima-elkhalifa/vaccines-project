<?php
header("Access-Control-Allow-Origin: http://localhost:5661");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

include './../db_connection.php'; // 
// جيب كل المستخدمين
$users = $conn->query("SELECT id, name, email, role FROM users ORDER BY id DESC");
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Dashboard - Child Vaccination</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet">

    <style>
        body {
            background-color: #f5f5f8;
        }
        .navbar-brand {
            font-weight: 600;
        }
        .badge-role {
            background-color: #6f42c1;
            color: #fff;
        }
        .btn-purple {
            background-color: #6f42c1;
            color: #fff;
        }
        .btn-purple:hover {
            background-color: #5a35a1;
            color: #fff;
        }
         .navbar-purple {
        background-color: #6f42c1;   
    }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-purple mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Child Vaccination - Admin</a>
    <div class="d-flex">
      <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container mb-5">
  <div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Users</h5>
      <a href="add_user.php" class="btn btn-purple btn-sm">Add User</a>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
          <thead class="table-light">
          <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 25%;">Name</th>
            <th style="width: 30%;">Email</th>
            <th style="width: 15%;">Role</th>
            <th class="text-end" style="width: 25%;">Actions</th>
          </tr>
          </thead>
          <tbody>
          <?php if ($users && $users->num_rows > 0): ?>
            <?php while ($user = $users->fetch_assoc()): ?>
              <tr>
                <td><?php echo (int)$user['id']; ?></td>
                <td><?php echo htmlspecialchars($user['name']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td>
                  <span class="badge badge-role text-capitalize">
                    <?php echo htmlspecialchars($user['role']); ?>
                  </span>
                </td>
                <td class="text-end">
                  <button
                    type="button"
                    class="btn btn-purple btn-sm me-1 view-children-btn"
                    data-user-id="<?php echo (int)$user['id']; ?>"
                    data-user-name="<?php echo htmlspecialchars($user['name']); ?>"
                    data-user-role="<?php echo htmlspecialchars($user['role']); ?>">
                    Children
                  </button>
                  <a href="edit_user.php?id=<?php echo (int)$user['id']; ?>"
                     class="btn btn-outline-secondary btn-sm me-1">
                    Edit
                  </a>
                  <a href="delete_user.php?id=<?php echo (int)$user['id']; ?>"
                     class="btn btn-outline-danger btn-sm"
                     onclick="return confirm('Are you sure you want to delete this user?');">
                    Delete
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="text-center py-4 text-muted">
                No users found.
              </td>
            </tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Children Modal -->
<div class="modal fade" id="childrenModal" tabindex="-1" aria-labelledby="childrenModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="childrenModalLabel">Children</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <h6 class="mb-3">
          Parent:
          <span id="parentName" class="fw-semibold"></span>
        </h6>
        <ul class="list-group" id="childrenList">
          <li class="list-group-item text-muted">Loading children...</li>
        </ul>
      </div>
      <div class="modal-footer">
        <button
          type="button"
          class="btn btn-outline-danger delete-all-children-btn d-none"
          data-user-id="">
          Delete all children
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          Close
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(function () {
  var childrenModalElement = document.getElementById("childrenModal");
  var childrenModal = new bootstrap.Modal(childrenModalElement);

  // فتح المودال وجلب الأطفال
  $(".view-children-btn").on("click", function () {
    var userId   = $(this).data("user-id");
    var userName = $(this).data("user-name");
    var userRole = $(this).data("user-role");

    $("#parentName").text(userName);
    $("#childrenList").html(
      '<li class="list-group-item text-muted">Loading children...</li>'
    );

    $(".delete-all-children-btn")
      .addClass("d-none")
      .data("user-id", userId);

    $.post(
      "admin_get_children.php",
      { user_id: userId, role: userRole },
      function (response) {
        var items = "";

        if (
          response &&
          response.status === "success" &&
          response.children &&
          response.children.length > 0
        ) {
          response.children.forEach(function (child) {
            items +=
              '<li class="list-group-item d-flex justify-content-between align-items-center">';
            items +=
              "<span>" +
              child.name +
              " - " +
              (child.dob || "") +
              "</span>";
            items +=
              '<button class="btn btn-sm btn-outline-danger delete-child-btn" data-child-id="' +
              child.id +
              '">Delete</button>';
            items += "</li>";
          });

          $(".delete-all-children-btn").removeClass("d-none");
        } else {
          items =
            '<li class="list-group-item text-muted">No children found.</li>';
        }

        $("#childrenList").html(items);
      },
      "json"
    );

    childrenModal.show();
  });

  // حذف طفل واحد
  $(document).on("click", ".delete-child-btn", function () {
    var childId = $(this).data("child-id");
    if (!confirm("Are you sure you want to delete this child?")) {
      return;
    }
    $.post(
      "delete_child.php",
      { child_id: childId },
      function (response) {
        if (response && response.status === "success") {
          $('[data-child-id="' + childId + '"]')
            .closest("li")
            .remove();
          if ($("#childrenList li").length === 0) {
            $("#childrenList").html(
              '<li class="list-group-item text-muted">No children found.</li>'
            );
          }
        } else {
          alert("Failed to delete child.");
        }
      },
      "json"
    );
  });

  // حذف كل الأطفال لمستخدم معيّن
  $(".delete-all-children-btn").on("click", function () {
    var userId = $(this).data("user-id");
    if (
      !confirm(
        "Are you sure you want to delete all children for this user?"
      )
    ) {
      return;
    }
    $.post(
      "delete_all_children.php",
      { user_id: userId },
      function (response) {
        if (response && response.status === "success") {
          $("#childrenList").html(
            '<li class="list-group-item text-muted">No children found.</li>'
          );
          $(".delete-all-children-btn").addClass("d-none");
        } else {
          alert("Failed to delete all children.");
        }
      },
      "json"
    );
  });
});
</script>
</body>
</html>
