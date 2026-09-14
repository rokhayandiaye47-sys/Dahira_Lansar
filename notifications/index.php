<?php

require_once "../auth/protection_role.php";

verifierRole([1, 2, 3, 4, 5]);

require_once "fonctions.php";

$notifications = listerNotifications($connexion, $_SESSION['id_utilisateur']);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Notifications - Dahira Lansar Guidick</title>

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
            max-width: 640px;
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
            align-items: center;
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

        .actions-entete {
            display: flex;
            gap: 14px;
            align-items: center;
        }

        .bouton-lien {
            color: var(--vert-profond);
            text-decoration: none;
            font-size: 13px;
            background: none;
            border: none;
            cursor: pointer;
        }

        .liste {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .notif {
            padding: 15px 17px;
            border-radius: 8px;
            background: var(--vert-clair);
            border-left: 3px solid #d7e6dc;
        }

        .notif.non-lue {
            background: #fdf6df;
            border-left-color: #8a6d1a;
        }

        .notif-entete {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .type {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--vert-profond);
            font-weight: bold;
        }

        .notif.non-lue .type {
            color: #8a6d1a;
        }

        .date {
            font-size: 12px;
            color: #8a9c92;
        }

        .texte {
            font-size: 14px;
            color: var(--vert-texte);
        }

        .action {
            margin-top: 8px;
            text-align: right;
        }

        .action a {
            font-size: 12px;
            color: var(--vert-profond);
            text-decoration: none;
        }

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
        <h1>Notifications</h1>

        <div class="actions-entete">
            <a class="bouton-lien" href="../paiements/mes_paiements.php">Mes paiements &amp; reçus</a>
            <?php if ($notifications): ?>
                <a class="bouton-lien" href="tout_marquer_lu.php">Tout marquer comme lu</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$notifications): ?>

        <div class="vide">
            Aucune notification pour l'instant.
        </div>

    <?php else: ?>

        <div class="liste">

            <?php foreach ($notifications as $n): ?>

                <div class="notif <?= !$n['lu'] ? 'non-lue' : '' ?>">

                    <div class="notif-entete">
                        <span class="type"><?= htmlspecialchars(libelleTypeNotification($n['type'])) ?></span>
                        <span class="date"><?= date('d/m/Y H:i', strtotime($n['date_creation'])) ?></span>
                    </div>

                    <div class="texte">
                        <?= htmlspecialchars($n['message']) ?>
                    </div>

                    <div class="action">
                        <?php if (!empty($n['id_paiement'])): ?>
                            <a href="../paiements/recu.php?id_paiement=<?= $n['id_paiement'] ?>" target="_blank">
                                Voir le reçu
                            </a>
                            &nbsp;·&nbsp;
                        <?php endif; ?>
                        <?php if (!$n['lu']): ?>
                            <a href="marquer_lu.php?id_notification=<?= $n['id_notification'] ?>">
                                Marquer comme lue
                            </a>
                        <?php endif; ?>
                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>

</body>

</html>