<?php

use mailBox\mailBox;

if (!function_exists('mailBox')) {
    /**
     * Create a new instance of the mailBox class with the given parameters.
     *
     * @param string $host The mail server hostname or IP address.
     * @param int $port The mail server port number.
     * @param string|null $encryption The encryption method to use (e.g., 'ssl', 'tls', or null for no encryption).
     * @param string|null $username The username for authenticating with the mail server (optional).
     * @param string|null $password The password for authenticating with the mail server (optional).
     *
     * @return mailBox\mailBox Returns an instance of the mailBox class.
     */
    function mailBox($host, $port, $encryption = null, $username = null, $password = null)
    {
        return new mailBox($host, $port, $encryption, $username, $password);
    }
}
