<?php

require_once "../auth/protection_role.php";

verifierRole([1]);

require_once "fonctions.php";

if (recupererCampagneActiveGlobale($connexion)) {

    die("Une campagne est déjà active — clôture-la avant d'en créer une nouvelle.");
}

$erreurs = $_SESSION['erreurs_campagne'] ?? [];
unset($_SESSION['erreurs_campagne']);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Nouvelle campagne - Dahira Lansar Guidick</title>

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

    <h1>Nouvelle campagne Gamou</h1>

    <?php foreach ($erreurs as $erreur): ?>

        <div class="message"><?= htmlspecialchars($erreur) ?></div>

    <?php endforeach; ?>

    <form action="traiter_creer.php" method="POST">

        <div class="champ">
            <label for="nom_edition">Nom de l'édition</label>
            <input type="text" id="nom_edition" name="nom_edition" placeholder="Ex : Gamou 2027" required>
        </div>

        <div class="champ">
            <label for="date_debut">Date de début</label>
            <input type="date" id="date_debut" name="date_debut" value="<?= date('Y-m-d') ?>" required>
        </div>

        <button type="submit">Créer la campagne</button>

    </form>

</div>

</body>

</html>