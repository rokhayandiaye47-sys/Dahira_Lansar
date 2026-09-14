<?php

require_once "../auth/protection_role.php";

verifierRole([4]);

require_once "../cotisations/fonctions.php";
require_once "../membres/fonctions.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: index.php");
    exit;
}

$idMembre = (int) ($_POST['id_membre'] ?? 0);
$idCotisation = (int) ($_POST['id_cotisation'] ?? 0);
$montant = (float) ($_POST['montant'] ?? 0);
$modePaiement = $_POST['mode_paiement'] ?? '';
$referenceTransaction = trim($_POST['reference_transaction'] ?? '');

$modesValides = ['ESPECES', 'WAVE', 'ORANGE_MONEY', 'AUTRE'];


/*
|--------------------------------------------------------------------------
| Vérifier que le membre appartient bien à la génération du trésorier
|--------------------------------------------------------------------------
*/

$sqlMembre = "SELECT id_generation FROM membre WHERE id_membre = :id_membre";
$requeteMembre = $connexion->prepare($sqlMembre);
$requeteMembre->execute([':id_membre' => $idMembre]);
$membre = $requeteMembre->fetch();

if (!$membre || (int) $membre['id_generation'] !== (int) $_SESSION['id_generation']) {
    die("Membre introuvable ou hors de ta génération.");
}


/*
|--------------------------------------------------------------------------
| Récupérer la cotisation
|--------------------------------------------------------------------------
*/

$sql = "SELECT * FROM cotisation WHERE id_cotisation = :id_cotisation AND id_membre = :id_membre";
$requete = $connexion->prepare($sql);
$requete->execute([':id_cotisation' => $idCotisation, ':id_membre' => $idMembre]);
$cotisation = $requete->fetch();

if (!$cotisation) {
    die("Cotisation introuvable.");
}


/*
|--------------------------------------------------------------------------
| Validations
|--------------------------------------------------------------------------
*/

if (
    $montant <= 0
    || $montant > $cotisation['reste']
    || !in_array($modePaiement, $modesValides, true)
) {

    $_SESSION['erreurs_enregistrement'] = ["Merci de vérifier le montant et le moyen de paiement."];

    header("Location: enregistrer.php?id_membre=" . $idMembre);
    exit;
}


/*
|--------------------------------------------------------------------------
| Générer une référence automatique si aucune n'a été saisie
| (typiquement pour les espèces, sans trace électronique)
|--------------------------------------------------------------------------
*/

if ($referenceTransaction === '') {
    $referenceTransaction = strtoupper($modePaiement) . '-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
}


try {

    $connexion->beginTransaction();

    $connexion->prepare("
        INSERT INTO paiement
            (id_cotisation, id_membre, montant, mode_paiement, reference_transaction, statut)
        VALUES
            (:id_cotisation, :id_membre, :montant, :mode_paiement, :reference_transaction, 'VALIDE')
    ")->execute([
        ':id_cotisation' => $idCotisation,
        ':id_membre' => $idMembre,
        ':montant' => $montant,
        ':mode_paiement' => $modePaiement,
        ':reference_transaction' => $referenceTransaction
    ]);

    $idPaiement = $connexion->lastInsertId();

    $connexion->prepare("
        UPDATE cotisation SET montant_paye = montant_paye + :montant WHERE id_cotisation = :id_cotisation
    ")->execute([
        ':montant' => $montant,
        ':id_cotisation' => $idCotisation
    ]);

    recalculerStatutCotisation($connexion, $idCotisation);

    $sqlCaisse = "SELECT id_caisse FROM caisse WHERE id_generation = :id_generation LIMIT 1";
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
            INSERT INTO entree (id_caisse, id_paiement, id_utilisateur, montant, motif, type)
            VALUES (:id_caisse, :id_paiement, :id_utilisateur, :montant, :motif, 'COTISATION')
        ")->execute([
            ':id_caisse' => $caisse['id_caisse'],
            ':id_paiement' => $idPaiement,
            ':id_utilisateur' => $_SESSION['id_utilisateur'],
            ':montant' => $montant,
            ':motif' => "Cotisation " . libelleMoisFr($cotisation['periode']) . " — encaissée par le trésorier"
        ]);
    }


    /*
    |----------------------------------------------------------------------
    | Notifier le membre que sa cotisation a bien été enregistrée,
    | avec un lien direct vers son reçu
    |----------------------------------------------------------------------
    */

    $sqlUtilisateurMembre = "SELECT id_utilisateur FROM utilisateur WHERE id_membre = :id_membre LIMIT 1";
    $requeteUtilisateurMembre = $connexion->prepare($sqlUtilisateurMembre);
    $requeteUtilisateurMembre->execute([':id_membre' => $idMembre]);
    $utilisateurMembre = $requeteUtilisateurMembre->fetch();

    if ($utilisateurMembre) {

        $connexion->prepare("
            INSERT INTO notification (id_utilisateur, id_paiement, type, message)
            VALUES (:id_utilisateur, :id_paiement, 'VALIDATION', :message)
        ")->execute([
            ':id_utilisateur' => $utilisateurMembre['id_utilisateur'],
            ':id_paiement' => $idPaiement,
            ':message' => "Ta cotisation de " . libelleMoisFr($cotisation['periode']) . " (" . number_format($montant, 0, ',', ' ') . " FCFA) a été bien enregistrée par le trésorier. Ton reçu est disponible."
        ]);
    }

    $connexion->prepare("
        INSERT INTO journal_operation (id_utilisateur, type_action, objet)
        VALUES (:id_utilisateur, 'paiement.enregistrement_tresorier', :objet)
    ")->execute([
        ':id_utilisateur' => $_SESSION['id_utilisateur'],
        ':objet' => "Versement de " . number_format($montant, 0, ',', ' ') . " FCFA enregistré manuellement pour le membre #$idMembre"
    ]);

    $connexion->commit();

} catch (PDOException $e) {

    $connexion->rollBack();

    $_SESSION['erreurs_enregistrement'] = ["Une erreur est survenue lors de l'enregistrement."];

    header("Location: enregistrer.php?id_membre=" . $idMembre);
    exit;
}

$_SESSION['message_paiements'] = "Versement enregistré pour le membre, et notification envoyée.";

header("Location: index.php");
exit;