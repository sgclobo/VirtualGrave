-- ============================================================
-- IN LOVING MEMORY - Database Schema
-- Memorial Platform for Hercio Maria da Neves Campos
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `in_loving_memory` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `in_loving_memory`;

-- ============================================================
-- ADMIN USERS
-- ============================================================
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `last_login` TIMESTAMP NULL
) ENGINE=InnoDB;

-- ============================================================
-- MEMBERS / USERS
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `country` VARCHAR(80) DEFAULT NULL,
  `relationship` ENUM('family','relative','friend','acquaintance','colleague','neighbor','spiritual') NOT NULL DEFAULT 'friend',
  `family_relation_detail` VARCHAR(100) DEFAULT NULL,
  `profile_photo` VARCHAR(255) DEFAULT NULL,
  `personal_message` TEXT DEFAULT NULL,
  `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `registered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `approved_at` TIMESTAMP NULL,
  `last_login` TIMESTAMP NULL,
  `remember_token` VARCHAR(100) DEFAULT NULL
) ENGINE=InnoDB;

-- ============================================================
-- FLOWER CATALOG
-- ============================================================
CREATE TABLE IF NOT EXISTS `flowers_catalog` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `flower_name` VARCHAR(80) NOT NULL,
  `symbolic_meaning` TEXT DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `color` VARCHAR(50) DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

-- ============================================================
-- CANDLE CATALOG
-- ============================================================
CREATE TABLE IF NOT EXISTS `candles_catalog` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `candle_name` VARCHAR(80) NOT NULL,
  `candle_type` VARCHAR(80) DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `glow_color` VARCHAR(30) DEFAULT '#FFA500',
  `is_active` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

-- ============================================================
-- DEPOSITED FLOWERS
-- ============================================================
CREATE TABLE IF NOT EXISTS `deposited_flowers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `flower_id` INT UNSIGNED NOT NULL,
  `message` TEXT DEFAULT NULL,
  `is_approved` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`flower_id`) REFERENCES `flowers_catalog`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- LIT CANDLES
-- ============================================================
CREATE TABLE IF NOT EXISTS `lit_candles` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `candle_id` INT UNSIGNED NOT NULL,
  `dedication` TEXT DEFAULT NULL,
  `is_approved` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`candle_id`) REFERENCES `candles_catalog`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- PRAYERS
-- ============================================================
CREATE TABLE IF NOT EXISTS `prayers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(150) DEFAULT NULL,
  `prayer_text` TEXT NOT NULL,
  `category` ENUM('peace','gratitude','healing','family','eternal_rest') NOT NULL DEFAULT 'peace',
  `visibility` ENUM('public','private') NOT NULL DEFAULT 'public',
  `is_approved` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TESTIMONIES / MEMORIES
-- ============================================================
CREATE TABLE IF NOT EXISTS `testimonies` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `testimony_text` TEXT NOT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `is_approved` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- GALLERY
-- ============================================================
CREATE TABLE IF NOT EXISTS `gallery` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `file_type` ENUM('photo','video') NOT NULL DEFAULT 'photo',
  `category` ENUM('childhood','family','work','celebrations','travels','special') NOT NULL DEFAULT 'family',
  `sort_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TIMELINE
-- ============================================================
CREATE TABLE IF NOT EXISTS `timeline` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `year` YEAR NOT NULL,
  `month` TINYINT UNSIGNED DEFAULT NULL,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `icon` VARCHAR(50) DEFAULT 'star',
  `category` ENUM('birth','education','work','family','achievement','personal','retirement','passing') DEFAULT 'personal',
  `sort_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

-- ============================================================
-- BIOGRAPHY SECTIONS
-- ============================================================
CREATE TABLE IF NOT EXISTS `biography` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `section_title` VARCHAR(200) NOT NULL,
  `section_content` LONGTEXT NOT NULL,
  `section_order` INT DEFAULT 0,
  `icon` VARCHAR(50) DEFAULT 'book',
  `is_active` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

-- ============================================================
-- SETTINGS
-- ============================================================
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT DEFAULT NULL,
  `setting_label` VARCHAR(200) DEFAULT NULL
) ENGINE=InnoDB;

