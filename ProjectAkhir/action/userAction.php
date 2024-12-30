<?php
include_once(__DIR__ . '/../Model/userModel.php');

class UserAction {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action'])) {
                // Validate username length
                if (strlen($_POST['username']) > 50) {
                    return $this->jsonResponse('error', 'Username must not exceed 50 characters!');
                }

                // Validate role
                $allowedRoles = ['admin', 'dosen', 'mahasiswa'];
                if (!in_array($_POST['role'], $allowedRoles)) {
                    return $this->jsonResponse('error', 'Invalid role selected!');
                }

                switch ($_POST['action']) {
                    case 'add':
                        return $this->addUser();
                    case 'edit':
                        return $this->editUser();
                    case 'delete':
                        return $this->deleteUser();
                }
            }
        }
        
        // For GET requests, return all users
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['act']) && $_GET['act'] === 'getUsers') {
            return $this->getUsers();
        }
    }

    private function addUser() {
        // Check if username already exists
        $existingUser = $this->userModel->getSingleDataByKeyword('username', $_POST['username']);
        if ($existingUser) {
            return $this->jsonResponse('error', 'Username already exists!');
        }

        try {
            $this->userModel->insertData([
                'username' => $_POST['username'],
                'password' => $_POST['password'],
                'role' => $_POST['role']
            ]);
            return $this->jsonResponse('success', 'User added successfully!');
        } catch (Exception $e) {
            return $this->jsonResponse('error', 'Failed to add user: ' . $e->getMessage());
        }
    }

    private function editUser() {
        // Check if new username already exists (except for current user)
        $existingUser = $this->userModel->getSingleDataByKeyword('username', $_POST['username']);
        if ($existingUser && $existingUser['id_user'] != $_POST['id_user']) {
            return $this->jsonResponse('error', 'Username already exists!');
        }

        try {
            // Only update password if a new one is provided
            $userData = [
                'username' => $_POST['username'],
                'role' => $_POST['role']
            ];
            
            if (!empty($_POST['password'])) {
                $userData['password'] = $_POST['password'];
            } else {
                // Get current user data to keep the existing password
                $currentUser = $this->userModel->getDataById($_POST['id_user']);
                $userData['password'] = $currentUser['password'];
            }

            $this->userModel->updateData($_POST['id_user'], $userData);
            return $this->jsonResponse('success', 'User updated successfully!');
        } catch (Exception $e) {
            return $this->jsonResponse('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    private function deleteUser() {
        try {
            // Prevent deleting the last admin
            $currentUser = $this->userModel->getDataById($_POST['id_user']);
            if ($currentUser['role'] === 'admin') {
                $allUsers = $this->userModel->getData();
                $adminCount = 0;
                foreach ($allUsers as $user) {
                    if ($user['role'] === 'admin') {
                        $adminCount++;
                    }
                }
                if ($adminCount <= 1) {
                    return $this->jsonResponse('error', 'Cannot delete the last admin user!');
                }
            }

            $this->userModel->deleteData($_POST['id_user']);
            return $this->jsonResponse('success', 'User deleted successfully!');
        } catch (Exception $e) {
            return $this->jsonResponse('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }

    private function getUsers() {
        try {
            $users = $this->userModel->getData();
            return $this->jsonResponse('success', 'Users retrieved successfully!', $users);
        } catch (Exception $e) {
            return $this->jsonResponse('error', 'Failed to get users: ' . $e->getMessage());
        }
    }

    private function jsonResponse($status, $message, $data = null) {
        header('Content-Type: application/json');
        $response = [
            'status' => $status,
            'message' => $message
        ];
        if ($data !== null) {
            $response['data'] = $data;
        }
        return json_encode($response);
    }
}

// Initialize and handle request
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $userAction = new UserAction();
    echo $userAction->handleRequest();
}
?>
