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
        $admin = new User('admin@gmail.com', 'pass', 'Admin', 'ADMIN', null);
        $this->assertTrue($admin->getRole() === 'ADMIN');
    }

    public function testNewUserHasNullIdBeforeSavingToDatabase() {
        // Simulation of creating a new user in the system (no ID transferred from the database)
        $newUser = new User('newuser@gmail.com', 'somehash', 'Newbie', 'USER', null);
        
        // Expecting that the ID will be null, allowing the repository to use INSERT instead of UPDATE
        $this->assertNull($newUser->getId());
    }

    public function testGetPasswordReturnsCorrectHash() {
        $fakeHash = password_hash('secret123', PASSWORD_BCRYPT);
        $user = new User('secure@gmail.com', $fakeHash, 'SecureUser', 'USER', 2);
        
        $this->assertEquals($fakeHash, $user->getPassword());
    }
}