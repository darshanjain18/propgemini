<?php
declare(strict_types=1);

require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/whatsapp.php';

function isPlaceholderContactValue(string $key, string $value): bool
{
    $value = trim($value);
    if ($value === '') {
        return true;
    }
    return match ($key) {
        'whatsapp_number' => str_starts_with(preg_replace('/\D+/', '', $value) ?? '', '910000'),
        'contact_phone' => str_contains($value, '00000'),
        'contact_email' => strcasecmp($value, 'hello@propgemini.com') === 0,
        default => false,
    };
}

function contactDetails(): array
{
    $whatsappRaw = siteSetting('whatsapp_number', '');
    $phoneRaw = siteSetting('contact_phone', '');
    $email = siteSetting('contact_email', '');
    $address = siteSetting('contact_address', '');

    $whatsapp = isPlaceholderContactValue('whatsapp_number', $whatsappRaw)
        ? ''
        : normalizeWhatsAppDigits($whatsappRaw);
    $phoneDigits = isPlaceholderContactValue('contact_phone', $phoneRaw)
        ? ''
        : normalizeWhatsAppDigits($phoneRaw);
    if (isPlaceholderContactValue('contact_email', $email)) {
        $email = '';
    }

    $displayPhone = '';
    if ($phoneDigits !== '' && strlen($phoneDigits) >= 10) {
        $local = substr($phoneDigits, -10);
        $displayPhone = '+91 ' . substr($local, 0, 5) . ' ' . substr($local, 5);
    } elseif ($phoneRaw !== '' && $phoneDigits !== '') {
        $displayPhone = $phoneRaw;
    }

    return [
        'whatsapp' => $whatsapp,
        'phone_digits' => $phoneDigits,
        'phone_display' => $displayPhone,
        'phone_tel' => $phoneDigits !== '' ? '+' . $phoneDigits : '',
        'email' => $email,
        'address' => $address,
        'whatsapp_url' => whatsappChatUrl($whatsapp),
        'has_phone' => $phoneDigits !== '',
        'has_email' => $email !== '',
        'has_whatsapp' => $whatsapp !== '',
        'has_address' => $address !== '',
    ];
}

function formatLeadPlain(array $lead): string
{
    $lines = [
        '*' . ($lead['type'] ?? 'New enquiry') . '* — PropGemini',
        'Name: ' . ($lead['name'] ?? ''),
        'Phone: ' . ($lead['phone'] ?? ''),
        'Email: ' . ($lead['email'] ?? ''),
    ];
    if (!empty($lead['property'])) {
        $lines[] = 'Property: ' . $lead['property'];
    }
    if (!empty($lead['visit_at'])) {
        $lines[] = 'Visit: ' . $lead['visit_at'];
    }
    if (!empty($lead['message'])) {
        $lines[] = 'Message: ' . $lead['message'];
    }
    $lines[] = 'Source: ' . ($lead['source'] ?? 'website');
    return implode("\n", $lines);
}

function formatLeadHtml(array $lead): string
{
    $rows = [
        'Type' => (string) ($lead['type'] ?? 'New enquiry'),
        'Name' => (string) ($lead['name'] ?? ''),
        'Phone' => (string) ($lead['phone'] ?? ''),
        'Email' => (string) ($lead['email'] ?? ''),
        'Property' => (string) ($lead['property'] ?? '—'),
        'Visit' => (string) ($lead['visit_at'] ?? '—'),
        'Message' => nl2br(h((string) ($lead['message'] ?? ''))),
        'Source' => (string) ($lead['source'] ?? 'website'),
    ];

    $html = '<div style="font-family:Georgia,serif;color:#1d1a16;line-height:1.6">';
    $html .= '<h2 style="font-weight:400">New PropGemini lead</h2><table style="width:100%;border-collapse:collapse">';
    foreach ($rows as $label => $value) {
        if ($value === '' || $value === '—') {
            if ($label === 'Property' || $label === 'Visit') {
                continue;
            }
        }
        $html .= '<tr><td style="padding:8px 0;border-bottom:1px solid #eadfca;width:120px;color:#6d6458">'
            . h($label) . '</td><td style="padding:8px 0;border-bottom:1px solid #eadfca">'
            . ($label === 'Message' ? $value : h((string) $value)) . '</td></tr>';
    }
    $html .= '</table></div>';
    return $html;
}

/**
 * Notify the business over WhatsApp (primary) and SMTP email (secondary).
 * Failures are logged; they do not block the user response.
 */
function notifyLead(array $lead): array
{
    $contact = contactDetails();
    $subject = '[PropGemini] ' . ((string) ($lead['type'] ?? 'New enquiry'));
    $plain = formatLeadPlain($lead);
    $html = formatLeadHtml($lead);

    $whatsappOk = false;
    $emailOk = false;

    try {
        // Primary channel: Meta WhatsApp Cloud API → business WhatsApp number from settings.
        $whatsappOk = sendWhatsAppText($contact['whatsapp'], $plain);
    } catch (Throwable $e) {
        error_log('Lead WhatsApp notify failed: ' . $e->getMessage());
    }

    try {
        if ($contact['email'] !== '' && (siteSetting('notify_email', $contact['email'])) !== '') {
            $to = siteSetting('notify_email', $contact['email']);
            $emailOk = sendEmail($to, $subject, $html, $plain);
        }
    } catch (Throwable $e) {
        error_log('Lead email notify failed: ' . $e->getMessage());
    }

    return [
        'whatsapp' => $whatsappOk,
        'email' => $emailOk,
    ];
}
