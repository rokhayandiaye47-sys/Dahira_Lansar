<?php

require_once "../auth/protection_role.php";

verifierRole([1, 2]);

require_once "fonctions.php";
require_once "../membres/fonctions.php";
require_once "../cotisations/fonctions.php";

$idGeneration = (int) ($_GET['id_generation'] ?? $_SESSION['id_generation']);

assurerAccesGeneration($idGeneration);

$generationActuelle = recupererGeneration($connexion, $idGeneration);

$campagne = recupererCampagneActive($connexion);

if (!$campagne) {

    die("Aucune campagne active — impossible de définir une règle de cotisation.");
}

$regle = recupererRegleCotisation($connexion, $idGeneration, $campagne['id_campagne']);

$dejaDemarree = desCotisationsExistentDeja($connexion, $idGeneration, $campagne['id_campagne']);

$erreurs = $_SESSION['erreurs_regle'] ?? [];
unset($_SESSION['erreurs_regle']);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Règle de cotisation - Dahira Lansar Guidick</title>

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
            --or: #8a6d1a;
            --or-clair: #fdf6df;
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
            max-width: 480px;
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
            font-size: 1.4rem;
            margin-bottom: 4px;
        }

        .sous-titre {
            color: #5c7568;
            margin-bottom: 20px;
            font-size: 13.5px;
        }

        .avertissement {
            background: var(--or-clair);
            color: var(--or);
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .message {
            background: var(--terracotta-clair);
            color: var(--terracotta);
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 13.5px;
        }

        .champ {
            margin-bottom: 16px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
            font-size: 13.5px;
        }

        input {
            width: 100%;
            padding: 11px 13px;
            border: 1px solid #d7e6dc;
            border-radius: 8px;
            font-size: 15px;
            background: var(--vert-clair);
        }

        input:focus {
            outline: none;
            border-color: var(--vert-profond);
            background: var(--blanc);
        }

        button {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 8px;
            background: var(--vert-profond);
            color: var(--blanc);
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: var(--vert-fonce);
        }

    </style>
    <link rel="stylesheet" href="../assets/css/theme.css">

</head>

<body>

<div class="conteneur">

    <a class="lien-retour" href="index.php?id_generation=<?= $idGeneration ?>">← Retour</a>

    <h1>Règle de cotisation</h1>

    <p class="sous-titre">
        <?= htmlspecialchars($generationActuelle['nom']) ?> — <?= htmlspecialchars($campagne['nom_edition']) ?>
    </p>

    <?php if ($dejaDemarree): ?>

        <div class="avertissement">
            Des cotisations ont déjà été générées pour cette campagne (RG14) — le montant reste modifiable
            mais ne s'appliquera qu'aux prochains mois, pas aux cotisations déjà émises.
        </div>

    <?php endif; ?>

    <?php foreach ($erreurs as $erreur): ?>

        <div class="message"><?= htmlspecialchars($erreur) ?></div>

    <?php endforeach; ?>

    <form action="traiter_regle.php" method="POST">

        <input type="hidden" name="id_generation" value="<?= $idGeneration ?>">
        <input type="hidden" name="id_campagne" value="<?= $campagne['id_campagne'] ?>">

        <div class="champ">
            <label for="montant">Montant mensuel (FCFA)</label>
            <input type="number" id="montant" name="montant" min="1" step="1"
                   value="<?= htmlspecialchars($regle['montant'] ?? '') ?>" required>
        </div>

        <div class="champ">
            <label for="jour_echeance">Jour d'échéance dans le mois</label>
            <input type="number" id="jour_echeance" name="jour_echeance" min="1" max="28"
                   value="<?= htmlspecialchars($regle['jour_echeance'] ?? '5') ?>">
        </div>

        <button type="submit">
            <?= $regle ? 'Mettre à jour la règle' : 'Créer la règle' ?>
        </button>

    </form>

</div>

</body>

</html>