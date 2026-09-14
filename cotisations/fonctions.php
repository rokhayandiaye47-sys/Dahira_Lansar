<?php

require_once __DIR__ . "/../config/database.php";


/*
|--------------------------------------------------------------------------
| Récupérer un membre par son id
|--------------------------------------------------------------------------
*/

function recupererMembre($connexion, $idMembre)
{
    $sql = "
        SELECT *

        FROM membre

        WHERE id_membre = :id_membre

        LIMIT 1
    ";

    $requete = $connexion->prepare($sql);

    $requete->execute([
        ':id_membre' => $idMembre
    ]);

    return $requete->fetch();
}


/*
|--------------------------------------------------------------------------
| Récupérer la campagne Gamou active
|--------------------------------------------------------------------------
*/

function recupererCampagneActive($connexion)
{
    $sql = "
        SELECT *

        FROM campagne_gamou

        WHERE statut = 'ACTIVE'

        ORDER BY id_campagne DESC

        LIMIT 1
    ";

    return $connexion->query($sql)->fetch();
}


/*
|--------------------------------------------------------------------------
| Récupérer la règle de cotisation d'une génération pour une campagne
|--------------------------------------------------------------------------
*/

function recupererRegleCotisation($connexion, $idGeneration, $idCampagne)
{
    $sql = "
        SELECT *

        FROM regle_cotisation

        WHERE id_generation = :id_generation
          AND id_campagne = :id_campagne

        LIMIT 1
    ";

    $requete = $connexion->prepare($sql);

    $requete->execute([
        ':id_generation' => $idGeneration,
        ':id_campagne' => $idCampagne
    ]);

    return $requete->fetch();
}


/*
|--------------------------------------------------------------------------
| Générer les cotisations manquantes d'un membre, mois par mois,
| depuis le début de la campagne active jusqu'au mois en cours
| (RG16-RG19 : chaque mois reste individualisé)
|--------------------------------------------------------------------------
*/

function genererCotisationsManquantes($connexion, $idMembre, $idGeneration)
{
    $campagne = recupererCampagneActive($connexion);

    if (!$campagne) {
        return;
    }

    $regle = recupererRegleCotisation($connexion, $idGeneration, $campagne['id_campagne']);

    if (!$regle) {
        return;
    }

    $debut = !empty($campagne['date_debut'])
        ? new DateTime($campagne['date_debut'])
        : new DateTime('first day of this month');

    $fin = new DateTime('first day of this month');

    $moisCourant = clone $debut;
    $moisCourant->modify('first day of this month');

    while ($moisCourant <= $fin) {

        $periode = $moisCourant->format('Y-m');

        $sqlVerif = "
            SELECT id_cotisation

            FROM cotisation

            WHERE id_membre = :id_membre
              AND id_campagne = :id_campagne
              AND periode = :periode

            LIMIT 1
        ";

        $requeteVerif = $connexion->prepare($sqlVerif);

        $requeteVerif->execute([
            ':id_membre' => $idMembre,
            ':id_campagne' => $campagne['id_campagne'],
            ':periode' => $periode
        ]);

        if (!$requeteVerif->fetch()) {

            $dateEcheance = !empty($regle['jour_echeance'])
                ? $moisCourant->format('Y-m-') . str_pad($regle['jour_echeance'], 2, '0', STR_PAD_LEFT)
                : null;

            $sqlInsert = "
                INSERT INTO cotisation
                    (id_membre, id_campagne, id_generation, periode, montant_prevu, date_echeance)
                VALUES
                    (:id_membre, :id_campagne, :id_generation, :periode, :montant_prevu, :date_echeance)
            ";

            $requeteInsert = $connexion->prepare($sqlInsert);

            $requeteInsert->execute([
                ':id_membre' => $idMembre,
                ':id_campagne' => $campagne['id_campagne'],
                ':id_generation' => $idGeneration,
                ':periode' => $periode,
                ':montant_prevu' => $regle['montant'],
                ':date_echeance' => $dateEcheance
            ]);
        }

        $moisCourant->modify('+1 month');
    }
}


/*
|--------------------------------------------------------------------------
| Recalculer le statut d'une cotisation (RG19 : vert/jaune/rouge)
|--------------------------------------------------------------------------
*/

function recalculerStatutCotisation($connexion, $idCotisation)
{
    $sql = "
        SELECT montant_prevu, montant_paye

        FROM cotisation

        WHERE id_cotisation = :id_cotisation
    ";

    $requete = $connexion->prepare($sql);

    $requete->execute([':id_cotisation' => $idCotisation]);

    $cotisation = $requete->fetch();

    if (!$cotisation) {
        return;
    }

    if ($cotisation['montant_paye'] <= 0) {
        $statut = 'NON_PAYE';
    } elseif ($cotisation['montant_paye'] < $cotisation['montant_prevu']) {
        $statut = 'PARTIEL';
    } else {
        $statut = 'PAYE';
    }

    $sqlUpdate = "
        UPDATE cotisation

        SET statut = :statut

        WHERE id_cotisation = :id_cotisation
    ";

    $requeteUpdate = $connexion->prepare($sqlUpdate);

    $requeteUpdate->execute([
        ':statut' => $statut,
        ':id_cotisation' => $idCotisation
    ]);
}


/*
|--------------------------------------------------------------------------
| Libellés d'affichage
|--------------------------------------------------------------------------
*/

function libelleMoisFr($periode)
{
    $mois = [
        '01'=>'Janvier','02'=>'Février','03'=>'Mars','04'=>'Avril',
        '05'=>'Mai','06'=>'Juin','07'=>'Juillet','08'=>'Août',
        '09'=>'Septembre','10'=>'Octobre','11'=>'Novembre','12'=>'Décembre'
    ];

    [$annee, $m] = explode('-', $periode);

    return ($mois[$m] ?? $m) . ' ' . $annee;
}