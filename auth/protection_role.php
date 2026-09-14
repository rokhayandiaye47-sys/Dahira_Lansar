
<?php

require_once __DIR__ . "/protection.php";

/*
|--------------------------------------------------------------------------
| Rôles qui utilisent un code personnel
|--------------------------------------------------------------------------
*/

const ROLES_AVEC_CODE_PERSONNEL = [1, 2, 3, 4];


/*
|--------------------------------------------------------------------------
| Vérification du rôle
|--------------------------------------------------------------------------
*/

function verifierRole(array $rolesAutorises): void
{
    // Vérifier que l'utilisateur est connecté
    if (!isset($_SESSION['id_utilisateur'])) {
        header("Location: /dahira/auth/connexion.php");
        exit;
    }

    // Vérifier que le rôle existe
    if (!isset($_SESSION['id_role'])) {
        http_response_code(403);
        exit("Accès refusé : rôle non défini.");
    }

    // Récupérer le rôle
    $idRole = (int) $_SESSION['id_role'];

    /*
    |--------------------------------------------------------------------------
    | Vérifier si le rôle est autorisé
    |--------------------------------------------------------------------------
    */

    if (!in_array($idRole, $rolesAutorises, true)) {

        http_response_code(403);

        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">

            <title>Accès refusé</title>

            <style>
                * {
                    box-sizing: border-box;
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
                    background: linear-gradient(135deg, #dcefe3 0%, #f4faf6 48%, #cfe6d8 100%);
                    margin: 0;
                    padding: 20px;
                    min-height: 100vh;

                    display: flex;
                    justify-content: center;
                    align-items: center;
                }

                .message {
                    background: var(--blanc);
                    width: 90%;
                    max-width: 500px;
                    padding: 38px 32px;

                    text-align: center;
                    border-radius: 12px;
                    border-top: 4px solid var(--vert-profond);
                    box-shadow: 0 14px 34px rgba(6, 48, 26, 0.14);
                }

                h1 {
                    color: #B42318;
                    font-family: Georgia, serif;
                    font-weight: normal;
                    margin: 0 0 16px;
                }

                p {
                    color: var(--vert-texte);
                    line-height: 1.6;
                    margin: 0 0 12px;
                }

                .bouton {
                    display: inline-block;
                    margin-top: 20px;
                    padding: 10px 18px;
                    background: var(--vert-profond);
                    color: var(--blanc);
                    text-decoration: none;
                    border-radius: 8px;
                    transition: background-color 0.2s ease, transform 0.2s ease;
                }

                .bouton:hover {
                    background: var(--vert-fonce);
                    transform: translateY(-1px);
                }
            </style>
        </head>

        <body>

            <div class="message">

                <h1>Accès refusé</h1>

                <p>
                    Bonjour
                    <?= htmlspecialchars($_SESSION['prenom'] ?? '') ?>
                    <?= htmlspecialchars($_SESSION['nom'] ?? '') ?>.
                </p>

                <p>
                    Votre rôle
                    <strong>
                        <?= htmlspecialchars($_SESSION['nom_role'] ?? '') ?>
                    </strong>
                    ne vous permet pas d'accéder à cette page.
                </p>

                <a class="bouton" href="/dahira/index.php">
                    Retour à l'accueil
                </a>

            </div>

        </body>
        </html>

        <?php

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Vérifier le code personnel
    |--------------------------------------------------------------------------
    */

    if (
        in_array($idRole, ROLES_AVEC_CODE_PERSONNEL, true)
        && ($_SESSION['code_verifie'] ?? false) !== true
    ) {

        // Mémoriser la page demandée
        $_SESSION['redirection_apres_code'] =
            $_SERVER['REQUEST_URI'];

        // Demander le code personnel
        header("Location: /dahira/auth/verification_code.php");
        exit;
    }
}

