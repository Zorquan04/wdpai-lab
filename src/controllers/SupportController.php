<?php

require_once 'AppController.php';

class SupportController extends AppController {
    public function index() {
        return $this->render('support', [
            'title' => 'GameNest - Support & FAQ'
        ]);
    }
}