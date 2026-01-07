CREATE DATABASE IF NOT EXISTS gram_sahayak;
USE gram_sahayak;

-- 1. USERS TABLE 
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(100) NOT NULL,
    user_email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ADMIN ACCOUNT ONLY
-- Password is 'password'
INSERT INTO users (user_name, user_email, password, role) 
VALUES ('Sarpanch Patil', 'admin', '$2y$10$.df1mLtHBsh90pW3yTosg.NcVOnGpX6ZPvOPATlGaL3u7035n8Wge', 'admin');

-- 2. SERVICES TABLE
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    user_name VARCHAR(100),
    service_type VARCHAR(100),
    details TEXT,
    document VARCHAR(255),
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    created_at DATE DEFAULT (CURRENT_DATE),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 3. MEETINGS TABLE
CREATE TABLE meetings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    location VARCHAR(255),
    date DATE,
    time TIME,
    purpose VARCHAR(255)
);

-- 4. WORK REPORTS TABLE
CREATE TABLE work_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    status ENUM('Planned', 'In Progress', 'Completed'),
    date DATE DEFAULT (CURRENT_DATE)
);

-- 5. WASTE REQUESTS TABLE
CREATE TABLE waste_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    user_name VARCHAR(100),
    location VARCHAR(255),
    status ENUM('Pending', 'Completed') DEFAULT 'Pending',
    date DATE DEFAULT (CURRENT_DATE),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 6. QUERIES TABLE
CREATE TABLE queries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    user_name VARCHAR(100),
    subject VARCHAR(255),
    message TEXT,
    status ENUM('pending', 'resolved') DEFAULT 'pending',
    date DATE DEFAULT (CURRENT_DATE),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 7. INCIDENTS TABLE
CREATE TABLE incidents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    user_name VARCHAR(100),
    incident_type VARCHAR(100),
    location VARCHAR(255),
    details TEXT,
    status ENUM('Pending', 'Resolved') DEFAULT 'Pending',
    created_at DATE DEFAULT (CURRENT_DATE)
);