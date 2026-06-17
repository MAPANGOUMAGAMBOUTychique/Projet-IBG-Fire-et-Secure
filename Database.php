<?php
/**
 * Classe Database - Gestionnaire de connexion à la base de données
 * * Cette classe utilise le Design Pattern "Singleton". 
 * Son but est de garantir qu'une seule et unique connexion à la base de données 
 * est ouverte durant toute l'exécution d'une page PHP, optimisant ainsi les performances du serveur.
 */
class Database {
    
    /**
     * @var Database|null Stocke l'unique instance de la classe Database
     */
    private static $instance = null;
    
    /**
     * @var PDO Stocke l'objet de connexion PDO actif
     */
    private $pdo;

    /**
     * Constructeur privé
     * * Le fait de le déclarer en "private" interdit l'utilisation de l'opérateur "new Database()" 
     * en dehors de cette classe. Cela force l'utilisation exclusive de la méthode statique getInstance().
     */
    private function __construct() {
        
        // --- CONFIGURATION DE LA BASE DE DONNÉES ---
        $host     = 'localhost';         // Adresse du serveur de base de données
        $dbname   = 'fire_secure';       // Nom de la base de données MySQL
        $username = 'ibg_admin';         // Utilisateur MySQL configuré avec caching_sha2_password
        $password = 'IbgSecure2026!';    // Mot de passe ultra-sécurisé associé
        // -------------------------------------------

        try {
            /**
             * Instanciation de l'objet PDO avec définition du Charset en UTF-8 pour 
             * éviter les problèmes d'affichage des caractères accentués.
             */
            $this->pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, [
                
                // 1. Gestion des erreurs : Active le lancer d'exceptions en cas d'erreur dans les requêtes SQL
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
                
                // 2. Mode de récupération : Renvoie par défaut les données sous forme de tableau associatif (clé => valeur)
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       
                
                // 3. Sécurité accrue : Désactive la simulation des requêtes préparées pour utiliser les vraies requêtes 
                // préparées de MySQL (Protection native et totale contre les injections SQL).
                PDO::ATTR_EMULATE_PREPARES   => false,                  
            ]);
            
        } catch (PDOException $e) {
            /**
             * En cas d'échec critique de connexion (mauvais mot de passe, serveur down, etc.),
             * on interrompt immédiatement le script et on affiche le message d'erreur.
             */
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }

    /**
     * Point d'accès unique à l'instance PDO (Méthode Singleton)
     * * Cette méthode vérifie si une connexion existe déjà. 
     * - Si non : elle appelle le constructeur privé pour la créer.
     * - Si oui : elle réutilise la connexion existante.
     * * @return PDO L'objet de connexion utilisable pour exécuter les requêtes (query, prepare, execute)
     */
    public static function getInstance() {
        // Si l'instance n'a pas encore été créée, on l'initialise
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        
        // On retourne uniquement l'objet PDO encapsulé à l'intérieur de l'instance
        return self::$instance->pdo;
    }
}