<?php
 
require_once "../auth/protection_role.php";
 
verifierRole([1, 2, 4]);
 
require_once "../cotisations/fonctions.php";
require_once "../membres/fonctions.php";
 
 
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
 
$generationActuelle = null;
 
foreach ($generations as $g) {
    if ($g['id_generation'] == $idGeneration) {
        $generationActuelle = $g;
    }
}
 
 
/*
|--------------------------------------------------------------------------
| Lister les paiements de cette génération (les plus récents d'abord)
|--------------------------------------------------------------------------
*/
 
$sql = "
    SELECT
        p.*,
        c.periode,
        m.nom,
        m.prenom
 
    FROM paiement p
 
    INNER JOIN cotisation c ON c.id_cotisation = p.id_cotisation
 
    INNER JOIN membre m ON m.id_membre = p.id_membre
 
    WHERE c.id_generation = :id_generation
 
    ORDER BY p.date_paiement DESC
 
    LIMIT 200
";
 
$requete = $connexion->prepare($sql);
$requete->execute([':id_generation' => $idGeneration]);
$paiements = $requete->fetchAll();
 
$totalValide = 0;
 
foreach ($paiements as $p) {
    if ($p['statut'] === 'VALIDE') {
        $totalValide += $p['montant'];
    }
}
 
$libellesMode = [
    'WAVE' => 'Wave',
    'ORANGE_MONEY' => 'Orange Money',
    'AUTRE' => 'Autre',
];
 
$message = $_SESSION['message_paiements'] ?? '';
unset($_SESSION['message_paiements']);
 
$peutGerer = ($_SESSION['id_role'] == 4);
 
?>
 
<!DOCTYPE html>
<html lang="fr">
 
<head>
 
    <meta charset="UTF-8">
 
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
 
    <title>Paiements - Dahira Lansar Guidick</title>
 
    <style>
 
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
 
        body {
            font-family: Arial, sans-serif;
            background: #f2f4f7;
            padding: 30px 20px;
        }
 
        .conteneur {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
 
        .lien-retour {
            display: inline-block;
            margin-bottom: 20px;
            color: #0B6B3A;
            text-decoration: none;
        }

        .lien-retour:hover {
            color: #06301A;
            text-decoration: underline;
        }
 
        h1 {
            color: #1f2937;
            margin-bottom: 4px;
        }
 
        .sous-titre {
            color: #6b7280;
            margin-bottom: 20px;
        }
 
        .message {
            background: #dcfce7;
            color: #166534;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
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
            background: #f3f4f6;
            color: #374151;
            text-decoration: none;
            font-size: 13px;
        }
 
        .onglets a.actif {
            background: #2563eb;
            color: white;
        }
 
        .carte-total {
            background: #f9fafb;
            border-radius: 10px;
            padding: 16px 18px;
            margin-bottom: 22px;
        }
 
        .carte-total .label {
            font-size: 12px;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 4px;
        }
 
        .carte-total .valeur {
            font-size: 24px;
            font-weight: bold;
            color: #166534;
        }
 
        table {
            width: 100%;
            border-collapse: collapse;
        }
 
        th {
            text-align: left;
            font-size: 13px;
            text-transform: uppercase;
            color: #6b7280;
            padding: 8px 10px;
            border-bottom: 2px solid #e5e7eb;
        }
 
        td {
            padding: 12px 10px;
            border-bottom: 1px solid #f0f1f3;
            font-size: 14px;
        }
 
        .ref {
            font-family: monospace;
            font-size: 13px;
            color: #6b7280;
        }
 
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 99px;
            font-size: 12px;
            font-weight: bold;
        }
 
        .badge-valide { color: #166534; background: #dcfce7; }
        .badge-annule { color: #991b1b; background: #fee2e2; }
 
        .lien-annuler {
            color: #991b1b;
            font-size: 13px;
            text-decoration: none;
        }
 
        .vide {
            padding: 30px;
            text-align: center;
            color: #6b7280;
        }
 
    </style>
    <link rel="stylesheet" href="../assets/css/theme.css">
 
</head>
 
<body>
 
<div class="conteneur">
 
    <a class="lien-retour" href="../index.php">&larr; Retour</a>
 
    <h1>Paiements — <?= htmlspecialchars($generationActuelle['nom'] ?? '') ?></h1>
 
    <p class="sous-titre">Historique des versements de cotisation</p>
     <?php if ($peutGerer): ?>
        <p style="margin-bottom:20px;">
            <a href="enregistrer.php" style="color:#0B6B3A; text-decoration:none; font-size:13.5px;">+ Enregistrer un versement reçu en main propre (espèces, Wave, Orange Money)</a>
        </p>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
 
        <div class="message"><?= htmlspecialchars($message) ?></div>
 
    <?php endif; ?>
 
    <?php if ($_SESSION['id_role'] == 1): ?>
 
        <div class="onglets">
 
            <?php foreach ($generations as $g): ?>
 
                <a
                    class="<?= $g['id_generation'] == $idGeneration ? 'actif' : '' ?>"
                    href="?id_generation=<?= $g['id_generation'] ?>"
                >
                    <?= htmlspecialchars($g['nom']) ?>
                </a>
 
            <?php endforeach; ?>
 
        </div>
 
    <?php endif; ?>
 
    <div class="carte-total">
        <div class="label">Total encaissé </div>
        <div class="valeur"><?= number_format($totalValide, 0, ',', ' ') ?> FCFA</div>
    </div>
 
    <?php if (!$paiements): ?>
 
        <div class="vide">
            Aucun paiement enregistré pour cette génération.
        </div>
 
    <?php else: ?>
 
        <table>
 
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Membre</th>
                    <th>Période</th>
                    <th>Montant</th>
                    <th>Moyen</th>
                    <th>Référence</th>
                    <th>Statut</th>
                    <?php if ($peutGerer): ?><th></th><?php endif; ?>
                </tr>
            </thead>
 
            <tbody>
 
                <?php foreach ($paiements as $p): ?>
 
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($p['date_paiement'])) ?></td>
                    <td><?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?></td>
                    <td><?= htmlspecialchars(libelleMoisFr($p['periode'])) ?></td>
                    <td><?= number_format($p['montant'], 0, ',', ' ') ?> FCFA</td>
                    <td><?= htmlspecialchars($libellesMode[$p['mode_paiement']] ?? $p['mode_paiement']) ?></td>
                    <td class="ref"><?= htmlspecialchars($p['reference_transaction']) ?></td>
                    <td>
                        <span class="badge <?= $p['statut'] === 'VALIDE' ? 'badge-valide' : 'badge-annule' ?>">
                            <?= $p['statut'] === 'VALIDE' ? 'Validé' : 'Annulé' ?>
                        </span>
                    </td>
                    <?php if ($peutGerer): ?>
                        <td>
                            <?php if ($p['statut'] === 'VALIDE'): ?>
                                <a
                                    class="lien-annuler"
                                    href="annuler.php?id_paiement=<?= $p['id_paiement'] ?>"
                                    onclick="return confirm('Annuler ce paiement ? La cotisation et la caisse seront ajustées.');"
                                >
                                    Annuler
                                </a>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                </tr>
 
                <?php endforeach; ?>
 
            </tbody>
 
        </table>
 
    <?php endif; ?>
 
</div>
 
</body>
 
</html>
 