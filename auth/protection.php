<?php
// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_utilisateur'])) {

    // Chemin absolu depuis la racine web : à adapter si le dossier du
    // projet n'est pas "dahira" à la racine de htdocs/
    header("Location: /dahira/auth/connexion.php");
    exit;
}
