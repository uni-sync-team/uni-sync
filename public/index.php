<?php

// this not for production just to test it out
try {
    $pdo = new PDO(
        "mysql:host=" . getenv('DB_HOST') . ";dbname=" . getenv('DB_NAME') . ";charset=utf8mb4",
        getenv('DB_USER'),
        getenv('DB_PASSWORD')
    );
    echo "UniSync is connected to the database";
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
