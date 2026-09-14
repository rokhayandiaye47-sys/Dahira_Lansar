<?php

require_once "../auth/protection_role.php";

verifierRole([4]);

require_once "../cotisations/fonctions.php";
require_once "../caisse/fonctions.php";
require_once "../membres/fonctions.php";

$idPaiement = (int) ($_GET['id_paiement'] ?? 0);


/*
|--------------------------------------------------------------------------
| Récupérer le paiement avec les infos de cotisation nécessaires
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        p.*,
        c.id_generation,
        c.periode,
        c.id_cotisation

    FROM paiement p

    INNER JOIN cotisation c ON c.id_cotisation = p.id_cotisation

    WHERE p.id_paiement = :id_paiement

    LIMIT 1
";

$requete = $connexion->prepare($sql);
$requete->execute([':id_paiement' => $idPaiement]);
$paiement = $requete->fetch();

if (!$paiement) {

    die("Paiement introuvable.");
}

assurerAccesGeneration($paiement['id_generation']);

if ($paiement['statut'] !== 'VALIDE') {

    $_SESSION['message_paiements'] = "Ce paiement est déjà annulé.";

    header("Location: index.php?id_generation=" . $paiement['id_generation']);
    exit;
}

try {

    $connexion->beginTransaction();


    /*
    |----------------------------------------------------------------------
    | Marquer le paiement comme annulé (RG25 : jamais supprimé)
    |----------------------------------------------------------------------
    */

    $connexion->prepare("
        UPDATE paiement

        SET statut = 'ANNULE',
            motif_annulation = :motif_annulation

        WHERE id_paiement = :id_paiement
    ")->execute([
        ':motif_annulation' => "Annulé par " . $_SESSION['prenom'] . ' ' . $_SESSION['nom'],
        ':id_paiement' => $idPaiement
    ]);


    /*
    |----------------------------------------------------------------------
    | Retirer le montant de la cotisation et recalculer son statut
    |----------------------------------------------------------------------
    */

    $connexion->prepare("
        UPDATE cotisation

        SET montant_paye = montant_paye - :montant

        WHERE id_cotisation = :id_cotisation
    ")->execute([
        ':montant' => $paiement['montant'],
        ':id_cotisation' => $paiement['id_cotisation']
    ]);

    recalculerStatutCotisation($connexion, $paiement['id_cotisation']);


    /*
    |----------------------------------------------------------------------
    | Débiter la caisse du montant annulé
    |----------------------------------------------------------------------
    */

    $caisse = recupererCaisse($connexion, $paiement['id_generation']);

    if ($caisse) {

        $connexion->prepare("
            UPDATE caisse SET solde = solde - :montant WHERE id_caisse = :id_caisse
        ")->execute([
            ':montant' => $paiement['montant'],
            ':id_caisse' => $caisse['id_caisse']
        ]);
    }


    /*
    |----------------------------------------------------------------------
    | Notifier le membre concerné
    |----------------------------------------------------------------------
    */

    $sqlUtilisateur = "
        SELECT id_utilisateur

        FROM utilisateur

        WHERE id_membre = :id_membre

        LIMIT 1
    ";

    $requeteUtilisateur = $connexion->prepare($sqlUtilisateur);
    $requeteUtilisateur->execute([':id_membre' => $paiement['id_membre']]);
    $utilisateurMembre = $requeteUtilisateur->fetch();

    if ($utilisateurMembre) {

        $connexion->prepare("
            INSERT INTO notification (id_utilisateur, id_paiement, type, message)
            VALUES (:id_utilisateur, :id_paiement, 'VALIDATION', :message)
        ")->execute([
            ':id_utilisateur' => $utilisateurMembre['id_utilisateur'],
            ':id_paiement' => $idPaiement,
            ':message' =>
                "Ton versement de " . number_format($paiement['montant'], 0, ',', ' ')
                . " FCFA pour " . libelleMoisFr($paiement['periode'])
                . " a été annulé. Contacte le trésorier pour plus d'informations."
        ]);
    }


    /*
    |----------------------------------------------------------------------
    | Journal d'audit
    |----------------------------------------------------------------------
    */

    $connexion->prepare("
        INSERT INTO journal_operation (id_utilisateur, type_action, objet)
        VALUES (:id_utilisateur, 'paiement.annulation', :objet)
    ")->execute([
        ':id_utilisateur' => $_SESSION['id_utilisateur'],
        ':objet' => "Paiement #$idPaiement annulé — " . number_format($paiement['montant'], 0, ',', ' ') . " FCFA débités de la caisse"
    ]);


    $connexion->commit();

} catch (PDOException $e) {

    $connexion->rollBack();

    $_SESSION['message_paiements'] = "Une erreur est survenue lors de l'annulation.";

    header("Location: index.php?id_generation=" . $paiement['id_generation']);
    exit;
}

$_SESSION['message_paiements'] = "Paiement annulé, cotisation et caisse mises à jour.";

header("Location: index.php?id_generation=" . $paiement['id_generation']);
exit;