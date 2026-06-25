-- ============================================================
-- 냐냐냥 생성기  |  DB 스키마  (자동 생성)
-- 2026-06-22 23:33:25
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

INSERT INTO `home_users` (`username`, `password_hash`, `nickname`, `role`) VALUES ('admin', '$2a$11$Oz4aHn/kzdAal6J.7bOK3O6nijfH917EiJK.DWqLeL02XjPI/38em', 'admin', 'admin');
INSERT INTO `home_admins` (`username`, `password`, `user_id`) VALUES ('admin', '$2a$11$Oz4aHn/kzdAal6J.7bOK3O6nijfH917EiJK.DWqLeL02XjPI/38em', LAST_INSERT_ID());

-- home_settings
CREATE TABLE IF NOT EXISTS `home_settings` (
  `key`        VARCHAR(80)  NOT NULL,
  `value`      TEXT,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `home_settings` (`key`, `value`) VALUES
  ('site_title', '냐냐냥'),
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

INSERT INTO `home_pages` (`slug`, `title`) VALUES ('GUIDELINE', 'GUIDELINE');

-- home_gallery_voicebank
CREATE TABLE IF NOT EXISTS `home_gallery_voicebank` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`         VARCHAR(255) NOT NULL,
  `content`       LONGTEXT,
  `profile`       LONGTEXT,
  `download_link` VARCHAR(500),
  `thumbnail`     VARCHAR(255),
  `thumb_mode`    ENUM('auto','select','upload') NOT NULL DEFAULT 'auto',
  `password_hash` VARCHAR(255),
  `is_private`    TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- home_gallery_voicebank_audio
CREATE TABLE IF NOT EXISTS `home_gallery_voicebank_audio` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_id`    INT UNSIGNED NOT NULL,
  `title`      VARCHAR(255) NOT NULL DEFAULT '',
  `url`        VARCHAR(500) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`post_id`) REFERENCES `home_gallery_voicebank` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 기존 DB에 적용할 ALTER (이미 테이블이 있는 경우 — MySQL 5.x는 IF NOT EXISTS 미지원):
-- ALTER TABLE `home_gallery_voicebank` ADD COLUMN `profile` LONGTEXT AFTER `content`, ADD COLUMN `download_link` VARCHAR(500) AFTER `profile`;

-- home_ask_board
CREATE TABLE IF NOT EXISTS `home_ask_board` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(100) NOT NULL DEFAULT '',
  `email`       VARCHAR(255) NOT NULL DEFAULT '',
  `question`    TEXT         NOT NULL,
  `answer`      TEXT         DEFAULT NULL,
  `is_answered` TINYINT(1)   NOT NULL DEFAULT 0,
  `answered_at` DATETIME     DEFAULT NULL,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- ALTER (기존 DB에 적용):
-- ALTER TABLE `home_ask_board` ADD COLUMN `name` VARCHAR(100) NOT NULL DEFAULT '' AFTER `id`, ADD COLUMN `email` VARCHAR(255) NOT NULL DEFAULT '' AFTER `name`;

SET FOREIGN_KEY_CHECKS = 1;
