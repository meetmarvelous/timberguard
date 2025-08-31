-- Drop database if it exists and create new one
DROP DATABASE IF EXISTS reserve;
CREATE DATABASE reserve CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE reserve;

-- Create Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'forest manager', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
);

-- Create Forest_Reserves table
CREATE TABLE forest_reserves (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserve_name VARCHAR(100) NOT NULL,
    location VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_location (location)
);

-- Create Trees table
CREATE TABLE trees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserve_id INT NOT NULL,
    species VARCHAR(100) NOT NULL DEFAULT 'Tectona Grandis',
    MTH DECIMAL(10,4) NOT NULL,
    THT DECIMAL(10,4) NOT NULL,
    DBH DECIMAL(10,4) NOT NULL,
    basal_area DECIMAL(10,6) NOT NULL,
    volume DECIMAL(10,6) NOT NULL,
    status ENUM('available', 'sold') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    lat DECIMAL(10,8),
    lng DECIMAL(10,8),
    db_cm DECIMAL(10,4),
    dm_cm DECIMAL(10,4),
    dt_cm DECIMAL(10,4),
    d_cm DECIMAL(10,4),
    FOREIGN KEY (reserve_id) REFERENCES forest_reserves(id) ON DELETE CASCADE,
    INDEX idx_reserve_id (reserve_id),
    INDEX idx_species (species),
    INDEX idx_status (status),
    INDEX idx_coordinates (lat, lng)
);

-- Create Transactions table
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tree_id INT NOT NULL,
    user_id INT NOT NULL,
    reserve_id INT NOT NULL,
    payment_code VARCHAR(50) NOT NULL UNIQUE,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('failed', 'completed') DEFAULT 'failed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tree_id) REFERENCES trees(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reserve_id) REFERENCES forest_reserves(id) ON DELETE CASCADE,
    INDEX idx_tree_id (tree_id),
    INDEX idx_user_id (user_id),
    INDEX idx_payment_code (payment_code),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Create Illegal_Reports table
CREATE TABLE illegal_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reporter_name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    coordinates VARCHAR(100) NOT NULL,
    reserve_id INT NOT NULL,
    date_reported TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'resolved') DEFAULT 'pending',
    resolution_notes TEXT,
    FOREIGN KEY (reserve_id) REFERENCES forest_reserves(id) ON DELETE CASCADE,
    INDEX idx_reserve_id (reserve_id),
    INDEX idx_status (status),
    INDEX idx_date_reported (date_reported)
);

-- Create Logs table
CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_timestamp (timestamp)
);

-- Insert Idanre Forest Reserve
INSERT INTO forest_reserves (reserve_name, location, description) 
VALUES ('Idanre Forest Reserve', 'Ondo State, Nigeria', 
        'One of the most biodiverse forest ecosystems in Nigeria, covering approximately 18,168 hectares of land.');

