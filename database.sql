CREATE TABLE users (
  id_user INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('relawan', 'admin') NOT NULL DEFAULT 'relawan',
  organization VARCHAR(120) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE plant_locations (
  id_p_location INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  region VARCHAR(120) NULL,
  description TEXT NULL,
  center_lat DECIMAL(10,8) NOT NULL,
  center_lng DECIMAL(11,8) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE tree_groups (
  id_t_groups INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_p_location INT UNSIGNED NOT NULL,
  created_by INT UNSIGNED NOT NULL,
  group_name VARCHAR(150) NOT NULL,
  species VARCHAR(100) NOT NULL,
  total_planted INT UNSIGNED NOT NULL DEFAULT 0,
  alive_count INT UNSIGNED NOT NULL DEFAULT 0,
  center_lat DECIMAL(10,8) NOT NULL,
  center_lng DECIMAL(11,8) NOT NULL,
  planted_at DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_tg_location FOREIGN KEY (id_p_location) REFERENCES plant_locations(id_p_location),
  CONSTRAINT fk_tg_user FOREIGN KEY (created_by) REFERENCES users(id_user)
);

CREATE TABLE trees (
  id_tree INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tree_code VARCHAR(50) NOT NULL UNIQUE,
  id_p_location INT UNSIGNED NOT NULL,
  id_t_groups INT UNSIGNED NOT NULL,
  planted_by INT UNSIGNED NOT NULL,
  species VARCHAR(100) NOT NULL,
  latitude DECIMAL(10,8) NOT NULL,
  longitude DECIMAL(11,8) NOT NULL,
  planted_at DATETIME NOT NULL,
  current_status ENUM('sehat', 'butuh_perawatan', 'mati') NOT NULL DEFAULT 'sehat',
  verification_status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  CONSTRAINT fk_trees_location FOREIGN KEY (id_p_location) REFERENCES plant_locations(id_p_location),
  CONSTRAINT fk_trees_group FOREIGN KEY (id_t_groups) REFERENCES tree_groups(id_t_groups),
  CONSTRAINT fk_trees_user FOREIGN KEY (planted_by) REFERENCES users(id_user)
);

CREATE TABLE tree_logs (
  id_t_logs INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_tree INT UNSIGNED NOT NULL,
  id_t_groups INT UNSIGNED NOT NULL,
  updated_by INT UNSIGNED NOT NULL,
  photo_path VARCHAR(255) NOT NULL,
  height_cm INT UNSIGNED NULL,
  health_status ENUM('sehat', 'butuh_perawatan', 'mati') NOT NULL,
  notes TEXT NULL,
  captured_lat DECIMAL(10,8) NOT NULL,
  captured_lng DECIMAL(11,8) NOT NULL,
  captured_at DATETIME NOT NULL,
  verification_status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  verified_by INT UNSIGNED NULL,
  CONSTRAINT fk_logs_tree FOREIGN KEY (id_tree) REFERENCES trees(id_tree),
  CONSTRAINT fk_logs_group FOREIGN KEY (id_t_groups) REFERENCES tree_groups(id_t_groups),
  CONSTRAINT fk_logs_updater FOREIGN KEY (updated_by) REFERENCES users(id_user),
  CONSTRAINT fk_logs_verifier FOREIGN KEY (verified_by) REFERENCES users(id_user)
);

CREATE TABLE reports (
  id_report INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_p_location INT UNSIGNED NOT NULL,
  generated_by INT UNSIGNED NOT NULL,
  report_title VARCHAR(180) NOT NULL,
  total_trees INT UNSIGNED NOT NULL DEFAULT 0,
  survival_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  file_path VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reports_location FOREIGN KEY (id_p_location) REFERENCES plant_locations(id_p_location),
  CONSTRAINT fk_reports_user FOREIGN KEY (generated_by) REFERENCES users(id_user)
);