<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../caisse/fonctions.php";


/*
|--------------------------------------------------------------------------
| Lister les dépenses d'une génération (les plus récentes d'abord)
|--------------------------------------------------------------------------
*/

function listerDepensesGeneration($connexion, $idGeneration)
{
    $sql = "
        SELECT
            d.*,
            u.email AS auteur_email

        FROM depense d

        INNER JOIN caisse c ON c.id_caisse = d.id_caisse

        INNER JOIN utilisateur u ON u.id_utilisateur = d.id_utilisateur

        WHERE c.id_generation = :id_generation

        ORDER BY d.date_depense DESC

        LIMIT 200
    ";

    $requete = $connexion->prepare($sql);
    $requete->execute([':id_generation' => $idGeneration]);

    return $requete->fetchAll();
}


/*
|--------------------------------------------------------------------------
| Récupérer une dépense avec la génération de sa caisse
|--------------------------------------------------------------------------
*/

function recupererDepense($connexion, $idDepense)
{
    $sql = "
        SELECT
            d.*,
            c.id_generation

        FROM depense d

        INNER JOIN caisse c ON c.id_caisse = d.id_caisse

        WHERE d.id_depense = :id_depense

        LIMIT 1
    ";

    $requete = $connexion->prepare($sql);
    $requete->execute([':id_depense' => $idDepense]);

    return $requete->fetch();
}