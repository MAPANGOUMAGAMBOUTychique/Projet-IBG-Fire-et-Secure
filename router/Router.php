<?php
class Router {
    public function dispatch(): void {
        $action = $_GET['action'] ?? '';

        // On définit la racine des vues de manière absolue pour éviter les erreurs de chemin
        // __DIR__ . '/../' permet de remonter d'un niveau (sortir du dossier router)
        $basePath = __DIR__ . '/../';

        switch ($action) {

            case 'login':
                require_once $basePath . 'Controllers/AuthController.php';
                $controller = new AuthController();
                $controller->login();
                break;

            case 'seconnecter':
                require_once $basePath . 'views/auth/SeConnecter.php';
                break;

            case 'admin':
                require_once $basePath . 'Controllers/CompteController.php';
                $controller = new CompteController();
                $controller->admin();
                break;

            case 'contact':
                require_once $basePath . 'views/pages/NousContacter.php';
                break;

            case 'postuler':
                require_once $basePath . 'views/pages/Postuler.php';
                break;

            case 'creercompte':
                require_once $basePath . 'views/auth/CreerUnCompte.php';
                break;

            case 'mentions':
                require_once $basePath . 'views/pages/MentionsLegales.php';
                break;

            case 'motdepasseoublie':
                require_once $basePath . 'views/auth/MotDePasseOublier.php';
                break;

            // Page d'accueil par défaut
            default:
                // ATTENTION : Vérifiez si le fichier s'appelle index.php ou accueil.php !
                require_once $basePath . 'views/pages/Accueil.php'; 
                break;
        }
    }
}