<?php

require_once "../auth/protection_role.php";

verifierRole([4]);

require_once "fonctions.php";
require_once "../membres/fonctions.php";

$idDepense = (int) ($_GET['id_depense'] ?? 0);

$depense = recupererDepense($connexion, $idDepense);

if (!$depense) {

    die("Dépense introuvable.");
}

assurerAccesGeneration($depense['id_generation']);

if ($depense['statut'] !== 'ACTIVE') {

    $_SESSION['message_depense_liste'] = "Cette dépense est déjà annulée.";

    header("Location: index.php?id_generation=" . $depense['id_generation']);
    exit;
}

try {

    $connexion->beginTransaction();

    $connexion->prepare("
        UPDATE caisse SET solde = solde + :montant WHERE id_caisse = :id_caisse
    ")->execute([
        ':montant' => $depense['montant'],
        ':id_caisse' => $depense['id_caisse']
    ]);

    $connexion->prepare("
        UPDATE depense

        SET statut = 'ANNULEE',
            motif_annulation = :motif_annulation

        WHERE id_depense = :id_depense
    ")->execute([
        ':motif_annulation' => "Annulée par " . $_SESSION['prenom'] . ' ' . $_SESSION['nom'],
        ':id_depense' => $idDepense
    ]);

    $connexion->prepare("
        INSERT INTO journal_operation (id_utilisateur, type_action, objet)
        VALUES (:id_utilisateur, 'depense.annulation', :objet)
    ")->execute([
        ':id_utilisateur' => $_SESSION['id_utilisateur'],
        ':objet' => "Dépense #$idDepense annulée — " . number_format($depense['montant'], 0, ',', ' ') . " FCFA recrédités"
    ]);

    $connexion->commit();

} catch (PDOException $e) {

    $connexion->rollBack();

    $_SESSION['message_depense_liste'] = "Une erreur est survenue lors de l'annulation.";

    header("Location: index.php?id_generation=" . $depense['id_generation']);
    exit;
}

$_SESSION['message_depense_liste'] = "Dépense annulée, montant recrédité à la caisse.";

header("Location: index.php?id_generation=" . $depense['id_generation']);
exit;