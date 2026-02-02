<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/env.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendMail($to, $toName, $subject, $bodyHtml, $fromName = null)
{
    $mail = new PHPMailer(true);

    try {
        // --- Konfigurasi dasar SMTP ---
        $mail->isSMTP();
        $mail->Host       = $_ENV['MAIL_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAIL_USERNAME'];
        $mail->Password   = $_ENV['MAIL_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SMTPS (port 465)
        $mail->Port       = $_ENV['MAIL_PORT'];

        // --- Pengirim ---
        $fromName = $fromName ?: $_ENV['MAIL_FROM_NAME'];
        $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'], $fromName);

        // --- Penerima ---
        $mail->addAddress($to, $toName);
        if (!empty($_ENV['MAIL_REPLY_TO'])) {
            $mail->addReplyTo($_ENV['MAIL_REPLY_TO'], $_ENV['MAIL_REPLY_NAME'] ?? '');
        }
        if (!empty($_ENV['MAIL_BCC'])) {
            $mail->addBCC($_ENV['MAIL_BCC']);
        }

        // --- Konten email ---
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $bodyHtml;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
