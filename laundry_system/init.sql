-- Initialize Laundry Service Database
CREATE DATABASE IF NOT EXISTS laundry_db;
USE laundry_db;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS price_list (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  price_per_kilo DECIMAL(10,2) DEFAULT 0,
  price_per_item DECIMAL(10,2) DEFAULT 0,
  express_fee DECIMAL(10,2) DEFAULT 0,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  service_mode ENUM('per_kilo','per_piece') NOT NULL,
  options VARCHAR(255),
  weight DECIMAL(8,2) DEFAULT 0,
  items INT DEFAULT 0,
  total_cost DECIMAL(10,2) DEFAULT 0,
  status ENUM('Pending','In Progress','Ready','Delivered') DEFAULT 'Pending',
  payment_status ENUM('Unpaid','Partial','Paid') DEFAULT 'Unpaid',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Insert an admin user (password: admin123)
INSERT INTO users (name, email, password, role) VALUES
('Admin','admin@laundry.local', SHA2('admin123',256), 'admin')
ON DUPLICATE KEY UPDATE email=email;

-- Default price list example
INSERT INTO price_list (name, price_per_kilo, price_per_item, express_fee) VALUES
('Default', 50.00, 20.00, 25.00)
ON DUPLICATE KEY UPDATE name=name;
