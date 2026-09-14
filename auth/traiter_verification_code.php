<?php

require_once __DIR__ . "/protection.php";
require_once __DIR__ . "/../config/database.php";


/*
|--------------------------------------------------------------------------
| Vérifier que le formulaire a été envoyé
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: verification_code.php");
    exit;
}


$codeSaisi = trim($_POST['code_personnel'] ?? '');

if (empty($codeSaisi)) {

    $_SESSION['message_code'] =
        "Merci de saisir ton code personnel.";

    header("Location: verification_code.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Récupérer le hash du code personnel de l'utilisateur connecté
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT code_personnel

    FROM utilisateur

    WHERE id_utilisateur = :id_utilisateur

    LIMIT 1
";

$requete = $connexion->prepare($sql);

$requete->execute([
    ':id_utilisateur' => $_SESSION['id_utilisateur']
]);

$utilisateur = $requete->fetch();


/*
|--------------------------------------------------------------------------
| Vérifier le code
|--------------------------------------------------------------------------
*/

if (
    !$utilisateur
    || empty($utilisateur['code_personnel'])
    || !password_verify($codeSaisi, $utilisateur['code_personnel'])
) {

    $_SESSION['message_code'] =
        "Code personnel incorrect.";

    header("Location: verification_code.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Code validé : marquer la session comme vérifiée
|--------------------------------------------------------------------------
*/

$_SESSION['code_verifie'] = true;


/*
|--------------------------------------------------------------------------
| Retour vers la page initialement demandée
|--------------------------------------------------------------------------
*/

$destination = $_SESSION['redirection_apres_code'] ?? '/dahira/index.php';

unset($_SESSION['redirection_apres_code']);

header("Location: " . $destination);
exit;
