<?php

require_once 'AppController.php';

class SecurityController extends AppController {
    // Handle user login logic
    public function login() {
        // Start session to store user data
        session_start();

        // If login form was submitted
        if ($this->isPost()) {
            // Temporary mock authentication (to be replaced with DB check)
            $_SESSION['user'] = [
                "id" => 1,
                "login" => "admin",
                "role" => "ADMIN"
            ];

            // Redirect after successful login
            header("Location: /dashboard");
            exit();
        }

        // Render login page
        return $this->render("login");
    }
}