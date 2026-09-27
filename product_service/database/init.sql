CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    description TEXT,
    stock INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)


-- Data Contoh
INSERT INTO products (name, description, stock) VALUES
('Keyboard Mekanikal', 'Keyboard mekanikal dengan switch blue', 500000, 25),
('Mouse Wireless', 'Mouse Wireless dengan sensor optical', 350000, 10),
('Headset Gaming', 'headset gaming dengan kualitas audio terbaik', 800000, 15);