<?php

require_once "../auth/protection_role.php";

verifierRole([1,2,3,4,5]);

require_once "fonctions.php";

$idMembre = (int) ($_SESSION['id_membre'] ?? 0);
$membre = $idMembre > 0
    ? recupererMembreAvecCompte($connexion, $idMembre)
    : false;

if (!$membre) {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Espace personnel - Dahira Lansar Guidick</title>
        <link rel="stylesheet" href="../assets/css/theme.css">
        <style>
            body { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
            .message-espace { max-width: 500px; padding: 34px; background: var(--blanc); border-top: 4px solid var(--vert-profond); border-radius: 12px; box-shadow: var(--ombre-verte); text-align: center; }
            .message-espace h1 { margin-bottom: 14px; }
            .message-espace p { margin-bottom: 20px; color: var(--vert-texte); line-height: 1.6; }
            .message-espace a { display: inline-block; padding: 11px 18px; background: var(--vert-profond); color: var(--blanc); border-radius: 8px; text-decoration: none; }
        </style>
    </head>
    <body>
        <div class="message-espace">
            <h1>Espace personnel indisponible</h1>
            <p>Ce compte trésorier n'est pas encore rattaché à une fiche membre.</p>
            <a href="../index.php">Retour à l'accueil</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$generations = listerGenerations($connexion);

$nomGeneration = '';

foreach ($generations as $g) {
    if ($g['id_generation'] == $membre['id_generation']) {
        $nomGeneration = $g['nom'];
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Mon espace - Dahira Lansar Guidick</title>

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
            max-width: 500px;
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

        .fiche {
            background: var(--vert-clair);
            border-radius: 10px;
            padding: 18px 20px;
            margin-bottom: 22px;
        }

        .ligne {
            display: flex;
            justify-content: space-between;
            padding: 9px 0;
            border-bottom: 1px solid #d7e6dc;
            font-size: 14px;
        }

        .ligne:last-child {
            border-bottom: none;
        }

        .ligne .label {
            color: #5c7568;
        }

        .ligne .valeur {
            font-weight: bold;
            color: var(--vert-fonce);
        }

        .bouton {
            display: block;
            text-align: center;
            padding: 13px;
            background: var(--vert-profond);
            color: var(--blanc);
            border-radius: 8px;
            text-decoration: none;
            font-size: 15px;
            font-weight: bold;
        }

        .bouton:hover {
            background: var(--vert-fonce);
        }

    </style>
    <link rel="stylesheet" href="../assets/css/theme.css">

</head>

<body>

<div class="conteneur">

    <a class="lien-retour" href="../index.php">← Retour</a>

    <h1><?= htmlspecialchars($membre['prenom'] . ' ' . $membre['nom']) ?></h1>


    <div class="fiche">

        <div class="ligne">
            <span class="label">Génération</span>
            <span class="valeur"><?= htmlspecialchars($nomGeneration) ?></span>
        </div>

        <div class="ligne">
            <span class="label">Téléphone</span>
            <span class="valeur"><?= htmlspecialchars($membre['telephone'] ?: '—') ?></span>
        </div>

        <div class="ligne">
            <span class="label">Email</span>
            <span class="valeur"><?= htmlspecialchars($membre['email'] ?: '—') ?></span>
        </div>

        <div class="ligne">
            <span class="label">Membre depuis le</span>
            <span class="valeur"><?= date('d/m/Y', strtotime($membre['date_adhesion'])) ?></span>
        </div>

        <div class="ligne">
            <span class="label">Statut</span>
            <span class="valeur"><?= $membre['statut'] === 'ACTIF' ? 'Actif' : 'Inactif' ?></span>
        </div>

    </div>

    <a class="bouton" href="../cotisations/index.php">
        Voir mes cotisations
    </a>
    <a class="bouton" href="../paiements/mes_paiements.php" style="margin-top:10px;">
        Voir mes paiements & reçus
    </a>
</div>

</body>

</html>