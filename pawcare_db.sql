-- ============================================
-- PawCare Pet Grooming System — Database v2
-- ============================================

CREATE DATABASE IF NOT EXISTS pet_grooming CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pet_grooming;

-- ── USERS ──
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100)  NOT NULL,
    email      VARCHAR(150)  NOT NULL UNIQUE,
    phone      VARCHAR(20)   DEFAULT NULL,
    password   VARCHAR(255)  NOT NULL,
    role       ENUM('user','admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── PETS ──
CREATE TABLE IF NOT EXISTS pets (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    name       VARCHAR(100) NOT NULL,
    pet_type   VARCHAR(50)  NOT NULL,
    breed      VARCHAR(100) DEFAULT NULL,
    size       ENUM('Small','Medium','Large','Extra Large') DEFAULT NULL,
    notes      TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── SERVICES ──
CREATE TABLE IF NOT EXISTS services (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)  NOT NULL,
    description TEXT          DEFAULT NULL,
    price       DECIMAL(10,2) NOT NULL,
    duration    INT           DEFAULT 60 COMMENT 'Minutes',
    is_active   TINYINT(1)    DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── APPOINTMENTS ──
CREATE TABLE IF NOT EXISTS appointments (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    pet_id     INT NOT NULL,
    service_id INT NOT NULL,
    appt_date  DATE NOT NULL,
    appt_time  TIME NOT NULL,
    notes      TEXT DEFAULT NULL,
    status     ENUM('pending','confirmed','done','cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (pet_id)     REFERENCES pets(id)     ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- SEED DATA
-- ============================================

-- Default Admin  →  admin@pawcare.ph / password
INSERT INTO users (name, email, phone, password, role) VALUES
('PawCare Admin', 'admin@pawcare.ph', '0917-000-0000',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Sample Customer  →  maria@email.com / password
INSERT INTO users (name, email, phone, password, role) VALUES
('Maria Santos', 'maria@email.com', '0917-123-4567',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Services
INSERT INTO services (name, description, price, duration) VALUES
('Bath & Dry',        'Shampoo, conditioner, and blow-dry with pet-safe products.',     350.00,  60),
('Haircut & Styling', 'Breed-specific cuts or custom styles by certified groomers.',    500.00,  90),
('Nail Trimming',     'Safe clipping and filing for your pet\'s comfort.',              150.00,  30),
('Ear Cleaning',      'Gentle ear cleaning to prevent infections.',                     120.00,  20),
('Full Spa Package',  'Bath, haircut, nail trim, ear cleaning, and pet cologne.',       900.00, 150),
('Teeth Brushing',    'Freshens breath and promotes dental health.',                    100.00,  20);

-- Sample Pet
INSERT INTO pets (user_id, name, pet_type, breed, size, notes) VALUES
(2, 'Coco', 'Dog', 'Poodle', 'Small', 'Nervous around loud noises — please handle gently.');

-- Sample Appointment
INSERT INTO appointments (user_id, pet_id, service_id, appt_date, appt_time, status) VALUES
(2, 1, 5, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '10:00:00', 'confirmed');

-- ============================================
-- Done! Import via phpMyAdmin or:
--   mysql -u root -p < pawcare_db.sql
-- Default password for both accounts: password
-- !! Change passwords after first login !!
-- ============================================
