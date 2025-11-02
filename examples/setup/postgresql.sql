-- DynamicCRUD - PostgreSQL Setup Script
-- Creates all tables needed for examples

-- Drop existing tables
DROP TABLE IF EXISTS post_tags CASCADE;
DROP TABLE IF EXISTS tags CASCADE;
DROP TABLE IF EXISTS posts CASCADE;
DROP TABLE IF EXISTS products CASCADE;
DROP TABLE IF EXISTS categories CASCADE;
DROP TABLE IF EXISTS contacts CASCADE;
DROP TABLE IF EXISTS advanced_inputs CASCADE;
DROP TABLE IF EXISTS users CASCADE;
DROP TABLE IF EXISTS audit_log CASCADE;

-- Users table
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON COLUMN users.name IS '{"label": "Full Name", "placeholder": "Enter your full name", "minlength": 3}';
COMMENT ON COLUMN users.email IS '{"type": "email", "label": "Email Address", "placeholder": "user@example.com", "tooltip": "We will never share your email", "autocomplete": "email"}';
COMMENT ON COLUMN users.password IS '{"type": "password", "label": "Password", "minlength": 8, "placeholder": "Min 8 characters", "tooltip": "Use a strong password"}';
COMMENT ON COLUMN users.created_at IS '{"hidden": true}';

-- Categories table
CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON COLUMN categories.name IS '{"label": "Category Name", "placeholder": "e.g., Technology, Business"}';
COMMENT ON COLUMN categories.description IS '{"label": "Description", "placeholder": "Describe this category..."}';
COMMENT ON COLUMN categories.created_at IS '{"hidden": true}';

