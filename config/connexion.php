<?php

class Database {
    private static $pdo = null;

    public static function getConnection(): PDO{
        if (self::$pdo === null) {
            $host = '127.0.0.1';
            $dbname = 'fire_secure';
            $user = 'fire_secure_user';
            $password = 'Fsulre@ib1n';

            try {
                self::$pdo = new PDO(
                    "mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }  catch (PDOException $e) {
                  die("Erreur de connexion: " . $e->getMessage());
                }
            
        }
        return self::$pdo;
    }
}



?>