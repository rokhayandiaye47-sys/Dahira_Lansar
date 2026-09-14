<?php

require_once "../auth/protection_role.php";

verifierRole([3]);

require_once "fonctions.php";

$idMembre = (int) ($_GET['id_membre'] ?? 0);

$membre = recupererMembreAvecCompte($connexion, $idMembre);

if (!$membre) {

    die("Membre introuvable.");
}

assurerAccesGeneration($membre['id_generation']);

$nouveauStatut = $membre['statut'] === 'ACTIF' ? 'INACTIF' : 'ACTIF';


/*
|--------------------------------------------------------------------------
| On ne supprime jamais un membre (RG conservation d'historique) —
| on bascule juste son statut, ainsi que celui de son compte
|--------------------------------------------------------------------------
*/

$connexion->prepare("
    UPDATE membre SET statut = :statut WHERE id_membre = :id_membre
")->execute([
    ':statut' => $nouveauStatut,
    ':id_membre' => $idMembre
]);

if ($membre['id_utilisateur']) {

    $connexion->prepare("
        UPDATE utilisateur SET statut_compte = :statut WHERE id_membre = :id_membre
    ")->execute([
        ':statut' => $nouveauStatut,
        ':id_membre' => $idMembre
    ]);
}

$connexion->prepare("
    INSERT INTO journal_operation (id_utilisateur, type_action, objet)
    VALUES (:id_utilisateur, 'membre.statut', :objet)
")->execute([
    ':id_utilisateur' => $_SESSION['id_utilisateur'],
    ':objet' => $membre['prenom'] . ' ' . $membre['nom'] . " — statut changé en $nouveauStatut"
]);

$_SESSION['message_membres'] = "Statut mis à jour.";

header("Location: index.php?id_generation=" . $membre['id_generation']);
exit;