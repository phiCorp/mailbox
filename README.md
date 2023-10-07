# mailBox

A tool for easier work with sending emails for the PHP language

## Installation

Install Cipher with composer

```bash
composer require phicorp/mailbox
```

## Features

- Easy to use
- HTML sending support
- SMTP support
- And more features

## Usage/Examples

- Send a normal email

```php
use mailBox\mailBox;

// Create an instance of mailBox with SMTP server details.
$mailer = new mailBox('smtp.example.com', 587, 'STARTTLS');

// Set SMTP authentication credentials.
$mailer->auth('your_username', 'your_password');

// Set sender information.
$mailer->setFrom('your_email@example.com', 'Your Name');

// Set recipient email.
$mailer->to('recipient@example.com');

// Set email subject and message.
$mailer->subject('Hello, World!');
$mailer->message('This is a test email sent using mailBox class.');

// Optionally, enable HTML email if needed.
// $mailer->isHTML();

// Send the email.
if ($mailer->send()) {
    echo "Email sent successfully.";
} else {
    echo "Email sending failed.";
}

// Optionally, you can retrieve the logs for debugging purposes.
// $logs = $mailer->getLogs();
```

- If you want to use PHP's own built-in functions to send your email, you can use this method

```php
use mailBox\mailBox;

// Create an instance of mailBox with SMTP server details.
$mailer = new mailBox('your_smtp_host', 25, 'tls', 'your_username', 'your_password');

// Set sender information.
$mailer->setFrom('your_email@example.com', 'Your Name');

// Set recipient email.
$mailer->to('recipient@example.com');

// Set email subject and message.
$mailer->subject('Hello, World!');
$mailer->message('<p>This is a test email sent using mailBox class.</p>');

// Enable HTML mode
$mailer->isHTML();

// Send the email.
$result = $mailer->sendRAW();
if ($result) {
    echo "Email sent successfully using default mail() function.";
} else {
    echo "Email could not be sent using default mail() function.";
}

```

- Sending by Outlook, with method chaining

```php
mailBox::create('smtp.office365.com', 587)
    ->encryption('STARTTLS')
    ->auth('your_username@outlook.com', 'your_password')
    ->setFrom('your_username@outlook.com', 'your_name')
    ->to('recipient@example.com')
    ->subject("Hello, World!")
    ->message("This is a test email sent using mailBox class.")
    ->send();
```

- Sending by Gmail, with method chaining and using helper function

```php
mailBox('smtp.gmail.com', 587)
    ->encryption('STARTTLS')
    ->auth('your_username@gmail.com', 'your_password')
    ->setFrom('your_username@gmail.com', 'your_name')
    ->to('recipient@example.com')
    ->subject("Hello, World!")
    ->message("<p>This is a test email sent using mailBox class.</p>")->isHTML()
    ->send();
```

## Authors

- [@thephibonacci](https://www.github.com/thephibonacci)

## License

[MIT](https://choosealicense.com/licenses/mit/)
