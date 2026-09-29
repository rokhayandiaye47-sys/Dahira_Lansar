<?php

session_start();


/*
|--------------------------------------------------------------------------
| Si déjà connecté, on va directement au tableau de bord
|--------------------------------------------------------------------------
*/

if (isset($_SESSION['id_utilisateur'])) {

    header("Location: index.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Dahira Lansar Guidick</title>

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

        html {
            scroll-behavior: smooth;
            scroll-padding-top: 78px;
        }

        body {
            font-family: Georgia, 'Times New Roman', serif;
            background: var(--blanc);
            color: var(--vert-texte);
        }

        /* ---------- Barre de navigation ---------- */

        .barre-nav {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 30px;
            background: transparent;
            transition: background 0.3s ease, box-shadow 0.3s ease, padding 0.3s ease;
            font-family: Arial, sans-serif;
        }

        .barre-nav.scrollee {
            background: var(--blanc);
            box-shadow: 0 2px 14px rgba(6,48,26,0.08);
            padding: 12px 30px;
        }

        .barre-nav .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--blanc);
            font-family: Georgia, serif;
            font-size: 1.1rem;
            transition: color 0.3s ease;
        }

        .barre-nav.scrollee .logo {
            color: var(--vert-fonce);
        }

        .barre-nav .logo .sceau-nav {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 1.5px solid currentColor;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            letter-spacing: 0.04em;
        }

        .liens-nav {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .liens-nav a {
            text-decoration: none;
            color: rgba(255,255,255,0.9);
            font-size: 13.5px;
            letter-spacing: 0.02em;
            transition: color 0.3s ease;
        }

        .barre-nav.scrollee .liens-nav a {
            color: var(--vert-texte);
        }

        .liens-nav a:hover {
            color: var(--blanc);
            opacity: 0.8;
        }

        .barre-nav.scrollee .liens-nav a:hover {
            color: var(--vert-profond);
            opacity: 1;
        }

        .bouton-nav {
            padding: 9px 20px;
            border-radius: 4px;
            background: var(--blanc);
            color: var(--vert-fonce);
            text-decoration: none;
            font-size: 13px;
        }

        .barre-nav.scrollee .bouton-nav {
            background: var(--vert-profond);
            color: var(--blanc);
        }

        /* ---------- Section Héro ---------- */

        .hero {
            position: relative;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            overflow: hidden;
            color: var(--blanc);
        }

        .hero video,
        .hero .fond-secours {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
        }

        .hero .fond-secours {
            background: linear-gradient(160deg, var(--vert-fonce) 0%, var(--vert-profond) 55%, var(--vert-fonce) 100%);
        }

        .hero .voile {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(180deg, rgba(6,48,26,0.55) 0%, rgba(6,48,26,0.75) 60%, rgba(6,48,26,0.92) 100%);
            z-index: 1;
        }

        .hero-contenu {
            position: relative;
            z-index: 2;
            padding: 20px;
            animation: apparition 1.4s ease both;
        }

        @keyframes apparition {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: none; }
        }

        .sceau {
            width: 68px;
            height: 68px;
            border: 1.5px solid rgba(255,255,255,0.7);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 22px;
            font-size: 22px;
            letter-spacing: 0.05em;
        }

        .hero h1 {
            font-size: 2.6rem;
            font-weight: normal;
            letter-spacing: 0.03em;
            margin-bottom: 10px;
        }

        .hero .accroche {
            font-size: 1.05rem;
            font-style: italic;
            color: var(--vert-clair);
            margin-bottom: 34px;
        }

        .boutons-hero {
            display: flex;
            gap: 14px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .bouton {
            display: inline-block;
            padding: 13px 28px;
            border-radius: 4px;
            text-decoration: none;
            font-family: Arial, sans-serif;
            font-size: 14px;
            letter-spacing: 0.03em;
            transition: transform 0.15s ease;
        }

        .bouton:hover {
            transform: translateY(-2px);
        }

        .bouton-plein {
            background: var(--blanc);
            color: var(--vert-fonce);
        }

        .bouton-contour {
            background: transparent;
            color: var(--blanc);
            border: 1px solid rgba(255,255,255,0.75);
        }

        .defiler {
            position: absolute;
            bottom: 26px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2;
            color: rgba(255,255,255,0.75);
            font-size: 12px;
            font-family: Arial, sans-serif;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        /* ---------- Sections générales ---------- */

        section {
            padding: 90px 20px;
        }

        .conteneur {
            max-width: 780px;
            margin: 0 auto;
        }

        .conteneur-large {
            max-width: 980px;
            margin: 0 auto;
        }

        .etiquette {
            display: block;
            text-align: center;
            font-family: Arial, sans-serif;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--vert-profond);
            margin-bottom: 10px;
        }

        h2 {
            text-align: center;
            font-weight: normal;
            font-size: 2rem;
            color: var(--vert-fonce);
            margin-bottom: 26px;
        }

        .texte-apropos {
            font-size: 1.05rem;
            line-height: 1.9;
            color: #33453c;
            text-align: center;
        }

        .separateur {
            width: 60px;
            height: 2px;
            background: var(--vert-profond);
            margin: 30px auto;
            opacity: 0.4;
        }

        /* ---------- Section Hommage ---------- */

        .hommage {
            background: var(--vert-clair);
        }

        .grille-hommage {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        .carte-hommage {
            text-align: center;
        }

        .portrait {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            overflow: hidden;
            margin: 0 auto 16px;
            background: linear-gradient(160deg, #ffffff, var(--vert-clair));
            border: 2px solid var(--vert-profond);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--vert-profond);
            font-family: Arial, sans-serif;
            font-size: 12px;
            text-align: center;
            padding: 10px;
        }

        .portrait img {
            display: block;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            object-position: center;
        }

        .carte-hommage h3 {
            font-weight: normal;
            font-size: 1.05rem;
            color: var(--vert-fonce);
            margin-bottom: 4px;
        }

        .carte-hommage .dates {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #5c7568;
        }

        .mention-hommage {
            text-align: center;
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #5c7568;
            margin-top: 40px;
            font-style: italic;
        }

        /* ---------- Section Bureau ---------- */

        .grille-bureau {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 34px;
            margin-top: 50px;
        }

        .carte-bureau {
            text-align: center;
        }

        .portrait-bureau {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            overflow: hidden;
            margin: 0 auto 16px;
            background: var(--vert-clair);
            border: 2px solid var(--vert-profond);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--vert-profond);
            font-family: Arial, sans-serif;
            font-size: 12px;
            padding: 10px;
        }

        /* Une fois la photo ajoutée : <img> à l'intérieur avec cette classe */
        .portrait-bureau img {
            display: block;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            object-position: center;
        }

        .carte-bureau h3 {
            font-weight: normal;
            font-size: 1.05rem;
            color: var(--vert-fonce);
            margin-bottom: 4px;
        }

        .carte-bureau .fonction {
            font-family: Arial, sans-serif;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--vert-profond);
        }

        .carte-bureau .generation-bureau {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #5c7568;
            margin-top: 2px;
        }

        /* ---------- Section Générations ---------- */

        .grille-generations {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 40px;
        }

        .carte-generation {
            border: 1px solid #d7e6dc;
            border-radius: 8px;
            padding: 26px 20px;
            text-align: center;
        }

        .carte-generation h3 {
            font-weight: normal;
            color: var(--vert-fonce);
            margin-bottom: 8px;
        }

        .carte-generation p {
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #5c7568;
            line-height: 1.6;
        }

        /* ---------- Section Contact ---------- */

        .contact {
            background: var(--vert-fonce);
            color: var(--blanc);
        }

        .contact h2 {
            color: var(--blanc);
        }

        .contact .etiquette {
            color: var(--vert-clair);
        }

        .grille-contact {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 30px;
            margin-top: 40px;
            text-align: center;
            font-family: Arial, sans-serif;
        }

        .bloc-contact .titre-contact {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255,255,255,0.6);
            margin-bottom: 8px;
        }

        .bloc-contact .valeur-contact {
            font-size: 15px;
            color: var(--blanc);
        }

        /* ---------- Pied de page ---------- */

        footer {
            background: #041f11;
            color: rgba(255,255,255,0.7);
            text-align: center;
            padding: 30px 20px;
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        footer a {
            color: var(--blanc);
        }

        /* ---------- Apparition au défilement ---------- */

        .reveal {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity 0.9s ease, transform 0.9s ease;
        }

        .reveal.visible {
            opacity: 1;
            transform: none;
        }

        @media (max-width: 700px) {

            .liens-nav {
                gap: 16px;
            }

            .liens-nav a {
                font-size: 12px;
            }

        }

        @media (prefers-reduced-motion: reduce) {
            * { animation: none !important; transition: none !important; }
        }

    </style>

</head>

<body>

    <!-- ============================================================
         BARRE DE NAVIGATION
         ============================================================ -->

    <nav class="barre-nav" id="barre-nav">

        <a class="logo" href="#accueil">
            <span class="sceau-nav">DLG</span>
            Dahira Lansar Guidick
        </a>

        <div class="liens-nav">
            <a href="#accueil">Accueil</a>
            <a href="#a-propos">À propos</a>
            <a href="#bureau">Dirigeants</a>
            <a href="#generations">Générations</a>
            <a href="#contact">Contact</a>
            <a class="bouton-nav" href="auth/connexion.php">Se connecter</a>
        </div>

    </nav>

    <!-- ============================================================
         HÉRO — REMPLACE LA VIDÉO CI-DESSOUS PAR LA TIENNE
         ============================================================ -->

    <section class="hero" id="accueil">

        <!--
            Dépose ta vidéo dans un dossier "assets/video/" et
            décommente la balise <video> ci-dessous. En attendant,
            le fond dégradé vert sert de secours (.fond-secours).
        -->

        <!--
        <video autoplay muted loop playsinline poster="assets/images/accueil-poster.jpg">
            <source src="assets/video/accueil.mp4" type="video/mp4">
        </video>
        -->

        <div class="fond-secours"></div>

        <div class="voile"></div>

        <div class="hero-contenu">

            <div class="sceau">DLG</div>

            <h1>Dahira Lansar Guidick</h1>

            <p class="accroche">
                Une communauté, trois générations, une même foi
            </p>

            <div class="boutons-hero">

                <a class="bouton bouton-plein" href="auth/connexion.php">
                    Se connecter
                </a>


            </div>

        </div>

        <div class="defiler">
            ↓ Découvrir
        </div>

    </section>

    <!-- ============================================================
         À PROPOS — TEXTE D'EXEMPLE, À REMPLACER PAR TON HISTORIQUE
         ============================================================ -->

    <section id="a-propos">

        <div class="conteneur reveal">

            <span class="etiquette">À propos</span>

            <h2>Notre histoire</h2>

            <p class="texte-apropos">
                Fondée en [ANNÉE DE CRÉATION À COMPLÉTER], la Dahira Lansar Guidick
                rassemble depuis lors les familles et fidèles autour de la prière,
                de l'entraide et de la mémoire de nos guides spirituels. Ce qui a
                commencé comme un petit cercle de récitation s'est transformé, au
                fil des années, en une communauté structurée en trois générations —
                les Aînés, les Jeunes et le Village — unies par les mêmes valeurs
                de solidarité et de foi transmises de génération en génération.
            </p>

            <div class="separateur"></div>

            <p class="texte-apropos">
                [Complète ici avec l'histoire réelle : qui a fondé la Dahira,
                dans quelles circonstances, les grandes étapes de son évolution
                jusqu'à aujourd'hui.]
            </p>

        </div>

    </section>

    <!-- ============================================================
         HOMMAGE — REMPLACE LES CADRES PAR LES VRAIES PHOTOS
         ============================================================ -->

    <section class="hommage" id="hommage">

        <div class="conteneur reveal">

            <span class="etiquette">En mémoire</span>

            <h2>Nos guides disparus</h2>

            <div class="grille-hommage">

                <!--
                    Pour chaque marabout : remplace le contenu du
                    div .portrait par une image, ex :
                    <img src="assets/images/marabout-1.jpg" alt="...">
                    et adapte le style .portrait (object-fit: cover)
                -->

                <div class="carte-hommage">
                    <div class="portrait"> <img src="image/malick.jpeg" alt="Cheikh Seydi Hadji Malick SY"> </div>
                    <h3>Cheikh Seydi Hadji Malick SY</h3>
                    <div class="dates">1853— 1922</div>
                </div>

                <div class="carte-hommage">
                    <div class="portrait"> <img src="image/babacar.jpeg" alt="Serigne Babacar SY"> </div>
                    <h3>Serigne Babacar SY</h3>
                    <div class="dates">1885— 1957</div>
                </div>

                <div class="carte-hommage">
                    <div class="portrait"> <img src="image/jamil.jpeg" alt="Seydi Mouhamadoul Moustapha SY Al Jamil"> </div>
                    <h3>Seydi Mouhamadoul Moustapha SY Al Jamil</h3>
                    <div class="dates">1916— 1993</div>
                </div>

            </div>

            <p class="mention-hommage">
                Qu'Allah les accueille dans Son vaste Paradis et illumine leurs tombes.
            </p>

        </div>

    </section>

    <!-- ============================================================
         LE BUREAU — RESPONSABLES ACTUELS (pas tous les membres)
         ============================================================ -->

    <section id="bureau">

        <div class="conteneur-large reveal">

            <span class="etiquette">Direction</span>

            <h2>Dirigeants</h2>

            <div class="grille-bureau">

                <!--
                    Une carte par responsable clé. Remplace le contenu
                    de .portrait-bureau par une image quand tu l'as :
                    <img src="assets/images/president.jpg" alt="...">
                -->


                <div class="carte-bureau">
                    <div class="portrait-bureau"><img src="" alt=""></div>
                    <h3>[Nom]</h3>
                    <div class="fonction">Responsable — Aînés</div>
                    <div class="generation-bureau">Génération des Aînés</div>
                </div>

                <div class="carte-bureau">
                    <div class="portrait-bureau"><img src="" alt=""></div>
                    <h3>[Nom]</h3>
                    <div class="fonction">Responsable — Jeunes</div>
                    <div class="generation-bureau">Génération des Jeunes</div>
                </div>

                <div class="carte-bureau">
                    <div class="portrait-bureau"><img src="" alt=""></div>
                    <h3>[Nom]</h3>
                    <div class="fonction">Responsable — Village</div>
                    <div class="generation-bureau">Génération du Village</div>
                </div>

            </div>

        </div>

    </section>

    <!-- ============================================================
         LES 3 GÉNÉRATIONS
         ============================================================ -->

    <section id="generations" style="background:var(--vert-clair);">

        <div class="conteneur reveal">

            <span class="etiquette">Organisation</span>

            <h2>Trois générations, une communauté</h2>

            <div class="grille-generations">

                <div class="carte-generation">
                    <h3>Aînés</h3>
                    <p>Les fondateurs et gardiens de la mémoire de la Dahira.</p>
                </div>

                <div class="carte-generation">
                    <h3>Jeunes</h3>
                    <p>La relève, active et engagée dans la vie de la communauté.</p>
                </div>

                <div class="carte-generation">
                    <h3>Village</h3>
                    <p>Les membres restés proches de la terre d'origine.</p>
                </div>

            </div>

        </div>

    </section>

    <!-- ============================================================
         DEMANDE D'ADHÉSION (placeholder — pas encore fonctionnel)
         ============================================================ -->

    <section id="demande-adhesion">

        <div class="conteneur reveal" style="text-align:center;">

            <span class="etiquette">Rejoindre la Dahira</span>

            <h2>Devenir membre</h2>

            <p class="texte-apropos" style="margin-bottom:30px;">
                Les comptes sont créés par les responsables de chaque génération.
                Contacte le gestionnaire des membres de ta génération pour obtenir
                ton accès à la plateforme.
            </p>

        </div>

    </section>

    <!-- ============================================================
         CONTACT — INFOS D'EXEMPLE, À REMPLACER
         ============================================================ -->

    <section class="contact" id="contact">

        <div class="conteneur reveal">

            <span class="etiquette">Nous joindre</span>

            <h2>Contact</h2>

            <div class="grille-contact">

                <div class="bloc-contact">
                    <div class="titre-contact">Téléphone</div>
                    <div class="valeur-contact">+221 77 539 34 26</div>
                </div>

                <div class="bloc-contact">
                    <div class="titre-contact">Email</div>
                    <div class="valeur-contact">contact@dahira-lansar-guidick.org</div>
                </div>

                <div class="bloc-contact">
                    <div class="titre-contact">Adresse</div>
                    <div class="valeur-contact">Dakar, Sénégal</div>
                </div>

            </div>

        </div>

    </section>

    <footer>
        Dahira Lansar Guidick — Registre numérique<br>
        <a href="auth/connexion.php">Se connecter</a>
    </footer>

    <script>

        const barreNav = document.getElementById('barre-nav');

        window.addEventListener('scroll', () => {

            if (window.scrollY > 60) {
                barreNav.classList.add('scrollee');
            } else {
                barreNav.classList.remove('scrollee');
            }

        });

        const elements = document.querySelectorAll('.reveal');

        const observateur = new IntersectionObserver((entrees) => {

            entrees.forEach((entree) => {

                if (entree.isIntersecting) {
                    entree.target.classList.add('visible');
                }

            });

        }, { threshold: 0.15 });

        elements.forEach((el) => observateur.observe(el));

    </script>

</body>

</html>