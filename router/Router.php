<?php
class Router {
    public function dispatch(): void {
        $action = $_GET['action'] ?? '';

        switch ($action) {

            case 'login':
                require_once 'Controllers/AuthController.php';
                $controller = new AuthController();
                $controller->login();
                break;

            case 'seconnecter':
                require_once 'views/auth/SeConnecter.php';
                break;

            case 'admin':
                require_once 'Controllers/CompteController.php';
                $controller = new CompteController();
                $controller->admin();
                break;

            case 'contact':
                require_once 'views/pages/NousContacter.php';
                break;

            case 'postuler':
                require_once 'views/pages/Postuler.php';
                break;

            case 'creercompte':
                require_once 'views/auth/CreerUnCompte.php';
                break;

            case 'mentions':
                require_once 'views/pages/MentionsLegales.php';
                break;

            case 'motdepasseoublie':
                require_once 'views/auth/MotDePasseOublier.php';
                break;

            // Page d'accueil par défaut
            default:
                require_once 'views/pages/accueil.php';
                break;
        }
    }
}