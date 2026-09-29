<?php

session_start();

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Connexion - Dahira Lansar Guidick</title>

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

        .sceau {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            border: 1.5px solid var(--vert-profond);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            font-family: Georgia, serif;
            font-size: 1.2rem;
            color: var(--vert-profond);
        }

        h1 {
            text-align: center;
            font-family: Georgia, serif;
            font-weight: normal;
            font-size: 1.35rem;
            color: var(--vert-fonce);
            margin-bottom: 6px;
        }

        .sous-titre {
            text-align: center;
            color: #5c7568;
            font-size: 13.5px;
            margin-bottom: 26px;
        }

        .message {
            background: #fdecec;
            color: #7a1f1f;
            padding: 12px;
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
            margin-top: 6px;
        }

        button:hover {
            background: var(--vert-fonce);
        }

        .lien-retour {
            display: block;
            text-align: center;
            margin-top: 22px;
            color: var(--vert-profond);
            text-decoration: none;
            font-size: 13px;
        }

    </style>
    <link rel="stylesheet" href="../assets/css/theme.css">

</head>

<body>

<div class="conteneur">

    <div class="sceau">DLG</div>

    <h1>Dahira Lansar Guidick</h1>

    <p class="sous-titre">Connecte-toi à ton espace</p>

    <?php if (!empty($message)): ?>

        <div class="message"><?= htmlspecialchars($message) ?></div>

    <?php endif; ?>

    <form action="traiter_connexion.php" method="POST">

        <div class="champ">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required autofocus>
        </div>

        <div class="champ">
            <label for="mot_de_passe">Mot de passe</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required>
        </div>

        <button type="submit">Se connecter</button>

    </form>

    <a class="lien-retour" href="../accueil.php">← Retour à l'accueil</a>
    <a class="lien-retour" href="mot_de_pass_oublie.php" style="margin-top:8px;">
        Mot de passe oublié ?
    </a>
</div>

</body>

</html>