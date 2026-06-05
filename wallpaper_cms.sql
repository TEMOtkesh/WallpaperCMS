-- ============================================================
-- WallpaperCMS Database Schema
-- Requirement: Database with 5+ tables and proper relationships
-- ============================================================

CREATE DATABASE IF NOT EXISTS wallpaper_cms
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE wallpaper_cms;

-- ============================================================
-- TABLE: users
-- Requirement: User roles (admin / user), password hashing
-- ============================================================
CREATE TABLE users (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    name     VARCHAR(100)  NOT NULL,
    email    VARCHAR(150)  NOT NULL UNIQUE,
    password VARCHAR(255)  NOT NULL,          -- stored via password_hash()
    role     ENUM('admin','user') NOT NULL DEFAULT 'user',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: categories
-- Requirement: 1:N  Category -> Wallpapers
-- ============================================================
CREATE TABLE categories (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: wallpapers
-- Requirement: File upload (image_path stored); 1:N User->Wallpapers
-- ============================================================
CREATE TABLE wallpapers (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200)  NOT NULL,
    description TEXT,
    image_path  VARCHAR(300)  NOT NULL,
    user_id     INT           NOT NULL,
    category_id INT           NOT NULL,
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)     REFERENCES users(id)      ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: tags
-- Requirement: N:N Wallpapers <-> Tags (via wallpaper_tags)
-- ============================================================
CREATE TABLE tags (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: wallpaper_tags  (junction / pivot)
-- Requirement: N:N relationship
-- ============================================================
CREATE TABLE wallpaper_tags (
    wallpaper_id INT NOT NULL,
    tag_id       INT NOT NULL,
    PRIMARY KEY (wallpaper_id, tag_id),
    FOREIGN KEY (wallpaper_id) REFERENCES wallpapers(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id)       REFERENCES tags(id)       ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: contacts
-- Requirement: Contact form data persisted to DB
-- ============================================================
CREATE TABLE contacts (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL,
    message    TEXT         NOT NULL,
    sent_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Default admin account  (password: admin123)
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@wallpaper.cms', '$2y$10$P/wMEXID1YNLCX.B/FiIheDtA5fD.555pMaDe58DoRpf/s2NmsfN6', 'admin');

-- Default categories
INSERT INTO categories (name) VALUES
('Nature'),
('Space'),
('Architecture'),
('Abstract'),
('Minimalist');

-- Default tags
INSERT INTO tags (name) VALUES
('4K'),
('Dark'),
('Light'),
('Colorful'),
('Monochrome');
