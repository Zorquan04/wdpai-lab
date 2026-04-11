<?php

require_once 'Repository.php';
require_once __DIR__ . '/../models/Game.php';

// Handles all database operations related to the Game entity
class GameRepository extends Repository {
    // Retrieves all games from the database
    public function getGames(): array {
        $result = [];
        
        $stmt = $this->database->prepare('SELECT * FROM games ORDER BY created_at DESC');
        $stmt->execute();
        $games = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($games as $game) {
            $result[] = new Game(
                $game['id'],
                $game['title'],
                $game['description'],
                $game['category'],
                (float)$game['price'],
                $game['graphics'],
                (float)$game['average_rating']
            );
        }

        return $result;
    }
}