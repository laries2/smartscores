-- SmartScores Database Schema

-- Create database
CREATE DATABASE IF NOT EXISTS smartscores;
USE smartscores;

-- Create admin table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create judges table
CREATE TABLE IF NOT EXISTS judges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    password VARCHAR(255) DEFAULT 'password',
    is_first_login BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create participants (users) table
CREATE TABLE IF NOT EXISTS participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create scores table
CREATE TABLE IF NOT EXISTS scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    participant_id INT NOT NULL,
    judge_id INT NOT NULL,
    points INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (participant_id) REFERENCES participants(id),
    FOREIGN KEY (judge_id) REFERENCES judges(id)
);

-- Insert some sample data
INSERT INTO judges (username, display_name, password, is_first_login) VALUES
('judge1', 'Noela Sirma', 'password', TRUE),
('judge2', 'Victor Wangila', 'password', TRUE),
('judge3', 'John Bill', 'password', TRUE);

INSERT INTO participants (username, display_name) VALUES
('laries', 'Sally Kosgei'),
('methu', 'Peter Otieno'),
('almax', 'James Kamau'),
('jack', 'Victor Lotudo'),
('matata', 'Samson Kipchirchir');

-- Insert admin user
INSERT INTO admins (username, password) VALUES
('admin', 'huawei');
