-- Base de Datos para la Tienda de Don Manolo
-- Basado en el Modelo Relacional de las Prácticas 1, 2 y 3

CREATE DATABASE IF NOT EXISTS tienda_don_manolo;
USE tienda_don_manolo;

-- Tabla: Empleado
CREATE TABLE Empleado (
    ID_Empleado INT PRIMARY KEY AUTO_INCREMENT,
    Nombre VARCHAR(100) NOT NULL,
    Apellido VARCHAR(100) NOT NULL,
    Puesto VARCHAR(50),
    Usuario VARCHAR(50) UNIQUE NOT NULL,
    Password VARCHAR(255) NOT NULL,
    Fecha_Registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabla: Proveedor
CREATE TABLE Proveedor (
    ID_Proveedor INT PRIMARY KEY AUTO_INCREMENT,
    Nombre_Empresa VARCHAR(150) NOT NULL,
    Email VARCHAR(100) UNIQUE,
    Telefono VARCHAR(20),
    CONSTRAINT chk_email_proveedor CHECK (Email LIKE '%@%.%')
) ENGINE=InnoDB;

-- Tabla: Producto (con jerarquía ISA - Estrategia B: Tabla Única)
CREATE TABLE Producto (
    ID_Producto INT PRIMARY KEY AUTO_INCREMENT,
    Nombre VARCHAR(100) NOT NULL,
    Descripcion VARCHAR(255),
    Precio_Venta DECIMAL(10,2) NOT NULL,
    Cantidad_Stock INT NOT NULL DEFAULT 0,
    Tipo_Producto VARCHAR(20) NOT NULL,
    -- Atributos específicos de Perecedero
    Fecha_Caducidad DATE,
    Requiere_Refrigeracion BOOLEAN,
    -- Atributos específicos de Abarrote
    Marca VARCHAR(50),
    Contenido_Neto VARCHAR(50),
    CONSTRAINT chk_precio_venta CHECK (Precio_Venta > 0),
    CONSTRAINT chk_cantidad_stock CHECK (Cantidad_Stock >= 0),
    CONSTRAINT chk_tipo_producto CHECK (Tipo_Producto IN ('Perecedero', 'Abarrote'))
) ENGINE=InnoDB;

-- Tabla: Suministra (Relación N:M entre Proveedor y Producto)
CREATE TABLE Suministra (
    ID_Proveedor INT NOT NULL,
    ID_Producto INT NOT NULL,
    Costo_de_Compra DECIMAL(10,2),
    Fecha_Ultimo_Suministro DATE,
    PRIMARY KEY (ID_Proveedor, ID_Producto),
    FOREIGN KEY (ID_Proveedor) REFERENCES Proveedor(ID_Proveedor) ON DELETE CASCADE,
    FOREIGN KEY (ID_Producto) REFERENCES Producto(ID_Producto) ON DELETE CASCADE,
    CONSTRAINT chk_costo_compra CHECK (Costo_de_Compra > 0)
) ENGINE=InnoDB;

-- Tabla: Venta
CREATE TABLE Venta (
    ID_Venta INT PRIMARY KEY AUTO_INCREMENT,
    Venta_Fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    Total DECIMAL(10,2) NOT NULL DEFAULT 0,
    ID_Empleado INT NOT NULL,
    FOREIGN KEY (ID_Empleado) REFERENCES Empleado(ID_Empleado),
    CONSTRAINT chk_total_venta CHECK (Total >= 0)
) ENGINE=InnoDB;

-- Tabla: Detalle_Venta (Entidad Débil)
CREATE TABLE Detalle_Venta (
    ID_Venta INT NOT NULL,
    ID_Producto INT NOT NULL,
    Cantidad_vendida INT NOT NULL,
    Precio_en_Venta DECIMAL(10,2) NOT NULL,
    Subtotal DECIMAL(10,2) GENERATED ALWAYS AS (Cantidad_vendida * Precio_en_Venta) STORED,
    PRIMARY KEY (ID_Venta, ID_Producto),
    FOREIGN KEY (ID_Venta) REFERENCES Venta(ID_Venta) ON DELETE CASCADE,
    FOREIGN KEY (ID_Producto) REFERENCES Producto(ID_Producto),
    CONSTRAINT chk_cantidad_vendida CHECK (Cantidad_vendida > 0),
    CONSTRAINT chk_precio_en_venta CHECK (Precio_en_Venta > 0)
) ENGINE=InnoDB;

-- Trigger: Actualizar Total de Venta automáticamente
DELIMITER //
CREATE TRIGGER trg_actualizar_total_venta_insert
AFTER INSERT ON Detalle_Venta
FOR EACH ROW
BEGIN
    UPDATE Venta
    SET Total = (
        SELECT SUM(Subtotal)
        FROM Detalle_Venta
        WHERE ID_Venta = NEW.ID_Venta
    )
    WHERE ID_Venta = NEW.ID_Venta;
END//

CREATE TRIGGER trg_actualizar_total_venta_update
AFTER UPDATE ON Detalle_Venta
FOR EACH ROW
BEGIN
    UPDATE Venta
    SET Total = (
        SELECT SUM(Subtotal)
        FROM Detalle_Venta
        WHERE ID_Venta = NEW.ID_Venta
    )
    WHERE ID_Venta = NEW.ID_Venta;
END//

CREATE TRIGGER trg_actualizar_total_venta_delete
AFTER DELETE ON Detalle_Venta
FOR EACH ROW
BEGIN
    UPDATE Venta
    SET Total = COALESCE((
        SELECT SUM(Subtotal)
        FROM Detalle_Venta
        WHERE ID_Venta = OLD.ID_Venta
    ), 0)
    WHERE ID_Venta = OLD.ID_Venta;
END//

-- Trigger: Reducir Stock al registrar venta
CREATE TRIGGER trg_reducir_stock
AFTER INSERT ON Detalle_Venta
FOR EACH ROW
BEGIN
    UPDATE Producto
    SET Cantidad_Stock = Cantidad_Stock - NEW.Cantidad_vendida
    WHERE ID_Producto = NEW.ID_Producto;
END//

-- Trigger: Validar que una Venta tenga al menos un Detalle
CREATE TRIGGER trg_validar_venta_con_detalle
BEFORE DELETE ON Detalle_Venta
FOR EACH ROW
BEGIN
    DECLARE cantidad_detalles INT;
    
    SELECT COUNT(*) INTO cantidad_detalles
    FROM Detalle_Venta
    WHERE ID_Venta = OLD.ID_Venta;
    
    IF cantidad_detalles <= 1 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Una venta debe tener al menos un producto';
    END IF;
END//

DELIMITER ;

-- Insertar empleado por defecto (Don Manolo)
-- Password: admin123 (hasheado con PHP password_hash)
INSERT INTO Empleado (Nombre, Apellido, Puesto, Usuario, Password) VALUES
('Manolo', 'Pérez', 'Dueño', 'donmanolo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('María', 'González', 'Cajera', 'maria', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Datos de ejemplo: Proveedores
INSERT INTO Proveedor (Nombre_Empresa, Email, Telefono) VALUES
('Bimbo S.A.', 'contacto@bimbo.com', '5555-1234'),
('Coca-Cola FEMSA', 'ventas@cocacola.com', '5555-5678'),
('Lala', 'pedidos@lala.com', '5555-9012'),
('Sabritas', 'distribucion@sabritas.com', '5555-3456');

-- Datos de ejemplo: Productos
INSERT INTO Producto (Nombre, Descripcion, Precio_Venta, Cantidad_Stock, Tipo_Producto, Fecha_Caducidad, Requiere_Refrigeracion) VALUES
('Leche Lala 1L', 'Leche entera pasteurizada', 25.50, 30, 'Perecedero', '2025-12-15', TRUE),
('Yogurt Natural', 'Yogurt natural sin azúcar', 18.00, 20, 'Perecedero', '2025-11-30', TRUE),
('Pan Blanco Bimbo', 'Pan de caja blanco grande', 42.00, 15, 'Perecedero', '2025-11-20', FALSE);

INSERT INTO Producto (Nombre, Descripcion, Precio_Venta, Cantidad_Stock, Tipo_Producto, Marca, Contenido_Neto) VALUES
('Coca-Cola 600ml', 'Refresco de cola', 15.00, 50, 'Abarrote', 'Coca-Cola', '600ml'),
('Sabritas Originales', 'Papas fritas sal', 18.50, 40, 'Abarrote', 'Sabritas', '45g'),
('Arroz Verde Valle', 'Arroz blanco grano largo', 32.00, 25, 'Abarrote', 'Verde Valle', '1kg'),
('Frijoles La Costeña', 'Frijoles negros refritos', 22.00, 30, 'Abarrote', 'La Costeña', '430g');

-- Relaciones Suministra
INSERT INTO Suministra (ID_Proveedor, ID_Producto, Costo_de_Compra, Fecha_Ultimo_Suministro) VALUES
(3, 1, 20.00, '2025-11-01'), -- Lala suministra Leche
(3, 2, 14.00, '2025-11-01'), -- Lala suministra Yogurt
(1, 3, 35.00, '2025-11-05'), -- Bimbo suministra Pan
(2, 4, 10.00, '2025-11-10'), -- Coca-Cola suministra Coca-Cola
(4, 5, 12.00, '2025-11-08'); -- Sabritas suministra Sabritas