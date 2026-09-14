<?php
 
require_once "../auth/protection_role.php";
 
verifierRole([1, 2, 4]);
 
require_once "fonctions.php";
 
 
/*
|--------------------------------------------------------------------------
| Déterminer la génération à afficher
|--------------------------------------------------------------------------
*/
 
$membreConnecte = recupererMembre($connexion, $_SESSION['id_membre']);
 
if ($_SESSION['id_role'] == 1) {
 
    $idGeneration = (int) ($_GET['id_generation'] ?? $membreConnecte['id_generation']);
 
} else {
 
    $idGeneration = (int) $membreConnecte['id_generation'];
}
 
$generations = $connexion->query("SELECT * FROM generation ORDER BY nom")->fetchAll();
 
$generationActuelle = null;
 
foreach ($generations as $g) {
    if ($g['id_generation'] == $idGeneration) {
        $generationActuelle = $g;
    }
}
 
 
/*
|--------------------------------------------------------------------------
| Générer les cotisations manquantes de tous les membres actifs
|--------------------------------------------------------------------------
*/
 
$campagne = recupererCampagneActive($connexion);
 
if ($campagne) {
 
    $sqlMembres = "
        SELECT id_membre
 
        FROM membre
 
        WHERE id_generation = :id_generation
          AND statut = 'ACTIF'
    ";
 
    $requeteMembres = $connexion->prepare($sqlMembres);
    $requeteMembres->execute([':id_generation' => $idGeneration]);
 
    foreach ($requeteMembres->fetchAll() as $m) {
        genererCotisationsManquantes($connexion, $m['id_membre'], $idGeneration);
    }
}
 
 
/*
|--------------------------------------------------------------------------
| Récupérer la situation de chaque membre pour la campagne active
|--------------------------------------------------------------------------
*/
 
$situations = [];
 
if ($campagne) {
 
    $sql = "
        SELECT
            m.id_membre,
            m.nom,
            m.prenom,
            COALESCE(SUM(c.montant_prevu), 0) AS total_prevu,
            COALESCE(SUM(c.montant_paye), 0) AS total_paye
 
        FROM membre m
 
        LEFT JOIN cotisation c
            ON c.id_membre = m.id_membre
           AND c.id_campagne = :id_campagne
 
        WHERE m.id_generation = :id_generation
          AND m.statut = 'ACTIF'
 
        GROUP BY m.id_membre, m.nom, m.prenom
 
        ORDER BY m.nom, m.prenom
    ";
 
    $requete = $connexion->prepare($sql);
 
    $requete->execute([
        ':id_campagne' => $campagne['id_campagne'],
        ':id_generation' => $idGeneration
    ]);
 
    $situations = $requete->fetchAll();
}
 
 
/*
|--------------------------------------------------------------------------
| Totaux de la génération
|--------------------------------------------------------------------------
*/
 
$totalPrevu = 0;
$totalPaye = 0;
$nombreAJour = 0;
$nombreEnRetard = 0;
 
foreach ($situations as $s) {
 
    $totalPrevu += $s['total_prevu'];
    $totalPaye += $s['total_paye'];
 
    if ($s['total_prevu'] > 0 && $s['total_paye'] >= $s['total_prevu']) {
        $nombreAJour++;
    } elseif ($s['total_prevu'] > 0) {
        $nombreEnRetard++;
    }
}
 
?>
 
<!DOCTYPE html>
<html lang="fr">
 
<head>
 
    <meta charset="UTF-8">
 
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
 
    <title>Cotisations de la génération - Dahira Lansar Guidick</title>
 
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
            max-width: 940px;
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
            margin-bottom: 22px;
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
 
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 99px;
            font-size: 11.5px;
            font-weight: bold;
        }
 
        .vide {
            padding: 40px;
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
 
    <h1>Cotisations — <?= htmlspecialchars($generationActuelle['nom'] ?? '') ?></h1>
 
    <p class="sous-titre">
        <?= $campagne ? htmlspecialchars($campagne['nom_edition']) : 'Aucune campagne active' ?>
    </p>
 
    <?php if ($_SESSION['id_role'] == 1): ?>
 
        <div class="onglets">
 
            <?php foreach ($generations as $g): ?>
 
                <a
                    class="<?= $g['id_generation'] == $idGeneration ? 'actif' : '' ?>"
                    href="?id_generation=<?= $g['id_generation'] ?>"
                >
                    <?= htmlspecialchars($g['nom']) ?>
                </a>
 
            <?php endforeach; ?>
 
        </div>
 
    <?php endif; ?>
 
    <div class="cartes">
 
        <div class="carte">
            <div class="label">Membres</div>
            <div class="valeur"><?= count($situations) ?></div>
        </div>
 
        <div class="carte">
            <div class="label">Total attendu</div>
            <div class="valeur"><?= number_format($totalPrevu, 0, ',', ' ') ?> F</div>
        </div>
 
        <div class="carte">
            <div class="label">Total collecté</div>
            <div class="valeur"><?= number_format($totalPaye, 0, ',', ' ') ?> F</div>
        </div>
 
        <div class="carte">
            <div class="label">À jour / En retard</div>
            <div class="valeur"><?= $nombreAJour ?> / <?= $nombreEnRetard ?></div>
        </div>
 
    </div>
 
    <?php if (!$situations): ?>
 
        <div class="vide">
            Aucun membre actif dans cette génération.
        </div>
 
    <?php else: ?>
 
        <table>
 
            <thead>
                <tr>
                    <th>Membre</th>
                    <th>Attendu</th>
                    <th>Payé</th>
                    <th>Reste</th>
                    <th>État</th>
                </tr>
            </thead>
 
            <tbody>
 
                <?php foreach ($situations as $s):
 
                    $reste = $s['total_prevu'] - $s['total_paye'];
 
                    if ($s['total_prevu'] <= 0) {
                        $etatLabel = 'Sans cotisation';
                        $etatCouleur = '#5c7568';
                        $etatFond = '#f2f5f3';
                    } elseif ($s['total_paye'] >= $s['total_prevu']) {
                        $etatLabel = 'À jour';
                        $etatCouleur = '#0B6B3A';
                        $etatFond = '#E9F5EE';
                    } elseif ($s['total_paye'] > 0) {
                        $etatLabel = 'Partiel';
                        $etatCouleur = '#8a6d1a';
                        $etatFond = '#fdf6df';
                    } else {
                        $etatLabel = 'Non payé';
                        $etatCouleur = '#7a1f1f';
                        $etatFond = '#fdecec';
                    }
 
                ?>
 
                <tr>
                    <td><?= htmlspecialchars($s['prenom'] . ' ' . $s['nom']) ?></td>
                    <td><?= number_format($s['total_prevu'], 0, ',', ' ') ?> F</td>
                    <td><?= number_format($s['total_paye'], 0, ',', ' ') ?> F</td>
                    <td><?= number_format(max(0, $reste), 0, ',', ' ') ?> F</td>
                    <td>
                        <span class="badge" style="color: <?= $etatCouleur ?>; background: <?= $etatFond ?>;">
                            <?= $etatLabel ?>
                        </span>
                    </td>
                </tr>
 
                <?php endforeach; ?>
 
            </tbody>
 
        </table>
 
    <?php endif; ?>
 
</div>
 
</body>
 
</html>
 