-- Insert sample tree data from the provided dataset
INSERT INTO trees (
    reserve_id, species, MTH, THT, DBH, basal_area, volume, lat, lng, 
    db_cm, dbh_cm, dm_cm, dt_cm, d_cm
) VALUES
(1, 'Tectona Grandis', 13, 17, 0.413, 0.05726295, 0.602173857, 7.562858, 5.205806, 41.3, 27, 15, 10, 13),
(1, 'Tectona Grandis', 17, 24, 0.222, 0.019858226, 0.288385328, 7.562886, 5.205781, 22.2, 15.9, 10, 5, 17),
(1, 'Tectona Grandis', 19, 27, 0.286, 0.038712582, 0.642603411, 7.562939, 5.205806, 28.6, 22.2, 15, 10, 19),
(1, 'Tectona Grandis', 21, 31, 0.382, 0.05726295, 0.990351069, 7.562986, 5.2058, 38.2, 27, 15, 9, 21),
(1, 'Tectona Grandis', 25, 33.5, 0.414, 0.050677318, 1.190263699, 7.562944, 5.2058, 41.4, 25.4, 15, 10, 25),
(1, 'Tectona Grandis', 19, 29, 0.191, 0.01606269, 0.299857948, 7.562967, 5.2058, 19.1, 14.3, 10, 5, 19),
(1, 'Tectona Grandis', 12, 18.5, 0.277, 0.024055938, 0.291431824, 7.562997, 5.205792, 27.7, 17.5, 10, 6, 12),
(1, 'Tectona Grandis', 18, 26.5, 0.286, 0.033333478, 0.431219077, 7.563044, 5.205783, 28.6, 20.6, 10, 5, 18),
(1, 'Tectona Grandis', 18, 24.5, 0.271, 0.033333478, 0.270520243, 7.563047, 5.205778, 27.1, 20.6, 5, 3, 18),
(1, 'Tectona Grandis', 20, 26.5, 0.347, 0.047149638, 0.738643419, 7.563047, 5.205783, 34.7, 24.5, 15, 5, 20),
(1, 'Tectona Grandis', 16, 18.6, 0.229, 0.019858226, 0.231186082, 7.563028, 5.205778, 22.9, 15.9, 10, 5, 16),
(1, 'Tectona Grandis', 19, 21.5, 0.271, 0.03142, 0.326340099, 7.562986, 5.205761, 27.1, 20, 10, 5, 19),
(1, 'Tectona Grandis', 11.5, 13.5, 0.191, 0.0153958, 0.083739995, 7.562892, 5.205772, 19.1, 14, 5, 3, 11.5),
(1, 'Tectona Grandis', 18.5, 23.5, 0.222, 0.026593888, 0.2823773, 7.562911, 5.205725, 22.2, 18.4, 10, 5, 18.5),
(1, 'Tectona Grandis', 14, 18.5, 0.191, 0.01266933, 0.113543829, 7.562922, 5.205675, 19.1, 12.7, 5, 2, 14),
(1, 'Tectona Grandis', 20.5, 23.5, 0.206, 0.018148192, 0.179780789, 7.562939, 5.205636, 20.6, 15.2, 6, 4, 20.5),
(1, 'Tectona Grandis', 22, 28.5, 0.302, 0.041192406, 0.498866337, 7.562944, 5.205586, 30.2, 22.9, 10, 5, 22),
(1, 'Tectona Grandis', 10, 12.7, 0.197, 0.019858226, 0.082648255, 7.562953, 5.205561, 19.7, 15.9, 5, 3, 10),
(1, 'Tectona Grandis', 23, 30.5, 0.296, 0.042278752, 0.523940021, 7.562994, 5.205564, 29.6, 23.2, 10, 6, 23),
(1, 'Tectona Grandis', 21, 27.2, 0.334, 0.048311392, 0.730546839, 7.563036, 5.205578, 33.4, 24.8, 15, 6, 21),
(1, 'Tectona Grandis', 23, 28.5, 0.341, 0.048311392, 0.793539396, 7.563053, 5.2057, 34.1, 24.8, 15, 8, 23),
(1, 'Tectona Grandis', 22, 28.5, 0.337, 0.048311392, 0.789763498, 7.563053, 5.205775, 33.7, 24.8, 15, 9, 22),
(1, 'Tectona Grandis', 21.5, 29.5, 0.35, 0.0615832, 0.839607858, 7.563036, 5.205692, 35, 28, 15, 7, 21.5),
(1, 'Tectona Grandis', 9, 13.5, 0.191, 0.01606269, 0.143830745, 7.562994, 5.205717, 19.1, 14.3, 10, 7, 9),
(1, 'Tectona Grandis', 12, 18.5, 0.219, 0.023238232, 0.22853841, 7.562933, 5.205747, 21.9, 17.2, 10, 8, 12),
(1, 'Tectona Grandis', 14, 21, 0.302, 0.053509046, 0.508072397, 7.562961, 5.205736, 30.2, 26.1, 15, 6, 14),
(1, 'Tectona Grandis', 16, 20, 0.283, 0.044493862, 0.462107032, 7.563008, 5.205692, 28.3, 23.8, 15, 8, 16),
(1, 'Tectona Grandis', 14, 19.6, 0.286, 0.03236967, 0.318939393, 7.562983, 5.205675, 28.6, 20.3, 10, 5, 14),
(1, 'Tectona Grandis', 22, 27.8, 0.359, 0.05188149, 0.632473053, 7.562969, 5.205692, 35.9, 25.7, 10, 7, 22),
(1, 'Tectona Grandis', 13, 17.6, 0.207, 0.019858226, 0.196655476, 7.562983, 5.205711, 20.7, 15.9, 10, 5, 13),
(1, 'Tectona Grandis', 12, 17, 0.239, 0.024055938, 0.224162979, 7.562928, 5.205758, 23.9, 17.5, 10, 6, 12),
(1, 'Tectona Grandis', 10.5, 15.5, 0.223, 0.01606269, 0.192021955, 7.562956, 5.205739, 22.3, 14.3, 10, 7, 10.5),
(1, 'Tectona Grandis', 8, 11.5, 0.143, 0.007089138, 0.064059292, 7.562969, 5.205769, 14.3, 9.5, 7, 5, 8),
(1, 'Tectona Grandis', 24.5, 31.5, 0.366, 0.057687906, 1.25347655, 7.563022, 5.205733, 36.6, 27.1, 20, 10, 24.5),
(1, 'Tectona Grandis', 11, 14, 0.206, 0.018148192, 0.160072332, 7.563017, 5.205667, 20.6, 15.2, 10, 7, 11),
(1, 'Tectona Grandis', 15, 19, 0.229, 0.017438886, 0.233919151, 7.563025, 5.205678, 22.9, 14.9, 10, 4, 15),
(1, 'Tectona Grandis', 14, 18.5, 0.238, 0.019858226, 0.240122637, 7.563036, 5.205678, 23.8, 15.9, 10, 5, 14),
(1, 'Tectona Grandis', 12, 16.5, 0.159, 0.01266933, 0.146415433, 7.5631, 5.205586, 15.9, 12.7, 10, 5, 12),
(1, 'Tectona Grandis', 13, 15, 0.21, 0.01266933, 0.168293375, 7.563108, 5.205567, 21, 12.7, 10, 4, 13),
(1, 'Tectona Grandis', 12, 17.8, 0.239, 0.026593888, 0.366141685, 7.563119, 5.205544, 23.9, 18.4, 15, 10, 12),
(1, 'Tectona Grandis', 16, 21.3, 0.264, 0.025733766, 0.455354978, 7.563139, 5.205569, 26.4, 18.1, 15, 6, 16),
(1, 'Tectona Grandis', 14, 17.4, 0.223, 0.019858226, 0.215560131, 7.563161, 5.205522, 22.3, 15.9, 10, 7, 14),
(1, 'Tectona Grandis', 11, 15.1, 0.2, 0.01606269, 0.163089438, 7.563122, 5.205492, 20, 14.3, 10, 5, 11),
(1, 'Tectona Grandis', 7, 13.2, 0.153, 0.01266933, 0.113897343, 7.563125, 5.205453, 15.3, 12.7, 10, 5, 7);

