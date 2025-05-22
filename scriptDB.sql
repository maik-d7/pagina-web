CREATE TABLE users (
    id_user INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    PASSWORD VARCHAR(50) NOT NULL,
    email VARCHAR(50) NOT NULL,
    role VARCHAR(50) NOT NULL,
    enabled TINYINT(1) DEFAULT 1,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


CREATE TABLE tbl_servicios (
    id_servicio INT PRIMARY KEY AUTO_INCREMENT,
    icono VARCHAR(100),
    titulo VARCHAR(255),
    descripcion TEXT,
    orden INT DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


CREATE TABLE tbl_portafolio (
    id_portafolio INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(255),
    subtitulo VARCHAR(255),
    imagen VARCHAR(255),
    descripcion_modal TEXT,
    cliente_modal VARCHAR(255),
    categoria_modal VARCHAR(255),
    orden INT DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


CREATE TABLE tbl_entradas (
    id_entrada INT PRIMARY KEY AUTO_INCREMENT,
    fecha_evento VARCHAR(100),
    titulo VARCHAR(255),
    subtitulo VARCHAR(255) NULL,
    imagen VARCHAR(255),
    descripcion TEXT,
    alineacion_timeline VARCHAR(20) NULL,
    tipo_entrada VARCHAR(50) DEFAULT 'timeline',
    orden INT DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


CREATE TABLE tbl_equipo (
    id_miembro INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(255),
    puesto VARCHAR(255),
    imagen VARCHAR(255),
    twitter_url VARCHAR(255) NULL,
    facebook_url VARCHAR(255) NULL,
    linkedin_url VARCHAR(255) NULL,
    orden INT DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


CREATE TABLE tbl_configuraciones (
    id_config INT PRIMARY KEY AUTO_INCREMENT,
    nombre_config VARCHAR(100) UNIQUE,
    valor_config TEXT,
    descripcion_config VARCHAR(255) NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
