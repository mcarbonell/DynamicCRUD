-- Tabla de ejemplo para demostrar mejoras UX de Fase 3

CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL COMMENT '{"label": "Nombre completo", "minlength": 3, "tooltip": "Ingrese su nombre y apellido"}',
    email VARCHAR(255) NOT NULL COMMENT '{"type": "email", "label": "Correo electrónico", "tooltip": "Usaremos este email para contactarte"}',
    phone VARCHAR(20) COMMENT '{"label": "Teléfono", "tooltip": "Formato: +34 123 456 789"}',
    website VARCHAR(255) COMMENT '{"type": "url", "label": "Sitio web", "tooltip": "URL completa incluyendo http:// o https://"}',
    age INT COMMENT '{"type": "number", "min": 18, "max": 120, "label": "Edad", "tooltip": "Debes ser mayor de 18 años"}',
    message TEXT NOT NULL COMMENT '{"label": "Mensaje", "minlength": 10, "tooltip": "Cuéntanos en qué podemos ayudarte (mínimo 10 caracteres)"}',
    avatar VARCHAR(255) COMMENT '{"type": "file", "label": "Foto de perfil", "accept": "image/*", "allowed_mimes": ["image/jpeg", "image/png", "image/gif"], "max_size": 2097152, "tooltip": "Sube una foto (máx. 2MB)"}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '{"hidden": true}'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
