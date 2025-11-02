-- Setup PostgreSQL Database for DynamicCRUD Examples

-- Drop tables if exist
DROP TABLE IF EXISTS post_tags CASCADE;
DROP TABLE IF EXISTS posts CASCADE;
DROP TABLE IF EXISTS tags CASCADE;
DROP TABLE IF EXISTS categories CASCADE;
DROP TABLE IF EXISTS users CASCADE;
DROP TABLE IF EXISTS products CASCADE;
DROP TABLE IF EXISTS contacts CASCADE;
DROP TABLE IF EXISTS audit_log CASCADE;

-- Users table
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    age INTEGER CHECK (age >= 18 AND age <= 120),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON COLUMN users.email IS '{"type": "email", "label": "Email Address", "tooltip": "We will never share your email"}';
COMMENT ON COLUMN users.age IS '{"type": "number", "min": 18, "max": 120}';
COMMENT ON COLUMN users.created_at IS '{"hidden": true}';

-- Categories table
CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
);

-- Posts table with foreign key
CREATE TABLE posts (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    category_id INTEGER REFERENCES categories(id) ON DELETE SET NULL,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    slug VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON COLUMN posts.title IS '{"minlength": 5}';
COMMENT ON COLUMN posts.slug IS '{"readonly": true, "tooltip": "Auto-generated from title"}';

-- Tags table
CREATE TABLE tags (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- Post-Tags pivot table (Many-to-Many)
CREATE TABLE post_tags (
    post_id INTEGER REFERENCES posts(id) ON DELETE CASCADE,
    tag_id INTEGER REFERENCES tags(id) ON DELETE CASCADE,
    PRIMARY KEY (post_id, tag_id)
);

-- Products table with file upload
CREATE TABLE products (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price NUMERIC(10, 2) NOT NULL,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON COLUMN products.price IS '{"type": "number", "min": 0, "step": "0.01"}';
COMMENT ON COLUMN products.image IS '{"type": "file", "accept": "image/*", "max_size": 2097152}';

-- Contacts table with advanced inputs
CREATE TABLE contacts (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    website VARCHAR(255),
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON COLUMN contacts.email IS '{"type": "email", "placeholder": "your@email.com"}';
COMMENT ON COLUMN contacts.phone IS '{"type": "tel", "pattern": "[0-9]{3}-[0-9]{3}-[0-9]{4}", "placeholder": "123-456-7890"}';
COMMENT ON COLUMN contacts.website IS '{"type": "url", "placeholder": "https://example.com"}';

-- Audit log table
CREATE TABLE audit_log (
    id SERIAL PRIMARY KEY,
    table_name VARCHAR(100) NOT NULL,
    record_id INTEGER NOT NULL,
    action VARCHAR(20) NOT NULL,
    user_id INTEGER,
    old_values TEXT,
    new_values TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_audit_table_record ON audit_log(table_name, record_id);
CREATE INDEX idx_audit_created ON audit_log(created_at);

-- Insert sample data
INSERT INTO users (name, email, password, age) VALUES
('John Doe', 'john@example.com', '$2y$10$abcdefghijklmnopqrstuv', 30),
('Jane Smith', 'jane@example.com', '$2y$10$abcdefghijklmnopqrstuv', 25),
('Bob Johnson', 'bob@example.com', '$2y$10$abcdefghijklmnopqrstuv', 35);

INSERT INTO categories (name, description) VALUES
('Technology', 'Tech news and articles'),
('Lifestyle', 'Lifestyle and wellness'),
('Business', 'Business and finance');

INSERT INTO tags (name) VALUES
('PHP'), ('JavaScript'), ('MySQL'), ('PostgreSQL'), ('Docker');

INSERT INTO posts (title, content, category_id, user_id, slug) VALUES
('Getting Started with PostgreSQL', 'PostgreSQL is a powerful database...', 1, 1, 'getting-started-with-postgresql'),
('Healthy Living Tips', 'Here are some tips for healthy living...', 2, 2, 'healthy-living-tips');

INSERT INTO post_tags (post_id, tag_id) VALUES
(1, 4), -- PostgreSQL tag
(1, 5); -- Docker tag

INSERT INTO products (name, description, price) VALUES
('Laptop', 'High-performance laptop', 999.99),
('Mouse', 'Wireless mouse', 29.99);

-- Success message
SELECT 'PostgreSQL database setup completed successfully!' as message;
