-- =====================================================================
-- SISTEMA DE MATRÍCULA - SCHEMA NORMALIZADO
-- Colegio Secundario de Guabito
--
-- Cambios sobre el schema original:
--   * Las columnas LONGBLOB de archivos/fotos se reemplazaron por
--     VARCHAR(255) que guardan la RUTA RELATIVA al archivo físico
--     (ej. 'uploads/perfiles/12_1700000000.jpg').
--   * Los archivos físicos se almacenan en:
--       /uploads/perfiles/    -> fotos de perfil (usuario, estudiante, etc.)
--       /uploads/documentos/  -> boletines, certificados
-- =====================================================================

CREATE DATABASE IF NOT EXISTS sistema_matricula
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE sistema_matricula;

-- ---------------------------------------------------------------------
-- Tablas de catálogo (sin dependencias)
-- ---------------------------------------------------------------------

CREATE TABLE rol(
    id_rol INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(15) NOT NULL UNIQUE
);

INSERT INTO rol(nombre) VALUES ('Director');
INSERT INTO rol(nombre) VALUES ('Secretaria');
INSERT INTO rol(nombre) VALUES ('Profesor');
INSERT INTO rol(nombre) VALUES ('Estudiante');

CREATE TABLE area(
    id_area INT PRIMARY KEY AUTO_INCREMENT,
    nombre_area VARCHAR(30) NOT NULL UNIQUE
);

INSERT INTO area(nombre_area) VALUES ('Tecnológico');
INSERT INTO area(nombre_area) VALUES ('Humanístico');
INSERT INTO area(nombre_area) VALUES ('Científico');

