CREATE DATABASE car_marketplace;
USE car_marketplace;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    NAME VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    PASSWORD VARCHAR(255) NOT NULL,
    role ENUM('admin','seller','buyer') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE cars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,

    make VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    YEAR INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    mileage INT NOT NULL,
    TYPE VARCHAR(30),
    description TEXT,

    image_url VARCHAR(255),
    views INT DEFAULT 0,

    STATUS ENUM('Available','Pending','Sold','Removed') DEFAULT 'Pending',
    action_request ENUM('mark_sold','remove') DEFAULT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (seller_id) REFERENCES users(id)
);

CREATE TABLE favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    car_id INT NOT NULL,

    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (car_id) REFERENCES cars(id)
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    car_id INT DEFAULT 0,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (receiver_id) REFERENCES users(id)
);

CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    car_id INT NOT NULL,
    user_id INT NOT NULL,

    STATUS ENUM('Pending','Approved','Cancelled') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (car_id) REFERENCES cars(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    car_id INT NOT NULL,

    TYPE VARCHAR(50),
    amount DECIMAL(10,2),
    STATUS ENUM('Paid','Failed') DEFAULT 'Paid',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (car_id) REFERENCES cars(id)
);

INSERT INTO users (NAME, email, PASSWORD, role) VALUES
('Admin', 'admin@auto.com', 'admin123', 'admin'),
('Juan Seller', 'seller@auto.com', 'seller123', 'seller'),
('Maria Buyer', 'buyer@auto.com', 'buyer123', 'buyer');

INSERT INTO cars 
(seller_id, make, model, YEAR, price, mileage, TYPE, description, image_url, STATUS)
VALUES
(2, 'Toyota', 'Vios', 2021, 550000, 25000, 'Sedan', 'Well maintained', 'uploads/vios.jpg', 'Available'),
(2, 'Honda', 'Civic', 2020, 750000, 30000, 'Sedan', 'Low mileage', 'uploads/civic.jpg', 'Available');

