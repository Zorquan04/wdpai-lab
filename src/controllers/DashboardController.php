<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/UserRepository.php';

class DashboardController extends AppController {
    private UserRepository $userRepository;

    public function __construct() {
        $this->userRepository = new UserRepository();
    }

    public function index() {
        $this->checkAuth();
        $userId = $_SESSION['user_id'];
        
        $userDetails = $this->userRepository->getUserDetails($userId);
        $createdAt = $this->userRepository->getUserRegistrationDate($userId);
        
        // Clean retrieval of user data from the Repository
        $mainUser = $this->userRepository->getUserById($userId);

        // We read the available avatars from the folder
        $avatars = $this->getAvailableAvatars();

        return $this->render("dashboard", [
            "title" => "Profile - GameNest",
            "username" => $mainUser->getUsername(),
            "email" => $mainUser->getEmail(),
            "role" => $_SESSION['user_role'],
            "details" => $userDetails,
            'createdAt' => $createdAt,
            "avatars" => $avatars
        ]);
    }

    public function updateProfile() {
        $this->checkAuth();
        
        if ($this->isPost()) {
            // 400: Does the request really have a username and email?
            if (!isset($_POST['username']) || !isset($_POST['email'])) {
                $this->abort(400);
            }

            $userId = $_SESSION['user_id'];
            
            $data = [
                'username' => trim($_POST['username'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'password' => $_POST['password'] ?? '', 
                'name' => trim($_POST['name'] ?? ''),
                'surname' => trim($_POST['surname'] ?? ''),
                'bio' => trim($_POST['bio'] ?? ''),
                'avatar' => $_POST['avatar'] ?? 'gaming-console.jpg' // updated default avatar file
            ];

            try {
                $this->userRepository->updateFullProfile($userId, $data);
                $_SESSION['user_avatar'] = $_POST['avatar'];
                $_SESSION['username'] = $data['username']; 
                $_SESSION['success_message'] = "Profile updated successfully!";
            } catch (PDOException $e) {
                // Code 23505 indicates a duplicate value (UNIQUE constraint)
                if ($e->getCode() == 23505) {
                    $_SESSION['error_message'] = "This email or username is already taken!";
                } else {
                    $_SESSION['error_message'] = "Failed to update profile. Please try again.";
                }
            }
        }
        
        header("Location: /dashboard");
        exit();
    }

    public function deleteAccount() {
        $this->checkAuth(); // Security: only for logged in users

        if (!$this->isPost()) {
            header("Location: /dashboard");
            exit();
        }

        $password = $_POST['password'] ?? '';
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'] ?? 'USER';

        if ($userRole === 'ADMIN') {
            $_SESSION['error_message'] = "Administrators cannot delete their own accounts for security reasons.";
            header("Location: /dashboard");
            exit();
        }

        // We retrieve user data from the database to check the password
        $user = $this->userRepository->getUserById($userId);

        if (!$user || !password_verify($password, $user->getPassword())) {
            $_SESSION['error_message'] = "Incorrect password. Account deletion canceled.";
            header("Location: /dashboard");
            exit();
        }

        try {
            // We delete the user (cascading in SQL will handle the rest of the tables)
            $this->userRepository->deleteUser($userId);
            
            // We destroy the session and log the user out
            session_unset();
            session_destroy();

            session_start();
            $_SESSION['success_message'] = "Your account has been successfully deleted. We are sad to see you go!";

            // We redirect to the main page with a message
            header("Location: /?message=deleted");
            exit();
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Server error. Could not delete account.";
            header("Location: /dashboard");
            exit();
        }
    }
}