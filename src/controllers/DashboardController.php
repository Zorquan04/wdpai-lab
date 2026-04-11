<?php

require_once 'AppController.php';

class DashboardController extends AppController {
    // Main dashboard view handler
    public function index($id = null) {
        // Temporary mock data (will be replaced with DB data later)
        $data = [
            "user" => "Kacper",
            "role" => "admin",
            "selectedId" => $id
        ];

        // Render dashboard view with passed variables
        return $this->render("index", [
            "title" => "Dashboard",
            "data" => $data
        ]);
    }
}