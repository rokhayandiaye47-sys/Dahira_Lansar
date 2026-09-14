<?php

require_once __DIR__ . "/../config/database.php";


/*
|--------------------------------------------------------------------------
| Récupérer une génération par son id
|--------------------------------------------------------------------------
*/

function recupererGeneration($connexion, $idGeneration)
{
    $sql = "
        SELECT *

        FROM generation

        WHERE id_generation = :id_generation

        LIMIT 1
    ";

    $requete = $connexion->prepare($sql);
    $requete->execute([':id_generation' => $idGeneration]);

    return $requete->fetch();
}


/*
|--------------------------------------------------------------------------
| Compter les membres actifs d'une génération
|--------------------------------------------------------------------------
*/

function compterMembresActifs($connexion, $idGeneration)
{
    $sql = "
        SELECT COUNT(*)

        FROM membre

        WHERE id_generation = :id_generation
          AND statut = 'ACTIF'
    ";

    $requete = $connexion->prepare($sql);
    $requete->execute([':id_generation' => $idGeneration]);

    return (int) $requete->fetchColumn();
}


/*
|--------------------------------------------------------------------------
| Vérifier si des cotisations existent déjà pour cette génération et
| cette campagne (sert à figer le montant une fois la collecte lancée
| — RG14)
|--------------------------------------------------------------------------
*/

function desCotisationsExistentDeja($connexion, $idGeneration, $idCampagne)
{
    $sql = "
        SELECT COUNT(*)

        FROM cotisation

        WHERE id_generation = :id_generation
          AND id_campagne = :id_campagne
    ";

    $requete = $connexion->prepare($sql);

    $requete->execute([
        ':id_generation' => $idGeneration,
        ':id_campagne' => $idCampagne
    ]);

    return ((int) $requete->fetchColumn()) > 0;
}