-- Posts table
CREATE TABLE posts (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    content TEXT,
    status VARCHAR(20) DEFAULT 'draft' CHECK (status IN ('draft', 'published')),
    published_at TIMESTAMP,
    category_id INT REFERENCES categories(id) ON DELETE SET NULL,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON COLUMN posts.title IS '{"label": "Post Title", "placeholder": "Enter an engaging title", "minlength": 5}';
COMMENT ON COLUMN posts.slug IS '{"label": "URL Slug", "placeholder": "auto-generated-from-title", "tooltip": "Leave empty to auto-generate", "pattern": "[a-z0-9-]+"}';
COMMENT ON COLUMN posts.content IS '{"label": "Content", "placeholder": "Write your post content here..."}';
COMMENT ON COLUMN posts.status IS '{"type": "select", "label": "Status"}';
COMMENT ON COLUMN posts.published_at IS '{"type": "datetime-local", "label": "Publish Date", "tooltip": "Auto-set when status is published"}';
COMMENT ON COLUMN posts.category_id IS '{"label": "Category"}';
COMMENT ON COLUMN posts.user_id IS '{"label": "Author", "display_column": "name"}';
COMMENT ON COLUMN posts.created_at IS '{"hidden": true}';

-- Tags table
CREATE TABLE tags (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON COLUMN tags.name IS '{"label": "Tag Name", "placeholder": "e.g., PHP, MySQL"}';
COMMENT ON COLUMN tags.created_at IS '{"hidden": true}';

-- Post-Tags pivot table
CREATE TABLE post_tags (
    post_id INT NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
    tag_id INT NOT NULL REFERENCES tags(id) ON DELETE CASCADE,
    PRIMARY KEY (post_id, tag_id)
);

-- Products table
CREATE TABLE products (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price NUMERIC(10, 2) NOT NULL,
    image VARCHAR(255),
    category_id INT REFERENCES categories(id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON COLUMN products.name IS '{"label": "Product Name", "placeholder": "Enter product name"}';
COMMENT ON COLUMN products.description IS '{"label": "Description", "placeholder": "Describe your product..."}';
COMMENT ON COLUMN products.price IS '{"type": "number", "step": "0.01", "min": 0, "label": "Price (USD)", "placeholder": "0.00"}';
COMMENT ON COLUMN products.image IS '{"type": "file", "accept": "image/*", "max_size": 2097152, "label": "Product Image", "tooltip": "JPG, PNG or WebP. Max 2MB"}';
COMMENT ON COLUMN products.category_id IS '{"label": "Category"}';
COMMENT ON COLUMN products.created_at IS '{"hidden": true}';

-- Contacts table
CREATE TABLE contacts (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    website VARCHAR(255),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON COLUMN contacts.name IS '{"label": "Full Name", "placeholder": "Enter your full name", "minlength": 3}';
COMMENT ON COLUMN contacts.email IS '{"type": "email", "label": "Email Address", "placeholder": "user@example.com", "tooltip": "We never share your email", "autocomplete": "email"}';
COMMENT ON COLUMN contacts.phone IS '{"type": "tel", "label": "Phone Number", "placeholder": "+1 (555) 123-4567", "pattern": "[0-9+\\-\\s()]+", "autocomplete": "tel"}';
COMMENT ON COLUMN contacts.website IS '{"type": "url", "label": "Website", "placeholder": "https://example.com", "tooltip": "Enter a valid URL"}';
COMMENT ON COLUMN contacts.message IS '{"label": "Your Message", "placeholder": "Tell us what you need...", "minlength": 10}';
COMMENT ON COLUMN contacts.created_at IS '{"hidden": true}';

-- Advanced inputs table
CREATE TABLE advanced_inputs (
    id SERIAL PRIMARY KEY,
    brand_color VARCHAR(7),
    phone VARCHAR(20),
    password VARCHAR(255),
    search_query VARCHAR(255),
    appointment_time TIME,
    birth_week VARCHAR(10),
    birth_month VARCHAR(7),
    satisfaction INT,
    email VARCHAR(255),
    website VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON COLUMN advanced_inputs.brand_color IS '{"type": "color", "label": "Brand Color", "placeholder": "#000000", "tooltip": "Pick your brand color"}';
COMMENT ON COLUMN advanced_inputs.phone IS '{"type": "tel", "label": "Phone Number", "placeholder": "555-123-4567", "pattern": "[0-9]{3}-[0-9]{3}-[0-9]{4}"}';
COMMENT ON COLUMN advanced_inputs.password IS '{"type": "password", "label": "Password", "minlength": 8, "placeholder": "Min 8 characters"}';
COMMENT ON COLUMN advanced_inputs.search_query IS '{"type": "search", "label": "Search", "placeholder": "Search..."}';
COMMENT ON COLUMN advanced_inputs.appointment_time IS '{"type": "time", "label": "Appointment Time"}';
COMMENT ON COLUMN advanced_inputs.birth_week IS '{"type": "week", "label": "Birth Week"}';
COMMENT ON COLUMN advanced_inputs.birth_month IS '{"type": "month", "label": "Birth Month"}';
COMMENT ON COLUMN advanced_inputs.satisfaction IS '{"type": "range", "label": "Satisfaction Level", "min": 0, "max": 100, "step": 10, "tooltip": "Rate from 0 to 100"}';
COMMENT ON COLUMN advanced_inputs.email IS '{"type": "email", "label": "Email", "placeholder": "user@example.com", "autocomplete": "email"}';
COMMENT ON COLUMN advanced_inputs.website IS '{"type": "url", "label": "Website", "placeholder": "https://example.com"}';
COMMENT ON COLUMN advanced_inputs.notes IS '{"label": "Notes", "placeholder": "Enter your notes here..."}';
COMMENT ON COLUMN advanced_inputs.created_at IS '{"readonly": true, "label": "Created At"}';

-- Audit log table
CREATE TABLE audit_log (
    id SERIAL PRIMARY KEY,
    table_name VARCHAR(100) NOT NULL,
    record_id INT NOT NULL,
    action VARCHAR(20) NOT NULL CHECK (action IN ('create', 'update', 'delete')),
    user_id INT,
    old_values JSONB,
    new_values JSONB,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_audit_table_record ON audit_log(table_name, record_id);
CREATE INDEX idx_audit_user ON audit_log(user_id);
CREATE INDEX idx_audit_created ON audit_log(created_at);

-- Insert sample data
INSERT INTO categories (name, description) VALUES
('Technology', 'Tech news and articles'),
('Business', 'Business and finance'),
('Lifestyle', 'Health and lifestyle');

INSERT INTO tags (name) VALUES
('PHP'), ('MySQL'), ('JavaScript'), ('Tutorial'), ('News');

INSERT INTO users (name, email, password) VALUES
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Jane Smith', 'jane@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO posts (title, slug, content, status, category_id, user_id) VALUES
('Getting Started with DynamicCRUD', 'getting-started-dynamiccrud', 'Learn how to use DynamicCRUD...', 'published', 1, 1),
('Advanced PHP Techniques', 'advanced-php-techniques', 'Explore advanced PHP patterns...', 'draft', 1, 2);

INSERT INTO post_tags (post_id, tag_id) VALUES
(1, 1), (1, 2), (1, 4),
(2, 1), (2, 4);

INSERT INTO products (name, description, price, category_id) VALUES
('Laptop Pro', 'High-performance laptop', 1299.99, 1),
('Wireless Mouse', 'Ergonomic wireless mouse', 29.99, 1);

SELECT 'Database setup completed successfully!' as message;
