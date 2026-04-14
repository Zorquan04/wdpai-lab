<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/UserRepository.php';

class AdminController extends AppController {
    private UserRepository $userRepository;

    public function __construct() {
        $this->userRepository = new UserRepository();
    }

    public function index() {
        $this->checkAdmin(); // Ensure only admins can access this page
        
        $users = $this->userRepository->getAllUsers($_SESSION['user_id']);
        
        return $this->render("admin", [
            "title" => "Admin Panel - GameNest",
            "users" => $users
        ]);
    }

    public function deleteUser() {
        $this->checkAdmin();
        
        if ($this->isPost() && isset($_POST['user_id'])) {
            $userIdToDelete = (int)$_POST['user_id'];
            
            // Security: Admin cannot delete himself
            if ($userIdToDelete !== $_SESSION['user_id']) {
                $this->userRepository->deleteUser($userIdToDelete);
            }
        }
        header("Location: /admin");
        exit();
    }

    public function changeRole() {
        $this->checkAdmin();
        
        if ($this->isPost() && isset($_POST['user_id']) && isset($_POST['role'])) {
            $userId = (int)$_POST['user_id'];
            $newRole = $_POST['role'];
            
            // Security: Admin cannot change role to himself
            if ($userId !== $_SESSION['user_id'] && in_array($newRole, ['USER', 'ADMIN'])) {
                $this->userRepository->updateUserRole($userId, $newRole);
            }
        }
        header("Location: /admin");
        exit();
    }

    // Displaying the edit form for the selected user
    public function editUser($id = null) {
        $this->checkAdmin();
        
        if (!$id) {
            header("Location: /admin");
            exit();
        }

        // Fetch the data of the selected user from the repository
        $user = $this->userRepository->getUserById((int)$id);
        $userDetails = $this->userRepository->getUserDetails((int)$id);
        
        if (!$user) {
            header("Location: /admin");
            exit();
        }

        // Fetch the list of available avatars
        $avatars = $this->getAvailableAvatars();

        return $this->render("admin_edit", [
            "title" => "Edit User - GameNest",
            "editedUser" => $user,
            "details" => $userDetails,
            "avatars" => $avatars
        ]);
    }

    // Updating user information (almost the same as in Dashboard, but for a specific ID)
    public function updateUser() {
        $this->checkAdmin();
        
        if ($this->isPost() && isset($_POST['user_id'])) {
            $userId = (int)$_POST['user_id'];
            
            $data = [
                'username' => trim($_POST['username'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'password' => $_POST['password'] ?? '', 
                'name' => trim($_POST['name'] ?? ''),
                'surname' => trim($_POST['surname'] ?? ''),
                'bio' => trim($_POST['bio'] ?? ''),
                'avatar' => $_POST['avatar'] ?? 'gaming-console.png'
            ];

            try {
                $this->userRepository->updateFullProfile($userId, $data);
            } catch (Exception $e) {
                // Error handling, e.g. email already in use
            }
        }
        
        header("Location: /admin");
        exit();
    }
}