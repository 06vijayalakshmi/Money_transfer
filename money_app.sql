CREATE DATABASE IF NOT EXISTS money_app;
USE money_app;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    full_name VARCHAR(100),
    phone VARCHAR(15),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Accounts table
CREATE TABLE accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    account_no VARCHAR(20),
    bank_name VARCHAR(100),
    balance DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Contacts table
CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100),
    account_no VARCHAR(20),
    bank_name VARCHAR(100),
    phone VARCHAR(15),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Transactions table
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    from_account INT,
    from_user_id INT,
    to_account VARCHAR(20),
    to_name VARCHAR(100),
    to_bank VARCHAR(100),
    amount DECIMAL(10,2),
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description TEXT,
    FOREIGN KEY (from_account) REFERENCES accounts(id),
    FOREIGN KEY (from_user_id) REFERENCES users(id)
);

-- Insert sample data
INSERT INTO users (username, password, full_name, phone) VALUES
('john', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', '1111111111'),
('jane', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Smith', '2222222222'),
('mike', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mike Johnson', '3333333333');

INSERT INTO accounts (user_id, account_no, bank_name, balance) VALUES
(1, 'ACC001', 'State Bank of India', 50000.00),
(1, 'ACC002', 'HDFC Bank', 25000.00),
(2, 'ACC003', 'ICICI Bank', 75000.00),
(3, 'ACC004', 'Axis Bank', 35000.00);

INSERT INTO contacts (user_id, name, account_no, bank_name, phone) VALUES
(1, 'Jane Smith', 'ACC003', 'ICICI Bank', '2222222222'),
(1, 'Mike Johnson', 'ACC004', 'Axis Bank', '3333333333'),
(2, 'John Doe', 'ACC001', 'State Bank of India', '1111111111'),
(3, 'Jane Smith', 'ACC003', 'ICICI Bank', '2222222222');

INSERT INTO transactions (from_account, from_user_id, to_account, to_name, to_bank, amount, description) VALUES
(1, 1, 'ACC003', 'Jane Smith', 'ICICI Bank', 5000.00, 'Dinner payment'),
(3, 2, 'ACC001', 'John Doe', 'State Bank of India', 3000.00, 'Shopping refund'),
(1, 1, 'ACC004', 'Mike Johnson', 'Axis Bank', 2000.00, 'Birthday gift');