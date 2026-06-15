<?php
class Database {
    // Stocke l'unique instance de la classe
    private static $instance = null;
    // Stocke l'objet de connexion PDO
    private $pdo;

    /**
     * Le constructeur est privé pour empêcher l'instanciation directe via "new Database()"
     */
    private function __construct() {
        // --- CONFIGURATION ADAPTÉE À VOTRE NOUVEL UTILISATEUR MYSQL ---
        $host     = 'localhost';
        $dbname   = 'fire_secure'; // <-- /!\ METTEZ LE VRAI NOM DE VOTRE BASE ICI /!\
        $username = 'ibg_admin';                     // L'utilisateur créé avec caching_sha2_password
        $password = 'IbgSecure2026!';                // Le mot de passe associé
        // -------------------------------------------------------------

        try {
            // Création de la connexion PDO avec gestion du charset UTF-8
            $this->pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Active les exceptions en cas d'erreur SQL
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retourne les résultats sous forme de tableau associatif
                PDO::ATTR_EMULATE_PREPARES   => false,                  // Utilise les vraies requêtes préparées pour la sécurité
            ]);
        } catch (PDOException $e) {
            // En cas d'échec de connexion, on arrête le script et on affiche l'erreur
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }

    /**
     * Méthode statique magique qui crée l'instance si elle n'existe pas, 
     * puis retourne l'objet PDO utilisable directement pour vos requêtes.
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance->pdo;
    }
}