<?php

require_once 'Repository.php';
require_once __DIR__ . '/../models/User.php';

// Handles all database operations related to the User entity
class UserRepository extends Repository {
    // Retrieves a user from the database by their email address
    public function getUser(string $email): ?User {
        // Prepare statement to prevent SQL Injection
        $stmt = $this->database->prepare('
            SELECT * FROM users WHERE email = :email
        ');
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        // Fetch the result as an associative array
        $user = $stmt->fetch();

        // Return null if user is not found
        if (!$user) {
            return null;
        }

        // Map database array to User object
        return new User(
            $user['email'],
            $user['password_hash'],
            $user['username'],
            $user['role'],
            $user['id']
        );
    }

    // Retrieves user profile details (name, bio, avatar) from user_details table
    public function getUserDetails(int $userId): ?array {
        $stmt = $this->database->prepare('
            SELECT * FROM user_details WHERE user_id = :userId
        ');
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $details = $stmt->fetch(PDO::FETCH_ASSOC);

        // If the user has not yet completed their profile, we return null
        if (!$details) {
            return null;
        }

        return $details;
    }

    public function getAllUsers(int $currentUserId): array {
        $stmt = $this->database->prepare('
            SELECT id, username, email, role, created_at 
            FROM users 
            WHERE id != :currentId
            ORDER BY created_at DESC
        ');
        $stmt->bindParam(':currentId', $currentUserId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Retrieves a user from the database by their ID
    public function getUserById(int $id): ?User {
        $stmt = $this->database->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return null;
        }

        return new User(
            $user['email'],
            $user['password_hash'],
            $user['username'],
            $user['role'],
            $user['id']
        );
    }

    // Inserts a new user into the database and creates an empty profile using Transactions
    public function addUser(User $user): void {
        try {
            // The database is now "waiting" for final approval
            $this->database->beginTransaction();

            // Add the user to the main table by using RETURNING id - immediately returns the ID of the newly added record
            $stmtUser = $this->database->prepare('
                INSERT INTO users (username, email, password_hash, role)
                VALUES (?, ?, ?, ?) RETURNING id
            ');

            $stmtUser->execute([
                $user->getUsername(),
                $user->getEmail(),
                $user->getPassword(),
                $user->getRole()
            ]);

            // We extract the ID of the created user
            $userId = $stmtUser->fetchColumn();

            // We create a related, empty profile for it in user_details
            $stmtDetails = $this->database->prepare('
                INSERT INTO user_details (user_id) VALUES (?)
            ');
            $stmtDetails->execute([$userId]);

            // If we've reached this point, it means both queries were successful! We're committing the changes to the database
            $this->database->commit();

        } catch (PDOException $e) {
            // We are rolling back all changes from this transaction to maintain 3NF consistency
            $this->database->rollBack();
            
            // We throw an error above so that our SecurityController can display a message in the form
            throw $e; 
        }
    }

    public function updateUserRole(int $id, string $role): void {
        $stmt = $this->database->prepare('UPDATE users SET role = :role WHERE id = :id');
        $stmt->execute(['role' => $role, 'id' => $id]);
    }

    // Updates full user profile (users + user_details tables)
    public function updateFullProfile(int $userId, array $data): void {
        $this->database->beginTransaction();

        try {
            // Updating the users table (username, email)
            $stmtUser = $this->database->prepare('
                UPDATE users SET username = :username, email = :email WHERE id = :id
            ');
            $stmtUser->execute([
                'username' => $data['username'],
                'email' => $data['email'],
                'id' => $userId
            ]);

            // If a new password has been entered, we update it
            if (!empty($data['password'])) {
                $hash = password_hash($data['password'], PASSWORD_BCRYPT);
                $stmtPwd = $this->database->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
                $stmtPwd->execute(['hash' => $hash, 'id' => $userId]);
            }

            // Updating the user_details table (name, surname, bio, avatar)
            $stmtDetails = $this->database->prepare('
                UPDATE user_details 
                SET name = :name, surname = :surname, bio = :bio, avatar = :avatar 
                WHERE user_id = :id
            ');
            $stmtDetails->execute([
                'name' => $data['name'],
                'surname' => $data['surname'],
                'bio' => $data['bio'],
                'avatar' => $data['avatar'] ?? 'default.png',
                'id' => $userId
            ]);

            $this->database->commit();
        } catch (PDOException $e) {
            $this->database->rollBack();
            throw $e;
        }
    }

    public function deleteUser(int $id): void {
        $stmt = $this->database->prepare('DELETE FROM users WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}