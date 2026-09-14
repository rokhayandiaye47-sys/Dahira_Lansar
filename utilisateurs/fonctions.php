<?php

require_once __DIR__ . "/../config/database.php";


/*
|--------------------------------------------------------------------------
| Lister tous les comptes à responsabilité (hors Membre simple)
|--------------------------------------------------------------------------
*/

function listerComptesResponsabilite($connexion)
{
    $sql = "
        SELECT
            u.id_utilisateur,
            u.email,
            u.statut_compte,
            u.id_role,

            m.id_membre,
            m.nom,
            m.prenom,

            r.nom_role,

            g.nom AS nom_generation

        FROM utilisateur u

        INNER JOIN membre m ON m.id_membre = u.id_membre

        INNER JOIN role r ON r.id_role = u.id_role

        INNER JOIN generation g ON g.id_generation = m.id_generation

        WHERE u.id_role IN (1, 2, 3, 4)

        ORDER BY FIELD(u.id_role, 1, 2, 3, 4), g.nom, m.nom
    ";

    return $connexion->query($sql)->fetchAll();
}


/*
|--------------------------------------------------------------------------
| Libellés des rôles à responsabilité (Membre simple exclu, géré ailleurs)
|--------------------------------------------------------------------------
*/

function libellesRolesResponsabilite()
{
    return [
        1 => 'Administrateur général',
        2 => 'Responsable de génération',
        3 => 'Gestionnaire des membres',
        4 => 'Trésorier',
    ];
}