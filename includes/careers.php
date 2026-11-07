<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';
require_once dirname(__DIR__) . '/config/database.php';

function ensureCareerSchema(): void
{
    static $ready = false;
    if ($ready) {
        return;
    }
    $ready = true;

    $pdo = getDatabaseConnection();
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS career_applications (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(120) NOT NULL,
            email VARCHAR(190) NOT NULL,
            country_code VARCHAR(12) NOT NULL DEFAULT '+91',
            phone VARCHAR(20) NOT NULL,
            role VARCHAR(120) NOT NULL,
            experience VARCHAR(40) DEFAULT NULL,
            notice_period VARCHAR(40) DEFAULT NULL,
            message TEXT DEFAULT NULL,
            resume_path VARCHAR(255) NOT NULL,
            resume_original_name VARCHAR(190) DEFAULT NULL,
            status ENUM('New','Reviewing','Shortlisted','Rejected') NOT NULL DEFAULT 'New',
            admin_notes TEXT DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY status_created (status, created_at),
            KEY email_created (email, created_at),
            KEY role_created (role, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

function careerRoleOptions(): array
{
    return [
        'Field Executive',
        'Sales',
        'Sales Lead',
        'Tally Sales',
        'Operation Lead',
        'Digital Marketing',
        'Software Developer',
        'Admin',
        'Quality Assurance',
        'Audit',
        'HR / Recruitment',
    ];
}

function careerExperienceOptions(): array
{
    return [
        'Fresher',
        '0–1 Years',
        '1–3 Years',
        '3–5 Years',
        '5+ Years',
    ];
}

function careerNoticeOptions(): array
{
    return [
        'Immediate',
        '15 Days',
        '30 Days',
        '60 Days',
        '90 Days',
    ];
}

function careerStatusOptions(): array
{
    return ['New', 'Reviewing', 'Shortlisted', 'Rejected'];
}

function careerResumeStorageDir(): string
{
    $dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'careers' . DIRECTORY_SEPARATOR . 'resumes';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return $dir;
}

function storeCareerResume(array $file): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Please upload your resume.');
    }
    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException('Resume must be 5 MB or smaller.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']) ?: '';
    $allowed = [
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    ];
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Resume must be a PDF, DOC or DOCX file.');
    }

    $original = basename((string) ($file['name'] ?? 'resume'));
    $original = preg_replace('/[^\w.\- ()]+/u', '_', $original) ?: 'resume.' . $allowed[$mime];
    if (mb_strlen($original) > 180) {
        $original = mb_substr($original, 0, 180);
    }

    $storedName = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $dest = careerResumeStorageDir() . DIRECTORY_SEPARATOR . $storedName;
    if (!move_uploaded_file((string) $file['tmp_name'], $dest)) {
        throw new RuntimeException('Could not save the resume. Please try again.');
    }

    return [
        'path' => 'storage/careers/resumes/' . $storedName,
        'original' => $original,
    ];
}

function careerResumeAbsolutePath(string $relative): ?string
{
    $relative = str_replace('\\', '/', trim($relative));
    if ($relative === '' || str_contains($relative, '..')) {
        return null;
    }
    if (!str_starts_with($relative, 'storage/careers/resumes/')) {
        return null;
    }
    $absolute = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    return is_file($absolute) ? $absolute : null;
}

function recentCareerDuplicate(string $email, string $phone, string $role): bool
{
    ensureCareerSchema();
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare(
        'SELECT id FROM career_applications
         WHERE email = ? AND phone = ? AND role = ?
           AND created_at >= (NOW() - INTERVAL 24 HOUR)
         LIMIT 1'
    );
    $stmt->execute([mb_strtolower($email), $phone, $role]);
    return $stmt->fetchColumn() !== false;
}

function createCareerApplication(array $data): int
{
    ensureCareerSchema();
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare(
        'INSERT INTO career_applications
         (full_name, email, country_code, phone, role, experience, notice_period, message,
          resume_path, resume_original_name, status, ip_address, user_agent)
         VALUES
         (:full_name, :email, :country_code, :phone, :role, :experience, :notice_period, :message,
          :resume_path, :resume_original_name, \'New\', :ip_address, :user_agent)'
    );
    $stmt->execute([
        'full_name' => $data['full_name'],
        'email' => $data['email'],
        'country_code' => $data['country_code'],
        'phone' => $data['phone'],
        'role' => $data['role'],
        'experience' => $data['experience'],
        'notice_period' => $data['notice_period'],
        'message' => $data['message'],
        'resume_path' => $data['resume_path'],
        'resume_original_name' => $data['resume_original_name'],
        'ip_address' => $data['ip_address'],
        'user_agent' => $data['user_agent'],
    ]);
    return (int) $pdo->lastInsertId();
}

function careerApplicationById(int $id): ?array
{
    ensureCareerSchema();
    if ($id <= 0) {
        return null;
    }
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare('SELECT * FROM career_applications WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}
