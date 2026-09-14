<?php

require_once "../auth/protection_role.php";

verifierRole([4]);

require_once "fonctions.php";
require_once "../membres/fonctions.php";

$idGeneration = (int) ($_GET['id_generation'] ?? $_SESSION['id_generation']);

assurerAccesGeneration($idGeneration);

$message = $_SESSION['message_depense'] ?? '';
unset($_SESSION['message_depense']);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Nouvelle dépense - Dahira Lansar Guidick</title>

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
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .conteneur {
            width: 100%;
            max-width: 420px;
            background: var(--blanc);
            border-radius: 14px;
            padding: 34px;
            box-shadow: 0 10px 30px rgba(6,48,26,0.10);
        }

        h1 {
            font-family: Georgia, serif;
            font-weight: normal;
            text-align: center;
            color: var(--vert-fonce);
            font-size: 1.4rem;
            margin-bottom: 22px;
        }

        .lien-retour {
            display: inline-block;
            margin-bottom: 22px;
            color: var(--vert-profond);
            text-decoration: none;
            font-size: 13.5px;
        }

        .lien-retour:hover {
            color: var(--vert-fonce);
            text-decoration: underline;
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

        .aide {
            font-size: 12px;
            color: #5c7568;
            margin-top: -12px;
            margin-bottom: 14px;
        }

        input, textarea {
            width: 100%;
            padding: 11px 13px;
            border: 1px solid #d7e6dc;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            background: var(--vert-clair);
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: var(--vert-profond);
            background: var(--blanc);
        }

        button {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 8px;
            background: var(--terracotta);
            color: var(--blanc);
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            opacity: 0.9;
        }

    </style>
    <link rel="stylesheet" href="../assets/css/theme.css">

</head>

<body>

<div class="conteneur">

    <a class="lien-retour" href="index.php?id_generation=<?= $idGeneration ?>">← Retour aux dépenses</a>

    <h1>Nouvelle dépense</h1>

    <?php if (!empty($message)): ?>

        <div class="message"><?= htmlspecialchars($message) ?></div>

    <?php endif; ?>

    <form action="traiter_depense.php" method="POST">

        <input type="hidden" name="id_generation" value="<?= $idGeneration ?>">

        <div class="champ">
            <label for="montant">Montant (FCFA)</label>
            <input type="number" id="montant" name="montant" min="1" step="1" required>
        </div>

        <div class="champ">
            <label for="motif">Motif</label>
            <textarea id="motif" name="motif" rows="3" placeholder="Ex : achat de matériel, transport..." required></textarea>
        </div>

        <div class="champ">
            <label for="justificatif">Référence du justificatif</label>
            <input type="text" id="justificatif" name="justificatif" placeholder="Ex : facture n°042, reçu boutique X" required>
        </div>
        <p class="aide">Obligatoire (RG24) — décris la pièce justificative conservée physiquement ou son numéro.</p>

        <button type="submit">Enregistrer la dépense</button>

    </form>

</div>

</body>

</html>