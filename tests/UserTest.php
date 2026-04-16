<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../src/models/User.php';

class UserTest extends TestCase {
    
    public function testUserCreationAndGetters() {
        $user = new User('test@gmail.com', 'hashedpassword123', 'Tester', 'USER', 1);

        $this->assertEquals('test@gmail.com', $user->getEmail());
        $this->assertEquals('Tester', $user->getUsername());
        $this->assertEquals('USER', $user->getRole());
        $this->assertEquals(1, $user->getId());
    }

    public function testUserAdminRoleIsCorrect() {
        $admin = new User('admin@gmail.com', 'pass', 'Admin', 'ADMIN');
        $this->assertTrue($admin->getRole() === 'ADMIN');
    }
}