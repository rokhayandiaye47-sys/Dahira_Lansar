<?php

require_once "../auth/protection_role.php";

verifierRole([1, 2, 4]);

require_once "fonctions.php";
require_once "../membres/fonctions.php";

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

$depenses = listerDepensesGeneration($connexion, $idGeneration);

$peutGerer = ($_SESSION['id_role'] == 4);

$message = $_SESSION['message_depense_liste'] ?? '';
unset($_SESSION['message_depense_liste']);

$totalActif = 0;

foreach ($depenses as $d) {
    if ($d['statut'] === 'ACTIVE') {
        $totalActif += $d['montant'];
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Dépenses - Dahira Lansar Guidick</title>

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
            --terracotta: #7a1f1f;
            --terracotta-clair: #fdecec;
        }

        body {
            font-family: Arial, sans-serif;
            background: var(--vert-clair);
            color: var(--vert-texte);
            padding: 36px 20px;
        }

        .conteneur {
            max-width: 920px;
            margin: 0 auto;
            background: var(--blanc);
            border-radius: 14px;
            padding: 32px;
            box-shadow: 0 10px 30px rgba(6,48,26,0.10);
            border-top: 4px solid var(--vert-profond);
        }

        .lien-retour {
            display: inline-block;
            margin-bottom: 20px;
            color: var(--vert-profond);
            text-decoration: none;
            font-size: 13.5px;
        }

        .entete {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 22px;
        }

        h1 {
            font-family: Georgia, serif;
            font-weight: normal;
            color: var(--vert-fonce);
            font-size: 1.5rem;
        }

        .bouton {
            display: inline-block;
            padding: 9px 16px;
            background: var(--vert-profond);
            color: var(--blanc);
            border-radius: 8px;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }

        .bouton-sm {
            padding: 5px 10px;
            font-size: 12px;
            background: var(--terracotta-clair) !important;
            color: var(--terracotta) !important;
        }

        .sous-titre {
            color: #5c7568;
            margin: 4px 0 20px;
            font-size: 13.5px;
        }

        .message {
            background: var(--vert-clair);
            color: var(--vert-fonce);
            border: 1px solid #cfe6da;
            padding: 12px 14px;
            border-radius: 8px;
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

        .carte-total {
            background: var(--vert-clair);
            border-left: 4px solid var(--vert-profond);
            border-radius: 10px;
            padding: 16px 18px;
            margin: 0 0 26px;
        }

        .carte-total .label {
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #5c7568;
            margin-bottom: 4px;
        }

        .carte-total .valeur {
            font-family: Georgia, serif;
            font-size: 22px;
            font-weight: bold;
            color: var(--vert-profond);
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

        .badge-active { color: var(--vert-fonce); background: var(--vert-clair); }
        .badge-annulee { color: var(--terracotta); background: var(--terracotta-clair); }

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

    <a class="lien-retour" href="../caisse/index.php?id_generation=<?= $idGeneration ?>">← Retour à la caisse</a>

    <div class="entete">

        <h1>Dépenses — <?= htmlspecialchars($generationActuelle['nom'] ?? '') ?></h1>

        <?php if ($peutGerer): ?>
            <a class="bouton" href="depense.php?id_generation=<?= $idGeneration ?>">+ Nouvelle dépense</a>
        <?php endif; ?>

    </div>


    <?php if (!empty($message)): ?>

        <div class="message"><?= htmlspecialchars($message) ?></div>

    <?php endif; ?>

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

    <div class="carte-total">
        <div class="label">Total dépensé (dépenses actives)</div>
        <div class="valeur"><?= number_format($totalActif, 0, ',', ' ') ?> FCFA</div>
    </div>

    <?php if (!$depenses): ?>

        <div class="vide">
            Aucune dépense enregistrée pour cette génération.
        </div>

    <?php else: ?>

        <table>

            <thead>
                <tr>
                    <th>Date</th>
                    <th>Motif</th>
                    <th>Justificatif</th>
                    <th>Montant</th>
                    <th>Auteur</th>
                    <th>Statut</th>
                    <?php if ($peutGerer): ?><th></th><?php endif; ?>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($depenses as $d): ?>

                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($d['date_depense'])) ?></td>
                    <td><?= htmlspecialchars($d['motif']) ?></td>
                    <td><?= htmlspecialchars($d['justificatif']) ?></td>
                    <td>−<?= number_format($d['montant'], 0, ',', ' ') ?> FCFA</td>
                    <td><?= htmlspecialchars($d['auteur_email']) ?></td>
                    <td>
                        <span class="badge <?= $d['statut'] === 'ACTIVE' ? 'badge-active' : 'badge-annulee' ?>">
                            <?= $d['statut'] === 'ACTIVE' ? 'Active' : 'Annulée' ?>
                        </span>
                    </td>
                   <?php if ($peutGerer): ?>
        <td>
            <?php if ($d['statut'] === 'ACTIVE'): ?>
                <a class="bouton bouton-sm"
                   href="annuler.php?id_depense=<?= $d['id_depense'] ?>"
                   onclick="return confirm('Annuler cette dépense ? Le montant sera recrédité à la caisse.');">
                    Annuler
                </a>
            <?php endif; ?>
        </td>
    <?php endif; ?>
                </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>

</div>

</body>

</html>