<?php

namespace services;

use Config;
use Exception;
use general\Logger;
use general\PHPMailer\src\PHPMailer;

class MailService
{
    private PHPMailer $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);
		$this->mail->CharSet = 'UTF-8';
        $this->setup();
    }

    private function setup()
    {
        // Настройка PHPMailer
        $this->mail->isSMTP();
        $this->mail->Host = Config::MAIL['HOST'];
        $this->mail->SMTPAuth = true;
        $this->mail->Username = Config::MAIL['USERNAME'];
        $this->mail->Password = Config::MAIL['PASSWORD'];
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $this->mail->Port = Config::MAIL['PORT'];

        //$this->mail->SMTPDebug = 2; // Включает вывод подробных ошибок (1 для простых сообщений, 2 для подробных)
        //$this->mail->Debugoutput = function($str, $level) { Logger::logMessage("Debug level $level; message: $str"); };
    }

    /**
     * @param $to
     * @param $subject
     * @param $body
     * @param bool $isHtml
     * @return bool
     */
    public function sendEmail($to, $subject, $body, bool $isHtml = true): bool
    {
        try {
            $this->mail->setFrom(Config::MAIL['USERNAME'], Config::COMPANY['NAME']);
			$this->mail->clearAddresses();
            $this->mail->addAddress($to);

            $this->mail->isHTML($isHtml);
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;

            if (!$isHtml) {
                $this->mail->AltBody = strip_tags($body);
            }

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            Logger::logMessage("Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}");
            return false;
        }
    }
}