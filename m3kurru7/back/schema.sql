-- ============================================================
-- 냐냐냥 생성기  |  DB 스키마  (자동 생성)
-- 2026-06-22 02:06:46
-- ============================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- home_users
CREATE TABLE IF NOT EXISTS `home_users` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`      VARCHAR(60)  NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `nickname`      VARCHAR(50)  NOT NULL UNIQUE,
  `role`       ENUM('user','admin','banned') NOT NULL DEFAULT 'user',
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- home_admins
CREATE TABLE IF NOT EXISTS `home_admins` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`   VARCHAR(60)  NOT NULL UNIQUE,
  `password`   VARCHAR(255) NOT NULL,
  `user_id`    INT UNSIGNED NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `home_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `home_users` (`username`, `password_hash`, `nickname`, `role`) VALUES ('m3kurn77', '$2a$11$ucIQ1aGP/3RYNm7qge1QvuAVAD.oF39PMvi6L0PMTdrzgsBllY3ai', 'm3kurn77', 'admin');
INSERT INTO `home_admins` (`username`, `password`, `user_id`) VALUES ('m3kurn77', '$2a$11$ucIQ1aGP/3RYNm7qge1QvuAVAD.oF39PMvi6L0PMTdrzgsBllY3ai', LAST_INSERT_ID());

-- home_settings
CREATE TABLE IF NOT EXISTS `home_settings` (
  `key`        VARCHAR(80)  NOT NULL,
  `value`      TEXT,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `home_settings` (`key`, `value`) VALUES
  ('site_title', '냐냐냥'),
  ('dday_date', ''),
  ('_dummy', '0');

-- home_pages (메인 콘텐츠 페이지)
CREATE TABLE IF NOT EXISTS `home_pages` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`       VARCHAR(120) NOT NULL UNIQUE,
  `title`      VARCHAR(255) NOT NULL,
  `content`    LONGTEXT,
  `is_private` TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- home_road_M3_posts
CREATE TABLE IF NOT EXISTS `home_road_M3_posts` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`      VARCHAR(255) NOT NULL,
  `content`    LONGTEXT,
  `thumbnail`  VARCHAR(255),
  `collapsed`  TINYINT      NOT NULL DEFAULT 0,
  `is_secret`  TINYINT      NOT NULL DEFAULT 0,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- home_road_M3_comments
CREATE TABLE IF NOT EXISTS `home_road_M3_comments` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_id`    INT UNSIGNED NOT NULL,
  `author`     VARCHAR(80)  DEFAULT NULL,
  `user_id`    INT UNSIGNED NULL,
  `content`    TEXT         NOT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_post` (`post_id`),
  FOREIGN KEY (`user_id`) REFERENCES `home_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- home_road_KURU_posts
CREATE TABLE IF NOT EXISTS `home_road_KURU_posts` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`      VARCHAR(255) NOT NULL,
  `content`    LONGTEXT,
  `thumbnail`  VARCHAR(255),
  `collapsed`  TINYINT      NOT NULL DEFAULT 0,
  `is_secret`  TINYINT      NOT NULL DEFAULT 0,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- home_road_KURU_comments
CREATE TABLE IF NOT EXISTS `home_road_KURU_comments` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_id`    INT UNSIGNED NOT NULL,
  `author`     VARCHAR(80)  DEFAULT NULL,
  `user_id`    INT UNSIGNED NULL,
  `content`    TEXT         NOT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_post` (`post_id`),
  FOREIGN KEY (`user_id`) REFERENCES `home_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- home_bgm_playlist
CREATE TABLE IF NOT EXISTS `home_bgm_playlist` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`      VARCHAR(255) NOT NULL,
  `type`       ENUM('mp3','youtube') NOT NULL DEFAULT 'youtube',
  `src`        VARCHAR(500) NOT NULL,
  `order_num`  INT          NOT NULL DEFAULT 0,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- home_guestbook
CREATE TABLE IF NOT EXISTS `home_guestbook` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `content`    TEXT         NOT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
