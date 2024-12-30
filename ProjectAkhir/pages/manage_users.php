<?php
include_once(__DIR__ . '/../Model/userModel.php');
$userModel = new UserModel();
$users = $userModel->getData();
?>

<div class="content-wrapper">
    <div class="container-fluid">
        <h1 class="mt-4">User Management</h1>
        <div class="card mt-4">
            <div class="card-body">
                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#addUserModal">
                        + Add New User
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id_user']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['role']); ?></td>
                                <td>
                                    <button class="btn btn-warning btn-sm" onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                        Edit
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="deleteUser(<?php echo $user['id_user']; ?>)">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addUserForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select class="form-control" name="role" required>
                            <option value="admin">Admin</option>
                            <option value="dosen">Dosen</option>
                            <option value="mahasiswa">Mahasiswa</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editUserForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id_user" id="edit_id_user">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" class="form-control" name="username" id="edit_username" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" class="form-control" name="password" placeholder="Leave blank to keep current password">
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select class="form-control" name="role" id="edit_role" required>
                            <option value="admin">Admin</option>
                            <option value="dosen">Dosen</option>
                            <option value="mahasiswa">Mahasiswa</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Add User Form Submit
    $('#addUserForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'action/userAction.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                const result = JSON.parse(response);
                Swal.fire({
                    icon: result.status,
                    title: result.status === 'success' ? 'Success' : 'Error',
                    text: result.message
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.reload();
                    }
                });
            }
        });
    });

    // Edit User Form Submit
    $('#editUserForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'action/userAction.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                const result = JSON.parse(response);
                Swal.fire({
                    icon: result.status,
                    title: result.status === 'success' ? 'Success' : 'Error',
                    text: result.message
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.reload();
                    }
                });
            }
        });
    });

    // Initialize DataTable
    $('table').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true
    });
});

function editUser(user) {
    $('#edit_id_user').val(user.id_user);
    $('#edit_username').val(user.username);
    $('#edit_role').val(user.role);
    $('#editUserModal').modal('show');
}

function deleteUser(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'action/userAction.php',
                type: 'POST',
                data: {
                    action: 'delete',
                    id_user: id
                },
                success: function(response) {
                    const result = JSON.parse(response);
                    Swal.fire({
                        icon: result.status,
                        title: result.status === 'success' ? 'Success' : 'Error',
                        text: result.message
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload();
                        }
                    });
                }
            });
        }
    });
}
</script>

<style>
    .content-wrapper {
        text-align: left;
    }
    .table-responsive {
        text-align: left;
    }
    .table-controls {
        text-align: left;
    }
    .entries-control {
        text-align: left;
    }
    .search-control {
        text-align: left;
    }
    .table-footer {
        text-align: left;
    }
    .entries-info {
        text-align: left;
    }
    .pagination {
        text-align: left;
    }
</style>
