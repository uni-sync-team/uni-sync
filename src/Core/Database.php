<?php

class Database {
  private static ?PDO $pdo = null;

  public static function connect(): PDO 
  {
    if (self::$pdo === null) {
      $path = sprintf(
        "mysql:host=%s;dbname=%s;charset=utf8mb4",
        getenv('DB_HOST'),
        getenv('DB_NAME'));

      $user = getenv('DB_USER');
      $pass = getenv('DB_PASSWORD');
      try {
        self::$pdo = new PDO($path, $user, $pass, [
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
      } catch (PDOException $e) {
        die("PDO Error: " . $e->getMessage());
      }
    }
    return self::$pdo;
  }

  public static function isUp() {
    if (self::$pdo === null)
      self::connect();

    try {
      self::$pdo->query("SELECT 1"); 
      echo "Database is Up";
    } catch (PDOException) {
      echo "Database is Down. reconnecting </br>
                  try again later";
      self::connect();
    }
    return self::$pdo;
  }

  public static function getUsers(): void {
    $rows = null;
    try {
      $stmt = self::$pdo->query('SELECT * From utilisateur');
      $rows = $stmt->fetchAll();
    } catch (PDOException $e) {
      echo "PDO Error: ". $e->getMessage();
    }
    if ($rows != null) {
      foreach ($rows as $user) {
        echo "{$user['idUser']}: {$user['login']} </br>";
      }
    }
  }
}
