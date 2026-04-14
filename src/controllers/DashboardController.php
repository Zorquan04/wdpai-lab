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
            "avatars" => $avatars
        ]);
    }

    public function updateProfile() {
        $this->checkAuth();
        
        if ($this->isPost()) {
            $userId = $_SESSION['user_id'];
            
            $data = [
                'username' => trim($_POST['username'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'password' => $_POST['password'] ?? '', 
                'name' => trim($_POST['name'] ?? ''),
                'surname' => trim($_POST['surname'] ?? ''),
                'bio' => trim($_POST['bio'] ?? ''),
                'avatar' => $_POST['avatar'] ?? 'gaming-console.png' // updated default avatar file
            ];

            try {
                $this->userRepository->updateFullProfile($userId, $data);
                $_SESSION['username'] = $data['username']; 
            } catch (Exception $e) {
                // Database error
            }
        }
        
        header("Location: /dashboard");
        exit();
    }
}