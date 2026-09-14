<?php

require_once "../auth/protection_role.php";

verifierRole([1, 2, 3, 4, 5]);

require_once "fonctions.php";

$idNotification = (int) ($_GET['id_notification'] ?? 0);


/*
|--------------------------------------------------------------------------
| Vérifier que la notification appartient bien à l'utilisateur connecté
|--------------------------------------------------------------------------
*/

$connexion->prepare("
    UPDATE notification

    SET lu = 1

    WHERE id_notification = :id_notification
      AND id_utilisateur = :id_utilisateur
")->execute([
    ':id_notification' => $idNotification,
    ':id_utilisateur' => $_SESSION['id_utilisateur']
]);

header("Location: index.php");
exit;