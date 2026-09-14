<?php

require_once __DIR__ . "/../config/database.php";


/*
|--------------------------------------------------------------------------
| Génération de référence pour l'utilisateur connecté
| (celle du membre auquel son compte est rattaché)
|--------------------------------------------------------------------------
*/

function recupererGenerationConnecte($connexion, $idMembre)
{
    $sql = "
        SELECT id_generation

        FROM membre

        WHERE id_membre = :id_membre
    ";

    $requete = $connexion->prepare($sql);
    $requete->execute([':id_membre' => $idMembre]);

    return (int) $requete->fetchColumn();
}


/*
|--------------------------------------------------------------------------
| RG03 : cloisonnement par génération — un non-admin ne peut agir que
| sur les membres de sa propre génération
|--------------------------------------------------------------------------
*/

function assurerAccesGeneration($idGenerationCible)
{
    if ($_SESSION['id_role'] == 1) {
        return; // admin général : accès à tout
    }

    if ((int) $idGenerationCible !== (int) $_SESSION['id_generation']) {

        http_response_code(403);

        die("Accès refusé : ce membre n'appartient pas à ta génération.");
    }
}


/*
|--------------------------------------------------------------------------
| Générer un mot de passe temporaire lisible
|--------------------------------------------------------------------------
*/

function genererMotDePasseTemporaire()
{
    return substr(bin2hex(random_bytes(4)), 0, 8);
}


/*
|--------------------------------------------------------------------------
| Générer un code personnel unique (2e facteur, rôles à responsabilité)
|--------------------------------------------------------------------------
*/

function genererCodePersonnel()
{
    return 'DLG-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
}


/*
|--------------------------------------------------------------------------
| Récupérer toutes les générations
|--------------------------------------------------------------------------
*/

function listerGenerations($connexion)
{
    return $connexion->query("SELECT * FROM generation ORDER BY nom")->fetchAll();
}


/*
|--------------------------------------------------------------------------
| Récupérer un membre (+ son compte utilisateur s'il existe)
|--------------------------------------------------------------------------
*/

function recupererMembreAvecCompte($connexion, $idMembre)
{
    $sql = "
        SELECT
            m.*,
            u.id_utilisateur,
            u.email,
            u.id_role,
            u.statut_compte,

            r.nom_role

        FROM membre m

        LEFT JOIN utilisateur u ON u.id_membre = m.id_membre

        LEFT JOIN role r ON r.id_role = u.id_role

        WHERE m.id_membre = :id_membre

        LIMIT 1
    ";

    $requete = $connexion->prepare($sql);
    $requete->execute([':id_membre' => $idMembre]);

    return $requete->fetch();
}


/*
|--------------------------------------------------------------------------
| Lister les membres d'une génération (+ leur compte s'il existe)
|--------------------------------------------------------------------------
*/

function listerMembresGeneration($connexion, $idGeneration)
{
    $sql = "
        SELECT
            m.*,
            u.email,
            u.statut_compte,

            r.nom_role

        FROM membre m

        LEFT JOIN utilisateur u ON u.id_membre = m.id_membre

        LEFT JOIN role r ON r.id_role = u.id_role

        WHERE m.id_generation = :id_generation

        ORDER BY m.nom, m.prenom
    ";

    $requete = $connexion->prepare($sql);
    $requete->execute([':id_generation' => $idGeneration]);

    return $requete->fetchAll();
}