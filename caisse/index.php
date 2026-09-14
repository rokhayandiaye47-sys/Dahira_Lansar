<?php

require_once "../auth/protection_role.php";

verifierRole([1, 2, 4]);

require_once "fonctions.php";
require_once "../membres/fonctions.php";


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

$caisse = recupererCaisse($connexion, $idGeneration);

$mouvements = $caisse ? listerMouvements($connexion, $caisse['id_caisse']) : [];

$peutGerer = ($_SESSION['id_role'] == 4);

$message = $_SESSION['message_caisse'] ?? '';
unset($_SESSION['message_caisse']);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Caisse - Dahira Lansar Guidick</title>

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
            margin-bottom: 4px;
        }

        h1 {
            font-family: Georgia, serif;
            font-weight: normal;
            color: var(--vert-fonce);
            font-size: 1.5rem;
        }

        .actions {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }

        .bouton {
            display: inline-block;
            padding: 9px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }

        .bouton-entree {
            background: var(--vert-profond);
            color: var(--blanc);
        }

        .bouton-entree:hover {
            background: var(--vert-fonce);
        }

        .bouton-depense {
            background: var(--terracotta);
            color: var(--blanc);
        }

        .sous-titre {
            color: #5c7568;
            margin-bottom: 20px;
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

        .carte-solde {
            background: var(--vert-fonce);
            color: var(--blanc);
            border-radius: 12px;
            padding: 22px 24px;
            margin-bottom: 26px;
        }

        .carte-solde .label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--vert-clair);
            opacity: 0.85;
            margin-bottom: 6px;
        }

        .carte-solde .valeur {
            font-family: Georgia, serif;
            font-size: 30px;
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

        .montant-entree { color: var(--vert-profond); font-weight: bold; }
        .montant-depense { color: var(--terracotta); font-weight: bold; }

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

        .pied {
            margin-top: 18px;
            text-align: right;
        }

        .pied a {
            color: var(--vert-profond);
            text-decoration: none;
            font-size: 13px;
        }

    </style>
    <link rel="stylesheet" href="../assets/css/theme.css">

</head>

<body>

<div class="conteneur">

    <a class="lien-retour" href="../index.php">← Retour</a>

    <div class="entete">

        <h1>Caisse — <?= htmlspecialchars($generationActuelle['nom'] ?? '') ?></h1>

        <?php if ($peutGerer): ?>

            <div class="actions">
                <a class="bouton bouton-entree" href="entree.php?id_generation=<?= $idGeneration ?>">+ Entrée</a>
                <a class="bouton bouton-depense" href="../depenses/depense.php?id_generation=<?= $idGeneration ?>">+ Dépense</a>
            </div>

        <?php endif; ?>

    </div>


    <?php if (!empty($message)): ?>

        <div class="message">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>

    <?php if ($_SESSION['id_role'] == 1): ?>

        <div class="onglets">

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

        </div>

    <?php endif; ?>

    <div class="carte-solde">
        <div class="label">Solde actuel</div>
        <div class="valeur"><?= number_format($caisse['solde'] ?? 0, 0, ',', ' ') ?> FCFA</div>
    </div>

    <?php if (!$mouvements): ?>

        <div class="vide">
            Aucun mouvement enregistré pour cette caisse.
        </div>

    <?php else: ?>

        <table>

            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Motif</th>
                    <th>Montant</th>
                    <th>Auteur</th>
                    <th>Statut</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($mouvements as $m): ?>

                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($m['date_mouvement'])) ?></td>
                    <td><?= $m['nature'] === 'ENTREE' ? 'Entrée' : 'Dépense' ?></td>
                    <td><?= htmlspecialchars($m['motif']) ?></td>
                    <td class="<?= $m['nature'] === 'ENTREE' ? 'montant-entree' : 'montant-depense' ?>">
                        <?= $m['nature'] === 'ENTREE' ? '+' : '−' ?><?= number_format($m['montant'], 0, ',', ' ') ?> FCFA
                    </td>
                    <td><?= htmlspecialchars($m['auteur_email']) ?></td>
                    <td>
                        <?php if ($m['nature'] === 'DEPENSE'): ?>
                            <span class="badge <?= $m['statut'] === 'ACTIVE' ? 'badge-active' : 'badge-annulee' ?>">
                                <?= $m['statut'] === 'ACTIVE' ? 'Active' : 'Annulée' ?>
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

        <div class="pied">
            <a href="../depenses/index.php?id_generation=<?= $idGeneration ?>">Voir toutes les dépenses (annulation) →</a>
            &nbsp;·&nbsp;
            <a href="../paiements/index.php?id_generation=<?= $idGeneration ?>">Voir tous les paiements →</a>
        </div>

    <?php endif; ?>

</div>

</body>

</html>