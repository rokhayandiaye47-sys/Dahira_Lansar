session_start();

$message = $_SESSION['message_mdp_oublie'] ?? '';
unset($_SESSION['message_mdp_oublie']);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Mot de passe oublié - Dahira Lansar Guidick</title>

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
            background: var(--vert-clair);
            color: var(--vert-fonce);
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

</head>

<body>

<div class="conteneur">

    <h1>Mot de passe oublié</h1>

    <p class="sous-titre">Renseigne ton email, la personne responsable de ton compte te transmettra un nouveau mot de passe.</p>

    <?php if (!empty($message)): ?>

        <div class="message"><?= htmlspecialchars($message) ?></div>

    <?php endif; ?>

    <form action="traiter_mot_de_pass_oublie.php" method="POST">

        <div class="champ">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required autofocus>
        </div>

        <button type="submit">Envoyer la demande</button>

    </form>

    <a class="lien-retour" href="connexion.php">← Retour à la connexion</a>

</div>

</body>

</html>