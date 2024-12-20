<?php
if (isset($_POST['Email'])) {
    // Configuration
    $email_to = "jeff@jeffrey-winters.com";
    $email_subject = "Contact Form Submission"; // Default subject since form doesn't have subject field

    // Security headers
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');

    function sendJsonResponse($status, $message) {
        header('Content-Type: application/json');
        die(json_encode(['status' => $status, 'message' => $message]));
    }

    // Validate required fields
    $required_fields = ['Name', 'Email', 'Message'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            sendJsonResponse('error', "Please fill in all required fields.");
        }
    }

    // Sanitize and validate input
    $name = filter_var(trim($_POST['Name']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['Email']), FILTER_VALIDATE_EMAIL);
    $message = filter_var(trim($_POST['Message']), FILTER_SANITIZE_STRING);

    if (!$email) {
        sendJsonResponse('error', 'Please provide a valid email address.');
    }

    if (!preg_match("/^[A-Za-z .'-]+$/", $name)) {
        sendJsonResponse('error', 'Please provide a valid name.');
    }

    if (strlen($message) < 2) {
        sendJsonResponse('error', 'Please provide a valid message.');
    }

    // Prepare email content
    $email_message = "New contact form submission:\n\n";
    $email_message .= "Name: " . $name . "\n";
    $email_message .= "Email: " . $email . "\n";
    $email_message .= "Message:\n" . $message . "\n";

    // Prepare headers with proper encoding
    $headers = [
        'From' => $email,
        'Reply-To' => $email,
        'X-Mailer' => 'PHP/' . phpversion(),
        'Content-Type' => 'text/plain; charset=UTF-8',
        'MIME-Version' => '1.0'
    ];

    // Send email
    $mail_sent = mail($email_to, $email_subject, $email_message, $headers);

    if (!$mail_sent) {
        error_log("Failed to send contact form email from: " . $email);
        sendJsonResponse('error', 'Failed to send message. Please try again later.');
    }

    sendJsonResponse('success', 'Thank you for your message. We will contact you soon.');
}
?>