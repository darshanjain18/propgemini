<?php
declare(strict_types=1);

function validateEnquiry(array $data): array
{
    $errors = [];

    $name = trim((string) ($data['name'] ?? ''));
    $email = trim((string) ($data['email'] ?? ''));
    $phone = trim((string) ($data['phone'] ?? ''));
    $subject = trim((string) ($data['subject'] ?? ''));
    $message = trim((string) ($data['message'] ?? ''));

    if ($name === '' || mb_strlen($name) > 100) {
        $errors[] = 'Enter a name with at most 100 characters.';
    }

    if (
        $email === ''
        || strlen($email) > 254
        || !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        $errors[] = 'Enter a valid email address.';
    }

    if (
        $phone === ''
        || strlen($phone) > 30
        || !preg_match('/^[0-9+().\-\s]{10,30}$/', $phone)
    ) {
        $errors[] = 'Enter a valid phone number.';
    }

    if (mb_strlen($subject) > 200) {
        $errors[] = 'Subject must be at most 200 characters.';
    }

    if ($message === '' || mb_strlen($message) > 5000) {
        $errors[] = 'Message is required and must be at most 5000 characters.';
    }

    return $errors;
}
