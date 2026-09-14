<?php

require_once "../auth/protection_role.php";

verifierRole([1, 2]);

require_once "fonctions.php";
require_once "../membres/fonctions.php";
require_once "../cotisations/fonctions.php";
require_once "../caisse/fonctions.php";


/*
|--------------------------------------------------------------------------
| Déterminer la génération à afficher
|--------------------------------------------------------------------------
*/

$generations = listerGenerations($connexion);

if ($_SESSION['id_role'] == 1) {

    $idGeneration = (int) ($_GET['id_generation'] ?? ($generations[0]['id_generation'] ?? 0));

} else {

    $idGeneration = (int) $_SESSION['id_generation'];
}

assurerAccesGeneration($idGeneration);

$generationActuelle = recupererGeneration($connexion, $idGeneration);

$nombreMembres = compterMembresActifs($connexion, $idGeneration);

$caisse = recupererCaisse($connexion, $idGeneration);

$campagne = recupererCampagneActive($connexion);

$regle = $campagne ? recupererRegleCotisation($connexion, $idGeneration, $campagne['id_campagne']) : null;


/*
|--------------------------------------------------------------------------
| Résumé des cotisations de la campagne active
|--------------------------------------------------------------------------
*/

$totalPrevu = 0;
$totalPaye = 0;

if ($campagne) {

    $sql = "
        SELECT
            COALESCE(SUM(montant_prevu), 0) AS total_prevu,
            COALESCE(SUM(montant_paye), 0) AS total_paye

        FROM cotisation

        WHERE id_generation = :id_generation
          AND id_campagne = :id_campagne
    ";

    $requete = $connexion->prepare($sql);

    $requete->execute([
        ':id_generation' => $idGeneration,
        ':id_campagne' => $campagne['id_campagne']
    ]);

    $totaux = $requete->fetch();

    $totalPrevu = $totaux['total_prevu'];
    $totalPaye = $totaux['total_paye'];
}

