<?php

session_start();

require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| Vérifier que le formulaire a été envoyé
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: connexion.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Récupération des données
|--------------------------------------------------------------------------
*/

$email = trim($_POST['email'] ?? '');
$motDePasse = $_POST['mot_de_passe'] ?? '';


/*
|--------------------------------------------------------------------------
| Vérification des champs
|--------------------------------------------------------------------------
*/

if (empty($email) || empty($motDePasse)) {

    $_SESSION['message'] = "Veuillez remplir tous les champs.";

    header("Location: connexion.php");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    $_SESSION['message'] = "Adresse email invalide.";

    header("Location: connexion.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Rechercher l'utilisateur
|--------------------------------------------------------------------------
|
| IMPORTANT :
| m.id_generation permet de récupérer la génération du membre.
|
*/

$sql = "
    SELECT
        u.id_utilisateur,
        u.id_membre,
        u.id_role,
        u.email,
        u.mot_de_passe,
        u.code_personnel,
        u.statut_compte,

        m.nom,
        m.prenom,
        m.id_generation,

        r.nom_role

    FROM utilisateur u

    INNER JOIN membre m
        ON u.id_membre = m.id_membre

    INNER JOIN role r
        ON u.id_role = r.id_role

    WHERE u.email = :email

    LIMIT 1
";


$requete = $connexion->prepare($sql);

$requete->execute([
    ':email' => $email
]);


$utilisateur = $requete->fetch();


/*
|--------------------------------------------------------------------------
| Vérifier si l'utilisateur existe
|--------------------------------------------------------------------------
*/

if (!$utilisateur) {

    $_SESSION['message'] =
        "Email ou mot de passe incorrect.";

    header("Location: connexion.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Vérifier le statut du compte
|--------------------------------------------------------------------------
*/
if ($utilisateur['statut_compte'] === 'EN_ATTENTE') {

    $_SESSION['message'] =
        "Ton compte n'est pas encore activé. Vérifie ton email pour le lien d'activation.";

    header("Location: connexion.php");
    exit;
}

if ($utilisateur['statut_compte'] !== 'ACTIF') {

    $_SESSION['message'] =
        "Votre compte est désactivé.";

    header("Location: connexion.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Vérifier le mot de passe
|--------------------------------------------------------------------------
*/

if (!password_verify(
    $motDePasse,
    $utilisateur['mot_de_passe']
)) {

    $_SESSION['message'] =
        "Email ou mot de passe incorrect.";

    header("Location: connexion.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Sécurité : régénérer l'identifiant de session
|--------------------------------------------------------------------------
*/

session_regenerate_id(true);


/*
|--------------------------------------------------------------------------
| Création de la session
|--------------------------------------------------------------------------
*/

$_SESSION['id_utilisateur'] =
    (int) $utilisateur['id_utilisateur'];

$_SESSION['id_membre'] =
    (int) $utilisateur['id_membre'];

$_SESSION['id_role'] =
    (int) $utilisateur['id_role'];

/*
 * IMPORTANT :
 * On récupère maintenant la génération du membre.
 */
$_SESSION['id_generation'] =
    (int) $utilisateur['id_generation'];

$_SESSION['email'] =
    $utilisateur['email'];

$_SESSION['nom'] =
    $utilisateur['nom'];

$_SESSION['prenom'] =
    $utilisateur['prenom'];

$_SESSION['nom_role'] =
    $utilisateur['nom_role'];


/*
|--------------------------------------------------------------------------
| Code personnel
|--------------------------------------------------------------------------
|
| 1 = Administrateur général
| 2 = Responsable génération
| 3 = Gestionnaire membres
| 4 = Trésorier
|
| Ces rôles devront vérifier leur code personnel.
|
*/

$_SESSION['code_verifie'] = false;


/*
|--------------------------------------------------------------------------
| Supprimer une ancienne redirection
|--------------------------------------------------------------------------
*/

unset($_SESSION['redirection_apres_code']);


/*
|--------------------------------------------------------------------------
| Enregistrer la dernière connexion
|--------------------------------------------------------------------------
*/

$sqlConnexion = "
    UPDATE utilisateur
    SET derniere_connexion = NOW()
    WHERE id_utilisateur = :id_utilisateur
";

$requeteConnexion =
    $connexion->prepare($sqlConnexion);

$requeteConnexion->execute([
    ':id_utilisateur' =>
        $_SESSION['id_utilisateur']
]);


/*

/*
|--------------------------------------------------------------------------
| Redirection — tout le monde arrive sur le menu général (index.php),
| qui affiche déjà les accès propres à chaque rôle
|--------------------------------------------------------------------------
*/

$rolesValides = [1, 2, 3, 4, 5];

if (!in_array($_SESSION['id_role'], $rolesValides, true)) {

    session_unset();
    session_destroy();

    header("Location: connexion.php");
    exit;
}

header("Location: ../index.php");
exit;