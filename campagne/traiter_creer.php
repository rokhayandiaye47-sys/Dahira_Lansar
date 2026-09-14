<?php

require_once "../auth/protection_role.php";

verifierRole([1]);

require_once "fonctions.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: index.php");
    exit;
}

if (recupererCampagneActiveGlobale($connexion)) {

    die("Une campagne est déjà active.");
}

$nomEdition = trim($_POST['nom_edition'] ?? '');
$dateDebut = $_POST['date_debut'] ?? date('Y-m-d');

if ($nomEdition === '') {

    $_SESSION['erreurs_campagne'] = ["Le nom de l'édition est obligatoire."];

    header("Location: creer.php");
    exit;
}

$connexion->prepare("
    INSERT INTO campagne_gamou (nom_edition, date_debut, statut)
    VALUES (:nom_edition, :date_debut, 'ACTIVE')
")->execute([
    ':nom_edition' => $nomEdition,
    ':date_debut' => $dateDebut
]);

$idCampagne = $connexion->lastInsertId();

$connexion->prepare("
    INSERT INTO journal_operation (id_utilisateur, type_action, objet)
    VALUES (:id_utilisateur, 'campagne.creation', :objet)
")->execute([
    ':id_utilisateur' => $_SESSION['id_utilisateur'],
    ':objet' => "Campagne « $nomEdition » créée et activée"
]);

$_SESSION['message_campagnes'] = "Campagne créée et activée.";

header("Location: index.php");
exit;