<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer-6.10.0/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer-6.10.0/src/SMTP.php';
require_once __DIR__ . '/PHPMailer-6.10.0/src/Exception.php';

function sendMail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ];
        $mail->SMTPDebug = 0; // PRODUCTION MODE

        //$mail->Debugoutput = 'html';

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'zabtugap2@gmail.com';
        $mail->Password = 'qmlzeedpohxrzdkl';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('zabtugap2@gmail.com', 'AutoMarket');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "<pre>";
        echo "Mailer Error: " . $mail->ErrorInfo;
        echo "</pre>";
        return false;
    }
}

