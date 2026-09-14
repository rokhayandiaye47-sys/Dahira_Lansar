<?php

require_once "../config/database.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: connexion.php");
    exit;
}

$jeton = trim($_POST['jeton'] ?? '');
$motDePasse = $_POST['mot_de_passe'] ?? '';
$confirmation = $_POST['confirmation'] ?? '';

$sql = "
    SELECT id_utilisateur, jeton_expiration

    FROM utilisateur

    WHERE jeton_activation = :jeton
      AND statut_compte = 'EN_ATTENTE'

    LIMIT 1
";

$requete = $connexion->prepare($sql);
$requete->execute([':jeton' => $jeton]);
$utilisateur = $requete->fetch();

if (!$utilisateur || strtotime($utilisateur['jeton_expiration']) <= time()) {

    header("Location: activer_compte.php?jeton=" . urlencode($jeton));
    exit;
}

if (strlen($motDePasse) < 8 || $motDePasse !== $confirmation) {

    header("Location: activer_compte.php?jeton=" . urlencode($jeton) . "&erreur=" . urlencode("Les mots de passe ne correspondent pas ou sont trop courts (8 caractères minimum)."));
    exit;
}

$connexion->prepare("
    UPDATE utilisateur

    SET mot_de_passe = :mot_de_passe,
        statut_compte = 'ACTIF',
        jeton_activation = NULL,
        jeton_expiration = NULL

    WHERE id_utilisateur = :id_utilisateur
")->execute([
    ':mot_de_passe' => password_hash($motDePasse, PASSWORD_DEFAULT),
    ':id_utilisateur' => $utilisateur['id_utilisateur']
]);

$connexion->prepare("
    INSERT INTO journal_operation (id_utilisateur, type_action, objet)
    VALUES (:id_utilisateur, 'utilisateur.activation', 'Compte activé par le titulaire')
")->execute([
    ':id_utilisateur' => $utilisateur['id_utilisateur']
]);

$_SESSION['message'] = "Ton compte est activé ! Tu peux te connecter avec ton mot de passe.";

header("Location: connexion.php");
exit;