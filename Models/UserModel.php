<?php
require_once __DIR__ . '/../config/connexion.php';

class UserModel {

    // Recherche un employé, utilisateur ou admin par email
    public function findByEmail(string $email): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT * FROM utilisateur 
             WHERE email_utilisateur = :email 
             LIMIT 1"
        );
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    // Recherche une entreprise par SIRET
    public function findBySiret(string $siret): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT utilisateur.*, entreprise.*
             FROM entreprise
             JOIN utilisateur ON entreprise.id_utilisateur = utilisateur.id_utilisateur
             WHERE entreprise.siret_entreprise = :siret
             LIMIT 1"
        );
        $stmt->execute([':siret' => $siret]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }
}