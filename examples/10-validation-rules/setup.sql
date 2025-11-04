-- Setup for Validation Rules Example

-- Drop tables if exist
DROP TABLE IF EXISTS vr_products;
DROP TABLE IF EXISTS vr_subscriptions;

-- Products table with validation rules
CREATE TABLE vr_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    sku VARCHAR(100) NOT NULL,
    category VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    discount DECIMAL(5,2) DEFAULT 0,
    stock INT DEFAULT 0,
    min_stock INT DEFAULT 10,
    status ENUM('draft', 'active', 'inactive') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) COMMENT = '{
    "display_name": "VR Products",
    "icon": "📦",
    "validation_rules": {
        "unique_together": [
            ["sku", "category"]
        ],
        "required_if": {
            "min_stock": {"status": "active"}
        },
        "conditional": {
            "discount": {
                "condition": "price > 100",
                "max": 50
            }
        }
    }
}';

-- Subscriptions table with approval workflow
CREATE TABLE vr_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan VARCHAR(50) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    status ENUM('pending', 'active', 'cancelled') DEFAULT 'pending',
    approved_at TIMESTAMP NULL,
    approved_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) COMMENT = '{
    "display_name": "VR Subscriptions",
    "icon": "💳",
    "validation_rules": {
        "unique_together": [
            ["email", "plan"]
        ],
        "required_if": {
            "phone": {"status": "active"}
        }
    },
    "business_rules": {
        "require_approval": true,
        "approval_field": "approved_at",
        "approval_roles": ["admin", "supervisor"],
        "max_records_per_user": 5,
        "owner_field": "user_id"
    }
}';

-- Sample data
INSERT INTO vr_products (name, sku, category, price, discount, stock, status) VALUES
('Laptop Pro', 'LP-001', 'electronics', 1200.00, 10, 50, 'active'),
('Mouse Wireless', 'MW-001', 'electronics', 25.00, 0, 200, 'active'),
('Keyboard Mechanical', 'KM-001', 'electronics', 150.00, 20, 75, 'active');

INSERT INTO vr_subscriptions (user_id, plan, email, phone, status) VALUES
(1, 'basic', 'user1@example.com', '555-0001', 'active'),
(1, 'premium', 'user1@example.com', '555-0001', 'pending'),
(2, 'basic', 'user2@example.com', NULL, 'pending');
