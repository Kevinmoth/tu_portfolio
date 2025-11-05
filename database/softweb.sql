CREATE DATABASE IF NOT EXISTS portfolio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE portfolio_db;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    slug VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tecnologias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    icono VARCHAR(255),
    color VARCHAR(7)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE proyectos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    descripcion_corta VARCHAR(500),
    categoria_id INT NOT NULL,
    imagen LONGBLOB,
    imagen_tipo VARCHAR(50),
    imagen_tamanio INT,
    imagen_nombre VARCHAR(255),
    url_demo VARCHAR(255),
    url_github VARCHAR(255),
    url_repo VARCHAR(255),
    destacado BOOLEAN DEFAULT FALSE,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE proyectos_tecnologias (
    proyecto_id INT NOT NULL,
    tecnologia_id INT NOT NULL,
    PRIMARY KEY (proyecto_id, tecnologia_id),
    FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE CASCADE,
    FOREIGN KEY (tecnologia_id) REFERENCES tecnologias(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE imagenes_proyecto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proyecto_id INT NOT NULL,
    imagen LONGBLOB NOT NULL,
    imagen_nombre VARCHAR(255),
    imagen_tipo VARCHAR(50),
    imagen_tamanio INT,
    es_principal BOOLEAN DEFAULT FALSE,
    orden INT DEFAULT 0,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE mensajes_contacto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    asunto VARCHAR(200),
    mensaje TEXT NOT NULL,
    leido BOOLEAN DEFAULT FALSE,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT,
    tipo VARCHAR(20) DEFAULT 'text',
    descripcion VARCHAR(255),
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE visitas_proyectos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proyecto_id INT NOT NULL,
    ip_visitante VARCHAR(45),
    fecha_visita TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE CASCADE,
    INDEX idx_proyecto (proyecto_id),
    INDEX idx_fecha (fecha_visita)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE logs_admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    accion VARCHAR(50) NOT NULL,
    tabla_afectada VARCHAR(50),
    registro_id INT,
    detalles TEXT,
    ip VARCHAR(45),
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO categorias (nombre, descripcion, slug) VALUES
('Bots', 'Proyectos de bots y automatizaciÃ³n', 'bots'),
('Programas', 'Software y aplicaciones desarrolladas', 'programas'),
('Scripts', 'Scripts y utilidades', 'scripts');

INSERT INTO tecnologias (nombre, icono, color) VALUES
('PHP', NULL, '#777BB4'),
('JavaScript', NULL, '#F7DF1E'),
('Python', NULL, '#3776AB'),
('MySQL', NULL, '#4479A1'),
('HTML', NULL, '#E34F26'),
('CSS', NULL, '#1572B6'),
('Java', NULL, '#007396'),
('Node.js', NULL, '#339933'),
('React', NULL, '#61DAFB'),
('Discord.py', NULL, '#5865F2');

INSERT INTO usuarios (usuario, email, password) VALUES
('admin', 'admin@portfolio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO configuracion (clave, valor, tipo, descripcion) VALUES
('nombre_sitio', 'Mi Portfolio', 'text', 'Nombre del sitio web'),
('sobre_mi_titulo', 'Sobre MÃ­', 'text', 'TÃ­tulo de la secciÃ³n Sobre MÃ­'),
('sobre_mi_contenido', 'Soy un desarrollador apasionado por la tecnologÃ­a...', 'textarea', 'Contenido Sobre MÃ­'),
('email_contacto', 'contacto@portfolio.com', 'text', 'Email de contacto'),
('github_url', 'https://github.com/usuario', 'text', 'URL de GitHub'),
('linkedin_url', '', 'text', 'URL de LinkedIn'),
('twitter_url', '', 'text', 'URL de Twitter'),
('proyectos_por_pagina', '9', 'number', 'Proyectos a mostrar por pagina');