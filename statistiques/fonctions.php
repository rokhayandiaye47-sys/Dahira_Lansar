<?php

require_once __DIR__ . "/../config/database.php";


/*
|--------------------------------------------------------------------------
| Statistiques globales d'une génération pour une campagne
|--------------------------------------------------------------------------
*/

function statistiquesGeneration($connexion, $idGeneration, $idCampagne)
{
    $sql = "
        SELECT
            COALESCE(SUM(montant_prevu), 0) AS total_prevu,
            COALESCE(SUM(montant_paye), 0) AS total_paye,
            COUNT(DISTINCT id_membre) AS nombre_membres_avec_cotisation,
            SUM(CASE WHEN statut = 'PAYE' THEN 1 ELSE 0 END) AS nombre_a_jour,
            SUM(CASE WHEN statut = 'PARTIEL' THEN 1 ELSE 0 END) AS nombre_partiel,
            SUM(CASE WHEN statut = 'NON_PAYE' THEN 1 ELSE 0 END) AS nombre_non_paye

        FROM cotisation

        WHERE id_generation = :id_generation
          AND id_campagne = :id_campagne
    ";

    $requete = $connexion->prepare($sql);

    $requete->execute([
        ':id_generation' => $idGeneration,
        ':id_campagne' => $idCampagne
    ]);

    return $requete->fetch();
}


/*
|--------------------------------------------------------------------------
| Évolution mensuelle des cotisations (prévu/payé par mois)
|--------------------------------------------------------------------------
*/

function evolutionMensuelle($connexion, $idGeneration, $idCampagne)
{
    $sql = "
        SELECT
            periode,
            COALESCE(SUM(montant_prevu), 0) AS total_prevu,
            COALESCE(SUM(montant_paye), 0) AS total_paye

        FROM cotisation

        WHERE id_generation = :id_generation
          AND id_campagne = :id_campagne

        GROUP BY periode

        ORDER BY periode ASC
    ";

    $requete = $connexion->prepare($sql);

    $requete->execute([
        ':id_generation' => $idGeneration,
        ':id_campagne' => $idCampagne
    ]);

    return $requete->fetchAll();
}


/*
|--------------------------------------------------------------------------
| Comparatif entre toutes les générations (réservé à l'admin général)
|--------------------------------------------------------------------------
*/

function comparatifGenerations($connexion, $idCampagne)
{
    $sql = "
        SELECT
            g.id_generation,
            g.nom,
            COALESCE(c.solde, 0) AS solde_caisse,
            COALESCE(SUM(cot.montant_prevu), 0) AS total_prevu,
            COALESCE(SUM(cot.montant_paye), 0) AS total_paye

        FROM generation g

        LEFT JOIN caisse c ON c.id_generation = g.id_generation

        LEFT JOIN cotisation cot
            ON cot.id_generation = g.id_generation
           AND cot.id_campagne = :id_campagne

        GROUP BY g.id_generation, g.nom, c.solde

        ORDER BY g.nom
    ";

    $requete = $connexion->prepare($sql);
    $requete->execute([':id_campagne' => $idCampagne]);

    return $requete->fetchAll();
}


/*
|--------------------------------------------------------------------------
| Statistiques globales (toutes générations confondues) pour une campagne
|--------------------------------------------------------------------------
*/

function statistiquesGlobales($connexion, $idCampagne) {
    $sql = "SELECT 
                COUNT(DISTINCT c.id_membre) AS nombre_membres_total,
                SUM(c.montant_prevu) AS total_prevu,
                SUM(c.montant_paye) AS total_paye
            FROM cotisation c
            WHERE c.id_campagne = ?";
    
    $stmt = $connexion->prepare($sql);
    $stmt->execute([$idCampagne]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


/*
|--------------------------------------------------------------------------
| Évolution comparative entre générations pour le graphique linéaire
|--------------------------------------------------------------------------
*/

function evolutionComparativeGenerations($connexion, $idCampagne) {
    // Récupérer toutes les périodes distinctes de la campagne
    $sqlPeriodes = "SELECT DISTINCT periode FROM cotisation WHERE id_campagne = ? ORDER BY periode ASC";
    $stmt = $connexion->prepare($sqlPeriodes);
    $stmt->execute([$idCampagne]);
    $periodes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($periodes)) {
        return null;
    }

    // Récupérer les générations
    $sqlGen = "SELECT id_generation, nom FROM generation ORDER BY nom ASC";
    $generations = $connexion->query($sqlGen)->fetchAll(PDO::FETCH_ASSOC);

    $series = [];
    foreach ($generations as $gen) {
        $valeurs = [];
        foreach ($periodes as $periode) {
            $sqlSum = "SELECT COALESCE(SUM(montant_paye), 0) 
                       FROM cotisation 
                       WHERE id_generation = ? AND id_campagne = ? AND periode = ?";
            $stmtSum = $connexion->prepare($sqlSum);
            $stmtSum->execute([$gen['id_generation'], $idCampagne, $periode]);
            $valeurs[] = (float) $stmtSum->fetchColumn();
        }
        $series[] = [
            'nom' => $gen['nom'],
            'valeurs' => $valeurs
        ];
    }

    return [
        'periodes' => $periodes,
        'series' => $series
    ];
}