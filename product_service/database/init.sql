CREATE TABLE
    IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        description TEXT,
        stock INT NOT NULL DEFAULT 0,
        image MEDIUMTEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

INSERT INTO
    products (name, description, price, stock, image)
VALUES
    (
        'Keyboard Mekanikal',
        'Keyboard mekanikal switch blue',
        50000,
        25,
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/F2sAAAAASUVORK5CYII='
    ),
    (
        'Mouse Wireless',
        'Mouse wireless dengan sensor optical',
        350000,
        1,
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/F2sAAAAASUVORK5CYII='
    ),
    (
        'Headset Gaming',
        'Headset gaming dengan kualitas audio terbaik',
        800000,
        10,
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/F2sAAAAASUVORK5CYII='
    );