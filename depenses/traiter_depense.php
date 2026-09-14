<?php

require_once "../auth/protection_role.php";

verifierRole([4]);

require_once "fonctions.php";
require_once "../membres/fonctions.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: index.php");
    exit;
}

$idGeneration = (int) ($_POST['id_generation'] ?? 0);

assurerAccesGeneration($idGeneration);

$montant = (float) ($_POST['montant'] ?? 0);
$motif = trim($_POST['motif'] ?? '');
$justificatif = trim($_POST['justificatif'] ?? '');

if ($montant <= 0 || $motif === '' || $justificatif === '') {

    $_SESSION['message_depense'] = "Merci de vérifier le montant, le motif et le justificatif.";

    header("Location: depense.php?id_generation=" . $idGeneration);
    exit;
}

$caisse = recupererCaisse($connexion, $idGeneration);

if (!$caisse) {

    die("Caisse introuvable pour cette génération.");
}

try {

    $connexion->beginTransaction();

    $connexion->prepare("
        UPDATE caisse SET solde = solde - :montant WHERE id_caisse = :id_caisse
    ")->execute([
        ':montant' => $montant,
        ':id_caisse' => $caisse['id_caisse']
    ]);

    $connexion->prepare("
        INSERT INTO depense (id_caisse, id_utilisateur, montant, motif, justificatif, statut)
        VALUES (:id_caisse, :id_utilisateur, :montant, :motif, :justificatif, 'ACTIVE')
    ")->execute([
        ':id_caisse' => $caisse['id_caisse'],
        ':id_utilisateur' => $_SESSION['id_utilisateur'],
        ':montant' => $montant,
        ':motif' => $motif,
        ':justificatif' => $justificatif
    ]);

    $connexion->prepare("
        INSERT INTO journal_operation (id_utilisateur, type_action, objet)
        VALUES (:id_utilisateur, 'depense.creation', :objet)
    ")->execute([
        ':id_utilisateur' => $_SESSION['id_utilisateur'],
        ':objet' => "Dépense de " . number_format($montant, 0, ',', ' ') . " FCFA — $motif"
    ]);

    $connexion->commit();

} catch (PDOException $e) {

    $connexion->rollBack();

    $_SESSION['message_depense'] = "Une erreur est survenue lors de l'enregistrement.";

    header("Location: depense.php?id_generation=" . $idGeneration);
    exit;
}

$_SESSION['message_depense_liste'] = "Dépense enregistrée.";

header("Location: index.php?id_generation=" . $idGeneration);
exit;