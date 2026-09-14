<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . "/../phpmailer/src/Exception.php";
require_once __DIR__ . "/../phpmailer/src/PHPMailer.php";
require_once __DIR__ . "/../phpmailer/src/SMTP.php";

function envoyerEmail(string $destinataire, string $nomDestinataire, string $sujet, string $corps): bool
{
    $mailer = new PHPMailer(true);

    try {
        $mailer->isSMTP();
        $mailer->Host = getenv('DAHIRA_SMTP_HOST') ?: 'localhost';
        $mailer->Port = (int) (getenv('DAHIRA_SMTP_PORT') ?: 25);
        $mailer->SMTPAuth = filter_var(getenv('DAHIRA_SMTP_AUTH') ?: false, FILTER_VALIDATE_BOOLEAN);

        if ($mailer->SMTPAuth) {
            $mailer->Username = getenv('DAHIRA_SMTP_USERNAME') ?: '';
            $mailer->Password = getenv('DAHIRA_SMTP_PASSWORD') ?: '';
        }

        $mailer->CharSet = 'UTF-8';
        $mailer->setFrom(
            getenv('DAHIRA_MAIL_FROM') ?: 'no-reply@localhost',
            getenv('DAHIRA_MAIL_FROM_NAME') ?: 'Dahira Lansar Guidick'
        );
        $mailer->addAddress($destinataire, $nomDestinataire);
        $mailer->isHTML(true);
        $mailer->Subject = $sujet;
        $mailer->Body = $corps;

        return $mailer->send();
    } catch (Exception $exception) {
        error_log('Erreur d\'envoi d\'email : ' . $exception->getMessage());
        return false;
    }
}