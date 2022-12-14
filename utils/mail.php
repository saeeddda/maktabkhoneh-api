<?php

use PHPMailer\PHPMailer\PHPMailer;

include_once $_SERVER['DOCUMENT_ROOT']  . '/vendor/autoload.php';
include_once $_SERVER['DOCUMENT_ROOT']  . '/consts/configs.php';

function send_mail($to, $subject, $content){
    try {
        $mail = new PHPMailer(true);
        //Server settings
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;        //Enable verbose debug output
        $mail->isSMTP();                                 //Send using SMTP
        $mail->Host       = MAIL_HOST;                   //Set the SMTP server to send through
        $mail->Port       = MAIL_PORT;                   //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        $mail->SMTPAuth   = true;                        //Enable SMTP authentication
        $mail->Username   = MAIL_USERNAME;               //SMTP username
        $mail->Password   = MAIL_PASSWORD;               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; //Enable implicit TLS encryption

        //Recipients
        $mail->setFrom(MAIL_FROM, MAIL_NAME);
        $mail->addAddress($to);

        //Content
        //$mail->isHTML(true);
        $mail->CharSet = "UTF-8";
        $mail->Subject = $subject;
        $mail->Body    = $content;

        return $mail->send();
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        echo "Message could not be sent. Mailer Error: {$e->getMessage()}";
    }
}
