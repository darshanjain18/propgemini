<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

function whatsappConfig(): array
{
    static $config = null;
    if ($config === null) {
        $config = require dirname(__DIR__) . '/config/whatsapp.php';
    }
    return $config;
}

function normalizeWhatsAppDigits(string $raw): string
{
    $digits = preg_replace('/\D+/', '', $raw) ?? '';
    if ($digits === '') {
        return '';
    }
    // Common Indian local numbers → E.164 without plus.
    if (strlen($digits) === 10) {
        return '91' . $digits;
    }
    if (str_starts_with($digits, '0') && strlen($digits) === 11) {
        return '91' . substr($digits, 1);
    }
    return $digits;
}

function whatsappChatUrl(string $number, string $prefill = 'Hi PropGemini'): string
{
    $digits = normalizeWhatsAppDigits($number);
    if ($digits === '') {
        return '#';
    }
    $text = rawurlencode($prefill);
    return 'https://wa.me/' . $digits . '?text=' . $text;
}

function isWhatsAppApiConfigured(): bool
{
    $config = whatsappConfig();
    return ($config['provider'] ?? '') === 'meta'
        && ($config['phone_number_id'] ?? '') !== ''
        && ($config['access_token'] ?? '') !== '';
}

/**
 * Send a text message via Meta WhatsApp Cloud API.
 * Recipient must be in E.164 digits without + (e.g. 919876543210).
 */
function sendWhatsAppText(string $toDigits, string $body): bool
{
    $to = normalizeWhatsAppDigits($toDigits);
    $body = trim($body);
    if ($to === '' || $body === '') {
        return false;
    }

    if (!isWhatsAppApiConfigured()) {
        error_log('WhatsApp Cloud API is not configured. Skipping outbound WhatsApp message.');
        return false;
    }

    $config = whatsappConfig();
    $url = sprintf(
        'https://graph.facebook.com/%s/%s/messages',
        rawurlencode((string) $config['api_version']),
        rawurlencode((string) $config['phone_number_id'])
    );

    $payload = [
        'messaging_product' => 'whatsapp',
        'recipient_type' => 'individual',
        'to' => $to,
        'type' => 'text',
        'text' => [
            'preview_url' => false,
            'body' => mb_substr($body, 0, 4000),
        ],
    ];

    $ch = curl_init($url);
    if ($ch === false) {
        error_log('WhatsApp curl_init failed.');
        return false;
    }

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $config['access_token'],
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT => 20,
    ]);

    $response = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false || $status < 200 || $status >= 300) {
        error_log('WhatsApp send failed HTTP ' . $status . ': ' . ($error !== '' ? $error : (string) $response));
        return false;
    }

    return true;
}
