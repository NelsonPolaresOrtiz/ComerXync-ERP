-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3307
-- Tiempo de generación: 14-10-2026 a las 14:40:46
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
-- Base de datos: `comerxync_bd`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `articulos`
--

CREATE TABLE `articulos` (
  `id_articulo` int(11) NOT NULL,
  `sku_vin` varchar(50) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `categoria` enum('Repuesto','Maquinaria','Vehiculo','Accesorio') NOT NULL,
  `imagen_url` varchar(500) DEFAULT NULL,
  `id_proveedor` int(11) DEFAULT NULL,
  `precio_compra` decimal(10,2) NOT NULL,
  `precio_venta` decimal(10,2) NOT NULL,
  `stock_minimo` int(11) DEFAULT 5
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `articulos`
--

INSERT INTO `articulos` (`id_articulo`, `sku_vin`, `nombre`, `categoria`, `imagen_url`, `id_proveedor`, `precio_compra`, `precio_venta`, `stock_minimo`) VALUES
(1, 'BAT-BOS-75A', 'Batería Bosch S4 75Ah 12V', 'Repuesto', 'https://placehold.co/400x400/f8fafc/1e293b?text=Bateria\nBosch', 1, 450.00, 680.00, 10),
(2, 'FIL-ACE-TOY', 'Filtro de Aceite Original Toyota', 'Repuesto', 'https://placehold.co/400x400/f8fafc/1e293b?text=Filtro\nDe+Aceite', 1, 45.00, 85.00, 20),
(3, 'BUJ-NGK-IR', 'Bujía NGK Iridium IX (Juego x4)', 'Repuesto', 'https://placehold.co/400x400/f8fafc/1e293b?text=Bujias\nNGK', 1, 120.00, 210.00, 15),
(4, 'LLA-MIC-205', 'Llanta Michelin 205/55 R16 Primacy', 'Accesorio', 'https://placehold.co/400x400/f8fafc/1e293b?text=Llanta\nMichelin', 1, 550.00, 820.00, 12),
(5, 'AMOR-KYB-DEL', 'Amortiguador Delantero KYB Excel-G', 'Repuesto', 'https://placehold.co/400x400/f8fafc/1e293b?text=Amortiguador\nKYB', 1, 320.00, 480.00, 8),
(6, 'PAS-FRE-BOS', 'Pastillas de Freno Cerámicas Bosch', 'Repuesto', 'https://placehold.co/400x400/f8fafc/1e293b?text=Pastillas\nDe+Freno', 1, 180.00, 290.00, 15),
(7, 'ACE-MOT-5W30', 'Aceite de Motor Castrol EDGE 5W-30 (5L)', 'Repuesto', 'https://placehold.co/400x400/f8fafc/1e293b?text=Aceite\nCastrol', 1, 250.00, 380.00, 30),
(8, 'FAR-LED-H4', 'Focos LED H4 Philips Ultinon (Par)', 'Accesorio', 'https://placehold.co/400x400/f8fafc/1e293b?text=Focos\nLED+H4', 1, 190.00, 310.00, 10),
(9, 'RAD-TOY-HIL', 'Radiador de Aluminio Toyota Hilux', 'Repuesto', 'https://placehold.co/400x400/f8fafc/1e293b?text=Radiador\nHilux', 1, 850.00, 1250.00, 4),
(10, 'ALT-DEN-12V', 'Alternador Denso 12V 100A', 'Repuesto', 'https://placehold.co/400x400/f8fafc/1e293b?text=Alternador\nDenso', 1, 950.00, 1400.00, 3),
(11, 'EXC-CAT-320', 'Excavadora Hidráulica CAT 320', 'Maquinaria', 'https://placehold.co/400x400/f8fafc/1e293b?text=Excavadora\nCAT+320', 1, 850000.00, 1150000.00, 1),
(12, 'RET-JCB-3CX', 'Retroexcavadora JCB 3CX', 'Maquinaria', 'https://placehold.co/400x400/f8fafc/1e293b?text=Retroexcavadora\nJCB', 1, 650000.00, 890000.00, 1),
(13, 'CAM-HIN-300', 'Camión Hino Serie 300 (Chasis Cabina)', 'Vehiculo', 'https://placehold.co/400x400/f8fafc/1e293b?text=Camion\nHino+300', 1, 210000.00, 285000.00, 2),
(14, 'MOT-CUM-ISX', 'Motor Cummins ISX15 Completo', 'Maquinaria', 'https://placehold.co/400x400/f8fafc/1e293b?text=Motor\nCummins', 1, 120000.00, 165000.00, 1),
(15, 'KIT-EMB-LUK', 'Kit de Embrague LuK RepSet', 'Repuesto', 'https://placehold.co/400x400/f8fafc/1e293b?text=Kit+Embrague\nLuK', 1, 750.00, 1100.00, 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedidos`
--

CREATE TABLE `detalle_pedidos` (
  `id_detalle` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_articulo` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `kardex`
--

CREATE TABLE `kardex` (
  `id_movimiento` int(11) NOT NULL,
  `id_articulo` int(11) NOT NULL,
  `tipo_movimiento` enum('ENTRADA','SALIDA') NOT NULL,
  `cantidad` int(11) NOT NULL,
  `saldo_actual` int(11) NOT NULL COMMENT 'Stock calculado en ese momento',
  `referencia` varchar(100) DEFAULT NULL COMMENT 'Nro de Factura de Venta o Póliza de Ingreso',
  `id_usuario` int(11) NOT NULL COMMENT 'Quién hizo el movimiento',
  `fecha_movimiento` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `kardex`
--

INSERT INTO `kardex` (`id_movimiento`, `id_articulo`, `tipo_movimiento`, `cantidad`, `saldo_actual`, `referencia`, `id_usuario`, `fecha_movimiento`) VALUES
(1, 1, 'ENTRADA', 15, 15, 'Inv. Inicial', 1, '2026-10-14 11:11:37'),
(2, 2, 'ENTRADA', 45, 45, 'Inv. Inicial', 1, '2026-10-14 11:11:37'),
(3, 3, 'ENTRADA', 22, 22, 'Inv. Inicial', 1, '2026-10-14 11:11:37'),
(4, 4, 'ENTRADA', 16, 16, 'Inv. Inicial', 1, '2026-10-14 11:11:37'),
(5, 5, 'ENTRADA', 6, 6, 'Inv. Inicial', 1, '2026-10-14 11:11:37'),
(6, 7, 'ENTRADA', 50, 50, 'Inv. Inicial', 1, '2026-10-14 11:11:37'),
(7, 8, 'ENTRADA', 12, 12, 'Inv. Inicial', 1, '2026-10-14 11:11:37'),
(8, 13, 'ENTRADA', 3, 3, 'Inv. Inicial', 1, '2026-10-14 11:11:37'),
(9, 15, 'ENTRADA', 8, 8, 'Inv. Inicial', 1, '2026-10-14 11:11:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id_pedido` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_pedido` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('Pendiente','Aprobado','En Ruta','Entregado') DEFAULT 'Pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id_proveedor` int(11) NOT NULL,
  `razon_social` varchar(150) NOT NULL,
  `pais` varchar(50) NOT NULL,
  `contacto` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `tiempo_entrega_dias` int(11) DEFAULT 0,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id_proveedor`, `razon_social`, `pais`, `contacto`, `telefono`, `tiempo_entrega_dias`, `fecha_registro`) VALUES
(1, 'AutoParts Global', 'Japón', 'Kenji Sato', '+81 900 123', 15, '2026-10-14 11:06:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('Administrador','Vendedor','Almacenero','Cliente_B2B') NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `email`, `password`, `rol`, `fecha_creacion`) VALUES
(1, 'Taller Mecánico Los Andes', 'taller@cliente.com', '123456', 'Cliente_B2B', '2026-10-14 10:43:36'),
(2, 'Líder Técnico', 'admin@comerxync.com', '123456', 'Administrador', '2026-10-14 10:55:49'),
(3, 'Vendedor Tienda', 'vendedor@comerxync.com', '123456', 'Vendedor', '2026-10-14 10:55:49'),
(4, 'Almacén Central', 'almacen@comerxync.com', '123456', 'Almacenero', '2026-10-14 10:55:49');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `articulos`
--
ALTER TABLE `articulos`
  ADD PRIMARY KEY (`id_articulo`),
  ADD UNIQUE KEY `sku_vin` (`sku_vin`),
  ADD KEY `id_proveedor` (`id_proveedor`);

--
-- Indices de la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_pedido` (`id_pedido`),
  ADD KEY `id_articulo` (`id_articulo`);

--
-- Indices de la tabla `kardex`
--
ALTER TABLE `kardex`
  ADD PRIMARY KEY (`id_movimiento`),
  ADD KEY `fk_kardex_articulo` (`id_articulo`),
  ADD KEY `fk_kardex_usuario` (`id_usuario`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id_proveedor`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `articulos`
--
ALTER TABLE `articulos`
  MODIFY `id_articulo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `kardex`
--
ALTER TABLE `kardex`
  MODIFY `id_movimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id_proveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `articulos`
--
ALTER TABLE `articulos`
  ADD CONSTRAINT `articulos_ibfk_1` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`) ON DELETE SET NULL;

--
-- Filtros para la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  ADD CONSTRAINT `fk_detalle_articulo` FOREIGN KEY (`id_articulo`) REFERENCES `articulos` (`id_articulo`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_detalle_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`) ON DELETE CASCADE;

--
-- Filtros para la tabla `kardex`
--
ALTER TABLE `kardex`
  ADD CONSTRAINT `fk_kardex_articulo` FOREIGN KEY (`id_articulo`) REFERENCES `articulos` (`id_articulo`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_kardex_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `fk_pedidos_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
