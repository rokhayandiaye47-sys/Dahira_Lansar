<?php

require_once "../auth/protection_role.php";
verifierRole([1]);
require_once "fonctions.php";

$campagnes = listerCampagnes($connexion);
$campagneActive = recupererCampagneActiveGlobale($connexion);
$message = $_SESSION['message_campagnes'] ?? '';
unset($_SESSION['message_campagnes']);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campagnes Gamou - Dahira Lansar Guidick</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --vert-profond: #0B6B3A;
            --vert-fonce: #06301A;
            --vert-clair: #E9F5EE;
            --vert-texte: #0F3D24;
            --blanc: #FFFFFF;
            --terracotta: #7a1f1f;
        }
        body {
            font-family: Arial, sans-serif;
            background: var(--vert-clair);
            color: var(--vert-texte);
            padding: 36px 20px;
        }
        .conteneur {
            max-width: 720px;
            margin: 0 auto;
            padding: 32px;
            background: var(--blanc);
            border-top: 4px solid var(--vert-profond);
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(6,48,26,0.10);
        }
        .lien-retour {
            display: inline-block;
            margin-bottom: 22px;
            color: var(--vert-profond);
            text-decoration: none;
            font-size: 13.5px;
        }
        .entete {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 22px;
            flex-wrap: wrap;
        }
        h1 {
            color: var(--vert-fonce);
            font-family: Georgia, serif;
            font-size: 1.5rem;
            font-weight: normal;
        }
        .message {
            margin-bottom: 20px;
            padding: 12px 14px;
            color: var(--vert-fonce);
            background: var(--vert-clair);
            border: 1px solid #cfe6da;
            border-radius: 8px;
            font-size: 13.5px;
        }
        .bouton, .bouton-cloturer {
            display: inline-block;
            padding: 9px 16px;
            color: var(--blanc);
            background: var(--vert-profond);
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: bold;
            cursor: pointer;
        }
        .bouton:hover, .bouton-cloturer:hover { background: var(--vert-fonce); }
        .carte-active {
            margin-bottom: 28px;
            padding: 22px 24px;
            color: var(--blanc);
            background: var(--vert-fonce);
            border-radius: 12px;
        }
        .carte-active .etiquette {
            margin-bottom: 8px;
            color: var(--vert-clair);
            font-size: 11px;
            letter-spacing: .06em;
            opacity: .85;
            text-transform: uppercase;
        }
        .carte-active h2 { margin-bottom: 8px; font-family: Georgia, serif; font-size: 19px; font-weight: normal; }
        .carte-active p { color: var(--vert-clair); font-size: 13.5px; opacity: .9; }
        .bouton-cloturer { margin-top: 14px; }
        .liste { display: flex; flex-direction: column; gap: 10px; margin-top: 14px; }
        .ligne {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 12px 14px;
            background: var(--vert-clair);
            border-radius: 8px;
            font-size: 14px;
        }
        .badge {
            display: inline-block;
            padding: 3px 10px;
            color: #5c7568;
            background: var(--blanc);
            border-radius: 99px;
            font-size: 11.5px;
            font-weight: bold;
            white-space: nowrap;
        }
        .vide { padding: 40px; color: #5c7568; text-align: center; font-family: Georgia, serif; font-style: italic; }
        .sous-titre-liste { margin: 0; color: var(--vert-fonce); font-family: Georgia, serif; font-size: 16px; }
        @media (max-width: 560px) {
            body { padding: 20px 14px; }
            .conteneur { padding: 24px 18px; }
            .ligne { align-items: flex-start; flex-direction: column; }
        }
    </style>
    <link rel="stylesheet" href="../assets/css/theme.css">
</head>
<body>
<div class="conteneur">
    <a class="lien-retour" href="../index.php">&larr; Retour</a>
    <div class="entete">
        <h1>Campagnes Gamou</h1>
        <?php if (!$campagneActive): ?>
            <a class="bouton" href="creer.php">+ Nouvelle campagne</a>
        <?php endif; ?>
    </div>
    <?php if (!empty($message)): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($campagneActive): ?>
        <div class="carte-active">
            <div class="etiquette">Campagne active</div>
            <h2><?= htmlspecialchars($campagneActive['nom_edition']) ?></h2>
            <p>Debutee le <?= date('d/m/Y', strtotime($campagneActive['date_debut'] ?? 'now')) ?></p>
            <a class="bouton-cloturer" href="cloturer.php?id_campagne=<?= $campagneActive['id_campagne'] ?>" onclick="return confirm('Cloturer cette campagne ? Aucune nouvelle cotisation ne sera generee pour elle apres cloture.');">Cloturer la campagne</a>
        </div>
    <?php else: ?>
        <div class="vide">Aucune campagne active pour l'instant - cree-en une nouvelle.</div>
    <?php endif; ?>
    <?php $campagnesPassees = array_filter($campagnes, fn($campagne) => $campagne['statut'] === 'CLOTUREE'); ?>
    <?php if ($campagnesPassees): ?>
        <div class="sous-titre-liste">Campagnes cloturees</div>
        <div class="liste">
            <?php foreach ($campagnesPassees as $campagne): ?>
                <div class="ligne">
                    <span>
                        <?= htmlspecialchars($campagne['nom_edition']) ?>
                        <?php if ($campagne['date_fin']): ?>
                            - cloturee le <?= date('d/m/Y', strtotime($campagne['date_fin'])) ?>
                        <?php endif; ?>
                    </span>
                    <span class="badge">Cloturee</span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
