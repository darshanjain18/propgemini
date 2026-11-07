<?php
declare(strict_types=1);

return [
    'provider' => strtolower(trim((string) ($_ENV['WHATSAPP_PROVIDER'] ?? 'meta'))),
    'phone_number_id' => trim((string) ($_ENV['WHATSAPP_PHONE_NUMBER_ID'] ?? '')),
    'access_token' => trim((string) ($_ENV['WHATSAPP_ACCESS_TOKEN'] ?? '')),
    'business_account_id' => trim((string) ($_ENV['WHATSAPP_BUSINESS_ACCOUNT_ID'] ?? '')),
    'api_version' => trim((string) ($_ENV['WHATSAPP_API_VERSION'] ?? 'v21.0')),
];
