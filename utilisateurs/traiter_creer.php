<?php

require_once "../auth/protection_role.php";

verifierRole([1]);

require_once "fonctions.php";
require_once "../membres/fonctions.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: index.php");
    exit;
}

$idRole = (int) ($_POST['id_role'] ?? 0);
$idGeneration = (int) ($_POST['id_generation'] ?? 0);
$prenom = trim($_POST['prenom'] ?? '');
$nom = trim($_POST['nom'] ?? '');
$telephone = trim($_POST['telephone'] ?? '');
$email = trim($_POST['email'] ?? '');

$rolesValides = array_keys(libellesRolesResponsabilite());

$erreurs = [];

if (!in_array($idRole, $rolesValides, true)) {
    $erreurs[] = "Rôle invalide.";
}

if ($nom === '' || $prenom === '') {
    $erreurs[] = "Le nom et le prénom sont obligatoires.";
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erreurs[] = "Merci de renseigner un email valide.";
}

$generationValide = false;
foreach (listerGenerations($connexion) as $g) {
    if ($g['id_generation'] == $idGeneration) {
        $generationValide = true;
    }
}

if (!$generationValide) {
    $erreurs[] = "Génération invalide.";
}


/*
|--------------------------------------------------------------------------
| Vérifier que l'email n'est pas déjà utilisé
|--------------------------------------------------------------------------
*/

if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {

    $sqlVerifEmail = "SELECT id_utilisateur FROM utilisateur WHERE email = :email LIMIT 1";
    $requeteVerifEmail = $connexion->prepare($sqlVerifEmail);
    $requeteVerifEmail->execute([':email' => $email]);

    if ($requeteVerifEmail->fetch()) {
        $erreurs[] = "Cet email est déjà utilisé par un autre compte.";
    }
}

if ($erreurs) {

    $_SESSION['erreurs_utilisateur'] = $erreurs;

    header("Location: creer.php");
    exit;
}

try {

    $connexion->beginTransaction();

    $connexion->prepare("
        INSERT INTO membre (id_generation, nom, prenom, telephone, date_adhesion, statut)
        VALUES (:id_generation, :nom, :prenom, :telephone, CURDATE(), 'ACTIF')
    ")->execute([
        ':id_generation' => $idGeneration,
        ':nom' => $nom,
        ':prenom' => $prenom,
        ':telephone' => $telephone
    ]);

    $nouvelIdMembre = $connexion->lastInsertId();

    $connexion->prepare("
        INSERT INTO historique_generation
            (id_membre, id_generation_origine, id_generation_destination, date_debut, motif)
        VALUES
            (:id_membre, NULL, :id_generation, CURDATE(), 'Création du compte')
    ")->execute([
        ':id_membre' => $nouvelIdMembre,
        ':id_generation' => $idGeneration
    ]);

    $motDePasseTemporaire = genererMotDePasseTemporaire();
    $codePersonnel = genererCodePersonnel();

    $connexion->prepare("
        INSERT INTO utilisateur (id_membre, id_role, email, mot_de_passe, code_personnel, statut_compte)
        VALUES (:id_membre, :id_role, :email, :mot_de_passe, :code_personnel, 'ACTIF')
    ")->execute([
        ':id_membre' => $nouvelIdMembre,
        ':id_role' => $idRole,
        ':email' => $email,
        ':mot_de_passe' => password_hash($motDePasseTemporaire, PASSWORD_DEFAULT),
        ':code_personnel' => password_hash($codePersonnel, PASSWORD_DEFAULT)
    ]);

    $nouvelIdUtilisateur = $connexion->lastInsertId();

       require_once "../includes/envoyer_email.php";

    $corpsEmail = "
        <p>Bonjour $prenom,</p>
        <p>Un compte vient d'être créé pour toi sur la plateforme Dahira Lansar Guidick, avec le rôle : <strong>" . htmlspecialchars(libellesRolesResponsabilite()[$idRole]) . "</strong>.</p>
        <p><strong>Email de connexion :</strong> $email<br>
        <strong>Mot de passe provisoire :</strong> $motDePasseTemporaire<br>
        <strong>Code personnel (2e facteur) :</strong> $codePersonnel</p>
        <p>Connecte-toi avec ces identifiants, puis garde le code personnel précieusement : il te sera redemandé pour accéder à ton espace de responsabilité.</p>
        <p>Si tu n'es pas concerné(e) par cette création de compte, contacte immédiatement un administrateur.</p>
    ";

    envoyerEmail($email, "$prenom $nom", "Ton compte Dahira Lansar Guidick a été créé", $corpsEmail);

    $connexion->prepare("
        INSERT INTO notification (id_utilisateur, type, message)
        VALUES (:id_utilisateur, 'COMPTE', :message)
    ")->execute([
        ':id_utilisateur' => $nouvelIdUtilisateur,
        ':message' => "Bienvenue à la Dahira Lansar Guidick, $prenom ! Ton compte a été créé."
    ]);

    $connexion->prepare("
        INSERT INTO journal_operation (id_utilisateur, type_action, objet)
        VALUES (:id_utilisateur, 'utilisateur.creation', :objet)
    ")->execute([
        ':id_utilisateur' => $_SESSION['id_utilisateur'],
        ':objet' => "Compte $prenom $nom créé avec le rôle #$idRole"
    ]);

    $connexion->commit();

} catch (PDOException $e) {

    $connexion->rollBack();

    $_SESSION['erreurs_utilisateur'] = ["Cet email est déjà utilisé par un autre compte."];

    header("Location: creer.php");
    exit;
}

$_SESSION['identifiants_utilisateur'] = [
    'nom' => "$prenom $nom",
    'email' => $email,
    'mot_de_passe' => $motDePasseTemporaire,
    'code_personnel' => $codePersonnel
];

header("Location: creer.php");
exit;