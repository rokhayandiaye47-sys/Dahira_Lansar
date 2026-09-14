<?php

require_once __DIR__ . "/../config/database.php";
require_once "../auth/protection_role.php";

verifierRole([1, 2, 3, 4, 5]);

$idPaiement = (int) ($_GET['id_paiement'] ?? 0);

$sql = "
    SELECT
        p.*,
        c.periode,
        c.montant_prevu,
        c.id_generation,

        m.nom,
        m.prenom,
        m.id_membre,

        g.nom AS nom_generation

    FROM paiement p

    INNER JOIN cotisation c ON c.id_cotisation = p.id_cotisation

    INNER JOIN membre m ON m.id_membre = p.id_membre

    INNER JOIN generation g ON g.id_generation = c.id_generation

    WHERE p.id_paiement = :id_paiement
      AND p.statut = 'VALIDE'

    LIMIT 1
";

$requete = $connexion->prepare($sql);
$requete->execute([':id_paiement' => $idPaiement]);
$paiement = $requete->fetch();

if (!$paiement) {
    die("Reçu introuvable ou paiement non validé.");
}


/*
|--------------------------------------------------------------------------
| Autorisation : le membre ne voit que ses propres reçus ; les rôles
| à responsabilité doivent appartenir à la même génération
|--------------------------------------------------------------------------
*/

if ($_SESSION['id_role'] == 5) {

    if ((int) $paiement['id_membre'] !== (int) $_SESSION['id_membre']) {
        die("Ce reçu ne t'appartient pas.");
    }

} elseif ($_SESSION['id_role'] != 1) {

    if ((int) $paiement['id_generation'] !== (int) $_SESSION['id_generation']) {
        die("Accès refusé.");
    }
}

$libellesMode = [
    'ESPECES' => 'Espèces',
    'WAVE' => 'Wave',
    'ORANGE_MONEY' => 'Orange Money',
    'AUTRE' => 'Autre',
];

function libelleMoisFacture($periode)
{
    $mois = [
        '01'=>'Janvier','02'=>'Février','03'=>'Mars','04'=>'Avril',
        '05'=>'Mai','06'=>'Juin','07'=>'Juillet','08'=>'Août',
        '09'=>'Septembre','10'=>'Octobre','11'=>'Novembre','12'=>'Décembre'
    ];
    [$annee, $m] = explode('-', $periode);
    return ($mois[$m] ?? $m) . ' ' . $annee;
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <title>Reçu <?= htmlspecialchars($paiement['reference_transaction']) ?></title>

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
            padding: 40px 20px;
        }

        .recu {
            max-width: 460px;
            margin: 0 auto;
            background: var(--blanc);
            border-radius: 14px;
            padding: 34px;
            box-shadow: 0 10px 30px rgba(6,48,26,0.12);
        }

        .sceau {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 1.5px solid var(--vert-profond);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Georgia, serif;
            font-weight: bold;
            color: var(--vert-profond);
            margin: 0 auto 14px;
        }

        h1 {
            text-align: center;
            font-family: Georgia, serif;
            font-weight: normal;
            font-size: 1.2rem;
            color: var(--vert-fonce);
        }

        .sous-titre {
            text-align: center;
            font-size: 12.5px;
            color: #5c7568;
            margin-bottom: 24px;
        }

        .montant {
            text-align: center;
            background: var(--vert-clair);
            border-radius: 10px;
            padding: 18px;
            margin-bottom: 22px;
        }

        .montant .label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #5c7568;
        }

        .montant .valeur {
            font-family: Georgia, serif;
            font-size: 1.9rem;
            color: var(--vert-fonce);
        }

        dl {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 12px;
            font-size: 13.5px;
            margin-bottom: 24px;
        }

        dt {
            color: #5c7568;
        }

        dd {
            text-align: right;
            font-weight: bold;
            color: var(--vert-texte);
        }

        .no-print {
            text-align: center;
        }

        .lien-retour {
            display: inline-block;
            margin-bottom: 14px;
            color: var(--vert-profond);
            text-decoration: none;
            font-size: 13.5px;
        }

        .lien-retour:hover {
            color: var(--vert-fonce);
            text-decoration: underline;
        }

        .no-print button {
            padding: 11px 22px;
            background: var(--vert-profond);
            color: var(--blanc);
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
        }

        @media print {
            .no-print { display: none; }
            body { background: white; padding: 0; }
            .recu { box-shadow: none; }
        }

    </style>

</head>

<body>

<div class="recu">

    <div class="sceau">DLG</div>

    <h1>DAHIRA LANSAR GUIDICK</h1>
    <div class="sous-titre">Reçu de cotisation — <?= htmlspecialchars($paiement['nom_generation']) ?></div>

    <div class="montant">
        <div class="label">Montant versé</div>
        <div class="valeur"><?= number_format($paiement['montant'], 0, ',', ' ') ?> FCFA</div>
    </div>

    <dl>
        <dt>Membre</dt><dd><?= htmlspecialchars($paiement['prenom'] . ' ' . $paiement['nom']) ?></dd>
        <dt>Période</dt><dd><?= htmlspecialchars(libelleMoisFacture($paiement['periode'])) ?></dd>
        <dt>Référence</dt><dd><?= htmlspecialchars($paiement['reference_transaction']) ?></dd>
        <dt>Moyen de paiement</dt><dd><?= htmlspecialchars($libellesMode[$paiement['mode_paiement']] ?? $paiement['mode_paiement']) ?></dd>
        <dt>Date</dt><dd><?= date('d/m/Y à H:i', strtotime($paiement['date_paiement'])) ?></dd>
        <dt>Statut</dt><dd>Validé</dd>
    </dl>

    <div class="no-print">
        <a class="lien-retour" href="../index.php">← Retour</a>
        <br>
        <button onclick="window.print()">Imprimer / Enregistrer en PDF</button>
    </div>

</div>

</body>

</html>