-- ============================================================
-- VISIT COUNTER
-- ============================================================
CREATE TABLE IF NOT EXISTS `visit_log` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `page` VARCHAR(100) DEFAULT NULL,
  `visited_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- GUESTBOOK
-- ============================================================
CREATE TABLE IF NOT EXISTS `guestbook` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `guest_name` VARCHAR(100) DEFAULT NULL,
  `message` TEXT NOT NULL,
  `is_approved` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- ANNIVERSARY REMINDERS
-- ============================================================
CREATE TABLE IF NOT EXISTS `anniversary_reminders` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `reminder_type` ENUM('birthday','death_anniversary','custom') DEFAULT 'death_anniversary',
  `custom_label` VARCHAR(100) DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- SAMPLE DATA
-- ============================================================

-- Admin user (password: Admin@123)
INSERT INTO `admin_users` (`username`, `email`, `password_hash`, `full_name`) VALUES
('admin', 'admin@inlovingmemory.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Site Administrator');

-- Settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_label`) VALUES
('deceased_name', 'Hercio Maria da Neves Campos', 'Name of Deceased'),
('deceased_born', '1945-03-15', 'Date of Birth'),
('deceased_died', '2024-01-08', 'Date of Passing'),
('deceased_tagline', 'A soul of boundless love, wisdom and grace', 'Memorial Tagline'),
('deceased_photo', 'assets/images/portrait.jpg', 'Portrait Photo'),
('site_title', 'In Loving Memory', 'Site Title'),
('ambient_music', '1', 'Enable Ambient Music'),
('music_file', 'assets/sounds/ambient.mp3', 'Music File'),
('footer_quote', 'In memory of Hercio Maria da Neves Campos, love continues.', 'Footer Quote'),
('maintenance_mode', '0', 'Maintenance Mode'),
('allow_registration', '1', 'Allow New Registrations'),
('auto_approve_members', '0', 'Auto-Approve Members');

-- Flower Catalog
INSERT INTO `flowers_catalog` (`flower_name`, `symbolic_meaning`, `image`, `color`) VALUES
('White Rose', 'Purity, innocence and eternal love', 'flowers/white-rose.png', '#FFFFFF'),
('Red Rose', 'Deep love and respect that transcends time', 'flowers/red-rose.png', '#DC143C'),
('Lily', 'Restored innocence and the majesty of the soul', 'flowers/lily.png', '#FFF8DC'),
('Orchid', 'Eternal beauty and rare spiritual grace', 'flowers/orchid.png', '#DA70D6'),
('Jasmine', 'Love, purity and the sweetness of memory', 'flowers/jasmine.png', '#FFFACD'),
('Sunflower', 'Warmth, adoration and lasting loyalty', 'flowers/sunflower.png', '#FFD700'),
('Carnation', 'Admiration, gratitude and pure love', 'flowers/carnation.png', '#FF69B4');

-- Candle Catalog
INSERT INTO `candles_catalog` (`candle_name`, `candle_type`, `image`, `glow_color`) VALUES
('White Memorial Candle', 'Memorial', 'candles/white-candle.png', '#FFFDD0'),
('Golden Prayer Candle', 'Prayer', 'candles/golden-candle.png', '#FFD700'),
('Eternal Flame Candle', 'Eternal', 'candles/eternal-candle.png', '#FF8C00'),
('Angel Light Candle', 'Spiritual', 'candles/angel-candle.png', '#E6E6FA');

