<?php

require_once "../auth/protection_role.php";

verifierRole([1]);

require_once "fonctions.php";
require_once "../membres/fonctions.php";

$comptes = listerComptesResponsabilite($connexion);
$libellesRoles = libellesRolesResponsabilite();

$message = $_SESSION['message_utilisateurs'] ?? '';
unset($_SESSION['message_utilisateurs']);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Comptes à responsabilité - Dahira Lansar Guidick</title>

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
            max-width: 900px;
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
            margin-bottom: 20px;
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
            padding: 9px 16px;
            background: var(--vert-profond);
            color: var(--blanc);
            border-radius: 8px;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: bold;
        }

        .bouton:hover {
            background: var(--vert-fonce);
        }

        .bouton-sm {
            padding: 5px 10px;
            font-size: 12px;
            background: var(--vert-clair);
            color: var(--vert-fonce);
            border: 1px solid #d7e6dc;
            border-radius: 6px;
            text-decoration: none;
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

        .badge-actif { color: var(--vert-fonce); background: var(--vert-clair); }
        .badge-inactif { color: var(--terracotta); background: var(--terracotta-clair); }

        .vide {
            padding: 40px;
            text-align: center;
            color: #5c7568;
            font-family: Georgia, serif;
            font-style: italic;
        }

    </style>

</head>

<body>

<div class="conteneur">

    <a class="lien-retour" href="../index.php">← Retour</a>

    <div class="entete">

        <h1>Comptes à responsabilité</h1>

        <a class="bouton" href="creer.php">+ Nouveau compte</a>

    </div>

    <?php if (!empty($message)): ?>

        <div class="message"><?= htmlspecialchars($message) ?></div>

    <?php endif; ?>

    <?php if (!$comptes): ?>

        <div class="vide">Aucun compte à responsabilité pour l'instant.</div>

    <?php else: ?>

        <table>

            <thead>
                <tr>
                    <th>Nom &amp; prénom</th>
                    <th>Rôle</th>
                    <th>Génération</th>
                    <th>Email</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($comptes as $c): ?>

                <tr>
                    <td><?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?></td>
                    <td><?= htmlspecialchars($libellesRoles[$c['id_role']] ?? '—') ?></td>
                    <td><?= htmlspecialchars($c['nom_generation']) ?></td>
                    <td><?= htmlspecialchars($c['email']) ?></td>
                    <td>
                        <span class="badge <?= $c['statut_compte'] === 'ACTIF' ? 'badge-actif' : 'badge-inactif' ?>">
                            <?= $c['statut_compte'] === 'ACTIF' ? 'Actif' : 'Inactif' ?>
                        </span>
                    </td>
                    <td>
                        <?php if ((int) $c['id_utilisateur'] !== (int) $_SESSION['id_utilisateur']): ?>
                            <a
                                class="bouton-sm"
                                href="desactiver.php?id_utilisateur=<?= $c['id_utilisateur'] ?>"
                                onclick="return confirm('Confirmer le changement de statut ?');"
                            >
                                <?= $c['statut_compte'] === 'ACTIF' ? 'Désactiver' : 'Réactiver' ?>
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