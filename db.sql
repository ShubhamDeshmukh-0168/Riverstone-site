-- Run this against your RDS MySQL instance before first use.
-- Example: mysql -h <rds-endpoint> -u admin -p < db.sql

CREATE DATABASE IF NOT EXISTS riverstone;
USE riverstone;

CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