CREATE TABLE bachillerato(
    id_bachiller INT PRIMARY KEY AUTO_INCREMENT,
    nombre_bachiller VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE turno(
    id_turno INT PRIMARY KEY AUTO_INCREMENT,
    nombre_turno VARCHAR(10) NOT NULL UNIQUE
);

INSERT INTO turno(nombre_turno) VALUES ('Matutino');

-- ---------------------------------------------------------------------
-- Personas
-- ---------------------------------------------------------------------

CREATE TABLE acudiente(
    id_acudiente INT PRIMARY KEY AUTO_INCREMENT,
    cedula VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    parentesco VARCHAR(50),
    foto VARCHAR(255) NULL             -- ruta: 'uploads/perfiles/xxx.jpg'
);

CREATE TABLE usuario(
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    cedula VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    contrasena VARCHAR(255),               -- hash bcrypt/argon2, nunca texto plano
    estado ENUM('Activo', 'Inactivo', 'Bloqueado') DEFAULT 'Inactivo',
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_rol INT NOT NULL,
    foto VARCHAR(255) NULL,            -- ruta: 'uploads/perfiles/xxx.jpg'
    -- Campos auxiliares para bloqueo por fuerza bruta (sección 6.8)
    intentos_fallidos INT NOT NULL DEFAULT 0,
    bloqueado_hasta DATETIME NULL,

    FOREIGN KEY (id_rol) REFERENCES rol(id_rol)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE especialidad(
    id_especialidad INT PRIMARY KEY AUTO_INCREMENT,
    nombre_especialidad VARCHAR(50) NOT NULL,
    id_area INT NOT NULL,

    FOREIGN KEY (id_area) REFERENCES area(id_area)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE estudiante(
    id_estudiante INT PRIMARY KEY AUTO_INCREMENT,
    segundo_nombre VARCHAR(100),
    segundo_apellido VARCHAR(100),
    sexo VARCHAR(10),                      -- unificado con solicitud_matricula
    telefono VARCHAR(20),
    correo VARCHAR(100) UNIQUE,
    fecha_nacimiento DATE,
    direccion VARCHAR(200),
    id_usuario INT,                        -- nombre/apellido/cédula se heredan de usuario
    id_acudiente INT,
    boletin VARCHAR(255) NULL,             -- ruta: 'uploads/documentos/xxx.pdf'
    certificado_nacimiento VARCHAR(255) NULL, -- ruta: 'uploads/documentos/xxx.pdf'

    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
        ON UPDATE CASCADE ON DELETE SET NULL,
    FOREIGN KEY (id_acudiente) REFERENCES acudiente(id_acudiente)
        ON UPDATE CASCADE ON DELETE SET NULL
);

CREATE TABLE grado(
    id_grado INT PRIMARY KEY AUTO_INCREMENT,
    nombre_grado VARCHAR(10) NOT NULL,
    id_bachiller INT NOT NULL,

    FOREIGN KEY (id_bachiller) REFERENCES bachillerato(id_bachiller)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE profesor(
    id_profesor INT PRIMARY KEY AUTO_INCREMENT,
    correo VARCHAR(50) NOT NULL UNIQUE,
    id_especialidad INT NOT NULL,
    id_usuario INT,
    id_turno INT NOT NULL DEFAULT 1,

    FOREIGN KEY (id_especialidad) REFERENCES especialidad(id_especialidad)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
        ON UPDATE CASCADE ON DELETE SET NULL,
    FOREIGN KEY (id_turno) REFERENCES turno(id_turno)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE materia(
    id_materia INT PRIMARY KEY AUTO_INCREMENT,
    nombre_materia VARCHAR(60) NOT NULL UNIQUE,
    id_profesor INT,
    id_especialidad INT,

    FOREIGN KEY (id_profesor) REFERENCES profesor(id_profesor)
        ON UPDATE CASCADE ON DELETE SET NULL,
    FOREIGN KEY (id_especialidad) REFERENCES especialidad(id_especialidad)
        ON UPDATE CASCADE ON DELETE SET NULL
);

CREATE TABLE grupo(
    id_grupo INT PRIMARY KEY AUTO_INCREMENT,
    letra_grupo VARCHAR(1) NOT NULL,
    limite_grupo INT NOT NULL,
    id_grado INT NOT NULL,
    id_turno INT NOT NULL,
    id_consejero INT UNIQUE,

    FOREIGN KEY (id_grado) REFERENCES grado(id_grado)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (id_turno) REFERENCES turno(id_turno)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (id_consejero) REFERENCES profesor(id_profesor)
        ON UPDATE CASCADE ON DELETE SET NULL
);

CREATE TABLE bachillerato_materia(
    id_bachiller_materia INT PRIMARY KEY AUTO_INCREMENT,
    id_bachiller INT NOT NULL,
    id_materia INT NOT NULL,
    nivel INT NOT NULL,

    UNIQUE(id_bachiller, id_materia, nivel),

    FOREIGN KEY (id_materia) REFERENCES materia(id_materia)
        ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (id_bachiller) REFERENCES bachillerato(id_bachiller)
        ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE horario(
    id_horario INT PRIMARY KEY AUTO_INCREMENT,
    dia VARCHAR(15) NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    id_grupo INT NOT NULL,
    id_materia INT NOT NULL,
    anio INT NOT NULL DEFAULT 2026,
    numero_fila INT NOT NULL DEFAULT 0,

    FOREIGN KEY (id_grupo) REFERENCES grupo(id_grupo)
        ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (id_materia) REFERENCES materia(id_materia)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE matricula(
    id_matricula INT PRIMARY KEY AUTO_INCREMENT,
    fecha_matricula DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_estudiante INT NOT NULL,
    id_grupo INT NOT NULL,
    anio INT NOT NULL DEFAULT (YEAR(CURDATE())),

    UNIQUE KEY uq_estudiante_anio (id_estudiante, anio),

    FOREIGN KEY (id_estudiante) REFERENCES estudiante(id_estudiante)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (id_grupo) REFERENCES grupo(id_grupo)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE solicitud_matricula(
    id_solicitud INT PRIMARY KEY AUTO_INCREMENT,
    cedula VARCHAR(20),
    nombre VARCHAR(60),
    segundo_nombre VARCHAR(60),
    apellido VARCHAR(60),
    segundo_apellido VARCHAR(60),
    sexo VARCHAR(10),                      -- unificado con estudiante
    telefono VARCHAR(20),
    correo VARCHAR(100),
    fecha_nacimiento DATE,
    direccion VARCHAR(200),
    boletin VARCHAR(255) NULL,             -- ruta: 'uploads/documentos/xxx.pdf'
    certificado_nacimiento VARCHAR(255) NULL, -- ruta: 'uploads/documentos/xxx.pdf'
    cedula_acudiente VARCHAR(20),
    nombre_acudiente VARCHAR(60),
    apellido_acudiente VARCHAR(60),
    telefono_acudiente VARCHAR(20),
    parentesco VARCHAR(30),
    id_grupo INT,
    estado ENUM('Pendiente','Aprobada','Rechazada') DEFAULT 'Pendiente',
    motivo_rechazo VARCHAR(300),
    fecha_solicitud DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_respuesta DATETIME,

    FOREIGN KEY (id_grupo) REFERENCES grupo(id_grupo)
        ON UPDATE CASCADE ON DELETE SET NULL
);

CREATE TABLE notificacion_secretaria(
    id_notificacion INT PRIMARY KEY AUTO_INCREMENT,
    id_solicitud INT NOT NULL,
    tipo ENUM('Aprobada','Rechazada') NOT NULL,
    mensaje VARCHAR(500),
    leida TINYINT(1) DEFAULT 0,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_solicitud) REFERENCES solicitud_matricula(id_solicitud)
        ON UPDATE CASCADE ON DELETE CASCADE
);

-- ---------------------------------------------------------------------
-- Recuperación de contraseña (sección 6.9: token aleatorio + expiración)
-- ---------------------------------------------------------------------
CREATE TABLE token_recuperacion(
    id_token INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    token VARCHAR(128) NOT NULL UNIQUE,    -- bin2hex(random_bytes(32))
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion DATETIME NOT NULL,   -- 1 hora después de creado
    usado TINYINT(1) NOT NULL DEFAULT 0,

    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
        ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE solicitud_reseteo(
    id_solicitud INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    fecha_solicitud DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('Pendiente', 'Atendida') DEFAULT 'Pendiente',

    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
        ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE intentos_activacion(
    id INT PRIMARY KEY AUTO_INCREMENT,
    ip VARCHAR(45) NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_intentos_act_ip_fecha (ip, fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Datos iniciales
-- ---------------------------------------------------------------------
--
-- IMPORTANTE: este usuario NO tiene contraseña (NULL). El primer acceso
-- del Director debe hacerse creando una contraseña desde el panel o
-- mediante un script de instalación (módulo posterior).
--
INSERT INTO usuario (cedula, nombre, apellido, contrasena, estado, id_rol)
VALUES ('111', 'José', 'Jimenez', NULL, 'Inactivo',
        (SELECT id_rol FROM rol WHERE nombre = 'Director'));

INSERT INTO materia (nombre_materia, id_profesor) VALUES ('Recreo', NULL);