<?php

require_once "../auth/protection_role.php";

verifierRole([1, 2, 4]);

require_once "fonctions.php";
require_once "../membres/fonctions.php";
require_once "../cotisations/fonctions.php";


/*
|--------------------------------------------------------------------------
| Déterminer la génération à afficher
|--------------------------------------------------------------------------
*/

$generations = listerGenerations($connexion);

if ($_SESSION['id_role'] == 1) {

    $idGeneration = (int) ($_GET['id_generation'] ?? ($generations[0]['id_generation'] ?? 0));

} else {

    $idGeneration = (int) $_SESSION['id_generation'];
}

assurerAccesGeneration($idGeneration);

$generationActuelle = null;

foreach ($generations as $g) {
    if ($g['id_generation'] == $idGeneration) {
        $generationActuelle = $g;
    }
}

$campagne = recupererCampagneActive($connexion);

$stats = null;
$evolution = [];
$comparatif = [];
$globales = null;
$evolutionComparative = null;

if ($campagne) {

    $stats = statistiquesGeneration($connexion, $idGeneration, $campagne['id_campagne']);
    $evolution = evolutionMensuelle($connexion, $idGeneration, $campagne['id_campagne']);

    if ($_SESSION['id_role'] == 1) {

        $comparatif = comparatifGenerations($connexion, $campagne['id_campagne']);
        $globales = statistiquesGlobales($connexion, $campagne['id_campagne']);
        $evolutionComparative = evolutionComparativeGenerations($connexion, $campagne['id_campagne']);
    }
}

$tauxRecouvrement = ($stats && $stats['total_prevu'] > 0)
    ? round(($stats['total_paye'] / $stats['total_prevu']) * 100)
    : 0;


/*
|--------------------------------------------------------------------------
| Préparer les données pour Chart.js (graphique de la génération)
|--------------------------------------------------------------------------
*/

