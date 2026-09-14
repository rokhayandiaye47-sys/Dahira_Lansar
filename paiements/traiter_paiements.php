<?php

require_once "../auth/protection_role.php";

verifierRole([1, 2, 3, 4, 5]);

require_once "../cotisations/fonctions.php";


/*
|--------------------------------------------------------------------------
| Vérifier que le formulaire a été envoyé
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: ../cotisations/index.php");
    exit;
}


$idCotisation = (int) ($_POST['id_cotisation'] ?? 0);
$montant = (float) ($_POST['montant'] ?? 0);
$modePaiement = $_POST['mode_paiement'] ?? '';
$referenceTransaction = trim($_POST['reference_transaction'] ?? '');

$modesValides = ['WAVE', 'ORANGE_MONEY', 'AUTRE'];


/*
|--------------------------------------------------------------------------
| Récupérer la cotisation et vérifier qu'elle appartient au membre
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT *

    FROM cotisation

    WHERE id_cotisation = :id_cotisation
      AND id_membre = :id_membre

    LIMIT 1
";

$requete = $connexion->prepare($sql);

$requete->execute([
    ':id_cotisation' => $idCotisation,
    ':id_membre' => $_SESSION['id_membre']
]);

$cotisation = $requete->fetch();


/*
|--------------------------------------------------------------------------
| Validations
|--------------------------------------------------------------------------
*/

if (!$cotisation) {

    die("Cotisation introuvable.");
}

if (
    $montant <= 0
    || $montant > $cotisation['reste']
    || !in_array($modePaiement, $modesValides, true)
    || empty($referenceTransaction)
) {

    $_SESSION['message_paiement'] =
        "Merci de vérifier les informations saisies (montant, moyen de paiement, référence).";

    header("Location: paiement.php?id_cotisation=" . $idCotisation);
    exit;
}


try {

    $connexion->beginTransaction();


    /*
    |----------------------------------------------------------------------
    | Enregistrer le paiement — validé directement, sans validation
    | manuelle du trésorier (RG21)
    |----------------------------------------------------------------------
    */

    $sqlPaiement = "
        INSERT INTO paiement
            (id_cotisation, id_membre, montant, mode_paiement, reference_transaction, statut)
        VALUES
            (:id_cotisation, :id_membre, :montant, :mode_paiement, :reference_transaction, 'VALIDE')
    ";

    $requetePaiement = $connexion->prepare($sqlPaiement);

    $requetePaiement->execute([
        ':id_cotisation' => $idCotisation,
        ':id_membre' => $_SESSION['id_membre'],
        ':montant' => $montant,
        ':mode_paiement' => $modePaiement,
        ':reference_transaction' => $referenceTransaction
    ]);

    $idPaiement = $connexion->lastInsertId();


    /*
    |----------------------------------------------------------------------
    | Mettre à jour le montant payé de la cotisation, puis son statut
    |----------------------------------------------------------------------
    */

    $sqlMajCotisation = "
        UPDATE cotisation

        SET montant_paye = montant_paye + :montant

        WHERE id_cotisation = :id_cotisation
    ";

    $requeteMajCotisation = $connexion->prepare($sqlMajCotisation);

    $requeteMajCotisation->execute([
        ':montant' => $montant,
        ':id_cotisation' => $idCotisation
    ]);

    recalculerStatutCotisation($connexion, $idCotisation);


    /*
    |----------------------------------------------------------------------
    | Créditer la caisse de la génération et journaliser l'entrée
    |----------------------------------------------------------------------
    */

    $sqlCaisse = "
        SELECT id_caisse

        FROM caisse

        WHERE id_generation = :id_generation

        LIMIT 1
    ";

    $requeteCaisse = $connexion->prepare($sqlCaisse);
    $requeteCaisse->execute([':id_generation' => $cotisation['id_generation']]);
    $caisse = $requeteCaisse->fetch();

    if ($caisse) {

        $connexion->prepare("
            UPDATE caisse SET solde = solde + :montant WHERE id_caisse = :id_caisse
        ")->execute([
            ':montant' => $montant,
            ':id_caisse' => $caisse['id_caisse']
        ]);

        $connexion->prepare("
            INSERT INTO entree
                (id_caisse, id_paiement, id_utilisateur, montant, motif, type)
            VALUES
                (:id_caisse, :id_paiement, :id_utilisateur, :montant, :motif, 'COTISATION')
        ")->execute([
            ':id_caisse' => $caisse['id_caisse'],
            ':id_paiement' => $idPaiement,
            ':id_utilisateur' => $_SESSION['id_utilisateur'],
            ':montant' => $montant,
            ':motif' => "Cotisation " . libelleMoisFr($cotisation['periode'])
        ]);
    }


    /*
    |----------------------------------------------------------------------
    | Notifier le membre lui-même, avec accès direct à son reçu
    |----------------------------------------------------------------------
    */

    $connexion->prepare("
        INSERT INTO notification (id_utilisateur, id_paiement, type, message)
        VALUES (:id_utilisateur, :id_paiement, 'VALIDATION', :message)
    ")->execute([
        ':id_utilisateur' => $_SESSION['id_utilisateur'],
        ':id_paiement' => $idPaiement,
        ':message' => "Ton paiement de " . number_format($montant, 0, ',', ' ') . " FCFA pour " . libelleMoisFr($cotisation['periode']) . " a été enregistré. Ton reçu est disponible."
    ]);


    /*
    |----------------------------------------------------------------------
    | Notifier le(s) trésorier(s) de la génération (RG22)
    |----------------------------------------------------------------------
    */

    $sqlTresoriers = "
        SELECT u.id_utilisateur

        FROM utilisateur u

        INNER JOIN membre m ON m.id_membre = u.id_membre

        WHERE m.id_generation = :id_generation
          AND u.id_role = 4
    ";

    $requeteTresoriers = $connexion->prepare($sqlTresoriers);
    $requeteTresoriers->execute([':id_generation' => $cotisation['id_generation']]);

    foreach ($requeteTresoriers->fetchAll() as $tresorier) {

        $connexion->prepare("
            INSERT INTO notification (id_utilisateur, id_paiement, type, message)
            VALUES (:id_utilisateur, :id_paiement, 'PAIEMENT', :message)
        ")->execute([
            ':id_utilisateur' => $tresorier['id_utilisateur'],
            ':id_paiement' => $idPaiement,
            ':message' =>
                htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom'])
                . " a versé " . number_format($montant, 0, ',', ' ')
                . " FCFA pour " . libelleMoisFr($cotisation['periode']) . "."
        ]);
    }


    /*
    |----------------------------------------------------------------------
    | Journal d'audit
    |----------------------------------------------------------------------
    */

    $connexion->prepare("
        INSERT INTO journal_operation (id_utilisateur, type_action, objet)
        VALUES (:id_utilisateur, 'paiement.creation', :objet)
    ")->execute([
        ':id_utilisateur' => $_SESSION['id_utilisateur'],
        ':objet' => "Paiement #$idPaiement — " . number_format($montant, 0, ',', ' ') . " FCFA (réf. $referenceTransaction)"
    ]);


    $connexion->commit();

} catch (PDOException $e) {

    $connexion->rollBack();

    $_SESSION['message_paiement'] =
        "Une erreur est survenue (référence de transaction peut-être déjà utilisée).";

    header("Location: paiement.php?id_cotisation=" . $idCotisation);
    exit;
}


header("Location: ../cotisations/index.php");
exit;