-- PropGemini canonical schema (create-only).
-- For a full dump with city/area seed data, import ../propgemini.sql first,
-- then run: php scripts/seed_properties.php

CREATE TABLE IF NOT EXISTS admins (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','editor') DEFAULT 'admin',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS admin_activity_logs (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    admin_id INT DEFAULT NULL,
    action VARCHAR(150) NOT NULL,
    entity_type VARCHAR(100) DEFAULT NULL,
    entity_id INT DEFAULT NULL,
    details TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY admin_id (admin_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cities (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS areas (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    city_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    UNIQUE KEY city_id (city_id, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS properties (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) DEFAULT NULL,
    city_id INT NOT NULL,
    area_id INT NOT NULL,
    property_type VARCHAR(100) DEFAULT NULL,
    property_status VARCHAR(100) DEFAULT NULL,
    bhk VARCHAR(50) DEFAULT NULL,
    min_price DECIMAL(15,2) DEFAULT NULL,
    max_price DECIMAL(15,2) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    overview TEXT DEFAULT NULL,
    amenities TEXT DEFAULT NULL,
    possession_date DATE DEFAULT NULL,
    latitude DECIMAL(10,7) DEFAULT NULL,
    longitude DECIMAL(10,7) DEFAULT NULL,
    map_url VARCHAR(500) DEFAULT NULL,
    is_featured TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL DEFAULT NULL,
    publication_status ENUM('Draft','Published','Unpublished') DEFAULT 'Draft',
    construction_status VARCHAR(100) DEFAULT NULL,
    construction_progress TINYINT UNSIGNED DEFAULT NULL,
    launch_date DATE DEFAULT NULL,
    builtup_min_sqft DECIMAL(10,2) DEFAULT NULL,
    builtup_max_sqft DECIMAL(10,2) DEFAULT NULL,
    about_builder TEXT DEFAULT NULL,
    seo_title VARCHAR(255) DEFAULT NULL,
    seo_description TEXT DEFAULT NULL,
    rera_number VARCHAR(100) DEFAULT NULL,
    rera_expiry DATE DEFAULT NULL,
    featured_rotated_at DATETIME NULL DEFAULT NULL,
    removal_flagged_at DATETIME NULL DEFAULT NULL,
    unavailable_reason VARCHAR(255) DEFAULT NULL,
    UNIQUE KEY slug (slug),
    KEY city_id (city_id),
    KEY area_id (area_id),
    KEY idx_properties_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS property_amenities (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    amenity_name VARCHAR(150) NOT NULL,
    KEY property_id (property_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS property_brochures (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    brochure_path VARCHAR(255) NOT NULL,
    KEY property_id (property_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS property_floor_plans (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    title VARCHAR(150) DEFAULT NULL,
    bhk VARCHAR(50) DEFAULT NULL,
    area_sqft DECIMAL(10,2) DEFAULT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    KEY property_id (property_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS property_images (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    media_type ENUM('image','video_thumbnail') DEFAULT 'image',
    KEY property_id (property_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS property_status_history (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    old_status VARCHAR(100) DEFAULT NULL,
    new_status VARCHAR(100) NOT NULL,
    changed_by INT DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY property_id (property_id),
    KEY changed_by (changed_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS property_unit_types (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    configuration VARCHAR(50) NOT NULL,
    carpet_area_sqft DECIMAL(10,2) DEFAULT NULL,
    price DECIMAL(15,2) DEFAULT NULL,
    floor_plan_path VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    is_available TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY property_id (property_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS property_videos (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    video_path VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    duration_seconds INT DEFAULT NULL,
    KEY property_id (property_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS enquiries (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(254) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    subject VARCHAR(200) DEFAULT NULL,
    message TEXT NOT NULL,
    whatsapp_consent TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('new','contacted','qualified','closed','spam') NOT NULL DEFAULT 'new',
    source VARCHAR(100) NOT NULL DEFAULT 'website',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    property_id INT DEFAULT NULL,
    follow_up_date DATETIME DEFAULT NULL,
    admin_notes TEXT DEFAULT NULL,
    assigned_admin_id INT DEFAULT NULL,
    INDEX idx_enquiries_status (status),
    INDEX idx_enquiries_created_at (created_at),
    INDEX idx_enquiries_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS brochure_requests (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    country_code VARCHAR(10) DEFAULT '+91',
    contact_number VARCHAR(20) NOT NULL,
    email VARCHAR(150) NOT NULL,
    status ENUM('New','Contacted','Completed') DEFAULT 'New',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY property_id (property_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS site_settings (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(150) NOT NULL UNIQUE,
    setting_value TEXT DEFAULT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS property_views (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    viewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    viewer_ip VARCHAR(45) DEFAULT NULL,
    KEY property_id (property_id),
    KEY viewed_at (viewed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS site_traffic (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    path VARCHAR(255) NOT NULL,
    page_title VARCHAR(255) DEFAULT NULL,
    property_id INT DEFAULT NULL,
    viewer_ip VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    referrer VARCHAR(500) DEFAULT NULL,
    viewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY viewed_at (viewed_at),
    KEY path (path),
    KEY property_id (property_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS whatsapp_clicks (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    source_label VARCHAR(100) NOT NULL DEFAULT 'site',
    property_id INT DEFAULT NULL,
    viewer_ip VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    clicked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY clicked_at (clicked_at),
    KEY source_label (source_label)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS site_visit_requests (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    country_code VARCHAR(10) DEFAULT '+91',
    contact_number VARCHAR(20) NOT NULL,
    email VARCHAR(150) NOT NULL,
    property_id INT DEFAULT NULL,
    visit_date DATE DEFAULT NULL,
    visit_time TIME DEFAULT NULL,
    reschedule_date DATE DEFAULT NULL,
    reschedule_time TIME DEFAULT NULL,
    message TEXT DEFAULT NULL,
    status ENUM('Pending','Confirmed','Completed','Cancelled','Rescheduled') DEFAULT 'Pending',
    admin_notes TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY property_id (property_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS news_posts (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    slug VARCHAR(190) NOT NULL UNIQUE,
    kicker VARCHAR(40) DEFAULT NULL,
    summary VARCHAR(500) DEFAULT NULL,
    body TEXT DEFAULT NULL,
    highlight VARCHAR(120) DEFAULT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    layout ENUM('spotlight','offer','editorial','note') NOT NULL DEFAULT 'editorial',
    status ENUM('Draft','Published') NOT NULL DEFAULT 'Draft',
    published_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY status_published (status, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS gallery_items (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    caption VARCHAR(400) DEFAULT NULL,
    media_type ENUM('image','video') NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    status ENUM('Draft','Published') NOT NULL DEFAULT 'Draft',
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY status_sort (status, sort_order, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
