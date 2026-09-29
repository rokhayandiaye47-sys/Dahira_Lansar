<?php

session_start();

require_once "../config/database.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: mot_de_pass_oublie.php");
    exit;
}

$email = trim($_POST['email'] ?? '');


/*
|--------------------------------------------------------------------------
| Message générique dans tous les cas (ne pas révéler si l'email existe
| ou non — évite de laisser deviner quels emails sont enregistrés)
|--------------------------------------------------------------------------
*/

$_SESSION['message_mdp_oublie'] = "Si cet email correspond à un compte, la personne responsable a été notifiée pour te transmettre un nouveau mot de passe.";

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {

    header("Location: mot_de_pass_oublie.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Rechercher le compte
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        u.id_utilisateur,
        u.id_role,

        m.id_membre,
        m.nom,
        m.prenom,
        m.id_generation

    FROM utilisateur u

    INNER JOIN membre m ON m.id_membre = u.id_membre

    WHERE u.email = :email
      AND u.statut_compte = 'ACTIF'

    LIMIT 1
";

$requete = $connexion->prepare($sql);
$requete->execute([':email' => $email]);
$utilisateur = $requete->fetch();

if (!$utilisateur) {

    header("Location: mot_de_pass_oublie.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Générer un nouveau mot de passe temporaire
|--------------------------------------------------------------------------
*/

$nouveauMotDePasse = strtolower(substr(bin2hex(random_bytes(4)), 0, 8));

$connexion->prepare("
    UPDATE utilisateur SET mot_de_passe = :mot_de_passe WHERE id_utilisateur = :id_utilisateur
")->execute([
    ':mot_de_passe' => password_hash($nouveauMotDePasse, PASSWORD_DEFAULT),
    ':id_utilisateur' => $utilisateur['id_utilisateur']
]);


/*
|--------------------------------------------------------------------------
| Déterminer qui doit être notifié pour transmettre le nouveau mot
| de passe :
| - Membre simple ou Trésorier/Responsable/Gestionnaire → le Gestionnaire
|   des membres de sa génération
| - Gestionnaire des membres lui-même, ou Admin général → l'Admin général
|--------------------------------------------------------------------------
*/

if ($utilisateur['id_role'] == 3 || $utilisateur['id_role'] == 1) {

    $sqlDestinataires = "
        SELECT id_utilisateur

        FROM utilisateur

        WHERE id_role = 1
          AND statut_compte = 'ACTIF'
    ";

    $requeteDestinataires = $connexion->prepare($sqlDestinataires);
    $requeteDestinataires->execute();

} else {

    $sqlDestinataires = "
        SELECT u.id_utilisateur

        FROM utilisateur u

        INNER JOIN membre m ON m.id_membre = u.id_membre

        WHERE u.id_role = 3
          AND u.statut_compte = 'ACTIF'
          AND m.id_generation = :id_generation
    ";

    $requeteDestinataires = $connexion->prepare($sqlDestinataires);
    $requeteDestinataires->execute([':id_generation' => $utilisateur['id_generation']]);
}

$destinataires = $requeteDestinataires->fetchAll();

$nomComplet = $utilisateur['prenom'] . ' ' . $utilisateur['nom'];

foreach ($destinataires as $destinataire) {

    $connexion->prepare("
        INSERT INTO notification (id_utilisateur, type, message)
        VALUES (:id_utilisateur, 'COMPTE', :message)
    ")->execute([
        ':id_utilisateur' => $destinataire['id_utilisateur'],
        ':message' => "$nomComplet a oublié son mot de passe. Nouveau mot de passe temporaire à lui transmettre : $nouveauMotDePasse"
    ]);
}

$connexion->prepare("
    INSERT INTO journal_operation (id_utilisateur, type_action, objet)
    VALUES (NULL, 'utilisateur.mot_de_passe_oublie', :objet)
")->execute([
    ':objet' => "Mot de passe réinitialisé pour $nomComplet (demande via 'mot de passe oublié')"
]);

header("Location: mot_de_pass_oublie.php");
exit;