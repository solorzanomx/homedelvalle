-- =============================================
-- CRM VB.NET - Esquema de Base de Datos
-- MySQL 8+
-- =============================================

CREATE DATABASE IF NOT EXISTS CRM_VBNet
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE CRM_VBNet;

-- =============================================
-- Tabla de Roles
-- Define 3 niveles de permisos:
--   Administrador: Leer + Escribir + Eliminar
--   Editor:        Leer + Escribir
--   Lector:        Solo Leer
-- =============================================
CREATE TABLE IF NOT EXISTS Roles (
    Id          INT AUTO_INCREMENT PRIMARY KEY,
    Name        VARCHAR(50)  NOT NULL UNIQUE,
    Description VARCHAR(200) NULL,
    CanRead     TINYINT(1) NOT NULL DEFAULT 1,
    CanWrite    TINYINT(1) NOT NULL DEFAULT 0,
    CanDelete   TINYINT(1) NOT NULL DEFAULT 0,
    CreatedAt   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =============================================
-- Tabla de Usuarios
-- Passwords almacenados con SHA-256 + salt aleatorio
-- =============================================
CREATE TABLE IF NOT EXISTS Users (
    Id           INT AUTO_INCREMENT PRIMARY KEY,
    Username     VARCHAR(50)  NOT NULL UNIQUE,
    Email        VARCHAR(100) NOT NULL UNIQUE,
    PasswordHash VARCHAR(256) NOT NULL,
    Salt         VARCHAR(128) NOT NULL,
    FirstName    VARCHAR(100) NOT NULL,
    LastName     VARCHAR(100) NOT NULL,
    Phone        VARCHAR(20)  NULL,
    WhatsApp     VARCHAR(20)  NULL,
    Address      VARCHAR(300) NULL,
    PhotoPath    VARCHAR(500) NULL,
    RoleId       INT NOT NULL,
    IsActive     TINYINT(1) NOT NULL DEFAULT 1,
    LastLogin    DATETIME NULL,
    CreatedAt    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT FK_Users_Roles FOREIGN KEY (RoleId) REFERENCES Roles(Id)
) ENGINE=InnoDB;

-- =============================================
-- Configuración General del Sitio
-- Almacena pares clave-valor para nombre, logo, colores, etc.
-- =============================================
CREATE TABLE IF NOT EXISTS SiteSettings (
    Id           INT AUTO_INCREMENT PRIMARY KEY,
    SettingKey   VARCHAR(100) NOT NULL UNIQUE,
    SettingValue VARCHAR(500) NULL,
    UpdatedAt    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UpdatedBy    INT NULL,
    CONSTRAINT FK_SiteSettings_Users FOREIGN KEY (UpdatedBy) REFERENCES Users(Id)
) ENGINE=InnoDB;

-- =============================================
-- Log de Auditoría
-- Registra todas las acciones CRUD, login y logout
-- =============================================
CREATE TABLE IF NOT EXISTS AuditLog (
    Id        INT AUTO_INCREMENT PRIMARY KEY,
    UserId    INT NOT NULL,
    Action    VARCHAR(50)  NOT NULL COMMENT 'CREATE, READ, UPDATE, DELETE, LOGIN, LOGOUT',
    TableName VARCHAR(100) NOT NULL,
    RecordId  INT NULL,
    Details   TEXT NULL,
    CreatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT FK_AuditLog_Users FOREIGN KEY (UserId) REFERENCES Users(Id)
) ENGINE=InnoDB;

-- Índices para mejorar rendimiento
CREATE INDEX IX_Users_RoleId ON Users(RoleId);
CREATE INDEX IX_Users_Email ON Users(Email);
CREATE INDEX IX_AuditLog_UserId ON AuditLog(UserId);
CREATE INDEX IX_AuditLog_CreatedAt ON AuditLog(CreatedAt);

-- =============================================
-- DATOS INICIALES
-- =============================================

-- Roles
INSERT INTO Roles (Name, Description, CanRead, CanWrite, CanDelete) VALUES
('Administrador', 'Acceso total al sistema - puede leer, escribir y eliminar', 1, 1, 1),
('Editor', 'Puede leer y modificar registros, no puede eliminar', 1, 1, 0),
('Lector', 'Solo puede ver información, sin modificar ni eliminar', 1, 0, 0);

-- Configuración del sitio
INSERT INTO SiteSettings (SettingKey, SettingValue) VALUES
('SiteName', 'CRM Platform'),
('LogoPath', ''),
('PrimaryColor', '#4f46e5'),
('CompanyEmail', 'admin@crm.com'),
('CompanyPhone', '');

-- =============================================
-- USUARIOS DE PRUEBA
-- Las contraseñas se generan con: SHA256(password + salt) -> Base64
-- En producción, ejecutar SeedDefaultAdmin() desde la aplicación
--
-- Credenciales:
--   admin  / Admin123!   (Administrador)
--   editor / Editor123!  (Editor)
--   lector / Lector123!  (Lector)
-- =============================================
-- NOTA: Los hashes reales se insertan al ejecutar este script
-- desde la aplicación o con el comando de seed.
