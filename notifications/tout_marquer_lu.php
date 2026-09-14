<?php

require_once "../auth/protection_role.php";

verifierRole([1, 2, 3, 4, 5]);

require_once "fonctions.php";

$connexion->prepare("
    UPDATE notification

    SET lu = 1

    WHERE id_utilisateur = :id_utilisateur
      AND lu = 0
")->execute([
    ':id_utilisateur' => $_SESSION['id_utilisateur']
]);

header("Location: index.php");
exit;