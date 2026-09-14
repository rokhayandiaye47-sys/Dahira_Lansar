<?php

session_start();

if (!isset($_SESSION['id_utilisateur'])) {

    header("Location: accueil.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Construire le menu selon le rôle connecté
|--------------------------------------------------------------------------
*/

$liens = [];

switch ($_SESSION['id_role']) {

    case 1: // Administrateur général

        $liens[] = ['label' => 'Générations', 'url' => 'generations/index.php'];
        $liens[] = ['label' => 'Membres', 'url' => 'membres/index.php'];
        $liens[] = ['label' => 'Cotisations', 'url' => 'cotisations/generation.php'];
        $liens[] = ['label' => 'Caisse', 'url' => 'caisse/index.php'];
        $liens[] = ['label' => 'Statistiques', 'url' => 'statistiques/index.php'];
        $liens[] = ['label' => 'Campagnes Gamou', 'url' => 'campagne/index.php'];
              $liens[] = ['label' => 'Comptes à responsabilité', 'url' => 'utilisateurs/index.php'];
        $liens[] = ['label' => 'Mon espace personnel', 'url' => 'membres/espace.php'];
        break;

      case 2: // Responsable de génération

        $liens[] = ['label' => 'Ma génération', 'url' => 'generations/index.php'];
        $liens[] = ['label' => 'Cotisations de ma génération', 'url' => 'cotisations/generation.php'];
        $liens[] = ['label' => 'Statistiques', 'url' => 'statistiques/index.php'];
        $liens[] = ['label' => 'Mon espace personnel', 'url' => 'membres/espace.php'];
        break;

    case 3: // Gestionnaire des membres

        $liens[] = ['label' => 'Membres', 'url' => 'membres/index.php'];
        $liens[] = ['label' => 'Mon espace personnel', 'url' => 'membres/espace.php'];
        break;

    case 4: // Trésorier

        $liens[] = ['label' => 'Caisse', 'url' => 'caisse/index.php'];
        $liens[] = ['label' => 'Dépenses', 'url' => 'depenses/index.php'];
        $liens[] = ['label' => 'Paiements', 'url' => 'paiements/index.php'];
        $liens[] = ['label' => 'Cotisations de ma génération', 'url' => 'cotisations/generation.php'];
        $liens[] = ['label' => 'Statistiques', 'url' => 'statistiques/index.php'];
        $liens[] = ['label' => 'Mon espace personnel', 'url' => 'membres/espace.php'];
        break;

    case 5: // Membre simple

        $liens[] = ['label' => 'Mon espace', 'url' => 'membres/espace.php'];
        $liens[] = ['label' => 'Mes cotisations', 'url' => 'cotisations/index.php'];
        break;
}


/*
|--------------------------------------------------------------------------
| Notifications : accessible à tous les rôles
|--------------------------------------------------------------------------
*/

require_once "notifications/fonctions.php";

$nombreNonLues = compterNotificationsNonLues($connexion, $_SESSION['id_utilisateur']);

$liens[] = [
    'label' => 'Notifications' . ($nombreNonLues > 0 ? " ($nombreNonLues)" : ''),
    'url' => 'notifications/index.php'
];


/*
|--------------------------------------------------------------------------
| Initiales pour l'avatar
|--------------------------------------------------------------------------
*/

$initiales = mb_strtoupper(mb_substr($_SESSION['prenom'], 0, 1) . mb_substr($_SESSION['nom'], 0, 1));

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Accueil - Dahira Lansar Guidick</title>

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
            min-height: 100vh;
            padding: 40px 20px;
        }

        .conteneur {
            max-width: 640px;
            margin: 0 auto;
        }

        /* ---------- Carte profil ---------- */

        .carte-profil {
            background: var(--vert-fonce);
            color: var(--blanc);
            border-radius: 14px;
            padding: 30px 28px;
            margin-bottom: 24px;
            box-shadow: 0 10px 30px rgba(6,48,26,0.18);
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .avatar {
            width: 58px;
            height: 58px;
            border-radius: 50%;
            border: 1.5px solid rgba(255,255,255,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Georgia, serif;
            font-size: 20px;
            flex-shrink: 0;
        }

        .carte-profil h1 {
            font-family: Georgia, serif;
            font-weight: normal;
            font-size: 1.3rem;
            margin-bottom: 3px;
        }

        .carte-profil .role {
            font-size: 12.5px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--vert-clair);
            opacity: 0.9;
        }

        /* ---------- Menu ---------- */

        .menu {
            display: grid;
            gap: 10px;
            margin-bottom: 26px;
        }

        .menu a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 18px;
            background: var(--blanc);
            border-radius: 10px;
            text-decoration: none;
            color: var(--vert-fonce);
            font-weight: bold;
            border: 1px solid #d7e6dc;
            box-shadow: 0 2px 8px rgba(6,48,26,0.05);
            transition: border-color 0.2s ease, transform 0.15s ease;
        }

        .menu a:hover {
            border-color: var(--vert-profond);
            transform: translateX(2px);
        }

        .menu a .fleche {
            color: var(--vert-profond);
            opacity: 0.6;
        }

        /* ---------- Bande hommage ---------- */

        .hommage {
            background: var(--blanc);
            border: 1px solid #d7e6dc;
            border-radius: 12px;
            padding: 20px 22px;
            margin-bottom: 26px;
            text-align: center;
        }

        .hommage .etiquette {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--vert-profond);
            margin-bottom: 14px;
            font-family: Arial, sans-serif;
        }

        .rangee-portraits {
            display: flex;
            justify-content: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .mini-portrait {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            border: 1.5px solid var(--vert-profond);
            background: var(--vert-clair);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            color: var(--vert-profond);
            text-align: center;
            padding: 4px;
            font-family: Arial, sans-serif;
        }

        .mini-portrait img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            border-radius: inherit;
        }

        .hommage .mention {
            margin-top: 12px;
            font-size: 12px;
            color: #5c7568;
            font-style: italic;
            font-family: Georgia, serif;
        }

        /* ---------- Déconnexion ---------- */

        .lien-deconnexion {
            display: block;
            text-align: center;
            color: #7a1f1f;
            text-decoration: none;
            font-size: 13px;
        }

    </style>
    <link rel="stylesheet" href="assets/css/theme.css">

