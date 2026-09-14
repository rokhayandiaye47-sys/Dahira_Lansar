<?php

require_once "../auth/protection_role.php";

verifierRole([1]);

require_once "fonctions.php";
require_once "../membres/fonctions.php";

$generations = listerGenerations($connexion);
$libellesRoles = libellesRolesResponsabilite();

$erreurs = $_SESSION['erreurs_utilisateur'] ?? [];
unset($_SESSION['erreurs_utilisateur']);

$identifiantsGeneres = $_SESSION['identifiants_utilisateur'] ?? null;
unset($_SESSION['identifiants_utilisateur']);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Nouveau compte - Dahira Lansar Guidick</title>

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

        .succes {
            background: var(--vert-clair);
            border: 1px solid #cfe6da;
            color: var(--vert-fonce);
            padding: 18px;
            border-radius: 10px;
            margin-bottom: 22px;
            font-size: 13.5px;
        }

        .succes strong {
            display: block;
            margin-bottom: 8px;
            font-family: Georgia, serif;
            font-size: 15px;
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

        input, select {
            width: 100%;
            padding: 11px 13px;
            border: 1px solid #d7e6dc;
            border-radius: 8px;
            font-size: 15px;
            background: var(--vert-clair);
            font-family: inherit;
        }

        input:focus, select:focus {
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

    <a class="lien-retour" href="index.php">← Retour à la liste</a>

    <h1>Nouveau compte à responsabilité</h1>

    <?php if ($identifiantsGeneres): ?>

        <div class="succes">
            <strong>Compte créé pour <?= htmlspecialchars($identifiantsGeneres['nom']) ?></strong>
            Email : <?= htmlspecialchars($identifiantsGeneres['email']) ?><br>
            Mot de passe provisoire : <strong><?= htmlspecialchars($identifiantsGeneres['mot_de_passe']) ?></strong><br>
            Code personnel (2e facteur) : <strong><?= htmlspecialchars($identifiantsGeneres['code_personnel']) ?></strong><br>
            <em>Transmets ces informations par un canal sûr (à part) — elles ne seront plus réaffichées.</em>
        </div>

    <?php endif; ?>

    <?php foreach ($erreurs as $erreur): ?>

        <div class="message"><?= htmlspecialchars($erreur) ?></div>

    <?php endforeach; ?>

    <form action="traiter_creer.php" method="POST">

        <div class="champ">
            <label for="id_role">Rôle</label>
            <select id="id_role" name="id_role" required>
                <?php foreach ($libellesRoles as $id => $label): ?>
                    <option value="<?= $id ?>"><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="champ">
            <label for="id_generation">Génération de rattachement</label>
            <select id="id_generation" name="id_generation" required>
                <?php foreach ($generations as $g): ?>
                    <option value="<?= $g['id_generation'] ?>"><?= htmlspecialchars($g['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="champ">
            <label for="prenom">Prénom</label>
            <input type="text" id="prenom" name="prenom" required>
        </div>

        <div class="champ">
            <label for="nom">Nom</label>
            <input type="text" id="nom" name="nom" required>
        </div>

        <div class="champ">
            <label for="telephone">Téléphone</label>
            <input type="text" id="telephone" name="telephone">
        </div>

        <div class="champ">
            <label for="email">Email (pour la connexion)</label>
            <input type="email" id="email" name="email" required>
        </div>

        <button type="submit">Créer le compte</button>

    </form>

</div>

</body>

</html>