<?php

require_once "../auth/protection_role.php";

verifierRole([1, 2, 3, 4, 5]);

require_once "../cotisations/fonctions.php";

$idCotisation = (int) ($_GET['id_cotisation'] ?? 0);


/*
|--------------------------------------------------------------------------
| Récupérer la cotisation et vérifier qu'elle appartient bien au membre
| connecté (RG08 : le membre consulte/paie ses propres informations)
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT *

    FROM cotisation

    WHERE id_cotisation = :id_cotisation
      AND id_membre = :id_membre

    LIMIT 1
";

$requete = $connexion->prepare($sql);

$requete->execute([
    ':id_cotisation' => $idCotisation,
    ':id_membre' => $_SESSION['id_membre']
]);

$cotisation = $requete->fetch();

if (!$cotisation) {

    die("Cotisation introuvable.");
}

if ($cotisation['reste'] <= 0) {

    die("Cette cotisation est déjà entièrement payée.");
}

$message = $_SESSION['message_paiement'] ?? '';
unset($_SESSION['message_paiement']);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Payer ma cotisation - Dahira Lansar Guidick</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f2f4f7;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .conteneur {
            width: 420px;
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        h1 {
            text-align: center;
            margin-bottom: 6px;
            color: #1f2937;
        }

        .lien-retour {
            display: inline-block;
            margin-bottom: 22px;
            color: #0B6B3A;
            text-decoration: none;
            font-size: 13.5px;
        }

        .lien-retour:hover {
            color: #06301A;
            text-decoration: underline;
        }

        .sous-titre {
            text-align: center;
            color: #6b7280;
            margin-bottom: 20px;
        }

        .recap {
            background: #f9fafb;
            border-radius: 8px;
            padding: 14px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #374151;
        }

        .recap strong {
            color: #1f2937;
        }

        .message {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }

        .champ {
            margin-bottom: 16px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input, select {
            width: 100%;
            padding: 11px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 15px;
        }

        button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 6px;
            background: #2563eb;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #1d4ed8;
        }

    </style>
    <link rel="stylesheet" href="../assets/css/theme.css">

</head>

<body>

<div class="conteneur">

    <a class="lien-retour" href="../cotisations/index.php">&larr; Retour à mes cotisations</a>

    <h1>Payer ma cotisation</h1>

    <p class="sous-titre">
        <?= htmlspecialchars(libelleMoisFr($cotisation['periode'])) ?>
    </p>

    <div class="recap">
        Prévu : <strong><?= number_format($cotisation['montant_prevu'], 0, ',', ' ') ?> FCFA</strong><br>
        Déjà payé : <strong><?= number_format($cotisation['montant_paye'], 0, ',', ' ') ?> FCFA</strong><br>
        Reste à payer : <strong><?= number_format($cotisation['reste'], 0, ',', ' ') ?> FCFA</strong>
    </div>

    <?php if (!empty($message)): ?>

        <div class="message">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>

    <form action="traiter_paiements.php" method="POST">

        <input type="hidden" name="id_cotisation" value="<?= $cotisation['id_cotisation'] ?>">

        <div class="champ">

            <label for="montant">
                Montant versé (FCFA)
            </label>

            <input
                type="number"
                id="montant"
                name="montant"
                min="1"
                max="<?= (int) $cotisation['reste'] ?>"
                step="1"
                required
            >

        </div>

        <div class="champ">

            <label for="mode_paiement">
                Moyen de paiement
            </label>

            <select id="mode_paiement" name="mode_paiement" required onchange="afficherInfoMode()">
                <option value="WAVE">Wave</option>
                <option value="ORANGE_MONEY">Orange Money</option>
                <option value="AUTRE">Autre</option>
            </select>

        </div>

        <div id="info-wave" style="display:none; background:#f0f7ff; border:1px solid #bfdbfe; border-radius:8px; padding:12px 14px; margin-bottom:16px; font-size:13px; color:#1e3a5f;">
            <strong>Pour payer via Wave :</strong><br>
            Ouvre ton appli Wave, envoie le montant au numéro du trésorier de ta génération (à récupérer auprès de lui), puis colle ici la référence de la transaction affichée dans Wave après l'envoi.
        </div>

        <div id="info-orange" style="display:none; background:#fff4eb; border:1px solid #fed7aa; border-radius:8px; padding:12px 14px; margin-bottom:16px; font-size:13px; color:#7c2d12;">
            <strong>Pour payer via Orange Money :</strong><br>
            Compose le code Orange Money habituel, envoie le montant au numéro du trésorier de ta génération, puis colle ici la référence/l'ID de transaction reçu par SMS.
        </div>

        <div class="champ">

            <label for="reference_transaction">
                Référence de la transaction
            </label>

            <input
                type="text"
                id="reference_transaction"
                name="reference_transaction"
                placeholder="Ex : WAVE-2026-0912-XXXX"
                required
            >

        </div>

        <button type="submit">
            Enregistrer le paiement
        </button>

    </form>

</div>

<script>
    function afficherInfoMode() {
        const mode = document.getElementById('mode_paiement').value;
        document.getElementById('info-wave').style.display = (mode === 'WAVE') ? 'block' : 'none';
        document.getElementById('info-orange').style.display = (mode === 'ORANGE_MONEY') ? 'block' : 'none';
    }
    afficherInfoMode();
</script>

</body>

</html>