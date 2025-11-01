-- Modificaciones para Fase 4: Hooks y Eventos

-- Añadir columna slug a la tabla posts
ALTER TABLE posts 
ADD COLUMN slug VARCHAR(255) COMMENT '{"label": "Slug (URL amigable)", "tooltip": "Se genera automáticamente desde el título"}' AFTER title;

-- Añadir columna status a la tabla posts
ALTER TABLE posts 
ADD COLUMN status ENUM('draft', 'published') DEFAULT 'draft' COMMENT '{"label": "Estado"}' AFTER content;

-- Añadir columna published_at a la tabla posts
ALTER TABLE posts 
ADD COLUMN published_at DATETIME NULL COMMENT '{"label": "Fecha de publicación", "hidden": true}' AFTER status;

-- Crear índice para slug (útil para búsquedas)
CREATE INDEX idx_posts_slug ON posts(slug);
