<?php

require_once "../auth/protection_role.php";

verifierRole([3]);

require_once "fonctions.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: index.php");
    exit;
}

$idMembre = !empty($_POST['id_membre']) ? (int) $_POST['id_membre'] : null;
$idGeneration = (int) ($_POST['id_generation'] ?? 0);

assurerAccesGeneration($idGeneration);

$nom = trim($_POST['nom'] ?? '');
$prenom = trim($_POST['prenom'] ?? '');
$telephone = trim($_POST['telephone'] ?? '');
$dateAdhesion = $_POST['date_adhesion'] ?? date('Y-m-d');

$erreurs = [];

if ($nom === '' || $prenom === '') {
    $erreurs[] = "Le nom et le prénom sont obligatoires.";
}


/*
|--------------------------------------------------------------------------
| Modification d'un membre existant
|--------------------------------------------------------------------------
*/

if ($idMembre) {

    $statut = in_array($_POST['statut'] ?? '', ['ACTIF', 'INACTIF'], true)
        ? $_POST['statut']
        : 'ACTIF';

    if ($erreurs) {

        $_SESSION['erreurs_membre'] = $erreurs;

        header("Location: formulaire.php?id_membre=" . $idMembre);
        exit;
    }

    $sql = "
        UPDATE membre

        SET nom = :nom,
            prenom = :prenom,
            telephone = :telephone,
            date_adhesion = :date_adhesion,
            statut = :statut

        WHERE id_membre = :id_membre
    ";

    $requete = $connexion->prepare($sql);

    $requete->execute([
        ':nom' => $nom,
        ':prenom' => $prenom,
        ':telephone' => $telephone,
        ':date_adhesion' => $dateAdhesion,
        ':statut' => $statut,
        ':id_membre' => $idMembre
    ]);

    $connexion->prepare("
        INSERT INTO journal_operation (id_utilisateur, type_action, objet)
        VALUES (:id_utilisateur, 'membre.modification', :objet)
    ")->execute([
        ':id_utilisateur' => $_SESSION['id_utilisateur'],
        ':objet' => "Profil de $prenom $nom modifié"
    ]);

    $_SESSION['message_membres'] = "Membre mis à jour.";

    header("Location: index.php?id_generation=" . $idGeneration);
    exit;
}


/*
|--------------------------------------------------------------------------
| Création d'un nouveau membre + de son compte utilisateur
| (mot de passe généré directement, transmis en main propre par le
| Gestionnaire — comme pour les comptes à responsabilité)
|--------------------------------------------------------------------------
*/

$email = trim($_POST['email'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erreurs[] = "Merci de renseigner un email valide.";
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

    $_SESSION['erreurs_membre'] = $erreurs;

    header("Location: formulaire.php?id_generation=" . $idGeneration);
    exit;
}

try {

    $connexion->beginTransaction();

    $sqlMembre = "
        INSERT INTO membre (id_generation, nom, prenom, telephone, date_adhesion, statut)
        VALUES (:id_generation, :nom, :prenom, :telephone, :date_adhesion, 'ACTIF')
    ";

    $requeteMembre = $connexion->prepare($sqlMembre);

    $requeteMembre->execute([
        ':id_generation' => $idGeneration,
        ':nom' => $nom,
        ':prenom' => $prenom,
        ':telephone' => $telephone,
        ':date_adhesion' => $dateAdhesion
    ]);

    $nouvelIdMembre = $connexion->lastInsertId();

    $connexion->prepare("
        INSERT INTO historique_generation
            (id_membre, id_generation_origine, id_generation_destination, date_debut, motif)
        VALUES
            (:id_membre, NULL, :id_generation, :date_debut, 'Adhésion initiale')
    ")->execute([
        ':id_membre' => $nouvelIdMembre,
        ':id_generation' => $idGeneration,
        ':date_debut' => $dateAdhesion
    ]);

    $motDePasseTemporaire = genererMotDePasseTemporaire();

    $sqlUtilisateur = "
        INSERT INTO utilisateur (id_membre, id_role, email, mot_de_passe, code_personnel, statut_compte)
        VALUES (:id_membre, 5, :email, :mot_de_passe, NULL, 'ACTIF')
    ";

    $requeteUtilisateur = $connexion->prepare($sqlUtilisateur);

    $requeteUtilisateur->execute([
        ':id_membre' => $nouvelIdMembre,
        ':email' => $email,
        ':mot_de_passe' => password_hash($motDePasseTemporaire, PASSWORD_DEFAULT)
    ]);

    $nouvelIdUtilisateur = $connexion->lastInsertId();

    $connexion->prepare("
        INSERT INTO notification (id_utilisateur, type, message)
        VALUES (:id_utilisateur, 'COMPTE', :message)
    ")->execute([
        ':id_utilisateur' => $nouvelIdUtilisateur,
        ':message' => "Bienvenue à la Dahira Lansar Guidick, $prenom !"
    ]);

    $connexion->prepare("
        INSERT INTO journal_operation (id_utilisateur, type_action, objet)
        VALUES (:id_utilisateur, 'membre.creation', :objet)
    ")->execute([
        ':id_utilisateur' => $_SESSION['id_utilisateur'],
        ':objet' => "Nouveau membre $prenom $nom créé ($email)"
    ]);

    $connexion->commit();

} catch (PDOException $e) {

    $connexion->rollBack();

    $_SESSION['erreurs_membre'] = ["Cet email est déjà utilisé par un autre compte."];

    header("Location: formulaire.php?id_generation=" . $idGeneration);
    exit;
}

$_SESSION['identifiants_generes'] = [
    'nom' => "$prenom $nom",
    'email' => $email,
    'mot_de_passe' => $motDePasseTemporaire
];

header("Location: formulaire.php?id_generation=" . $idGeneration);
exit;