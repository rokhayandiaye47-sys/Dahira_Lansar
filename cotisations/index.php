<?php
 
require_once "../auth/protection_role.php";
 
verifierRole([1, 2, 3, 4, 5]);
 
require_once "fonctions.php";
 
$membre = recupererMembre($connexion, $_SESSION['id_membre']);
 
genererCotisationsManquantes($connexion, $membre['id_membre'], $membre['id_generation']);
 
$campagne = recupererCampagneActive($connexion);
 
$cotisations = [];
 
if ($campagne) {
 
    $sql = "
        SELECT *
 
        FROM cotisation
 
        WHERE id_membre = :id_membre
          AND id_campagne = :id_campagne
 
        ORDER BY periode ASC
    ";
 
    $requete = $connexion->prepare($sql);
 
    $requete->execute([
        ':id_membre' => $membre['id_membre'],
        ':id_campagne' => $campagne['id_campagne']
    ]);
 
    $cotisations = $requete->fetchAll();
}
 
$libellesEtat = [
    'PAYE'     => ['Payé', '#0B6B3A', '#E9F5EE'],
    'PARTIEL'  => ['Partiel', '#8a6d1a', '#fdf6df'],
    'NON_PAYE' => ['Non payé', '#7a1f1f', '#fdecec'],
];
 
?>
 
<!DOCTYPE html>
<html lang="fr">
 
<head>
 
    <meta charset="UTF-8">
 
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
 
    <title>Mes cotisations - Dahira Lansar Guidick</title>
 
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
            max-width: 780px;
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
            margin-bottom: 24px;
            font-size: 13.5px;
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
 
        .lien-retour-liste {
            display: inline-block;
            margin-bottom: 20px;
        }
 
        .bouton {
            display: inline-block;
            padding: 6px 12px;
            background: var(--vert-profond);
            color: var(--blanc);
            border-radius: 6px;
            text-decoration: none;
            font-size: 12.5px;
            font-weight: bold;
        }
 
        .bouton:hover {
            background: var(--vert-fonce);
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
 
    <h1>Mes cotisations</h1>
 
    <p class="sous-titre">
        <?= htmlspecialchars($membre['prenom'] . ' ' . $membre['nom']) ?>
        &middot;
        <?= $campagne ? htmlspecialchars($campagne['nom_edition']) : 'Aucune campagne active' ?>
    </p>
 
    <?php if (!$cotisations): ?>
 
        <div class="vide">
            Aucune cotisation trouvée pour la campagne active.
        </div>
 
    <?php else: ?>
 
        <table>
 
            <thead>
                <tr>
                    <th>Mois</th>
                    <th>Prévu</th>
                    <th>Payé</th>
                    <th>Reste</th>
                    <th>État</th>
                    <th></th>
                </tr>
            </thead>
 
            <tbody>
 
                <?php foreach ($cotisations as $c):
 
                    $etat = $libellesEtat[$c['statut']];
 
                ?>
 
                <tr>
                    <td><?= htmlspecialchars(libelleMoisFr($c['periode'])) ?></td>
                    <td><?= number_format($c['montant_prevu'], 0, ',', ' ') ?> FCFA</td>
                    <td><?= number_format($c['montant_paye'], 0, ',', ' ') ?> FCFA</td>
                    <td><?= number_format($c['reste'], 0, ',', ' ') ?> FCFA</td>
                    <td>
                        <span class="badge" style="color: <?= $etat[1] ?>; background: <?= $etat[2] ?>;">
                            <?= $etat[0] ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($c['reste'] > 0): ?>
                            <a class="bouton" href="../paiements/paiement.php?id_cotisation=<?= $c['id_cotisation'] ?>">
                                Payer
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
 
                <?php endforeach; ?>
 
            </tbody>
 
        </table>
 
    <?php endif; ?>
 
</div>
 
</body>
 
</html>
 