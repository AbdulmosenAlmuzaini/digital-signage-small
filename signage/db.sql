-- قاعدة بيانات منصة اللافتات الرقمية
CREATE DATABASE IF NOT EXISTS signage_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE signage_db;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO users (username, password, role)
VALUES 
  ('admin', MD5('123456'), 'admin'),
  ('100491', MD5('Abd@0562292199'), 'admin')
ON DUPLICATE KEY UPDATE username = username;

CREATE TABLE IF NOT EXISTS screens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  orientation ENUM('landscape','portrait') NOT NULL DEFAULT 'landscape'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS media (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  filename VARCHAR(255) NOT NULL,
  type ENUM('image','video') NOT NULL,
  duration INT NOT NULL DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS playlists (
  id INT AUTO_INCREMENT PRIMARY KEY,
  screen_id INT NOT NULL,
  media_id INT NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  FOREIGN KEY (screen_id) REFERENCES screens(id) ON DELETE CASCADE,
  FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
