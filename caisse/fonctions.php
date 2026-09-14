<?php

require_once __DIR__ . "/../config/database.php";


/*
|--------------------------------------------------------------------------
| Récupérer la caisse d'une génération
|--------------------------------------------------------------------------
*/

function recupererCaisse($connexion, $idGeneration)
{
    $sql = "
        SELECT *

        FROM caisse

        WHERE id_generation = :id_generation

        LIMIT 1
    ";

    $requete = $connexion->prepare($sql);
    $requete->execute([':id_generation' => $idGeneration]);

    return $requete->fetch();
}


/*
|--------------------------------------------------------------------------
| Lister les entrées et dépenses d'une caisse, fusionnées et triées
| par date décroissante (pour l'affichage du registre)
|--------------------------------------------------------------------------
*/

function listerMouvements($connexion, $idCaisse, $limite = 200)
{
    $sql = "
        (
            SELECT
                'ENTREE' AS nature,
                e.id_entree AS id_mouvement,
                e.montant,
                e.motif,
                e.type,
                e.date_entree AS date_mouvement,
                NULL AS statut,
                u.email AS auteur_email

            FROM entree e

            INNER JOIN utilisateur u ON u.id_utilisateur = e.id_utilisateur

            WHERE e.id_caisse = :id_caisse_1
        )

        UNION ALL

        (
            SELECT
                'DEPENSE' AS nature,
                d.id_depense AS id_mouvement,
                d.montant,
                d.motif,
                NULL AS type,
                d.date_depense AS date_mouvement,
                d.statut,
                u.email AS auteur_email

            FROM depense d

            INNER JOIN utilisateur u ON u.id_utilisateur = d.id_utilisateur

            WHERE d.id_caisse = :id_caisse_2
        )

        ORDER BY date_mouvement DESC

        LIMIT $limite
    ";

    $requete = $connexion->prepare($sql);

    $requete->execute([
        ':id_caisse_1' => $idCaisse,
        ':id_caisse_2' => $idCaisse
    ]);

    return $requete->fetchAll();
}