$message = $_SESSION['message_generation'] ?? '';
unset($_SESSION['message_generation']);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Génération - Dahira Lansar Guidick</title>

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
            background: var(--vert-clair);
            color: var(--vert-texte);
            padding: 36px 20px;
        }

        .conteneur {
            max-width: 820px;
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
            font-size: 1.5rem;
            margin-bottom: 4px;
        }

        .sous-titre {
            color: #5c7568;
            margin-bottom: 20px;
            font-size: 13.5px;
        }

        .message {
            background: var(--vert-clair);
            color: var(--vert-fonce);
            border: 1px solid #cfe6da;
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13.5px;
        }

        .onglets {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .onglets a {
            padding: 6px 14px;
            border-radius: 99px;
            background: var(--vert-clair);
            color: var(--vert-fonce);
            text-decoration: none;
            font-size: 13px;
        }

        .onglets a.actif {
            background: var(--vert-profond);
            color: var(--blanc);
        }

        .cartes {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 14px;
            margin-bottom: 26px;
        }

        .carte {
            background: var(--vert-clair);
            border-radius: 10px;
            padding: 16px 18px;
        }

        .carte .label {
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #5c7568;
            margin-bottom: 4px;
        }

        .carte .valeur {
            font-size: 21px;
            font-weight: bold;
            color: var(--vert-fonce);
            font-family: Georgia, serif;
        }

        .section {
            background: var(--vert-clair);
            border-radius: 12px;
            padding: 20px 22px;
            margin-bottom: 18px;
        }

        .section h2 {
            font-family: Georgia, serif;
            font-weight: normal;
            font-size: 16px;
            color: var(--vert-fonce);
            margin-bottom: 14px;
        }

        .ligne {
            display: flex;
            justify-content: space-between;
            padding: 7px 0;
            font-size: 14px;
        }

        .ligne .label {
            color: #5c7568;
        }

        .ligne .valeur {
            font-weight: bold;
            color: var(--vert-fonce);
        }

        .bouton {
            display: inline-block;
            margin-top: 12px;
            padding: 9px 16px;
            background: var(--vert-profond);
            color: var(--blanc);
            border-radius: 8px;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: bold;
        }

        .bouton:hover {
            background: var(--vert-fonce);
        }

        .liens-rapides {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .lien-rapide {
            padding: 10px 16px;
            background: var(--blanc);
            border: 1px solid #d7e6dc;
            border-radius: 8px;
            text-decoration: none;
            color: var(--vert-fonce);
            font-weight: bold;
            font-size: 13.5px;
        }

        .lien-rapide:hover {
            border-color: var(--vert-profond);
        }

    </style>
    <link rel="stylesheet" href="../assets/css/theme.css">

</head>

<body>

<div class="conteneur">

    <a class="lien-retour" href="../index.php">← Retour</a>

    <h1><?= htmlspecialchars($generationActuelle['nom'] ?? '') ?></h1>

    <p class="sous-titre">
        <?= $campagne ? htmlspecialchars($campagne['nom_edition']) : 'Aucune campagne active' ?>
    </p>

    <?php if (!empty($message)): ?>

        <div class="message"><?= htmlspecialchars($message) ?></div>

    <?php endif; ?>

    <?php if ($_SESSION['id_role'] == 1): ?>

        <div class="onglets">

            <?php foreach ($generations as $g): ?>

    <a class="<?= $g['id_generation'] == $idGeneration ? 'actif' : '' ?>"
       href="?id_generation=<?= $g['id_generation'] ?>">
        <?= htmlspecialchars($g['nom']) ?>
    </a>

<?php endforeach; ?>
        </div>

    <?php endif; ?>

    <div class="cartes">

        <div class="carte">
            <div class="label">Membres actifs</div>
            <div class="valeur"><?= $nombreMembres ?></div>
        </div>

        <div class="carte">
            <div class="label">Solde de caisse</div>
            <div class="valeur"><?= number_format($caisse['solde'] ?? 0, 0, ',', ' ') ?> F</div>
        </div>

        <div class="carte">
            <div class="label">Cotisations collectées</div>
            <div class="valeur"><?= number_format($totalPaye, 0, ',', ' ') ?> F</div>
        </div>

    </div>

    <div class="section">

        <h2>Règle de cotisation — campagne active</h2>

        <?php if (!$campagne): ?>

            <p style="font-size:14px; color:#5c7568;">Aucune campagne active pour le moment.</p>

        <?php elseif (!$regle): ?>

            <p style="font-size:14px; color:#5c7568;">Aucune règle de cotisation définie pour cette génération sur la campagne en cours.</p>

            <a class="bouton" href="regle.php?id_generation=<?= $idGeneration ?>">Définir la règle</a>

        <?php else: ?>

            <div class="ligne">
                <span class="label">Montant</span>
                <span class="valeur"><?= number_format($regle['montant'], 0, ',', ' ') ?> FCFA / mois</span>
            </div>

            <div class="ligne">
                <span class="label">Jour d'échéance</span>
                <span class="valeur"><?= $regle['jour_echeance'] ? "le " . $regle['jour_echeance'] . " du mois" : '—' ?></span>
            </div>

            <a class="bouton" href="regle.php?id_generation=<?= $idGeneration ?>">Modifier la règle</a>

        <?php endif; ?>

    </div>

    <div class="section">

        <h2>Accès rapides</h2>

        <div class="liens-rapides">
            <a class="lien-rapide" href="../membres/index.php?id_generation=<?= $idGeneration ?>">Membres</a>
            <a class="lien-rapide" href="../cotisations/generation.php?id_generation=<?= $idGeneration ?>">Cotisations</a>
            <a class="lien-rapide" href="../paiements/index.php?id_generation=<?= $idGeneration ?>">Paiements</a>
            <a class="lien-rapide" href="../caisse/index.php?id_generation=<?= $idGeneration ?>">Caisse</a>
        </div>

    </div>

</div>

</body>

</html>