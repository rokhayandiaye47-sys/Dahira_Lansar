<?php

require_once __DIR__ . "/protection.php";

$message = $_SESSION['message_code'] ?? '';
unset($_SESSION['message_code']);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Code personnel - Dahira Lansar Guidick</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f2f4f7;

            min-height: 100vh;

            display: flex;
            justify-content: center;
            align-items: center;
        }

        .conteneur {
            width: 400px;
            background: white;

            padding: 35px;

            border-radius: 12px;

            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        h1 {
            text-align: center;
            margin-bottom: 10px;
            color: #1f2937;
        }

        .sous-titre {
            text-align: center;
            color: #6b7280;
            margin-bottom: 25px;
        }

        .message {
            background: #fee2e2;
            color: #991b1b;

            padding: 12px;

            border-radius: 6px;

            margin-bottom: 20px;

            text-align: center;
        }

        .champ {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input {
            width: 100%;

            padding: 12px;

            border: 1px solid #d1d5db;

            border-radius: 6px;

            font-size: 15px;
        }

        input:focus {
            outline: none;
            border-color: #06c23e;
        }

        button {
            width: 100%;

            padding: 12px;

            border: none;

            border-radius: 6px;

            background: #06c23e;

            color: white;

            font-size: 16px;

            cursor: pointer;
        }

        button:hover {
            background: #06c23e
        }

    </style>
    <link rel="stylesheet" href="../assets/css/theme.css">

</head>

<body>

<div class="conteneur">

    <h1>Vérification requise</h1>

    <p class="sous-titre">
        Ton rôle nécessite le code personnel qui t'a été envoyé par email
    </p>

    <?php if (!empty($message)): ?>

        <div class="message">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>


    <form action="traiter_verification_code.php" method="POST">

        <div class="champ">

            <label for="code_personnel">
                Code personnel
            </label>

            <input
                type="text"
                id="code_personnel"
                name="code_personnel"
                placeholder="Ex : DLG-ADMIN-007"
                required
                autofocus
            >

        </div>


        <button type="submit">
            Valider
        </button>

    </form>

</div>

</body>

</html>