</head>

<body>

<div class="conteneur">

    <div class="carte-profil">

        <div class="avatar"><?= htmlspecialchars($initiales) ?></div>

        <div>
            <h1><?= htmlspecialchars($_SESSION['prenom']) ?> <?= htmlspecialchars($_SESSION['nom']) ?></h1>
            <div class="role"><?= htmlspecialchars($_SESSION['nom_role']) ?></div>
        </div>

    </div>

    <div class="menu">

        <?php foreach ($liens as $lien): ?>

            <a href="<?= htmlspecialchars($lien['url']) ?>">
                <?= htmlspecialchars($lien['label']) ?>
                <span class="fleche">→</span>
            </a>

        <?php endforeach; ?>

    </div>

    <div class="hommage">

        <div class="etiquette">En mémoire de nos guides</div>

        <div class="rangee-portraits">

            <div class="mini-portrait">
                <img src="image/malick.jpeg" alt="Cheikh Seydi Hadji Malick SY">
            </div>
            <div class="mini-portrait">
                <img src="image/babacar.jpeg" alt="Serigne Babacar SY">
            </div>
            <div class="mini-portrait">
                <img src="image/jamil.jpeg" alt="Seydi Mouhamadoul Moustapha SY Al Jamil">
            </div>

        </div>

        <p class="mention">Qu'Allah les accueille dans Son vaste Paradis.</p>

    </div>

    <a class="lien-deconnexion" href="auth/deconnexion.php">
        Se déconnecter
    </a>

</div>

</body>

</html>