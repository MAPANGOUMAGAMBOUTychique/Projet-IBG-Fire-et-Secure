<?php
require_once 'Models/UserModel.php';

class AuthController {
    public function login(): void {
        session_start();
        $model = new UserModel();

        // -- Connexion EMPLOYÉ / ADMIN
        if (isset($_POST['user_email'])) {
            $email = trim($_POST['user_email']);
            $mdp   = $_POST['user_mot_de_passe'];

            $user = $model->findByEmail($email); // ✅ $user uniformisé

            if ($user && password_verify($mdp, $user['mot_de_passe_utilisateur'])) {
                $_SESSION['user_id']   = $user['id_utilisateur'];
                $_SESSION['user_role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header('Location: /SiteIBGFireSecure/views/comptes/administrateur.php'); // ✅ typo corrigée
                } elseif ($user['role'] === 'employe') {
                    header('Location: /SiteIBGFireSecure/views/comptes/CompteEmploye.php');
                } else {
                    $this->redirectWithError('Rôle inconnu.');
                }
                exit;

            } else {
                $this->redirectWithError('Email ou mot de passe incorrect.');
            }
        }

        // -- Connexion ENTREPRISE
        elseif (isset($_POST['user_siret'])) {
            $siret = preg_replace('/\s+/', '', $_POST['user_siret']);
            $mdp   = $_POST['user_mot_de_passe_entreprise'];

            $user = $model->findBySiret($siret);

            if ($user && password_verify($mdp, $user['mot_de_passe_utilisateur'])) {
                $_SESSION['user_id']   = $user['id_utilisateur'];
                $_SESSION['user_role'] = 'entreprise';

                header('Location: /SiteIBGFireSecure/views/comptes/CompteEntreprise.php');
                exit;
            } else {
                $this->redirectWithError('SIRET ou mot de passe incorrect.');
            }
        }
    }

    private function redirectWithError(string $msg): void {
        header('Location: /SiteIBGFireSecure/views/auth/SeConnecter.php?error=' . urlencode($msg));
        exit;
    }
}