<?php

namespace mailBox;

use Exception;

class mailBox
{
    /**
     * @var string The SMTP server host.
     */
    private $host;

    /**
     * @var int The SMTP server port.
     */
    private $port;

    /**
     * @var string|null The encryption method (e.g., 'tls', 'ssl').
     */
    private $encryption;

    /**
     * @var string|null The SMTP server username for authentication.
     */
    private $username;

    /**
     * @var string|null The SMTP server password for authentication.
     */
    private $password;

    /**
     * @var resource|null The socket resource used for the SMTP connection.
     */
    private $socket;

    /**
     * @var string The 'From' email address.
     */
    private $from;

    /**
     * @var string The 'From' name.
     */
    private $fromName;

    /**
     * @var string The 'To' email address.
     */
    private $to;

    /**
     * @var string The email subject.
     */
    private $subject;

    /**
     * @var string The email message content.
     */
    private $message;

    /**
     * @var bool Whether the email content is HTML (default is false).
     */
    private $isHTML = false;

    /**
     * @var array An array to store SMTP logs.
     */
    private $logs = [];

    /**
     * mailBox constructor.
     *
     * @param string      $host       The SMTP server host.
     * @param int         $port       The SMTP server port.
     * @param null|string $encryption The encryption method (e.g., 'tls', 'ssl').
     * @param null|string $username   The SMTP server username for authentication.
     * @param null|string $password   The SMTP server password for authentication.
     */
    public function __construct($host, $port, $encryption = null, $username = null, $password = null)
    {
        $this->host = $host;
        $this->port = $port;
        $this->encryption = $encryption;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Create a new instance of mailBox.
     *
     * @param string $host The SMTP server host.
     * @param int    $port The SMTP server port.
     *
     * @return mailBox
     */
    public static function create($host, $port)
    {
        return new self($host, $port);
    }

    /**
     * Set whether the email content is HTML.
     *
     * @param bool $isHTML Whether the email content is HTML (default is false).
     *
     * @return $this
     */
    public function isHTML(bool $isHTML = true)
    {
        $this->isHTML = $isHTML;
        return $this;
    }

    /**
     * Set the encryption method for the SMTP connection.
     *
     * @param string $encryption The encryption method (e.g., 'tls', 'ssl').
     *
     * @return $this
     */
    public function encryption($encryption)
    {
        $this->encryption = $encryption;
        return $this;
    }

    /**
     * Set SMTP authentication credentials.
     *
     * @param string $username The SMTP server username.
     * @param string $password The SMTP server password.
     *
     * @return $this
     */
    public function auth($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
        return $this;
    }

    /**
     * Set the 'From' address and name for the email.
     *
     * @param string $from     The 'From' email address.
     * @param string $fromName The 'From' name.
     *
     * @return $this
     */
    public function setFrom($from, $fromName)
    {
        $this->from = $from;
        $this->fromName = $fromName;
        return $this;
    }

    /**
     * Set the 'To' address for the email.
     *
     * @param string $to The 'To' email address.
     *
     * @return $this
     */
    public function to($to)
    {
        $this->to = $to;
        return $this;
    }

    /**
     * Set the email subject.
     *
     * @param string $subject The email subject.
     *
     * @return $this
     */
    public function subject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Set the email message content.
     *
     * @param string $message The email message content.
     *
     * @return $this
     */
    public function message($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Get the SMTP logs.
     *
     * @return array
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * Send the email using SMTP.
     *
     * @return bool True if the email is sent successfully, false otherwise.
     */
    public function send()
    {
        try {
            $this->openConnection();
            $this->sendCommands();
            $this->sendEmail();
            $this->closeConnection();
            $this->log("success");
            return true;
        } catch (Exception $e) {
            $this->log("Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send the email using the default mail settings in PHP.
     *
     * @return bool True if the email is sent successfully, false otherwise.
     */
    public function sendRAW()
    {
        $headers = "From: $this->fromName <$this->from>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";

        if ($this->isHTML) {
            $headers .= "Content-Type: text/html; charset=utf-8\r\n";
        } else {
            $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
        }

        $subject = $this->subject;
        $message = $this->message;
        $to = $this->to;

        $additional_headers = trim($headers);

        $success = mail($to, $subject, $message, $additional_headers);

        if ($success) {
            $this->log("Email sent using default mail() function");
        } else {
            $this->log("Error: Email could not be sent using default mail() function");
        }

        return $success;
    }

    /**
     * Open a connection to the SMTP server.
     *
     * @throws Exception If unable to establish a connection to the SMTP server.
     */
    private function openConnection()
    {
        $this->socket = fsockopen($this->host, $this->port, $errno, $errstr, 10);
        if (!$this->socket) {
            throw new Exception("Failed to connect to SMTP server: $errno - $errstr");
        }
        $this->getResponse();
    }

    /**
     * Close the SMTP server connection.
     */
    private function closeConnection()
    {
        if ($this->socket) {
            fclose($this->socket);
            $this->socket = null;
        }
    }

    /**
     * Send various SMTP commands during the connection setup.
     */
    private function sendCommands()
    {
        $this->sendCommand("EHLO example.com", 250);
        $this->enableEncryptionIfRequired();
        $this->sendAuthCredentials();
        $this->sendMailCommands();
        $this->sendCommand("DATA", 354);
    }

    /**
     * Enable encryption if required, typically for 'tls' or 'ssl' encryption methods.
     *
     * @throws Exception If encryption cannot be enabled.
     */
    private function enableEncryptionIfRequired()
    {
        if ($this->encryption == 'STARTTLS') {
            $this->sendCommand("STARTTLS", 220);
            if (!stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new Exception("Failed to enable encryption");
            }
            $this->sendCommand("EHLO example.com", 250);
        }
    }

    /**
     * Send SMTP authentication credentials if provided (username and password).
     *
     * @throws Exception If authentication fails.
     */
    private function sendAuthCredentials()
    {
        if ($this->username && $this->password) {
            $this->sendCommand("AUTH LOGIN", 334);
            $this->sendCommand(base64_encode($this->username), 334);
            $this->sendCommand(base64_encode($this->password), 235);
        }
    }

    /**
     * Send necessary SMTP commands for sending an email.
     */
    private function sendMailCommands()
    {
        $this->sendCommand("MAIL FROM:<$this->from>", 250);
        $this->sendCommand("RCPT TO:<$this->to>", 250);
    }

    /**
     * Send the email itself, including headers and content.
     */
    private function sendEmail()
    {
        $this->sendEmailHeadersAndContent();
        $this->sendCommand("QUIT", 221);
    }

    /**
     * Send an SMTP command and validate the expected response code.
     *
     * @param string $command      The SMTP command to send.
     * @param int    $expectedCode The expected response code.
     *
     * @throws Exception If the SMTP command fails or the response code doesn't match the expected code.
     */
    private function sendCommand($command, $expectedCode)
    {
        fwrite($this->socket, "$command\r\n");
        $response = $this->getResponse();
        $this->log("Sent command: $command");
        $this->log("SMTP response: $response");
        $code = (int) substr($response, 0, 3);
        if ($command === 'QUIT') {
            if ($code !== $expectedCode) {
                $this->log("SMTP error: $response");
            }
            return;
        }
        if ($code !== $expectedCode) {
            throw new Exception("SMTP error: $response");
        }
    }

    /**
     * Read and retrieve the SMTP server's response.
     *
     * @return string The SMTP server's response.
     */
    private function getResponse()
    {
        $response = '';
        while ($line = fgets($this->socket)) {
            $response .= $line;
            if (substr($line, 3, 1) == ' ') {
                break;
            }
        }
        return $response;
    }

    /**
     * Send email headers and content as part of the email sending process.
     */
    private function sendEmailHeadersAndContent()
    {
        $headers = "From: $this->fromName <$this->from>\r\n";
        $headers .= "To: $this->to\r\n";
        $headers .= "Subject: $this->subject\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        if ($this->isHTML) {
            $headers .= "Content-Type: text/html; charset=utf-8\r\n";
            $headers .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        } else {
            $headers .= "Content-Type: text/plain; charset=utf-8\r\n\r\n";
        }

        $message = "$headers$this->message\r\n.\r\n";
        $this->sendCommand($message, 250);
    }

    /**
     * Log a message to the internal logs array.
     *
     * @param string $message The message to log.
     */
    private function log($message)
    {
        $this->logs[] = $message;
    }
}
