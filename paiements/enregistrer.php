<?php

require_once "../auth/protection_role.php";

verifierRole([4]);

require_once "../cotisations/fonctions.php";
require_once "../membres/fonctions.php";

$idMembreChoisi = (int) ($_GET['id_membre'] ?? 0);

$sqlMembres = "
    SELECT id_membre, nom, prenom

    FROM membre

    WHERE id_generation = :id_generation
      AND statut = 'ACTIF'

    ORDER BY nom, prenom
";

$requeteMembres = $connexion->prepare($sqlMembres);
$requeteMembres->execute([':id_generation' => $_SESSION['id_generation']]);
$membres = $requeteMembres->fetchAll();

$cotisations = [];
$membreChoisi = null;

if ($idMembreChoisi) {

    foreach ($membres as $m) {
        if ($m['id_membre'] == $idMembreChoisi) {
            $membreChoisi = $m;
        }
    }

    if (!$membreChoisi) {
        die("Ce membre n'appartient pas à ta génération.");
    }

    genererCotisationsManquantes($connexion, $idMembreChoisi, $_SESSION['id_generation']);

    $campagne = recupererCampagneActive($connexion);

    if ($campagne) {

        $sqlCot = "
            SELECT *

            FROM cotisation

            WHERE id_membre = :id_membre
              AND id_campagne = :id_campagne
              AND reste > 0

            ORDER BY periode ASC
        ";

        $requeteCot = $connexion->prepare($sqlCot);

        $requeteCot->execute([
            ':id_membre' => $idMembreChoisi,
            ':id_campagne' => $campagne['id_campagne']
        ]);

        $cotisations = $requeteCot->fetchAll();
    }
}

$erreurs = $_SESSION['erreurs_enregistrement'] ?? [];
unset($_SESSION['erreurs_enregistrement']);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Enregistrer un versement - Dahira Lansar Guidick</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f2f4f7;
            padding: 48px 20px;
        }

        .conteneur {
            max-width: 560px;
            margin: 0 auto;
            background: white;
            padding: 36px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .lien-retour {
            display: inline-block;
            margin-bottom: 28px;
            color: #0B6B3A;
            text-decoration: none;
            font-size: 14px;
        }

        .lien-retour:hover {
            color: #06301A;
            text-decoration: underline;
        }

        .en-tete {
            padding-bottom: 24px;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 24px;
        }

        .en-tete h1 {
            color: #1f2937;
            margin-bottom: 8px;
            font-size: 28px;
        }

        .sous-titre {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.5;
        }

        .message {
            background: #fee2e2;
            color: #991b1b;
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            line-height: 1.45;
        }

        .bloc-formulaire {
            padding: 22px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .bloc-formulaire:last-child {
            margin-bottom: 0;
        }

        .bloc-titre {
            color: #0F3D24;
            font-size: 17px;
            margin-bottom: 18px;
        }

        .champ {
            margin-bottom: 18px;
        }

        .champ:last-child {
            margin-bottom: 0;
        }

        .champ label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 14px;
            color: #374151;
        }

        select,
        input {
            width: 100%;
            padding: 12px 13px;
            border: 1px solid #d1d5db;
            border-radius: 7px;
            font-size: 15px;
        }

        .actions {
            padding-top: 4px;
        }

        button {
            width: 100%;
            padding: 13px 16px;
            border: none;
            border-radius: 7px;
            background: #2563eb;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .vide {
            padding: 24px 18px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            line-height: 1.5;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
        }

        @media (max-width: 640px) {
            body {
                padding-top: 24px;
                padding-bottom: 24px;
            }

            .conteneur {
                padding: 24px 18px;
            }

            .bloc-formulaire {
                padding: 18px;
            }
        }

    </style>
    <link rel="stylesheet" href="../assets/css/theme.css">

</head>

<body>

<div class="conteneur">

    <a class="lien-retour" href="index.php">&larr; Retour aux paiements</a>

    <header class="en-tete">
        <h1>Enregistrer un versement</h1>
        <p class="sous-titre">Sélectionnez un membre, puis renseignez les détails du paiement.</p>
    </header>

    <?php foreach ($erreurs as $erreur): ?>
        <div class="message"><?= htmlspecialchars($erreur) ?></div>
    <?php endforeach; ?>

    <form method="get" class="bloc-formulaire formulaire-selection">
        <h2 class="bloc-titre">1. Choisir le membre</h2>

        <div class="champ">
            <label for="id_membre">Membre</label>
            <select id="id_membre" name="id_membre" onchange="this.form.submit()">
                <option value="">— Sélectionner un membre —</option>
                <?php foreach ($membres as $m): ?>
                    <option value="<?= $m['id_membre'] ?>" <?= $m['id_membre'] == $idMembreChoisi ? 'selected' : '' ?>>
                        <?= htmlspecialchars($m['prenom'] . ' ' . $m['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <?php if ($idMembreChoisi && !$cotisations): ?>

        <div class="vide">
            Aucune cotisation avec un reste à payer pour ce membre.
        </div>

    <?php elseif ($cotisations): ?>

        <form action="traiter_enregistrement.php" method="POST" class="bloc-formulaire formulaire-versement">

            <h2 class="bloc-titre">2. Détails du versement</h2>

            <input type="hidden" name="id_membre" value="<?= $idMembreChoisi ?>">

            <div class="champ">
                <label for="id_cotisation">Cotisation concernée</label>
                <select id="id_cotisation" name="id_cotisation" required>
                    <?php foreach ($cotisations as $c): ?>
                        <option value="<?= $c['id_cotisation'] ?>">
                            <?= htmlspecialchars(libelleMoisFr($c['periode'])) ?> — reste <?= number_format($c['reste'], 0, ',', ' ') ?> FCFA
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="champ">
                <label for="montant">Montant reçu (FCFA)</label>
                <input type="number" id="montant" name="montant" min="1" step="1" required>
            </div>

            <div class="champ">
                <label for="mode_paiement">Moyen de paiement</label>
                <select id="mode_paiement" name="mode_paiement" required>
                    <option value="ESPECES">Espèces</option>
                    <option value="WAVE">Wave</option>
                    <option value="ORANGE_MONEY">Orange Money</option>
                    <option value="AUTRE">Autre</option>
                </select>
            </div>

            <div class="champ">
                <label for="reference_transaction">Référence (facultatif pour les espèces)</label>
                <input type="text" id="reference_transaction" name="reference_transaction" placeholder="Laisse vide si espèces">
            </div>

            <div class="actions">
                <button type="submit">Enregistrer le versement</button>
            </div>

        </form>

    <?php endif; ?>

</div>

</body>

</html>