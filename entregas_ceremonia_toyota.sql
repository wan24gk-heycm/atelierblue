-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 02-09-2025 a las 07:41:24
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `entregas_ceremonia_toyota`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `ent_agregar_asesor` (`p_primer_nombre` VARCHAR(50), `p_segundo_nombre` VARCHAR(50), `p_primer_apellido` VARCHAR(50), `p_segundo_apellido` VARCHAR(50), `p_sucursal` ENUM('PACHUCA','TULANCINGO'), `p_rol` ENUM('ADMINISTRADOR','ASESOR DE VENTAS','RECEPCIONISTA'), `p_correo` VARCHAR(100), `p_telefono` CHAR(10), `p_contrasena` VARCHAR(255))   BEGIN
    DECLARE v_id INT;

    -- Normalizar entradas (Primera mayúscula, resto minúscula)
    SET p_primer_nombre   = CONCAT(UPPER(LEFT(TRIM(p_primer_nombre),1)), LOWER(SUBSTRING(TRIM(p_primer_nombre),2)));
    SET p_segundo_nombre  = IFNULL(NULLIF(TRIM(p_segundo_nombre),''), NULL);
    IF p_segundo_nombre IS NOT NULL THEN
        SET p_segundo_nombre = CONCAT(UPPER(LEFT(p_segundo_nombre,1)), LOWER(SUBSTRING(p_segundo_nombre,2)));
    END IF;

    SET p_primer_apellido = CONCAT(UPPER(LEFT(TRIM(p_primer_apellido),1)), LOWER(SUBSTRING(TRIM(p_primer_apellido),2)));
    SET p_segundo_apellido= IFNULL(NULLIF(TRIM(p_segundo_apellido),''), NULL);
    IF p_segundo_apellido IS NOT NULL THEN
        SET p_segundo_apellido = CONCAT(UPPER(LEFT(p_segundo_apellido,1)), LOWER(SUBSTRING(p_segundo_apellido,2)));
    END IF;

    -- Validar correo
    IF p_correo NOT LIKE '%_@_%._%' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El correo no tiene un formato válido';
    END IF;

    -- Validar teléfono
    IF p_telefono NOT REGEXP '^[0-9]{10}$' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El teléfono debe contener exactamente 10 dígitos';
    END IF;

    -- Validar duplicados por nombre completo
    IF EXISTS (
        SELECT 1 FROM ent_asesores 
        WHERE primer_nombre = p_primer_nombre
          AND COALESCE(segundo_nombre, '') = COALESCE(p_segundo_nombre, '')
          AND primer_apellido = p_primer_apellido
          AND COALESCE(segundo_apellido, '') = COALESCE(p_segundo_apellido, '')
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Ya existe un asesor con el mismo nombre completo';
    END IF;

    -- Insertar asesor
    INSERT INTO ent_asesores(
        primer_nombre, segundo_nombre, primer_apellido, segundo_apellido,
        sucursal, rol, correo, telefono, contrasena
    )
    VALUES (
        p_primer_nombre, p_segundo_nombre, p_primer_apellido, p_segundo_apellido,
        p_sucursal, COALESCE(p_rol, 'ASESOR DE VENTAS'),
        LOWER(p_correo), p_telefono, SHA2(p_contrasena, 256)
    );

    -- Obtener ID insertado
    SET v_id = LAST_INSERT_ID();

    -- Actualizar usuario generado
    UPDATE ent_asesores
    SET usuario = ent_generar_usuario(
        p_primer_nombre, p_segundo_nombre, p_primer_apellido, p_segundo_apellido, v_id
    )
    WHERE id_asesor = v_id;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ent_agregar_entrega` (IN `p_nombres` VARCHAR(100), IN `p_apellidos` VARCHAR(100), IN `p_lada` VARCHAR(5), IN `p_telefono` VARCHAR(15), IN `p_correo` VARCHAR(150), IN `p_unidad` VARCHAR(50), IN `p_vin` VARCHAR(50), IN `p_modelo` YEAR, IN `p_sucursal` VARCHAR(25), IN `p_color` VARCHAR(50), IN `p_fecha` DATE, IN `p_hora` TIME, IN `p_bahia` VARCHAR(20), IN `p_usuario` VARCHAR(50), IN `p_numero_asistentes` INT(5))   BEGIN
    DECLARE v_usuario VARCHAR(50);
    DECLARE v_correo_valido BOOLEAN;
    DECLARE v_exist INT DEFAULT 0;
    DECLARE v_fecha_hora DATETIME;

    -- Combinar fecha y hora
    SET v_fecha_hora = TIMESTAMP(p_fecha, p_hora);

    -- Validar que no sea en pasado (fecha/hora anteriores, mismo día u otro)
    IF v_fecha_hora < NOW() THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se puede agendar en fecha/hora anterior a la actual';
    END IF;

    -- Validar correo
    SET v_correo_valido = p_correo REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$';
    IF NOT v_correo_valido THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Correo inválido';
    END IF;

    -- Validar horario
    IF NOT (p_hora BETWEEN '09:30:00' AND '19:00:00' AND MINUTE(p_hora) IN (0,30)) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Hora inválida: 09:30 a 19:00 cada 30 min';
    END IF;

    -- Validar teléfono
    IF p_telefono NOT REGEXP '^[0-9]{10}$' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El teléfono debe contener exactamente 10 dígitos';
    END IF;

    -- Validar fecha/hora no ocupada
    SELECT COUNT(*) INTO v_exist FROM ent_entrega 
    WHERE fecha = p_fecha AND hora = p_hora;
    IF v_exist > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Fecha y hora ya ocupadas';
    END IF;

    -- Validar límite de citas por día (10 máximo)
    SELECT COUNT(*) INTO v_exist FROM ent_entrega 
    WHERE fecha = p_fecha;
    IF v_exist >= 10 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se pueden agendar más de 10 entregas en el mismo día';
    END IF;

    -- Convertir VIN a mayúsculas y validar unicidad
    SET p_vin = UPPER(p_vin);
    SELECT COUNT(*) INTO v_exist FROM ent_entrega WHERE vin = p_vin;
    IF v_exist > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'VIN ya registrado';
    END IF;

    -- Validar modelo (año actual o siguiente)
    IF p_modelo NOT IN (YEAR(CURDATE()), YEAR(CURDATE()) + 1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El modelo debe ser el año actual o el siguiente';
    END IF;

    -- Convertir nombres y apellidos a mayúsculas
    SET p_nombres   = UPPER(p_nombres);
    SET p_apellidos = UPPER(p_apellidos);

    -- Insertar registro
    INSERT INTO ent_entrega(
        nombres, apellidos, lada, telefono, correo,
        unidad, vin, modelo, sucursal, color, fecha, hora, bahia,
        usuario, estatus, recepcionista, numero_asistentes
    ) VALUES (
        p_nombres, p_apellidos, p_lada, p_telefono, p_correo,
        p_unidad, p_vin, p_modelo, p_sucursal, p_color, p_fecha, p_hora, p_bahia,
        p_usuario, 'PROGRAMADA', null, p_numero_asistentes
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ent_agregar_unidad` (IN `p_nombre` VARCHAR(50), IN `p_version` VARCHAR(50), IN `p_color` VARCHAR(25))   BEGIN
    DECLARE v_exist INT DEFAULT 0;

    -- Validar que no exista la misma combinación
    SELECT COUNT(*) INTO v_exist
    FROM ent_unidad
    WHERE nombre = p_nombre
      AND (version = p_version OR (version IS NULL AND p_version IS NULL))
      AND (color = p_color OR (color IS NULL AND p_color IS NULL));

    IF v_exist > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La unidad con ese nombre, versión y color ya existe';
    END IF;

    -- Insertar
    INSERT INTO ent_unidad(nombre, version, color)
    VALUES (p_nombre, p_version, p_color);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ent_horas_disponibles` (IN `p_fecha` DATE)   BEGIN
    -- Borrar tabla temporal si existe
    DROP TEMPORARY TABLE IF EXISTS tmp_slots;

    -- Crear tabla temporal para guardar los horarios
    CREATE TEMPORARY TABLE tmp_slots(
        h TIME PRIMARY KEY
    );

    -- Generar slots desde 09:30 hasta 19:00 cada 30 min
    SET @t := '09:30:00';
    WHILE @t <= '19:00:00' DO
        INSERT IGNORE INTO tmp_slots VALUES (@t);
        SET @t := ADDTIME(@t, '00:30:00');
    END WHILE;

    -- Seleccionar solo los horarios que no están ocupados
    SELECT h AS hora
    FROM tmp_slots
    WHERE h NOT IN (
        SELECT hora
        FROM ent_entrega
        WHERE fecha = p_fecha
    )
    ORDER BY h;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ent_login` (IN `p_usuario` VARCHAR(50), IN `p_contrasena` VARCHAR(255))   BEGIN
    SELECT id_asesor AS id, rol, primer_nombre, primer_apellido
    FROM ent_asesores
    WHERE usuario = p_usuario
      AND contrasena = SHA2(p_contrasena, 256)
      AND estatus = 'ACTIVO'
    LIMIT 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ent_modificar_asesor` (IN `p_id_asesor` INT, IN `p_primer_nombre` VARCHAR(50), IN `p_segundo_nombre` VARCHAR(50), IN `p_primer_apellido` VARCHAR(50), IN `p_segundo_apellido` VARCHAR(50), IN `p_sucursal` ENUM('PACHUCA','TULANCINGO'), IN `p_estatus` ENUM('ACTIVO','INACTIVO'), IN `p_rol` ENUM('ADMINISTRADOR','ASESOR DE VENTAS','RECEPCIONISTA'), IN `p_correo` VARCHAR(100), IN `p_telefono` VARCHAR(10), IN `p_contrasena` VARCHAR(255))   BEGIN
    DECLARE v_exist INT;
    DECLARE v_usuario VARCHAR(20);

    -- Validar que el asesor exista
    SELECT COUNT(*) INTO v_exist FROM ent_asesores WHERE id_asesor = p_id_asesor;
    IF v_exist = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El asesor no existe';
    END IF;

    -- Validar correo
    IF p_correo NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Correo inválido';
    END IF;

    -- Validar teléfono
    IF p_telefono NOT REGEXP '^[0-9]{10}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El teléfono debe contener exactamente 10 dígitos';
    END IF;

    -- Generar usuario automáticamente
    SET v_usuario = ent_generar_usuario(p_primer_nombre, p_segundo_nombre, p_primer_apellido, p_segundo_apellido, p_id_asesor);

    -- Validar que el usuario no se repita (excepto en el mismo registro)
    SELECT COUNT(*) INTO v_exist
    FROM ent_asesores
    WHERE usuario = v_usuario AND id_asesor <> p_id_asesor;
    IF v_exist > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El usuario generado ya está en uso por otro asesor';
    END IF;

 -- Normalizar entradas (Primera mayúscula, resto minúscula)
    SET p_primer_nombre   = CONCAT(UPPER(LEFT(TRIM(p_primer_nombre),1)), LOWER(SUBSTRING(TRIM(p_primer_nombre),2)));
    SET p_segundo_nombre  = IFNULL(NULLIF(TRIM(p_segundo_nombre),''), NULL);
    IF p_segundo_nombre IS NOT NULL THEN
        SET p_segundo_nombre = CONCAT(UPPER(LEFT(p_segundo_nombre,1)), LOWER(SUBSTRING(p_segundo_nombre,2)));
    END IF;

    SET p_primer_apellido = CONCAT(UPPER(LEFT(TRIM(p_primer_apellido),1)), LOWER(SUBSTRING(TRIM(p_primer_apellido),2)));
    SET p_segundo_apellido= IFNULL(NULLIF(TRIM(p_segundo_apellido),''), NULL);
    IF p_segundo_apellido IS NOT NULL THEN
        SET p_segundo_apellido = CONCAT(UPPER(LEFT(p_segundo_apellido,1)), LOWER(SUBSTRING(p_segundo_apellido,2)));
    END IF;

    -- Actualizar registro
    UPDATE ent_asesores
    SET 
     primer_nombre = p_primer_nombre,
     segundo_nombre = p_segundo_nombre,
     primer_apellido = p_primer_apellido,
     segundo_apellido = p_segundo_apellido,
        usuario         = v_usuario,
        sucursal        = p_sucursal,
        estatus         = p_estatus,
        rol             = p_rol,
        correo          = p_correo,
        telefono        = p_telefono,
        contrasena      = p_contrasena
    WHERE id_asesor = p_id_asesor;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ent_modificar_asesor_sin_pass` (IN `p_id_asesor` INT, IN `p_primer_nombre` VARCHAR(50), IN `p_segundo_nombre` VARCHAR(50), IN `p_primer_apellido` VARCHAR(50), IN `p_segundo_apellido` VARCHAR(50), IN `p_sucursal` ENUM('PACHUCA','TULANCINGO'), IN `p_estatus` ENUM('ACTIVO','INACTIVO'), IN `p_rol` ENUM('ADMINISTRADOR','ASESOR DE VENTAS','RECEPCIONISTA'), IN `p_correo` VARCHAR(100), IN `p_telefono` VARCHAR(10))   BEGIN
    DECLARE v_exist INT;
    DECLARE v_usuario VARCHAR(20);

    -- Validar que el asesor exista
    SELECT COUNT(*) INTO v_exist FROM ent_asesores WHERE id_asesor = p_id_asesor;
    IF v_exist = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El asesor no existe';
    END IF;

    -- Validar correo
    IF p_correo NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Correo inválido';
    END IF;

    -- Validar teléfono
    IF p_telefono NOT REGEXP '^[0-9]{10}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El teléfono debe contener exactamente 10 dígitos';
    END IF;

    -- Generar usuario automáticamente
    SET v_usuario = ent_generar_usuario(p_primer_nombre, p_segundo_nombre, p_primer_apellido, p_segundo_apellido, p_id_asesor);

    -- Validar que el usuario no se repita (excepto en el mismo registro)
    SELECT COUNT(*) INTO v_exist
    FROM ent_asesores
    WHERE usuario = v_usuario AND id_asesor <> p_id_asesor;
    IF v_exist > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El usuario generado ya está en uso por otro asesor';
    END IF;

    -- Actualizar registro (sin contraseña)
     -- Normalizar entradas (Primera mayúscula, resto minúscula)
    SET p_primer_nombre   = CONCAT(UPPER(LEFT(TRIM(p_primer_nombre),1)), LOWER(SUBSTRING(TRIM(p_primer_nombre),2)));
    SET p_segundo_nombre  = IFNULL(NULLIF(TRIM(p_segundo_nombre),''), NULL);
    IF p_segundo_nombre IS NOT NULL THEN
        SET p_segundo_nombre = CONCAT(UPPER(LEFT(p_segundo_nombre,1)), LOWER(SUBSTRING(p_segundo_nombre,2)));
    END IF;

    SET p_primer_apellido = CONCAT(UPPER(LEFT(TRIM(p_primer_apellido),1)), LOWER(SUBSTRING(TRIM(p_primer_apellido),2)));
    SET p_segundo_apellido= IFNULL(NULLIF(TRIM(p_segundo_apellido),''), NULL);
    IF p_segundo_apellido IS NOT NULL THEN
        SET p_segundo_apellido = CONCAT(UPPER(LEFT(p_segundo_apellido,1)), LOWER(SUBSTRING(p_segundo_apellido,2)));
    END IF;

    -- Actualizar registro
    UPDATE ent_asesores
    SET 
     primer_nombre = p_primer_nombre,
     segundo_nombre = p_segundo_nombre,
     primer_apellido = p_primer_apellido,
     segundo_apellido = p_segundo_apellido,
        usuario         = v_usuario,
        sucursal        = p_sucursal,
        estatus         = p_estatus,
        rol             = p_rol,
        correo          = p_correo,
        telefono        = p_telefono
    WHERE id_asesor = p_id_asesor;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ent_modificar_entrega` (IN `p_id_entrega` INT, IN `p_nombres` VARCHAR(100), IN `p_apellidos` VARCHAR(100), IN `p_lada` VARCHAR(5), IN `p_telefono` VARCHAR(15), IN `p_correo` VARCHAR(150), IN `p_unidad` VARCHAR(50), IN `p_vin` VARCHAR(50), IN `p_modelo` YEAR, IN `p_sucursal` VARCHAR(25), IN `p_color` VARCHAR(50), IN `p_fecha` DATE, IN `p_hora` TIME, IN `p_bahia` VARCHAR(20), IN `p_usuario` VARCHAR(50), IN `p_numero_asistentes` INT)   BEGIN
    DECLARE v_exist INT DEFAULT 0;
    DECLARE v_fecha_hora DATETIME;

    -- Combinar fecha y hora
    SET v_fecha_hora = TIMESTAMP(p_fecha, p_hora);

    -- Validar que no sea en pasado
    IF v_fecha_hora < NOW() THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se puede modificar a una fecha/hora anterior a la actual';
    END IF;

    -- Validar correo
    IF p_correo NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Correo inválido';
    END IF;

    -- Validar horario
    IF NOT (p_hora BETWEEN '09:30:00' AND '19:00:00' AND MINUTE(p_hora) IN (0,30)) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Hora inválida: 09:30 a 19:00 cada 30 min';
    END IF;

    -- Validar teléfono
    IF p_telefono NOT REGEXP '^[0-9]{10}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El teléfono debe contener exactamente 10 dígitos';
    END IF;

    -- Validar que la nueva fecha/hora no esté ocupada por otra entrega
    SELECT COUNT(*) INTO v_exist 
    FROM ent_entrega 
    WHERE fecha = p_fecha AND hora = p_hora AND id_entrega <> p_id_entrega;
    IF v_exist > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Fecha y hora ya ocupadas';
    END IF;

    -- Validar límite de citas por día (10 máximo, excluyendo esta misma)
    SELECT COUNT(*) INTO v_exist 
    FROM ent_entrega 
    WHERE fecha = p_fecha AND id_entrega <> p_id_entrega;
    IF v_exist >= 10 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No se pueden agendar más de 10 entregas en el mismo día';
    END IF;

    -- Validar VIN en mayúsculas y unicidad (excluyendo este mismo registro)
    SET p_vin = UPPER(p_vin);
    SELECT COUNT(*) INTO v_exist FROM ent_entrega 
    WHERE vin = p_vin AND id_entrega <> p_id_entrega;
    IF v_exist > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'VIN ya registrado';
    END IF;

    -- Validar modelo (año actual o siguiente)
    IF p_modelo NOT IN (YEAR(CURDATE()), YEAR(CURDATE()) + 1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El modelo debe ser el año actual o el siguiente';
    END IF;

    -- Validar número de asistentes (mínimo 1, máximo 10 por ejemplo)
    IF p_numero_asistentes < 1 OR p_numero_asistentes > 10 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El número de asistentes debe estar entre 1 y 10';
    END IF;

    -- Convertir nombres y apellidos a mayúsculas
    SET p_nombres   = UPPER(p_nombres);
    SET p_apellidos = UPPER(p_apellidos);

    -- Actualizar entrega
    UPDATE ent_entrega
    SET nombres = p_nombres,
        apellidos = p_apellidos,
        lada = p_lada,
        telefono = p_telefono,
        correo = p_correo,
        unidad = p_unidad,
        vin = p_vin,
        modelo = p_modelo,
        sucursal = p_sucursal,
        color = p_color,
        fecha = p_fecha,
        hora = p_hora,
        bahia = p_bahia,
        usuario = p_usuario,
        numero_asistentes = p_numero_asistentes
    WHERE id_entrega = p_id_entrega;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ent_modificar_estatus` (IN `p_id_entrega` INT, IN `p_estatus` ENUM('PROGRAMADA','NO ASISTE','EN ESPERA','ENTREGANDO','ENTREGADA'))   BEGIN
    DECLARE v_exist INT;

    -- Validar que la entrega exista
    SELECT COUNT(*) INTO v_exist 
    FROM ent_entrega 
    WHERE id_entrega = p_id_entrega;

    IF v_exist = 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'La entrega no existe';
    END IF;

    -- Actualizar solo el estatus
    UPDATE ent_entrega
    SET estatus = p_estatus
    WHERE id_entrega = p_id_entrega;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ent_modificar_recepcionista` (IN `p_id_entrega` INT, IN `p_recepcionista` VARCHAR(10))   BEGIN
    DECLARE v_exist INT;

    -- Validar que la entrega exista
    SELECT COUNT(*) INTO v_exist FROM ent_entrega WHERE id_entrega = p_id_entrega;
    IF v_exist = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La entrega no existe';
    END IF;

    -- Actualizar solo el recepcionista
    UPDATE ent_entrega
    SET recepcionista = p_recepcionista
    WHERE id_entrega = p_id_entrega;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ent_modificar_unidad` (IN `p_id_unidad` INT, IN `p_nombre` VARCHAR(50), IN `p_version` VARCHAR(50), IN `p_color` VARCHAR(25))   BEGIN
    DECLARE v_exist INT DEFAULT 0;

    -- Validar que no exista la misma combinación en otra unidad
    SELECT COUNT(*) INTO v_exist
    FROM ent_unidad
    WHERE nombre = p_nombre
      AND (version = p_version OR (version IS NULL AND p_version IS NULL))
      AND (color = p_color OR (color IS NULL AND p_color IS NULL))
      AND id_unidad <> p_id_unidad;

    IF v_exist > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Otra unidad con ese nombre, versión y color ya existe';
    END IF;

    -- Actualizar
    UPDATE ent_unidad
    SET nombre = p_nombre,
        version = p_version,
        color = p_color
    WHERE id_unidad = p_id_unidad;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ent_obtener_entregas` (IN `p_fecha` DATE)   BEGIN
    SELECT 
        e.*,
        CONCAT(
            SUBSTRING_INDEX(a.primer_nombre, ' ', 1), ' ',
SUBSTRING_INDEX(a.segundo_nombre, ' ', 1), ' ',            SUBSTRING_INDEX(a.primer_apellido, ' ', 1), ' ',SUBSTRING_INDEX(a.segundo_apellido, ' ', 1)
        ) AS nombre_asesor
    FROM ent_entrega e
    LEFT JOIN ent_asesores a ON e.usuario = a.usuario
    WHERE e.fecha = p_fecha
    ORDER BY 
        CASE e.estatus
            WHEN 'EN ESPERA' THEN 1
            WHEN 'PROGRAMADA' THEN 2
            WHEN 'ENTREGADA' THEN 3
            ELSE 4
        END,
        e.hora ASC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ent_obtener_nombre` (IN `p_id` INT)   BEGIN
    SELECT primer_nombre, primer_apellido
    FROM ent_asesores
    WHERE id_asesor = p_id;
END$$

--
-- Funciones
--
CREATE DEFINER=`root`@`localhost` FUNCTION `ent_generar_usuario` (`p_primer_nombre` VARCHAR(50), `p_segundo_nombre` VARCHAR(50), `p_primer_apellido` VARCHAR(50), `p_segundo_apellido` VARCHAR(50), `p_id` INT) RETURNS VARCHAR(20) CHARSET utf8mb4 COLLATE utf8mb4_general_ci DETERMINISTIC BEGIN
    DECLARE ini VARCHAR(20);

    SET ini = CONCAT(
        LEFT(TRIM(SUBSTRING_INDEX(p_primer_nombre, ' ', 1)), 1),
        LEFT(TRIM(SUBSTRING_INDEX(p_segundo_nombre, ' ', 1)), 1),
	LEFT(TRIM(SUBSTRING_INDEX(p_primer_apellido, ' ', 1)), 1),
        LEFT(TRIM(SUBSTRING_INDEX(p_segundo_apellido, ' ', 1)), 1),
        p_id
    );
    RETURN UPPER(ini);
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `INITCAP` (`str` VARCHAR(255)) RETURNS VARCHAR(255) CHARSET utf8mb4 COLLATE utf8mb4_general_ci DETERMINISTIC BEGIN
  DECLARE i INT DEFAULT 1;
  DECLARE len INT;
  DECLARE result VARCHAR(255) DEFAULT '';
  DECLARE ch CHAR(1);
  DECLARE prev CHAR(1) DEFAULT ' ';

  IF str IS NULL THEN RETURN NULL; END IF;

  -- Si tienes MySQL 8, puedes descomentar para colapsar espacios múltiples:
  -- SET str = REGEXP_REPLACE(TRIM(str), '[[:space:]]+', ' ');
  SET str = TRIM(str);
  SET len = CHAR_LENGTH(str);

  WHILE i <= len DO
    SET ch = SUBSTRING(str, i, 1);
    -- Nueva palabra si inicio, espacio, guión o apóstrofo
    IF i = 1 OR prev IN (' ', '-', '''') THEN
      SET result = CONCAT(result, UPPER(ch));
    ELSE
      SET result = CONCAT(result, LOWER(ch));
    END IF;
    SET prev = ch;
    SET i = i + 1;
  END WHILE;

  RETURN result;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `ProperCase` (`str` VARCHAR(255)) RETURNS VARCHAR(255) CHARSET utf8mb4 COLLATE utf8mb4_general_ci DETERMINISTIC BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE len INT;
    DECLARE resultado VARCHAR(255) DEFAULT '';
    DECLARE caracter CHAR(1);
    DECLARE poner_mayus BOOLEAN DEFAULT TRUE;

    SET len = CHAR_LENGTH(str);

    WHILE i <= len DO
        SET caracter = SUBSTRING(str, i, 1);

        -- Si es espacio, se mantiene y la siguiente letra será mayúscula
        IF caracter = ' ' THEN
            SET resultado = CONCAT(resultado, caracter);
            SET poner_mayus = TRUE;
        ELSE
            -- Si es el inicio de una palabra
            IF poner_mayus THEN
                SET resultado = CONCAT(resultado, UPPER(caracter));
                SET poner_mayus = FALSE;
            ELSE
                SET resultado = CONCAT(resultado, LOWER(caracter));
            END IF;
        END IF;

        SET i = i + 1;
    END WHILE;
    set i=1;
    set len = CHAR_LENGTH(resultado);
    WHILE i <= len DO
       	SET caracter = SUBSTRING(resultado, i, 1);
	IF caracter = UPPER(caracter) Then
		SET resultado = CONCAT(resultado, " ");
	ELSE
		SET resultado = CONCAT(resultado, caracter);

	END IF;
	SET i = i + 1;

    END WHILE;

    RETURN resultado;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ent_asesores`
--

CREATE TABLE `ent_asesores` (
  `id_asesor` int(11) NOT NULL,
  `primer_nombre` varchar(50) NOT NULL,
  `segundo_nombre` varchar(50) DEFAULT NULL,
  `primer_apellido` varchar(50) NOT NULL,
  `segundo_apellido` varchar(50) NOT NULL,
  `usuario` varchar(20) DEFAULT NULL,
  `sucursal` enum('PACHUCA','TULANCINGO') NOT NULL,
  `estatus` enum('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  `rol` enum('ADMINISTRADOR','ASESOR DE VENTAS','RECEPCIONISTA') NOT NULL DEFAULT 'ASESOR DE VENTAS',
  `correo` varchar(100) NOT NULL,
  `telefono` varchar(10) NOT NULL,
  `contrasena` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ent_asesores`
--

INSERT INTO `ent_asesores` (`id_asesor`, `primer_nombre`, `segundo_nombre`, `primer_apellido`, `segundo_apellido`, `usuario`, `sucursal`, `estatus`, `rol`, `correo`, `telefono`, `contrasena`) VALUES
(1, 'Administrador', 'Admin', 'Toyota', 'Pachuca', 'AATP1', 'PACHUCA', 'ACTIVO', 'ADMINISTRADOR', 'admin1@gmail.com', '7712587896', '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918'),
(2, 'Asesor', 'Prueba', 'Toyota', 'Pachuca', 'APTP2', 'PACHUCA', 'ACTIVO', 'ASESOR DE VENTAS', 'asesor1@gmail.com', '7712587896', '423d8c8ab13df8bae75f29ed59d367b598d244d056bd8175d2c91ca24642aa16'),
(3, 'ASESOR', 'PRUEBA', 'TOYOTA', 'TULANCINGO', 'APTT3', 'PACHUCA', 'ACTIVO', 'ASESOR DE VENTAS', 'impala@gmal.com', '7789512300', 'asesor2'),
(4, 'Recepcionista', 'Recepcion', 'Toyota', 'Pachuca', 'RRTP4', 'PACHUCA', 'ACTIVO', 'RECEPCIONISTA', 'recep@gmail.com', '7727587896', 'abbce88bd1788accc2ec66a738003efbe2342dea1c3e60c1459583289cbb3fed');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ent_entrega`
--

CREATE TABLE `ent_entrega` (
  `id_entrega` int(11) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `lada` varchar(5) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `unidad` varchar(50) NOT NULL,
  `vin` varchar(50) NOT NULL,
  `modelo` year(4) NOT NULL,
  `color` varchar(50) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `sucursal` enum('PACHUCA','TULANCINGO') NOT NULL,
  `bahia` varchar(20) DEFAULT NULL,
  `usuario` varchar(50) NOT NULL,
  `estatus` enum('PROGRAMADA','NO ASISTE','EN ESPERA','ENTREGANDO','ENTREGADA') NOT NULL DEFAULT 'PROGRAMADA',
  `recepcionista` varchar(10) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `numero_asistentes` int(11) NOT NULL DEFAULT 1
) ;

--
-- Volcado de datos para la tabla `ent_entrega`
--

INSERT INTO `ent_entrega` (`id_entrega`, `nombres`, `apellidos`, `lada`, `telefono`, `correo`, `unidad`, `vin`, `modelo`, `color`, `fecha`, `hora`, `sucursal`, `bahia`, `usuario`, `estatus`, `recepcionista`, `creado_en`, `numero_asistentes`) VALUES
(1, 'MARCO ANTONIO', 'DIAZ FLORES', '52', '7715896204', 'marco@gmail.com', 'Avanza', 'T1594894', '2025', 'Rojo', '2025-09-02', '17:30:00', 'PACHUCA', '1', 'APTP2', 'PROGRAMADA', 'PENDIENTE', '2025-09-01 05:09:54', 2),
(2, 'LUCIA MARIANA', 'ESCAMILLA SANCHEZ', '52', '7711596505', 'marilu@gmail.com', 'hilux', 'S1594894', '2025', 'Negro', '2025-09-01', '14:00:00', 'PACHUCA', '1', 'APTP2', 'PROGRAMADA', 'PENDIENTE', '2025-09-01 05:09:54', 1),
(3, 'MARCO ANTONIO', 'DIAZ FLORES', '52', '7715896204', 'marco@gmail.com', '', 'T1594844', '2025', 'rojo', '2025-09-01', '13:30:00', 'PACHUCA', '1', 'APTP2', 'PROGRAMADA', 'PENDIENTE', '2025-09-01 06:47:04', 2),
(4, 'JUAN', 'VARGAS CRUZ', '+52', '7755896215', 'juanp@gmail.com', 'Corolla', 'T1594891', '2025', 'Negro', '2025-09-02', '18:00:00', 'PACHUCA', '', 'AG1', 'PROGRAMADA', NULL, '2025-09-01 10:11:05', 0),
(5, 'MARIANA', 'DOMINGUEZ HERRERA', '+52', '7755885215', 'marilu@gmail.com', 'Prius', 'T1594890', '2025', 'Azul', '2025-09-02', '16:30:00', 'PACHUCA', '', 'AG1', 'PROGRAMADA', NULL, '2025-09-01 10:12:14', 0),
(6, 'ANA MARIA', 'ROJAS CONTRERAS', '+52', '7715896248', 'ana@gmail.com', 'Tacoma', 'T9589145', '2025', 'Verde', '2025-09-01', '10:00:00', 'PACHUCA', '', 'AG1', 'PROGRAMADA', NULL, '2025-09-01 15:10:49', 0),
(7, 'MARIA', 'FUENTES', '+52', '7751895420', 'impala@gmal.com', 'Corolla', 'DEDSE5195', '2025', 'Rojo', '2025-09-01', '15:00:00', 'PACHUCA', '', 'AATP1', 'PROGRAMADA', NULL, '2025-09-01 19:44:09', 4),
(8, 'ANTONIO', 'GUZMAN', '+52', '7788995544', 'juanp@gmail.com', 'Supra', 'E7845962S', '2025', 'Rojo', '2025-09-01', '14:30:00', 'PACHUCA', '', 'AATP1', 'PROGRAMADA', NULL, '2025-09-01 19:46:14', 0),
(9, 'OLIVIA', 'JUAREZ', '+52', '7713391746', 'impala@gmal.com', 'Supra', 'S8168966Y', '2025', 'Negro', '2025-09-01', '15:30:00', 'PACHUCA', '', 'AATP1', 'PROGRAMADA', NULL, '2025-09-01 19:48:17', 0),
(10, 'HEIDI YAMILETH', 'CILIS MOXTHE', '+52', '7713391746', 'juanp@gmail.com', 'Sequoia', 'DEDE5195S', '2025', 'Azul', '2025-09-01', '16:00:00', 'PACHUCA', '', 'AATP1', 'PROGRAMADA', NULL, '2025-09-01 19:50:06', 0),
(11, 'HEIDI YAMILETH', 'CILIS MOXTHE', '+52', '7713391746', 'juanp@gmail.com', 'Supra', 'E78459628', '2025', 'Rojo', '2025-09-01', '16:30:00', 'PACHUCA', '', 'AATP1', 'PROGRAMADA', NULL, '2025-09-01 19:51:07', 0),
(12, 'HEIDI YAMILETH', 'CILIS MOXTHE', '+52', '7713391746', 'impala@gmal.com', 'Tundra', 'DEDE5195F', '2025', 'Negro', '2025-09-01', '17:00:00', 'PACHUCA', '', 'AATP1', 'PROGRAMADA', NULL, '2025-09-01 19:53:52', 3),
(13, 'HEIDI YAMILETH', 'CILIS MOXTHE', '+52', '7713391746', 'juanp@gmail.com', 'Avanza', 'E7845962L', '2025', 'nm', '2025-09-01', '17:30:00', 'PACHUCA', '', 'AATP1', 'PROGRAMADA', NULL, '2025-09-01 19:55:17', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ent_unidad`
--

CREATE TABLE `ent_unidad` (
  `id_unidad` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `version` varchar(50) DEFAULT NULL,
  `color` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ent_unidad`
--

INSERT INTO `ent_unidad` (`id_unidad`, `nombre`, `version`, `color`) VALUES
(1, 'Tacoma', NULL, NULL),
(2, 'Hilux', NULL, NULL),
(3, 'Corolla', NULL, NULL),
(4, 'Prius', NULL, NULL),
(5, '4Runner', NULL, NULL),
(6, 'Supra', NULL, NULL),
(7, 'Avanza', NULL, NULL),
(8, 'Tundra', NULL, NULL),
(9, 'Sequoia', NULL, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `ent_asesores`
--
ALTER TABLE `ent_asesores`
  ADD PRIMARY KEY (`id_asesor`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- Indices de la tabla `ent_entrega`
--
ALTER TABLE `ent_entrega`
  ADD PRIMARY KEY (`id_entrega`),
  ADD UNIQUE KEY `uq_vin` (`vin`),
  ADD UNIQUE KEY `uq_fecha_hora` (`fecha`,`hora`),
  ADD KEY `idx_fecha` (`fecha`);

--
-- Indices de la tabla `ent_unidad`
--
ALTER TABLE `ent_unidad`
  ADD PRIMARY KEY (`id_unidad`),
  ADD UNIQUE KEY `uq_nombre_version` (`nombre`,`version`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `ent_asesores`
--
ALTER TABLE `ent_asesores`
  MODIFY `id_asesor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `ent_entrega`
--
ALTER TABLE `ent_entrega`
  MODIFY `id_entrega` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ent_unidad`
--
ALTER TABLE `ent_unidad`
  MODIFY `id_unidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
