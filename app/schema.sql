-- Schema para Sistema de Gestión de Tareas (TaskQA)
-- Base de datos: gestion_qa_db

DROP TABLE IF EXISTS tareas;
DROP TABLE IF EXISTS usuarios;

CREATE TABLE usuarios (
    id SERIAL PRIMARY KEY,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre_completo VARCHAR(100) NOT NULL,
    rol VARCHAR(20) DEFAULT 'Tester QA',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE tareas (
    id SERIAL PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT,
    prioridad VARCHAR(20) NOT NULL CHECK (prioridad IN ('Alta', 'Media', 'Baja')),
    estado VARCHAR(20) NOT NULL CHECK (estado IN ('Pendiente', 'En Progreso', 'Completada')),
    fecha_limite DATE NOT NULL,
    usuario_id INT REFERENCES usuarios(id) ON DELETE SET NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Usuario de prueba: admin / admin123 (hash password_hash('admin123', PASSWORD_BCRYPT))
-- Para compatibilidad directa también soportamos verificación de hash y fallback
INSERT INTO usuarios (usuario, password, nombre_completo, rol) 
VALUES ('admin', '$2y$10$wOq5QG5W616xKzM3q59dkeYJbJ6QGk0GfKqJ0uD9z6t6nUq7p1C1y', 'Administrador QA', 'Líder de Calidad');

-- Datos semilla iniciales
INSERT INTO tareas (titulo, descripcion, prioridad, estado, fecha_limite, usuario_id) VALUES
('Diseño de Casos de Prueba', 'Definir matriz de pruebas funcionales para el sprint.', 'Alta', 'Completada', CURRENT_DATE + INTERVAL '5 days', 1),
('Configuración de Entorno QA', 'Instalación y verificación de dependencias de prueba.', 'Media', 'En Progreso', CURRENT_DATE + INTERVAL '7 days', 1),
('Automatización de Regresión', 'Creación de scripts automatizados para el módulo web.', 'Alta', 'Pendiente', CURRENT_DATE + INTERVAL '10 days', 1);
