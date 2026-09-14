<?php

require_once "../auth/protection_role.php";

verifierRole([1, 2]);

require_once "fonctions.php";
require_once "../membres/fonctions.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: index.php");
    exit;
}

$idGeneration = (int) ($_POST['id_generation'] ?? 0);
$idCampagne = (int) ($_POST['id_campagne'] ?? 0);

assurerAccesGeneration($idGeneration);

$montant = (float) ($_POST['montant'] ?? 0);
$jourEcheance = !empty($_POST['jour_echeance']) ? (int) $_POST['jour_echeance'] : null;

if ($montant <= 0) {

    $_SESSION['erreurs_regle'] = ["Le montant doit être supérieur à zéro."];

    header("Location: regle.php?id_generation=" . $idGeneration);
    exit;
}

$sqlVerif = "
    SELECT id_regle

    FROM regle_cotisation

    WHERE id_generation = :id_generation
      AND id_campagne = :id_campagne

    LIMIT 1
";

$requeteVerif = $connexion->prepare($sqlVerif);

$requeteVerif->execute([
    ':id_generation' => $idGeneration,
    ':id_campagne' => $idCampagne
]);

$regleExistante = $requeteVerif->fetch();

if ($regleExistante) {

    $connexion->prepare("
        UPDATE regle_cotisation

        SET montant = :montant,
            jour_echeance = :jour_echeance

        WHERE id_regle = :id_regle
    ")->execute([
        ':montant' => $montant,
        ':jour_echeance' => $jourEcheance,
        ':id_regle' => $regleExistante['id_regle']
    ]);

} else {

    $connexion->prepare("
        INSERT INTO regle_cotisation (id_generation, id_campagne, montant, jour_echeance)
        VALUES (:id_generation, :id_campagne, :montant, :jour_echeance)
    ")->execute([
        ':id_generation' => $idGeneration,
        ':id_campagne' => $idCampagne,
        ':montant' => $montant,
        ':jour_echeance' => $jourEcheance
    ]);
}

$connexion->prepare("
    INSERT INTO journal_operation (id_utilisateur, type_action, objet)
    VALUES (:id_utilisateur, 'regle_cotisation.modification', :objet)
")->execute([
    ':id_utilisateur' => $_SESSION['id_utilisateur'],
    ':objet' => "Règle de cotisation mise à " . number_format($montant, 0, ',', ' ') . " FCFA"
]);

$_SESSION['message_generation'] = "Règle de cotisation enregistrée.";

header("Location: index.php?id_generation=" . $idGeneration);
exit;