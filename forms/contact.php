<?php
// Load SMTP config
$config = include('../config.php'); // adjust path if necessary

// Email to receive contact messages
$receiving_email_address = 'contact@sidev.co.za';

// Load PHP Email Form library
$php_email_form_path = '../assets/vendor/php-email-form/php-email-form.php';
if (!file_exists($php_email_form_path)) {
    die('Unable to load the "PHP Email Form" Library!');
}
include_once($php_email_form_path);

// Minimal fallback if library is missing
if (!class_exists('PHP_Email_Form')) {
    class PHP_Email_Form {
        public $ajax = false;
        public $to;
        public $from_name;
        public $from_email;
        public $subject;
        public $smtp = [];
        public $messages = [];

        public function add_message($value, $label = '', $priority = 0) {
            $this->messages[] = ($label ? "$label: " : '') . $value;
        }

        public function send() {
            $headers = 'From: ' . ($this->from_name ?: '') . ' <' . ($this->from_email ?: '') . ">\r\n";
            $body = implode("\n", $this->messages);
            return !empty($this->to) && @mail($this->to, $this->subject ?? '', $body, $headers);
        }
    }
}

// Sanitize POST inputs
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? 'New Contact Form Submission');
$message = trim($_POST['message'] ?? '');

// Initialize email form
$contact = new PHP_Email_Form();
$contact->ajax = true;
$contact->to = $receiving_email_address;
$contact->from_name = $name;
$contact->from_email = $email;
$contact->subject = $subject;
$contact->add_message($name, 'From');
$contact->add_message($email, 'Email');
$contact->add_message($message, 'Message', 10);

// SMTP configuration
$contact->smtp = [
    'host' => $config['smtp_host'] ?? '',
    'username' => $config['smtp_user'] ?? '',
    'password' => $config['smtp_pass'] ?? '',
    'port' => $config['smtp_port'] ?? 587,
    'secure' => $config['smtp_secure'] ?? 'tls',
];

// Send email and return JSON response
header('Content-Type: application/json');

try {
    $sent = $contact->send();
    echo json_encode([
        'status' => $sent ? 'success' : 'error',
        'message' => $sent ? 'Message sent successfully!' : 'Failed to send the message.'
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