-- Timeline
INSERT INTO `timeline` (`year`, `month`, `title`, `description`, `icon`, `category`, `sort_order`) VALUES
(1945, 3, 'Birth', 'Hercio Maria da Neves Campos was born on a warm March morning in Dili, Timor-Leste, bringing joy to his beloved family.', 'baby', 'birth', 1),
(1952, 9, 'First Steps in Education', 'Began his primary education with remarkable curiosity and dedication, showing early signs of his brilliant mind.', 'school', 'education', 2),
(1963, NULL, 'Secondary Education', 'Completed secondary schooling with distinction, earning the admiration of teachers and peers alike.', 'book', 'education', 3),
(1968, NULL, 'Higher Studies', 'Pursued higher education with unwavering determination, laying the foundation for a life of service.', 'graduation-cap', 'education', 4),
(1972, 6, 'Marriage', 'United in sacred matrimony, beginning a love story that would span decades and inspire generations.', 'heart', 'family', 5),
(1975, NULL, 'Professional Career Begins', 'Embarked on a distinguished professional journey, serving his community with integrity and passion.', 'briefcase', 'work', 6),
(1980, NULL, 'Community Leadership', 'Took on leadership roles in the community, becoming a pillar of strength and guidance for many.', 'star', 'achievement', 7),
(1990, NULL, 'Distinguished Service Recognition', 'Received recognition for decades of dedicated service to his community and nation.', 'award', 'achievement', 8),
(1995, NULL, 'Family Milestones', 'Celebrated the growing Campos family — children, grandchildren, and the bonds that would last forever.', 'family', 'family', 9),
(2005, NULL, 'Retirement', 'After a lifetime of service, stepped gracefully into retirement, sharing wisdom and love with family.', 'sunset', 'retirement', 10),
(2024, 1, 'Returned to Eternity', 'On January 8th, 2024, Hercio Maria da Neves Campos peacefully returned to the Lord\'s embrace, leaving behind a legacy of love.', 'dove', 'passing', 11);

-- Biography Sections
INSERT INTO `biography` (`section_title`, `section_content`, `section_order`, `icon`) VALUES
('A Life of Purpose', 'Hercio Maria da Neves Campos was born on March 15, 1945, in the beautiful land of Timor-Leste. From his earliest days, he carried within him a profound sense of purpose — a quiet strength that would define every chapter of his remarkable life. Raised in a family that valued faith, community, and education, Hercio learned early that life\'s greatest gifts are not possessions, but the love we give and the lives we touch.', 1, 'heart'),
('Education & Intellectual Journey', 'Hercio''s thirst for knowledge was insatiable. He pursued his education with extraordinary dedication, completing his primary and secondary studies with distinction before advancing to higher education. His academic journey was not merely about acquiring knowledge — it was about becoming a better servant to his people, his family, and his faith. He believed deeply that education was the greatest inheritance one could receive.', 2, 'book'),
('Professional Life & Service', 'For over three decades, Hercio dedicated himself to public service with an unwavering sense of duty. His career was marked not by personal ambition, but by a genuine desire to uplift those around him. Colleagues remember him as a man of exceptional integrity — someone who led by example, who listened before speaking, and who always placed the good of the community above personal gain.', 3, 'briefcase'),
('Family — His Greatest Joy', 'Nothing brought Hercio more joy than his family. He was a devoted husband, a loving father, and a grandfather whose embrace made grandchildren feel as though they were the most important people in the world. He built his home on the foundation of love, prayer, and laughter. Every family gathering was an occasion he treasured — cooking, storytelling, music, and the simple gift of being together.', 4, 'home'),
('Faith & Spiritual Life', 'Faith was the cornerstone of Hercio''s life. He was a man of deep and sincere spirituality — not the kind that performs for others, but the kind that quietly shapes every decision, every word, every act of kindness. He attended Mass faithfully, prayed with his family, and drew strength from his relationship with God. Those who knew him well saw a man who lived his faith rather than merely professing it.', 5, 'church'),
('Personal Values & Legacy', 'Hercio''s legacy is not written in monuments or headlines — it lives in the hearts of every person he touched. His values of honesty, humility, compassion, and perseverance continue to guide his children and grandchildren. He taught by example: that the measure of a life is not its length, but its depth of love. That wealth means nothing without generosity. That time spent with family is never wasted. He leaves behind not just memories, but a way of being in the world.', 6, 'star');

-- Sample Gallery Items
INSERT INTO `gallery` (`title`, `description`, `file_path`, `file_type`, `category`, `sort_order`) VALUES
('Young Hercio', 'A cherished photograph from childhood years', 'gallery/childhood-01.jpg', 'photo', 'childhood', 1),
('Wedding Day', 'The most beautiful day — united in love forever', 'gallery/wedding-01.jpg', 'photo', 'family', 2),
('Family Gathering 1990', 'A joyful family reunion — love in every smile', 'gallery/family-01.jpg', 'photo', 'family', 3),
('Professional Life', 'Dedicated years of service to his community', 'gallery/work-01.jpg', 'photo', 'work', 4),
('Golden Years', 'Peaceful retirement surrounded by loved ones', 'gallery/special-01.jpg', 'photo', 'special', 5);

SET FOREIGN_KEY_CHECKS = 1;
