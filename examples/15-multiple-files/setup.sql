-- Real Estate Example with Multiple File Upload

DROP TABLE IF EXISTS properties;

CREATE TABLE properties (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL COMMENT '{"label": "T\u00edtulo", "placeholder": "Ej: Casa moderna en el centro"}',
    description TEXT COMMENT '{"label": "Descripci\u00f3n"}',
    price DECIMAL(10,2) NOT NULL COMMENT '{"type": "number", "min": 0, "step": "0.01", "label": "Precio (\u20ac)"}',
    bedrooms INT COMMENT '{"type": "number", "min": 0, "label": "Habitaciones"}',
    bathrooms INT COMMENT '{"type": "number", "min": 0, "label": "Ba\u00f1os"}',
    area DECIMAL(10,2) COMMENT '{"type": "number", "min": 0, "step": "0.01", "label": "\u00c1rea (m\u00b2)"}',
    address VARCHAR(255) COMMENT '{"label": "Direcci\u00f3n"}',
    city VARCHAR(100) COMMENT '{"label": "Ciudad"}',
    status ENUM('available', 'sold', 'rented') DEFAULT 'available' COMMENT '{"label": "Estado"}',
    photos JSON COMMENT '{"type": "multiple_files", "accept": "image/*", "max_files": 10, "max_size": 5242880, "label": "Fotos"}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) COMMENT = '{
    "display_name": "Propiedades",
    "icon": "\ud83c\udfe0",
    "list_view": {
        "searchable": ["title", "city", "address"],
        "filters": ["city", "status", "bedrooms"],
        "per_page": 12
    },
    "behaviors": {
        "timestamps": {"created_at": "created_at"}
    }
}';

-- Sample data
INSERT INTO properties (title, description, price, bedrooms, bathrooms, area, address, city, status) VALUES
('Casa moderna en el centro', 'Hermosa casa con jard\u00edn y garaje', 250000.00, 3, 2, 150.00, 'Calle Principal 123', 'Madrid', 'available'),
('Apartamento con vistas', 'Apartamento luminoso en \u00faltima planta', 180000.00, 2, 1, 80.00, 'Avenida del Mar 45', 'Barcelona', 'available'),
('Chalet independiente', 'Chalet con piscina y amplio jard\u00edn', 450000.00, 5, 3, 300.00, 'Urbanizaci\u00f3n Las Rosas 7', 'Valencia', 'sold');
