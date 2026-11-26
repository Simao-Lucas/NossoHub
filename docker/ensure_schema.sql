-- Only runs on first MariaDB init (empty data dir).
-- Existing volume nosso_hub_mariadb_data is never touched by this file.

CREATE TABLE IF NOT EXISTS events (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT NULL,
  occurred_at DATE NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX events_occurred_at_index (occurred_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS event_media (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  event_id BIGINT UNSIGNED NOT NULL,
  path VARCHAR(255) NOT NULL,
  disk VARCHAR(255) NOT NULL DEFAULT 'public',
  original_name VARCHAR(255) NULL,
  mime_type VARCHAR(255) NULL,
  size BIGINT UNSIGNED NULL,
  type VARCHAR(255) NOT NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX event_media_event_id_type_index (event_id, type),
  CONSTRAINT event_media_event_id_foreign
    FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS plan_categories (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY plan_categories_slug_unique (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS plan_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT NULL,
  plan_category_id BIGINT UNSIGNED NOT NULL,
  priority VARCHAR(255) NOT NULL DEFAULT 'medium',
  status VARCHAR(255) NOT NULL DEFAULT 'pending',
  link VARCHAR(255) NULL,
  notes TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX plan_items_priority_index (priority),
  INDEX plan_items_plan_category_id_status_index (plan_category_id, status),
  CONSTRAINT plan_items_plan_category_id_foreign
    FOREIGN KEY (plan_category_id) REFERENCES plan_categories (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS appointments (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  summary VARCHAR(255) NOT NULL,
  description TEXT NULL,
  location VARCHAR(255) NULL,
  starts_at DATETIME NOT NULL,
  ends_at DATETIME NOT NULL,
  all_day TINYINT(1) NOT NULL DEFAULT 0,
  timezone VARCHAR(255) NOT NULL DEFAULT 'America/Sao_Paulo',
  status VARCHAR(255) NOT NULL DEFAULT 'confirmed',
  visibility VARCHAR(255) NOT NULL DEFAULT 'default',
  transparency VARCHAR(255) NOT NULL DEFAULT 'opaque',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX appointments_starts_at_ends_at_index (starts_at, ends_at),
  INDEX appointments_status_index (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS calendar_emails (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL,
  label VARCHAR(255) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY calendar_emails_email_unique (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO plan_categories (name, slug, sort_order, created_at, updated_at)
SELECT * FROM (
  SELECT 'Restaurante' AS name, 'restaurant' AS slug, 1 AS sort_order, NOW() AS created_at, NOW() AS updated_at
  UNION ALL SELECT 'Viagem', 'travel', 2, NOW(), NOW()
  UNION ALL SELECT 'Filme', 'movie', 3, NOW(), NOW()
  UNION ALL SELECT 'Presente', 'gift', 4, NOW(), NOW()
  UNION ALL SELECT 'Experiência', 'experience', 5, NOW(), NOW()
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM plan_categories LIMIT 1);