$labelsMois = array_map('libelleMoisFr', array_column($evolution, 'periode'));
$valeursPrevu = array_map('floatval', array_column($evolution, 'total_prevu'));
$valeursPaye = array_map('floatval', array_column($evolution, 'total_paye'));

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Statistiques - Dahira Lansar Guidick</title>

    <script src="../assets/js/chart.umd.min.js"></script>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --vert-profond: #0B6B3A;
            --vert-fonce: #06301A;
            --vert-clair: #E9F5EE;
            --vert-texte: #0F3D24;
            --blanc: #FFFFFF;
        }

        body {
            font-family: Arial, sans-serif;
            background: var(--vert-clair);
            color: var(--vert-texte);
            padding: 36px 20px;
        }

        .conteneur {
            max-width: 970px;
            margin: 0 auto;
            background: var(--blanc);
            border-radius: 14px;
            padding: 32px;
            box-shadow: 0 10px 30px rgba(6,48,26,0.10);
        }

        .lien-retour {
            display: inline-block;
            margin-bottom: 20px;
            color: var(--vert-profond);
            text-decoration: none;
            font-size: 13.5px;
        }

        h1 {
            font-family: Georgia, serif;
            font-weight: normal;
            color: var(--vert-fonce);
            font-size: 1.5rem;
            margin-bottom: 4px;
        }

        .sous-titre {
            color: #5c7568;
            margin-bottom: 20px;
            font-size: 13.5px;
        }

        .onglets {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .onglets a {
            padding: 6px 14px;
            border-radius: 99px;
            background: var(--vert-clair);
            color: var(--vert-fonce);
            text-decoration: none;
            font-size: 13px;
        }

        .onglets a.actif {
            background: var(--vert-profond);
            color: var(--blanc);
        }

        .cartes {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 14px;
            margin-bottom: 26px;
        }

        .carte {
            background: var(--vert-clair);
            border-radius: 10px;
            padding: 16px 18px;
        }

        .carte .label {
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #5c7568;
            margin-bottom: 4px;
        }

        .carte .valeur {
            font-size: 21px;
            font-weight: bold;
            color: var(--vert-fonce);
            font-family: Georgia, serif;
        }

        .section {
            margin-bottom: 34px;
        }

        .section h2 {
            font-family: Georgia, serif;
            font-weight: normal;
            font-size: 16px;
            color: var(--vert-fonce);
            margin-bottom: 14px;
        }

        .zone-graphique {
            background: var(--vert-clair);
            border-radius: 10px;
            padding: 20px;
            position: relative;
            height: 300px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            font-size: 12.5px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #5c7568;
            padding: 8px 10px;
            border-bottom: 2px solid var(--vert-clair);
        }

        td {
            padding: 13px 10px;
            border-bottom: 1px solid #eef4f0;
            font-size: 14.5px;
        }

        .vide {
            padding: 30px;
            text-align: center;
            color: #5c7568;
            font-family: Georgia, serif;
            font-style: italic;
        }

    </style>
    <link rel="stylesheet" href="../assets/css/theme.css">

</head>

<body>

<div class="conteneur">

    <a class="lien-retour" href="../index.php">← Retour</a>

    <h1>Statistiques — <?= htmlspecialchars($generationActuelle['nom'] ?? '') ?></h1>

    <p class="sous-titre">
        <?= $campagne ? htmlspecialchars($campagne['nom_edition']) : 'Aucune campagne active' ?>
    </p>

    <?php if ($_SESSION['id_role'] == 1): ?>

        <div class="onglets">

            <?php foreach ($generations as $g): ?>

                <a class="<?= $g['id_generation'] == $idGeneration ? 'actif' : '' ?>"
                   href="?id_generation=<?= $g['id_generation'] ?>">
                    <?= htmlspecialchars($g['nom']) ?>
                </a>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

    <?php if (!$campagne || !$stats): ?>

        <div class="vide">Aucune donnée disponible pour l'instant.</div>

    <?php else: ?>

        <?php if ($_SESSION['id_role'] == 1 && $globales): ?>

            <div class="section">

                <h2>Vue d'ensemble — toutes générations confondues</h2>

                <div class="cartes">

                    <div class="carte">
                        <div class="label">Membres actifs (total)</div>
                        <div class="valeur"><?= $globales['nombre_membres_total'] ?? 0 ?></div>
                    </div>

                    <div class="carte">
                        <div class="label">Total attendu (année)</div>
                        <div class="valeur"><?= number_format($globales['total_prevu'] ?? 0, 0, ',', ' ') ?> F</div>
                    </div>

                    <div class="carte">
                        <div class="label">Total collecté (année)</div>
                        <div class="valeur"><?= number_format($globales['total_paye'] ?? 0, 0, ',', ' ') ?> F</div>
                    </div>

                    <div class="carte">
                        <div class="label">Taux global</div>
                        <div class="valeur">
                            <?= ($globales['total_prevu'] ?? 0) > 0 ? round(($globales['total_paye'] / $globales['total_prevu']) * 100) : 0 ?>%
                        </div>
                    </div>

                </div>

                <?php if ($evolutionComparative && $evolutionComparative['periodes']): ?>

                    <div class="zone-graphique">
                        <canvas id="graphiqueComparatif"></canvas>
                    </div>

                <?php endif; ?>

            </div>

        <?php endif; ?>

        <div class="cartes">

            <div class="carte">
                <div class="label">Taux de recouvrement</div>
                <div class="valeur"><?= $tauxRecouvrement ?>%</div>
            </div>

            <div class="carte">
                <div class="label">Total attendu</div>
                <div class="valeur"><?= number_format($stats['total_prevu'] ?? 0, 0, ',', ' ') ?> F</div>
            </div>

            <div class="carte">
                <div class="label">Total collecté</div>
                <div class="valeur"><?= number_format($stats['total_paye'] ?? 0, 0, ',', ' ') ?> F</div>
            </div>

            <div class="carte">
                <div class="label">À jour / Partiel / Non payé</div>
                <div class="valeur" style="font-size:16px;">
                    <?= $stats['nombre_a_jour'] ?? 0 ?> / <?= $stats['nombre_partiel'] ?? 0 ?> / <?= $stats['nombre_non_paye'] ?? 0 ?>
                </div>
            </div>

        </div>

        <div class="section">

            <h2>Évolution mensuelle — <?= htmlspecialchars($generationActuelle['nom'] ?? '') ?></h2>

            <?php if (!$evolution): ?>

                <div class="vide">Aucune cotisation générée pour l'instant.</div>

            <?php else: ?>

                <div class="zone-graphique">
                    <canvas id="graphiqueGeneration"></canvas>
                </div>

            <?php endif; ?>

        </div>

        <?php if ($_SESSION['id_role'] == 1 && $comparatif): ?>

            <div class="section">

                <h2>Comparatif entre générations</h2>

                <table>

                    <thead>
                        <tr>
                            <th>Génération</th>
                            <th>Solde caisse</th>
                            <th>Attendu</th>
                            <th>Collecté</th>
                            <th>Taux</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($comparatif as $c):

                            $taux = $c['total_prevu'] > 0
                                ? round(($c['total_paye'] / $c['total_prevu']) * 100)
                                : 0;

                        ?>

                        <tr>
                            <td><?= htmlspecialchars($c['nom']) ?></td>
                            <td><?= number_format($c['solde_caisse'] ?? 0, 0, ',', ' ') ?> F</td>
                            <td><?= number_format($c['total_prevu'] ?? 0, 0, ',', ' ') ?> F</td>
                            <td><?= number_format($c['total_paye'] ?? 0, 0, ',', ' ') ?> F</td>
                            <td><?= $taux ?>%</td>
                        </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    <?php endif; ?>

</div>

<script>

    /*
    |------------------------------------------------------------------
    | Graphique : évolution de la génération affichée
    |------------------------------------------------------------------
    */

    <?php if ($evolution): ?>

    new Chart(document.getElementById('graphiqueGeneration'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($labelsMois) ?>,
            datasets: [
                {
                    label: 'Prévu',
                    data: <?= json_encode($valeursPrevu) ?>,
                    backgroundColor: '#cfe6da',
                    borderRadius: 4
                },
                {
                    label: 'Payé',
                    data: <?= json_encode($valeursPaye) ?>,
                    backgroundColor: '#0B6B3A',
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    <?php endif; ?>


    /*
    |------------------------------------------------------------------
    | Graphique : comparatif entre générations (admin uniquement)
    |------------------------------------------------------------------
    */

    <?php if ($evolutionComparative && $evolutionComparative['periodes']): ?>

    const couleursGenerations = ['#0B6B3A', '#8a6d1a', '#3d7a9e', '#7a1f1f', '#5b3a8a'];

    new Chart(document.getElementById('graphiqueComparatif'), {
        type: 'line',
        data: {
            labels: <?= json_encode(array_map('libelleMoisFr', $evolutionComparative['periodes'])) ?>,
            datasets: [
                <?php foreach ($evolutionComparative['series'] as $index => $serie): ?>
                {
                    label: <?= json_encode($serie['nom']) ?>,
                    data: <?= json_encode($serie['valeurs']) ?>,
                    borderColor: couleursGenerations[<?= $index ?> % couleursGenerations.length],
                    backgroundColor: couleursGenerations[<?= $index ?> % couleursGenerations.length],
                    tension: 0.3,
                    fill: false
                },
                <?php endforeach; ?>
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    <?php endif; ?>

</script>

</body>

</html>