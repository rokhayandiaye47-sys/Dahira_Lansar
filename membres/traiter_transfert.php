<?php

require_once "../auth/protection_role.php";

verifierRole([1]);

require_once "fonctions.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: index.php");
    exit;
}

$idMembre = (int) ($_POST['id_membre'] ?? 0);
$idGenerationDestination = (int) ($_POST['id_generation_destination'] ?? 0);
$motif = trim($_POST['motif'] ?? '');

$membre = recupererMembreAvecCompte($connexion, $idMembre);

if (!$membre) {

    die("Membre introuvable.");
}

$idGenerationOrigine = (int) $membre['id_generation'];

if ($idGenerationDestination === $idGenerationOrigine || $idGenerationDestination <= 0 || $motif === '') {

    $_SESSION['erreurs_transfert'] = ["Merci de choisir une génération différente et de renseigner un motif."];

    header("Location: transferer.php?id_membre=" . $idMembre);
    exit;
}

try {

    $connexion->beginTransaction();

    $connexion->prepare("
        UPDATE historique_generation

        SET date_fin = CURDATE()

        WHERE id_membre = :id_membre
          AND date_fin IS NULL
    ")->execute([
        ':id_membre' => $idMembre
    ]);

    $connexion->prepare("
        INSERT INTO historique_generation
            (id_membre, id_generation_origine, id_generation_destination, date_debut, motif)
        VALUES
            (:id_membre, :id_generation_origine, :id_generation_destination, CURDATE(), :motif)
    ")->execute([
        ':id_membre' => $idMembre,
        ':id_generation_origine' => $idGenerationOrigine,
        ':id_generation_destination' => $idGenerationDestination,
        ':motif' => $motif
    ]);

    $connexion->prepare("
        UPDATE membre SET id_generation = :id_generation WHERE id_membre = :id_membre
    ")->execute([
        ':id_generation' => $idGenerationDestination,
        ':id_membre' => $idMembre
    ]);

    $connexion->prepare("
        INSERT INTO journal_operation (id_utilisateur, type_action, objet)
        VALUES (:id_utilisateur, 'membre.transfert', :objet)
    ")->execute([
        ':id_utilisateur' => $_SESSION['id_utilisateur'],
        ':objet' => $membre['prenom'] . ' ' . $membre['nom'] . " transféré vers une nouvelle génération ($motif)"
    ]);

    $connexion->commit();

} catch (PDOException $e) {

    $connexion->rollBack();

    $_SESSION['erreurs_transfert'] = ["Une erreur est survenue lors du transfert."];

    header("Location: transferer.php?id_membre=" . $idMembre);
    exit;
}

$_SESSION['message_membres'] = "Membre transféré avec succès.";

header("Location: index.php?id_generation=" . $idGenerationDestination);
exit;