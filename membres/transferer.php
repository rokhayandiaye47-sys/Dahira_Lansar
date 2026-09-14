<?php

require_once "../auth/protection_role.php";

verifierRole([1]);

require_once "fonctions.php";

$idMembre = (int) ($_GET['id_membre'] ?? 0);

$membre = recupererMembreAvecCompte($connexion, $idMembre);

if (!$membre) {

    die("Membre introuvable.");
}

$generations = listerGenerations($connexion);

$erreurs = $_SESSION['erreurs_transfert'] ?? [];
unset($_SESSION['erreurs_transfert']);


/*
|--------------------------------------------------------------------------
| Historique des générations précédentes du membre
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        h.*,
        go.nom AS nom_origine,
        gd.nom AS nom_destination

    FROM historique_generation h

    LEFT JOIN generation go ON go.id_generation = h.id_generation_origine

    INNER JOIN generation gd ON gd.id_generation = h.id_generation_destination

    WHERE h.id_membre = :id_membre

    ORDER BY h.date_debut DESC
";

$requete = $connexion->prepare($sql);
$requete->execute([':id_membre' => $idMembre]);
$historique = $requete->fetchAll();

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Transférer un membre - Dahira Lansar Guidick</title>

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
            max-width: 500px;
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
            margin-bottom: 22px;
            font-size: 13.5px;
        }

        .sous-titre strong {
            color: var(--vert-fonce);
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

        select, input, textarea {
            width: 100%;
            padding: 11px 13px;
            border: 1px solid #d7e6dc;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            background: var(--vert-clair);
        }

        select:focus, input:focus, textarea:focus {
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

        .section {
            margin-top: 30px;
            border-top: 1px solid #eef4f0;
            padding-top: 20px;
        }

        .section h2 {
            font-family: Georgia, serif;
            font-weight: normal;
            font-size: 15px;
            color: var(--vert-fonce);
            margin-bottom: 12px;
        }

        .ligne-historique {
            padding: 10px 0;
            border-bottom: 1px solid #eef4f0;
            font-size: 13px;
            color: #445c4e;
        }

        .ligne-historique:last-child {
            border-bottom: none;
        }

        .ligne-historique strong {
            color: var(--vert-fonce);
        }

    </style>
    <link rel="stylesheet" href="../assets/css/theme.css">

</head>

<body>

<div class="conteneur">

    <a class="lien-retour" href="index.php?id_generation=<?= $membre['id_generation'] ?>">← Retour</a>

    <h1>Transférer <?= htmlspecialchars($membre['prenom'] . ' ' . $membre['nom']) ?></h1>

    <p class="sous-titre">
        Génération actuelle : <strong><?= htmlspecialchars($generations[array_search($membre['id_generation'], array_column($generations, 'id_generation'))]['nom'] ?? '—') ?></strong>
    </p>

    <?php foreach ($erreurs as $erreur): ?>

        <div class="message"><?= htmlspecialchars($erreur) ?></div>

    <?php endforeach; ?>

    <form action="traiter_transfert.php" method="POST">

        <input type="hidden" name="id_membre" value="<?= $membre['id_membre'] ?>">

        <div class="champ">
            <label for="id_generation_destination">Nouvelle génération</label>
            <select id="id_generation_destination" name="id_generation_destination" required>

                <?php foreach ($generations as $g): ?>

                    <?php if ($g['id_generation'] != $membre['id_generation']): ?>
                        <option value="<?= $g['id_generation'] ?>"><?= htmlspecialchars($g['nom']) ?></option>
                    <?php endif; ?>

                <?php endforeach; ?>

            </select>
        </div>

        <div class="champ">
            <label for="motif">Motif du transfert</label>
            <textarea id="motif" name="motif" rows="3" placeholder="Ex : déménagement, passage à l'âge adulte..." required></textarea>
        </div>

        <button type="submit">Confirmer le transfert</button>

    </form>

    <?php if ($historique): ?>

        <div class="section">

            <h2>Historique des générations</h2>

            <?php foreach ($historique as $h): ?>

                <div class="ligne-historique">
                    <?= $h['nom_origine'] ? htmlspecialchars($h['nom_origine']) . ' → ' : 'Adhésion directe dans ' ?>
                    <strong><?= htmlspecialchars($h['nom_destination']) ?></strong>
                    — depuis le <?= date('d/m/Y', strtotime($h['date_debut'])) ?>
                    <?php if ($h['date_fin']): ?>
                        jusqu'au <?= date('d/m/Y', strtotime($h['date_fin'])) ?>
                    <?php endif; ?>
                    <?php if ($h['motif']): ?>
                        <br><em><?= htmlspecialchars($h['motif']) ?></em>
                    <?php endif; ?>
                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>

</body>

</html>