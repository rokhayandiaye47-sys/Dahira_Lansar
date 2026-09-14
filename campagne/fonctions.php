<?php

require_once __DIR__ . "/../config/database.php";


/*
|--------------------------------------------------------------------------
| Lister toutes les campagnes (active en premier, puis les plus
| récemment clôturées) — jamais supprimées (§4)
|--------------------------------------------------------------------------
*/

function listerCampagnes($connexion)
{
    $sql = "
        SELECT *

        FROM campagne_gamou

        ORDER BY
            CASE WHEN statut = 'ACTIVE' THEN 0 ELSE 1 END,
            id_campagne DESC
    ";

    return $connexion->query($sql)->fetchAll();
}


/*
|--------------------------------------------------------------------------
| Récupérer la campagne active (une seule à la fois)
|--------------------------------------------------------------------------
*/

function recupererCampagneActiveGlobale($connexion)
{
    $sql = "
        SELECT *

        FROM campagne_gamou

        WHERE statut = 'ACTIVE'

        LIMIT 1
    ";

    return $connexion->query($sql)->fetch();
}