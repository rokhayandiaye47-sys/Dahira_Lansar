<?php

require_once "../auth/protection_role.php";

verifierRole([3]);

require_once "fonctions.php";

$idMembre = isset($_GET['id_membre']) ? (int) $_GET['id_membre'] : null;

$membre = null;

if ($idMembre) {

    $membre = recupererMembreAvecCompte($connexion, $idMembre);

    if (!$membre) {

        die("Membre introuvable.");
    }

    assurerAccesGeneration($membre['id_generation']);

    $idGeneration = (int) $membre['id_generation'];

} else {

    $idGeneration = (int) ($_GET['id_generation'] ?? $_SESSION['id_generation']);

    assurerAccesGeneration($idGeneration);
}

$generations = listerGenerations($connexion);

$erreurs = $_SESSION['erreurs_membre'] ?? [];
unset($_SESSION['erreurs_membre']);

$identifiantsGeneres = $_SESSION['identifiants_generes'] ?? null;
unset($_SESSION['identifiants_generes']);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title><?= $membre ? 'Modifier' : 'Nouveau' ?> membre - Dahira Lansar Guidick</title>

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
            margin-bottom: 16px;
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
            margin-top: 4px;
        }

        button:hover {
            background: var(--vert-fonce);
        }

    </style>
    <link rel="stylesheet" href="../assets/css/theme.css">

</head>

<body>

<div class="conteneur">

    <a class="lien-retour" href="index.php">← Retour à la liste</a>

    <h1><?= $membre ? 'Modifier le membre' : 'Nouveau membre' ?></h1>

       <?php if ($identifiantsGeneres): ?>

        <div class="succes">
            <strong>Compte créé pour <?= htmlspecialchars($identifiantsGeneres['nom']) ?></strong>
            Email : <?= htmlspecialchars($identifiantsGeneres['email']) ?><br>
            Mot de passe provisoire : <strong><?= htmlspecialchars($identifiantsGeneres['mot_de_passe']) ?></strong><br>
            <em>Transmets ces identifiants au membre — ils ne seront plus réaffichés.</em>
        </div>

    <?php endif; ?>

    <?php foreach ($erreurs as $erreur): ?>

        <div class="message"><?= htmlspecialchars($erreur) ?></div>

    <?php endforeach; ?>

    <form action="traiter_formulaire.php" method="POST">

        <?php if ($membre): ?>
            <input type="hidden" name="id_membre" value="<?= $membre['id_membre'] ?>">
        <?php endif; ?>

        <input type="hidden" name="id_generation" value="<?= $idGeneration ?>">

        <div class="champ">
            <label for="prenom">Prénom</label>
            <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($membre['prenom'] ?? '') ?>" required>
        </div>

        <div class="champ">
            <label for="nom">Nom</label>
            <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($membre['nom'] ?? '') ?>" required>
        </div>

        <div class="champ">
            <label for="telephone">Téléphone</label>
            <input type="text" id="telephone" name="telephone" value="<?= htmlspecialchars($membre['telephone'] ?? '') ?>">
        </div>

        <?php if (!$membre): ?>

            <div class="champ">
                <label for="email">Email (pour la connexion)</label>
                <input type="email" id="email" name="email" required>
            </div>

        <?php endif; ?>

        <div class="champ">
            <label for="date_adhesion">Date d'adhésion</label>
            <input type="date" id="date_adhesion" name="date_adhesion"
                   value="<?= htmlspecialchars($membre['date_adhesion'] ?? date('Y-m-d')) ?>">
        </div>

        <?php if ($membre): ?>

            <div class="champ">
                <label for="statut">Statut</label>
                <select id="statut" name="statut">
                    <option value="ACTIF" <?= ($membre['statut'] ?? '') === 'ACTIF' ? 'selected' : '' ?>>Actif</option>
                    <option value="INACTIF" <?= ($membre['statut'] ?? '') === 'INACTIF' ? 'selected' : '' ?>>Inactif</option>
                </select>
            </div>

        <?php endif; ?>

        <button type="submit">
            <?= $membre ? 'Enregistrer les modifications' : 'Créer le membre' ?>
        </button>

    </form>

</div>

</body>

</html>