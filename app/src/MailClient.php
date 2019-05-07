<?php

namespace pictogin;

/**
 * Class MailClient
 * @package pictogin
 * Send simple mail message to an e-mail address.
 * @remark Doesn't support multipart message.
 * @remark use text/html as content type and ut8 encoding by default.
 */
class MailClient {
    private $config;
    private $message;
    private $subject;
    private $contentType;
    private $charset;

    public function __construct() {
        $this->config = require_once(getcwd() . '/../configs/mail.php');
        $this->contentType = "text/html";
        $this->charset = "utf-8";
    }

    /**
     * Add a subject to the e-mail. If the subject was set previously, it will be overridden.
     * @param string $subject
     * @return $this
     */
    public function subject(string $subject) : MailClient {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Set the message of the message. If the message was set previously, it will be overridden.
     * @param string $message
     * @return MailClient
     */
    public function message(string $message) : MailClient {
        $this->message = $message;
        return $this;
    }

    /**
     * Set the content type string for your message.
     * @param $contentType {text/html, text/plain, etc}
     * @return $this
     */
    public function contentType($contentType) {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * @param $charset {us-ascii, utf-8}
     * @return $this
     */
    public function charset($charset) {
        $this->charset = $charset;
        return $this;
    }

    /**
     * Render the message using a twig template. If the message was set previously, it will be overridden.
     * @param string $templateName
     * @param array $params
     * @return MailClient
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function template(string $templateName, array $params = []) : MailClient {
        /** @var \Slim\Views\Twig $twig */
        $app = Pictogin::app(); // NOTE: not sure it should be static... maybe create a local twig instead.
        $twig = $app->getContainer()['view'];
        $this->message = $twig->fetch($templateName, $params);
        return $this;
    }

    /**
     * @param string $email Only support one e-mail for now, but could be an array.
     * @throws \Exception
     */
    public function send(string $email) {
        if (!$this->subject)
            throw new \Exception("subject cannot be null.");
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception("e-mail is not valid.");
        }

        $from = $this->config['from'];
        $headers = "From: $from\r\n";
        $headers .= "Reply-To: $from\r\n";
        $headers .= "Content-Type: $this->contentType; charset=\"$this->charset\"\r\n";
        $headers .= "X-Mailer: PHP/".phpversion();

        if (getenv('DOCKER')) {
            $output = "*** No mail client configured ***\r\n" .
                "$headers\r\n" .
                "----------------\r\n" .
                "From: $email\r\n" .
                "Subject: $this->subject\r\n" .
                "$this->message\r\n*****************\r\n";
            SimpleLogger::log($output);
        } else {
            mail($email, $this->subject, $this->message, $headers);
        }
    }
}