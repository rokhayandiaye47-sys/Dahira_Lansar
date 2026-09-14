<?php

require_once "../auth/protection_role.php";

verifierRole([1,2,3]);

require_once "fonctions.php";

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

$membres = listerMembresGeneration($connexion, $idGeneration);

$message = $_SESSION['message_membres'] ?? '';
unset($_SESSION['message_membres']);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Membres - Dahira Lansar Guidick</title>

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
            margin-bottom: 22px;
            flex-wrap: wrap;
            gap: 10px;
        }

        h1 {
            font-family: Georgia, serif;
            font-weight: normal;
            color: var(--vert-fonce);
            font-size: 1.5rem;
        }

        .bouton {
            display: inline-block;
            padding: 10px 18px;
            background: var(--vert-profond);
            color: var(--blanc);
            border-radius: 8px;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }

        .bouton:hover {
            background: var(--vert-fonce);
        }

        .bouton-sm {
            padding: 6px 12px;
            font-size: 12px;
            font-weight: normal;
        }

        .bouton-gris {
            background: var(--vert-clair);
            color: var(--vert-fonce);
            border: 1px solid #d7e6dc;
        }

        .bouton-gris:hover {
            background: #ddeee3;
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

        td.actions {
            min-width: 330px;
            padding: 10px;
            white-space: nowrap;
        }

        .actions-conteneur {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 8px;
            flex-wrap: nowrap;
        }

        .actions-conteneur .bouton {
            flex: 0 0 auto;
            white-space: nowrap;
        }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 99px;
            font-size: 11.5px;
            font-weight: bold;
        }

        .badge-actif { color: var(--vert-fonce); background: var(--vert-clair); }
        .badge-inactif { color: var(--terracotta); background: var(--terracotta-clair); }
        .badge-sans-compte { color: #5c7568; background: #f2f5f3; }

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

    <div class="entete">

        <h1>Membres — <?= htmlspecialchars($generationActuelle['nom'] ?? '') ?></h1>

        <a class="bouton" href="formulaire.php?id_generation=<?= $idGeneration ?>">
            + Nouveau membre
        </a>

    </div>

    <?php if (!empty($message)): ?>

        <div class="message">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>

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

    <?php if (!$membres): ?>

        <div class="vide">
            Aucun membre dans cette génération pour l'instant.
        </div>

    <?php else: ?>

        <table>

            <thead>
                <tr>
                    <th>Nom &amp; prénom</th>
                    <th>Téléphone</th>
                    <th>Compte</th>
                    <th>Rôle</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($membres as $m): ?>

                <tr>
                    <td><?= htmlspecialchars($m['prenom'] . ' ' . $m['nom']) ?></td>
                    <td><?= htmlspecialchars($m['telephone'] ?: '—') ?></td>
                    <td>
                        <?php if ($m['email']): ?>
                            <span class="badge badge-actif">Compte créé</span>
                        <?php else: ?>
                            <span class="badge badge-sans-compte">Sans compte</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($m['nom_role'] ?? '—') ?></td>
                    <td>
                        <span class="badge <?= $m['statut'] === 'ACTIF' ? 'badge-actif' : 'badge-inactif' ?>">
                            <?= $m['statut'] === 'ACTIF' ? 'Actif' : 'Inactif' ?>
                        </span>
                    </td>
                 <td class="actions">
    <div class="actions-conteneur">
    <a
        class="bouton bouton-sm bouton-gris"
        href="formulaire.php?id_membre=<?= $m['id_membre'] ?>"
    >
        Modifier
    </a>

    <a
        class="bouton bouton-sm bouton-gris"
        href="desactiver.php?id_membre=<?= $m['id_membre'] ?>"
        onclick="return confirm('Confirmer le changement de statut ?');"
    >
        <?= $m['statut'] === 'ACTIF' ? 'Désactiver' : 'Réactiver' ?>
    </a>

    <?php if ($_SESSION['id_role'] == 1): ?>
        <a
            class="bouton bouton-sm bouton-gris"
            href="transferer.php?id_membre=<?= $m['id_membre'] ?>"
        >
            Transférer
        </a>
    <?php endif; ?>
    </div>
</td>
                </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>

</div>

</body>

</html>