<?php

require_once "config/database.php";

function creerCompte(
    $connexion,
    $idMembre,
    $idRole,
    $email,
    $motDePasse,
    $codePersonnel = null
) {
    $motDePasseHash = password_hash(
        $motDePasse,
        PASSWORD_DEFAULT
    );

    /*
    |----------------------------------------------------------------------
    | Seuls les rôles à responsabilité (Admin général, Responsable de
    | génération, Gestionnaire des membres, Trésorier) reçoivent un code
    | personnel. Un simple Membre (rôle 5) n'en a pas.
    |----------------------------------------------------------------------
    */

    $codePersonnelHash = $codePersonnel !== null
        ? password_hash($codePersonnel, PASSWORD_DEFAULT)
        : null;

    $sql = "INSERT INTO utilisateur
            (
                id_membre,
                id_role,
                email,
                mot_de_passe,
                code_personnel,
                statut_compte
            )
            VALUES
            (
                :id_membre,
                :id_role,
                :email,
                :mot_de_passe,
                :code_personnel,
                'ACTIF'
            )";

    $requete = $connexion->prepare($sql);

    $requete->execute([
        ':id_membre' => $idMembre,
        ':id_role' => $idRole,
        ':email' => $email,
        ':mot_de_passe' => $motDePasseHash,
        ':code_personnel' => $codePersonnelHash
    ]);
}


/*
|--------------------------------------------------------------------------
| COMPTES DE TEST
|--------------------------------------------------------------------------
*/

/*
 * 1 - Mamadou DIOP
 * Role : MEMBRE (pas de code personnel)
 */
creerCompte(
    $connexion,
    1,
    5,
    'mamadou.diop@test.com',
    'Mamadou123!'
);


/*
 * 2 - Fatou NDIAYE
 * Role : GESTIONNAIRE_MEMBRES
 */
creerCompte(
    $connexion,
    2,
    3,
    'fatou.ndiaye@test.com',
    'Fatou123!',
    'DLG-FATOU-002'
);


/*
 * 3 - Abdou SECK
 * Role : TRESORIER
 */
creerCompte(
    $connexion,
    3,
    4,
    'abdou.seck@test.com',
    'Abdou123!',
    'DLG-ABDOU-003'
);


/*
 * 4 - Moussa FALL
 * Role : RESPONSABLE_GENERATION
 */
creerCompte(
    $connexion,
    4,
    2,
    'moussa.fall@test.com',
    'Moussa123!',
    'DLG-MOUSSA-004'
);


/*
 * 5 - Ibrahima BA
 * Role : MEMBRE (pas de code personnel)
 */
creerCompte(
    $connexion,
    5,
    5,
    'ibrahima.ba@test.com',
    'Ibrahima123!'
);


/*
 * 6 - Oumar GUEYE
 * Role : MEMBRE (pas de code personnel)
 */
creerCompte(
    $connexion,
    6,
    5,
    'oumar.gueye@test.com',
    'Oumar123!'
);


/*
 * 7 - Administrateur général
 */
creerCompte(
    $connexion,
    7,
    1,
    'admin@dahira.test',
    'Admin123!',
    'DLG-ADMIN-007'
);


echo "Les comptes de test ont été créés avec succès.";
