<?php

require_once __DIR__ . "/../config/database.php";


/*
|--------------------------------------------------------------------------
| Lister les notifications d'un utilisateur (les plus récentes d'abord)
|--------------------------------------------------------------------------
*/

function listerNotifications($connexion, $idUtilisateur, $limite = 100)
{
    $sql = "
        SELECT *

        FROM notification

        WHERE id_utilisateur = :id_utilisateur

        ORDER BY date_creation DESC

        LIMIT $limite
    ";

    $requete = $connexion->prepare($sql);
    $requete->execute([':id_utilisateur' => $idUtilisateur]);

    return $requete->fetchAll();
}


/*
|--------------------------------------------------------------------------
| Compter les notifications non lues d'un utilisateur
|--------------------------------------------------------------------------
*/

function compterNotificationsNonLues($connexion, $idUtilisateur)
{
    $sql = "
        SELECT COUNT(*)

        FROM notification

        WHERE id_utilisateur = :id_utilisateur
          AND lu = 0
    ";

    $requete = $connexion->prepare($sql);
    $requete->execute([':id_utilisateur' => $idUtilisateur]);

    return (int) $requete->fetchColumn();
}


/*
|--------------------------------------------------------------------------
| Libellés d'affichage par type de notification
|--------------------------------------------------------------------------
*/

function libelleTypeNotification($type)
{
    $libelles = [
        'PAIEMENT'  => 'Paiement',
        'COMPTE'    => 'Compte',
        'RAPPEL'    => 'Rappel',
        'VALIDATION'=> 'Validation',
    ];

    return $libelles[$type] ?? $type;
}