-- Insert admin user
INSERT INTO users (name, email, password, role) 
VALUES ('Admin User', 'admin@timberguard.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert forest manager user
INSERT INTO users (name, email, password, role) 
VALUES ('Forest Manager', 'meetmarvelous@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'forest manager');

-- Create a function to calculate tree price based on volume
DELIMITER //
CREATE FUNCTION calculate_tree_price(volume DECIMAL(10,6))
RETURNS DECIMAL(10,2)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE price DECIMAL(10,2);
    SET price = volume * 5000; -- Base price of ₦5,000 per cubic meter
    RETURN ROUND(price, 2);
END//
DELIMITER ;

-- Create a trigger to automatically set price when a tree is inserted
DELIMITER //
CREATE TRIGGER set_tree_price 
BEFORE INSERT ON trees
FOR EACH ROW 
BEGIN
    DECLARE calculated_price DECIMAL(10,2);
    SET calculated_price = calculate_tree_price(NEW.volume);
    -- You can add code here to store the price if you add a price column
END//
DELIMITER ;

-- Add an index on the trees table for better query performance
CREATE INDEX idx_tree_coordinates ON trees(lat, lng);
CREATE INDEX idx_tree_status ON trees(status);
CREATE INDEX idx_tree_species ON trees(species);

-- Display summary of the data
SELECT 
    'Database Creation Summary' as summary,
    (SELECT COUNT(*) FROM forest_reserves) as total_reserves,
    (SELECT COUNT(*) FROM trees) as total_trees,
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COUNT(*) FROM trees WHERE status = 'available') as available_trees;