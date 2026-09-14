<?php

require_once "../config/database.php";

$jeton = trim($_GET['jeton'] ?? '');

$sql = "
    SELECT id_utilisateur, jeton_expiration

    FROM utilisateur

    WHERE jeton_activation = :jeton
      AND statut_compte = 'EN_ATTENTE'

    LIMIT 1
";

$requete = $connexion->prepare($sql);
$requete->execute([':jeton' => $jeton]);
$utilisateur = $requete->fetch();

$jetonValide = $utilisateur && strtotime($utilisateur['jeton_expiration']) > time();

$message = $_GET['erreur'] ?? '';

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Activer mon compte - Dahira Lansar Guidick</title>

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
            background: linear-gradient(160deg, var(--vert-fonce) 0%, var(--vert-profond) 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .conteneur {
            width: 100%;
            max-width: 400px;
            background: var(--blanc);
            border-radius: 14px;
            padding: 38px 34px;
            box-shadow: 0 20px 50px rgba(6,48,26,0.35);
        }

        h1 {
            text-align: center;
            font-family: Georgia, serif;
            font-weight: normal;
            font-size: 1.3rem;
            color: var(--vert-fonce);
            margin-bottom: 6px;
        }

        .sous-titre {
            text-align: center;
            color: #5c7568;
            font-size: 13px;
            margin-bottom: 26px;
        }

        .message {
            background: var(--terracotta-clair);
            color: var(--terracotta);
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13.5px;
            text-align: center;
        }

        .champ {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: bold;
            color: var(--vert-texte);
        }

        input {
            width: 100%;
            padding: 12px 14px;
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

</head>

<body>

<div class="conteneur">

    <h1>Activer mon compte</h1>

    <?php if (!$jetonValide): ?>

        <div class="message">
            Ce lien d'activation est invalide ou a expiré. Demande à un responsable de te renvoyer une invitation.
        </div>

    <?php else: ?>

        <p class="sous-titre">Choisis ton mot de passe pour finaliser la création de ton compte.</p>

        <?php if (!empty($message)): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form action="traiter_activation.php" method="POST">

            <input type="hidden" name="jeton" value="<?= htmlspecialchars($jeton) ?>">

            <div class="champ">
                <label for="mot_de_passe">Mot de passe (8 caractères minimum)</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" minlength="8" required>
            </div>

            <div class="champ">
                <label for="confirmation">Confirme ton mot de passe</label>
                <input type="password" id="confirmation" name="confirmation" minlength="8" required>
            </div>

            <button type="submit">Activer mon compte</button>

        </form>

    <?php endif; ?>

</div>

</body>

</html>