<?php

namespace App\Core;

use App\Models\Setting;

/**
 * Minimal mail sender.
 *
 * Chooses between PHP's `mail()` and a basic SMTP implementation depending on settings.
 * Intended for low-volume transactional emails.
 */
class Mail
{
    private $to = [];
    private $subject = '';
    private $body = '';
    private $headers = [];
    private $isHtml = true;

    public function to($email, $name = null)
    {
        $this->to[] = $name ? "$name <$email>" : $email;
        return $this;
    }

    public function subject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    public function html($body)
    {
        $this->body = $body;
        $this->isHtml = true;
        return $this;
    }

    public function text($body)
    {
        $this->body = $body;
        $this->isHtml = false;
        return $this;
    }

    public function template($template, $data = [])
    {
        $templatePath = __DIR__ . '/../../resources/views/emails/' . $template . '.php';
        
        if (!file_exists($templatePath)) {
            throw new \Exception("Email template {$template} not found");
        }

        // Render the PHP email template into a string.
        ob_start();
        extract($data);
        require $templatePath;
        $content = ob_get_clean();

        $this->html($content);
        return $this;
    }

    public function send()
    {
        $settings = Setting::get();
        
        // If SMTP is not configured, fall back to PHP's mail().
        if (empty($settings->smtp_host)) {
            return $this->sendWithPhpMail();
        }

        return $this->sendWithSmtp($settings);
    }

    private function sendWithPhpMail()
    {
        $headers = $this->buildHeaders();
        $recipients = array_map([$this, 'extractEmail'], $this->to);
        $to = implode(', ', $recipients);

        return mail($to, $this->subject, $this->body, $headers);
    }

    private function sendWithSmtp($settings)
    {
        $socket = fsockopen($settings->smtp_host, (int)$settings->smtp_port, $errno, $errstr, 30);
        
        if (!$socket) {
            return false;
        }

        $this->readResponse($socket);

        $this->sendCommand($socket, "EHLO " . $_SERVER['SERVER_NAME']);
        
        if ($settings->smtp_secure === 'tls') {
            // STARTTLS upgrade.
            $this->sendCommand($socket, "STARTTLS");
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $this->sendCommand($socket, "EHLO " . $_SERVER['SERVER_NAME']);
        }

        if (!empty($settings->smtp_user) && !empty($settings->smtp_pass)) {
            // AUTH LOGIN with base64-encoded credentials.
            $this->sendCommand($socket, "AUTH LOGIN");
            $this->sendCommand($socket, base64_encode($settings->smtp_user));
            $this->sendCommand($socket, base64_encode($settings->smtp_pass));
        }

        $from = $settings->contact_email;
        $this->sendCommand($socket, "MAIL FROM: <{$from}>");

        foreach ($this->to as $recipient) {
            $email = $this->extractEmail($recipient);
            $this->sendCommand($socket, "RCPT TO: <{$email}>");
        }

        $this->sendCommand($socket, "DATA");

        $message = $this->buildEmailMessage($from);
        fputs($socket, $message . "\r\n.\r\n");
        $this->readResponse($socket);

        $this->sendCommand($socket, "QUIT");
        fclose($socket);

        return true;
    }

    private function sendCommand($socket, $command)
    {
        fputs($socket, $command . "\r\n");
        return $this->readResponse($socket);
    }

    private function readResponse($socket)
    {
        return fgets($socket, 512);
    }

    private function extractEmail($recipient)
    {
        if (preg_match('/<(.+)>/', $recipient, $matches)) {
            return $matches[1];
        }
        return $recipient;
    }

    private function buildHeaders()
    {
        $settings = Setting::get();
        $from = $settings->contact_email;
        
        $headers = [
            "From: {$from}",
            "Reply-To: {$from}",
            "X-Mailer: PHP/" . phpversion(),
        ];

        if ($this->isHtml) {
            $headers[] = "MIME-Version: 1.0";
            $headers[] = "Content-Type: text/html; charset=UTF-8";
        } else {
            $headers[] = "Content-Type: text/plain; charset=UTF-8";
        }

        return implode("\r\n", $headers);
    }

    private function buildEmailMessage($from)
    {
        $to = implode(', ', $this->to);
        
        $message = "From: {$from}\r\n";
        $message .= "To: {$to}\r\n";
        $message .= "Subject: {$this->subject}\r\n";
        
        if ($this->isHtml) {
            $message .= "MIME-Version: 1.0\r\n";
            $message .= "Content-Type: text/html; charset=UTF-8\r\n";
        } else {
            $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
        }
        
        $message .= "\r\n{$this->body}";
        
        return $message;
    }

    public static function create()
    {
        return new self();
    }
}
