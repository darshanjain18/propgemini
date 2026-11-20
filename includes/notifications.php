<?php
declare(strict_types=1);

require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/functions.php';
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

/**
 * @param array<string, scalar|null> $fields
 */
/**
 * Drop blank / placeholder values so unused form fields never appear in mail.
 *
 * @param array<string, scalar|null> $fields
 * @return array<string, string>
 */
function compactMailFields(array $fields): array
{
    $compact = [];
    foreach ($fields as $label => $value) {
        $text = trim((string) ($value ?? ''));
        if ($text === '' || $text === '—' || strcasecmp($text, 'n/a') === 0) {
            continue;
        }
        $compact[(string) $label] = $text;
    }
    return $compact;
}

/**
 * @param array<string, scalar|null> $fields
 */
function formatFormFieldsPlain(array $fields): string
{
    $lines = [];
    foreach (compactMailFields($fields) as $label => $text) {
        $lines[] = $label . ': ' . $text;
    }
    return implode("\n", $lines);
}

/**
 * Build an HTML email with submitted fields in a clean two-column table.
 * Empty values are omitted.
 *
 * @param array<string, scalar|null> $fields
 */
function formatFormFieldsHtml(string $title, array $fields): string
{
    $html = '<div style="margin:0;padding:0;background:#f7f4ef;font-family:Arial,Helvetica,sans-serif;color:#263b3d;">';
    $html .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f7f4ef;padding:24px 12px;">';
    $html .= '<tr><td align="center">';
    $html .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;background:#ffffff;border:1px solid #e4d8c4;border-radius:12px;overflow:hidden;">';
    $html .= '<tr><td style="padding:22px 24px;background:#263b3d;color:#ffffff;">';
    $html .= '<p style="margin:0 0 6px;font-size:12px;letter-spacing:.16em;text-transform:uppercase;color:#c6a36a;">PropGemini</p>';
    $html .= '<h1 style="margin:0;font-size:22px;line-height:1.3;font-weight:600;color:#ffffff;">' . h($title) . '</h1>';
    $html .= '</td></tr>';
    $html .= '<tr><td style="padding:8px 0 4px;">';
    $html .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">';

    $i = 0;
    foreach (compactMailFields($fields) as $label => $text) {
        $bg = ($i % 2 === 0) ? '#fffdf8' : '#ffffff';
        $html .= '<tr style="background:' . $bg . ';">';
        $html .= '<td style="padding:12px 24px;width:38%;vertical-align:top;border-bottom:1px solid #eadfca;font-size:13px;font-weight:700;color:#6d6458;">'
            . h($label) . '</td>';
        $html .= '<td style="padding:12px 24px;vertical-align:top;border-bottom:1px solid #eadfca;font-size:14px;line-height:1.55;color:#263b3d;white-space:pre-wrap;">'
            . nl2br(h($text)) . '</td>';
        $html .= '</tr>';
        $i++;
    }

    $html .= '</table></td></tr>';
    $html .= '<tr><td style="padding:16px 24px 22px;font-size:12px;color:#8a8074;">Submitted via the PropGemini website · propgemini.com</td></tr>';
    $html .= '</table></td></tr></table></div>';

    return $html;
}

/**
 * Email a form submission to the configured enquiry or careers inbox.
 *
 * @param array<string, scalar|null> $fields
 * @param list<array{path:string,name?:string}> $attachments
 */
function sendFormNotification(
    string $channel,
    string $subject,
    array $fields,
    ?string $replyTo = null,
    array $attachments = []
): bool {
    $to = mailRecipientFor($channel);
    $title = preg_replace('/^\[PropGemini\]\s*/i', '', $subject) ?: $subject;
    $html = formatFormFieldsHtml((string) $title, $fields);
    $plain = formatFormFieldsPlain($fields);

    try {
        return sendEmail($to, $subject, $html, $plain, $replyTo, $attachments, $channel);
    } catch (Throwable $e) {
        error_log('Form email notify failed: ' . $e->getMessage());
        return false;
    }
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
        'Property' => (string) ($lead['property'] ?? ''),
        'Visit' => (string) ($lead['visit_at'] ?? ''),
        'Message' => (string) ($lead['message'] ?? ''),
        'Source' => (string) ($lead['source'] ?? 'website'),
    ];

    return formatFormFieldsHtml('New PropGemini lead', $rows);
}

/**
 * Notify the business over WhatsApp (primary) and SMTP email (secondary).
 * Failures are logged; they do not block the user response.
 *
 * Optional lead keys:
 * - mail_channel: enquiry|careers
 * - fields: ordered label => value map for the email table
 * - skip_email: true to send WhatsApp only
 * - attachment_path / attachment_name: optional file for careers
 */
function notifyLead(array $lead): array
{
    $contact = contactDetails();
    $subject = '[PropGemini] ' . ((string) ($lead['type'] ?? 'New enquiry'));
    $plain = formatLeadPlain($lead);

    $whatsappOk = false;
    $emailOk = false;

    try {
        $whatsappOk = sendWhatsAppText($contact['whatsapp'], $plain);
    } catch (Throwable $e) {
        error_log('Lead WhatsApp notify failed: ' . $e->getMessage());
    }

    if (empty($lead['skip_email'])) {
        try {
            $channel = (string) ($lead['mail_channel'] ?? 'enquiry');
            /** @var array<string, scalar|null> $fields */
            $fields = [];
            if (!empty($lead['fields']) && is_array($lead['fields'])) {
                foreach ($lead['fields'] as $label => $value) {
                    if (is_scalar($value) || $value === null) {
                        $fields[(string) $label] = $value;
                    }
                }
            }
            if ($fields === []) {
                $fields = [
                    'Type' => (string) ($lead['type'] ?? 'New enquiry'),
                    'Name' => (string) ($lead['name'] ?? ''),
                    'Phone' => (string) ($lead['phone'] ?? ''),
                    'Email' => (string) ($lead['email'] ?? ''),
                ];
                if (!empty($lead['property'])) {
                    $fields['Property'] = (string) $lead['property'];
                }
                if (!empty($lead['visit_at'])) {
                    $fields['Visit date / time'] = (string) $lead['visit_at'];
                }
                if (!empty($lead['message'])) {
                    $fields['Message'] = (string) $lead['message'];
                }
                $fields['Source'] = (string) ($lead['source'] ?? 'website');
            }

            $attachments = [];
            $attachmentPath = trim((string) ($lead['attachment_path'] ?? ''));
            if ($attachmentPath !== '') {
                $attachments[] = [
                    'path' => $attachmentPath,
                    'name' => (string) ($lead['attachment_name'] ?? basename($attachmentPath)),
                ];
            }

            $replyTo = trim((string) ($lead['email'] ?? ''));
            $emailOk = sendFormNotification(
                $channel,
                $subject,
                $fields,
                $replyTo !== '' ? $replyTo : null,
                $attachments
            );
        } catch (Throwable $e) {
            error_log('Lead email notify failed: ' . $e->getMessage());
        }
    }

    return [
        'whatsapp' => $whatsappOk,
        'email' => $emailOk,
    ];
}
