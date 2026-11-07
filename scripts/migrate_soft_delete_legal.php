<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

$pdo = getDatabaseConnection();

function columnExists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
    );
    $stmt->execute([$table, $column]);
    return (int) $stmt->fetchColumn() > 0;
}

if (!columnExists($pdo, 'properties', 'deleted_at')) {
    $pdo->exec('ALTER TABLE properties ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL AFTER updated_at');
    $pdo->exec('ALTER TABLE properties ADD INDEX idx_properties_deleted_at (deleted_at)');
    echo "Added properties.deleted_at\n";
} else {
    echo "properties.deleted_at already exists\n";
}

$defaults = defaultLegalSettings();
$upsert = $pdo->prepare(
    'INSERT INTO site_settings (setting_key, setting_value)
     VALUES (?, ?)
     ON DUPLICATE KEY UPDATE setting_value = IF(setting_value IS NULL OR setting_value = \'\', VALUES(setting_value), setting_value)'
);

foreach ($defaults as $key => $value) {
    $upsert->execute([$key, $value]);
    echo "Ensured setting {$key}\n";
}

echo "DONE\n";

function defaultLegalSettings(): array
{
    $terms = <<<'TXT'
Last updated: November 2026

1. About these terms
These Terms & Conditions govern your use of the PropGemini website and any property enquiry, brochure request, or site-visit request you submit through it. By using this website you agree to these terms.

2. Property information
Listings, prices, configurations, carpet areas, possession timelines, amenities, floor plans, images, videos and project status shown on this website are indicative and may change without notice. Developers, RERA filings, sanctioned plans and on-site conditions prevail. Always verify critical details with a PropGemini advisor and the project developer before making any purchase decision.

3. No offer or reservation
Browsing listings or submitting an enquiry does not create a booking, reservation, allotment, agreement to sell, or any contractual right in a property. Any transaction is subject to separate documentation between you, the developer and/or the seller.

4. Advisory role
PropGemini acts as a property advisory / facilitation platform. We do not guarantee financing approval, investment returns, rental yields, appreciation, construction quality, or project completion timelines.

5. Enquiries and site visits
Contact details and preferences you share are used to respond to your request and arrange viewings. Site-visit slots are subject to availability, project access rules and confirmation by our team.

6. Acceptable use
You agree not to misuse the website, scrape content without permission, upload harmful material, or attempt unauthorised access to systems or data.

7. Intellectual property
Website design, copy, branding and curated content belong to PropGemini or its licensors. You may not copy or redistribute them for commercial use without written consent.

8. Third-party projects
Project names, logos and materials may belong to respective developers. References are for identification of listed inventory only.

9. Limitation of liability
To the fullest extent permitted by law, PropGemini is not liable for indirect or consequential loss arising from use of this website or reliance on published information. Confirm all commercial terms independently.

10. Changes
We may update these terms from time to time. Continued use of the website after changes means you accept the revised terms.

11. Contact
For questions about these terms, email hello@propgemini.com or use the Enquire page.
TXT;

    $privacy = <<<'TXT'
Last updated: November 2026

1. Introduction
This Privacy Policy explains how PropGemini collects, uses and protects personal information when you browse our website, enquire about a property, request a brochure, or book a site visit.

2. Information we collect
We may collect: name, phone number, email address, preferred city/area/budget, property of interest, message content, preferred visit date/time, and basic technical data such as browser type and pages viewed.

3. How we use your information
We use your information to respond to enquiries, schedule site visits, share relevant property options, improve our advisory service, and meet legal or compliance requirements where applicable.

4. Sharing
We may share necessary details with project developers, channel partners or service providers solely to fulfil your request (for example, confirming inventory or arranging a visit). We do not sell your personal information.

5. WhatsApp and email
If you contact us or provide a mobile number, we may communicate via phone, SMS, WhatsApp or email regarding your enquiry. You can ask us to stop marketing messages at any time.

6. Data retention
We retain enquiry and visit records for as long as needed to provide follow-up and maintain business records, unless a longer period is required by law or you request deletion where applicable.

7. Security
We take reasonable technical and organisational steps to protect personal data. No method of transmission over the internet is completely secure.

8. Your choices
You may request access, correction or deletion of your personal information by emailing hello@propgemini.com. We may need to verify your identity before acting on a request.

9. Cookies and analytics
Our site may use cookies or similar technologies to remember preferences and understand traffic. You can control cookies through your browser settings.

10. Children
This website is intended for adults exploring residential or commercial property. We do not knowingly collect data from children.

11. Updates
We may update this Privacy Policy periodically. The “Last updated” date at the top will change when we do.

12. Contact
Privacy questions: hello@propgemini.com
TXT;

    return [
        'legal_terms_title' => 'Terms & Conditions',
        'legal_terms_body' => $terms,
        'legal_privacy_title' => 'Privacy Policy',
        'legal_privacy_body' => $privacy,
    ];
}
