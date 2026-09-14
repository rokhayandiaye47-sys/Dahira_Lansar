<?php

require_once "../auth/protection_role.php";

verifierRole([1]);

require_once "fonctions.php";

$idCampagne = (int) ($_GET['id_campagne'] ?? 0);

$sql = "
    SELECT *

    FROM campagne_gamou

    WHERE id_campagne = :id_campagne

    LIMIT 1
";

$requete = $connexion->prepare($sql);
$requete->execute([':id_campagne' => $idCampagne]);
$campagne = $requete->fetch();

if (!$campagne) {

    die("Campagne introuvable.");
}

if ($campagne['statut'] !== 'ACTIVE') {

    $_SESSION['message_campagnes'] = "Cette campagne est déjà clôturée.";

    header("Location: index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Clôturer — jamais supprimée (§4), juste figée dans le temps
|--------------------------------------------------------------------------
*/

$connexion->prepare("
    UPDATE campagne_gamou

    SET statut = 'CLOTUREE',
        date_fin = CURDATE()

    WHERE id_campagne = :id_campagne
")->execute([
    ':id_campagne' => $idCampagne
]);

$connexion->prepare("
    INSERT INTO journal_operation (id_utilisateur, type_action, objet)
    VALUES (:id_utilisateur, 'campagne.cloture', :objet)
")->execute([
    ':id_utilisateur' => $_SESSION['id_utilisateur'],
    ':objet' => "Campagne « " . $campagne['nom_edition'] . " » clôturée"
]);

$_SESSION['message_campagnes'] = "Campagne clôturée.";

header("Location: index.php");
exit;