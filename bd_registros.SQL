CREATE TABLE IF NOT EXISTS users (
    -- IDENTIFICACIÓN
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- CREDENCIALES
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    
    -- SEGURIDAD: Almacena el HASH de la contraseña
    password_hash VARCHAR(255) NOT NULL,
    
    -- ESTADO Y PERMISOS (MEJORA)
    is_active BOOLEAN NOT NULL DEFAULT TRUE, 
    -- Útil para desactivar cuentas temporalmente sin borrarlas (ej. suspensión)
    
    role ENUM('user', 'admin', 'guest') NOT NULL DEFAULT 'user', 
    -- Permite diferenciar entre usuarios normales, administradores, etc.
    
    -- METADATA
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    -- Rastrea la última vez que el registro fue modificado (ej. cambio de contraseña)
);