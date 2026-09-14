<?php

require_once "../auth/protection_role.php";
require_once "../config/database.php";

verifierRole([1, 2, 3, 4, 5]);

$sql = "
    SELECT
        p.*,
        c.periode

    FROM paiement p

    INNER JOIN cotisation c ON c.id_cotisation = p.id_cotisation

    WHERE p.id_membre = :id_membre

    ORDER BY p.date_paiement DESC
";

$requete = $connexion->prepare($sql);
$requete->execute([':id_membre' => $_SESSION['id_membre']]);
$paiements = $requete->fetchAll();

function libelleMoisMesPaiements($periode)
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

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Mes paiements - Dahira Lansar Guidick</title>

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
            max-width: 700px;
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
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            font-size: 12.5px;
            text-transform: uppercase;
            color: #5c7568;
            padding: 8px 10px;
            border-bottom: 2px solid var(--vert-clair);
        }

        td {
            padding: 13px 10px;
            border-bottom: 1px solid #eef4f0;
            font-size: 14px;
        }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 99px;
            font-size: 11.5px;
            font-weight: bold;
        }

        .badge-valide { color: var(--vert-fonce); background: var(--vert-clair); }
        .badge-annule { color: #7a1f1f; background: #fdecec; }

        .lien-recu {
            color: var(--vert-profond);
            text-decoration: none;
            font-size: 13px;
        }

        .vide {
            padding: 40px;
            text-align: center;
            color: #5c7568;
            font-family: Georgia, serif;
            font-style: italic;
        }

    </style>

</head>

<body>

<div class="conteneur">

    <a class="lien-retour" href="../index.php">← Retour</a>

    <h1>Mes paiements</h1>

    <?php if (!$paiements): ?>

        <div class="vide">Aucun paiement effectué pour l'instant.</div>

    <?php else: ?>

        <table>

            <thead>
                <tr>
                    <th>Date</th>
                    <th>Période</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($paiements as $p): ?>

                <tr>
                    <td><?= date('d/m/Y', strtotime($p['date_paiement'])) ?></td>
                    <td><?= htmlspecialchars(libelleMoisMesPaiements($p['periode'])) ?></td>
                    <td><?= number_format($p['montant'], 0, ',', ' ') ?> FCFA</td>
                    <td>
                        <span class="badge <?= $p['statut'] === 'VALIDE' ? 'badge-valide' : 'badge-annule' ?>">
                            <?= $p['statut'] === 'VALIDE' ? 'Validé' : 'Annulé' ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($p['statut'] === 'VALIDE'): ?>
                            <a class="lien-recu" href="recu.php?id_paiement=<?= $p['id_paiement'] ?>" target="_blank">Voir le reçu</a>
                        <?php endif; ?>
                    </td>
                </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>

</div>

</body>

</html>