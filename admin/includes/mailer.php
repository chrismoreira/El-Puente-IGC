<?php
require_once __DIR__ . '/../../admin/vendor/autoload.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function mailer_send(string $to_email, string $to_name, string $subject, string $body_html): bool {
    $cfg = db_get('SELECT * FROM email_settings WHERE active = 1 LIMIT 1');
    if (!$cfg) return false;

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($cfg['from_email'], $cfg['from_name']);
        $mail->addAddress($to_email, $to_name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body_html;
        $mail->AltBody = strip_tags($body_html);

        switch ($cfg['provider']) {
            case 'mailgun':
                $mail->Host       = 'smtp.mailgun.org';
                $mail->SMTPAuth   = true;
                $mail->Username   = $cfg['smtp_user'];
                $mail->Password   = decrypt($cfg['smtp_pass']);
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                break;

            case 'sendgrid':
                $mail->Host       = 'smtp.sendgrid.net';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'apikey';
                $mail->Password   = decrypt($cfg['api_key']);
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                break;

            case 'resend':
                $mail->Host       = 'smtp.resend.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'resend';
                $mail->Password   = decrypt($cfg['api_key']);
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                break;

            default: // smtp
                $mail->Host       = $cfg['smtp_host'];
                $mail->Port       = (int) $cfg['smtp_port'];
                $mail->SMTPAuth   = true;
                $mail->Username   = $cfg['smtp_user'];
                $mail->Password   = decrypt($cfg['smtp_pass']);
                $mail->SMTPSecure = $cfg['smtp_port'] == 465
                    ? PHPMailer::ENCRYPTION_SMTPS
                    : PHPMailer::ENCRYPTION_STARTTLS;
                break;
        }

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mailer error: ' . $mail->ErrorInfo);
        return false;
    }
}
