-- VibeCodeWeb Instagram automation — database schema.
-- Import via phpMyAdmin (select the target DB first), or let install.php create it.

CREATE TABLE IF NOT EXISTS posts (
  id           VARCHAR(16)  NOT NULL,
  type         VARCHAR(16)  NOT NULL DEFAULT 'image',
  caption      TEXT         NULL,
  hashtags     TEXT         NULL,
  media        TEXT         NULL,            -- JSON array of filenames
  scheduled_at VARCHAR(32)  NULL,            -- ISO8601 string, e.g. 2026-06-08T10:30:00+05:30
  status       VARCHAR(20)  NOT NULL DEFAULT 'pending_review',
  created_at   VARCHAR(32)  NULL,
  approved_at  VARCHAR(32)  NULL,
  published_at VARCHAR(32)  NULL,
  failed_at    VARCHAR(32)  NULL,
  media_id     VARCHAR(64)  NULL,
  cover        VARCHAR(255) NULL,
  error        TEXT         NULL,
  PRIMARY KEY (id),
  KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username      VARCHAR(64)  NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at    VARCHAR(32)  NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed the initial admin (username: rehansh) only if no such user exists.
-- The hash below is the existing bcrypt hash carried over from .htpasswd.
INSERT INTO users (username, password_hash, created_at)
SELECT 'rehansh', '$2y$10$rJmkLZ/Gz0weBx/fPTdqaO3WFZ2CWNN3.zM8lJj.wRvFgr/6YBDmG',
       '2026-06-07T00:00:00+05:30'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'rehansh');
