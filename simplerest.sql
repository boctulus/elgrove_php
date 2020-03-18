-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-01-2020 a las 02:20:41
-- Versión del servidor: 10.4.8-MariaDB
-- Versión de PHP: 7.3.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `simplerest`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `emails`
--

CREATE TABLE `emails` (
  `id` int(11) NOT NULL,
  `text` varchar(60) COLLATE utf16_spanish_ci NOT NULL,
  `confirmed` tinyint(4) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `folders`
--

CREATE TABLE `folders` (
  `id` int(11) NOT NULL,
  `tb` varchar(40) NOT NULL,
  `name` varchar(40) NOT NULL,
  `belongs_to` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `folders`
--

INSERT INTO `folders` (`id`, `tb`, `name`, `belongs_to`) VALUES
(9, 'products', 'comparto', 4),
(10, 'products', 'electricos', 125),
(11, 'products', 'jardineria', 125),
(5, 'products', 'lista', 87),
(4, 'products', 'lista publica', 90),
(8, 'products', 'lista10', 90),
(6, 'products', 'lista2', 90),
(1, 'products', 'mylist', 1),
(2, 'products', 'otralista', 72),
(3, 'products', 'super', 89);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `foo`
--

CREATE TABLE `foo` (
  `id` int(11) NOT NULL,
  `bar` varchar(45) COLLATE utf16_spanish_ci NOT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_spanish_ci;

--
-- Volcado de datos para la tabla `foo`
--

INSERT INTO `foo` (`id`, `bar`, `deleted_at`) VALUES
(2, 'EEE', NULL),
(5, '200', NULL),
(6, '200', NULL),
(7, '200', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `group_permissions`
--

CREATE TABLE `group_permissions` (
  `id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `belongs_to` int(11) NOT NULL,
  `member` int(11) NOT NULL,
  `r` tinyint(4) NOT NULL,
  `w` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `group_permissions`
--

INSERT INTO `group_permissions` (`id`, `folder_id`, `belongs_to`, `member`, `r`, `w`) VALUES
(1, 1, 1, 4, 1, 1),
(2, 2, 72, 79, 1, 1),
(3, 4, 90, 87, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `from_email` varchar(320) NOT NULL,
  `from_name` varchar(60) NOT NULL,
  `to_email` varchar(320) NOT NULL,
  `to_name` varchar(40) DEFAULT NULL,
  `subject` varchar(40) NOT NULL,
  `body` text NOT NULL,
  `created_at` datetime NOT NULL,
  `sent_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `messages`
--

INSERT INTO `messages` (`id`, `from_email`, `from_name`, `to_email`, `to_name`, `subject`, `body`, `created_at`, `sent_at`) VALUES
(97, 'no_responder@simplerest.mapapulque.ro', 'No responder', 'nano2@g.c', '', 'Cambio de contraseña', 'Para cambiar la contraseña siga el enlace:<br/><a href=\'http://simplerest.lan/login/change_pass/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCIsImlhdCI6MTU3NzQ1NzgxMCwiZXhwIjoxNTc4MDYyNjEwLCJlbWFpbCI6Im5hbm8yQGcuYyJ9.LpHFXBpw5cxwoi_jdTaIBAIzcXXdWIAEOJikBRvUMFI/1578062610\'>http://simplerest.lan/login/change_pass/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCIsImlhdCI6MTU3NzQ1NzgxMCwiZXhwIjoxNTc4MDYyNjEwLCJlbWFpbCI6Im5hbm8yQGcuYyJ9.LpHFXBpw5cxwoi_jdTaIBAIzcXXdWIAEOJikBRvUMFI/1578062610</a>', '2019-12-27 11:43:30', NULL),
(98, 'no_responder@simplerest.mapapulque.ro', 'No responder', 'nano3@g.c', '', 'Cambio de contraseña', 'Para cambiar la contraseña siga el enlace:<br/><a href=\'http://simplerest.lan/login/change_pass/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCIsImlhdCI6MTU3NzQ2ODA5MCwiZXhwIjoxNTc4MDcyODkwLCJlbWFpbCI6Im5hbm8zQGcuYyJ9.NHFG4GzzyNQhlhBxTK4n4ADF0DUlIMKpemDuOfOR1lA/1578072890\'>http://simplerest.lan/login/change_pass/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCIsImlhdCI6MTU3NzQ2ODA5MCwiZXhwIjoxNTc4MDcyODkwLCJlbWFpbCI6Im5hbm8zQGcuYyJ9.NHFG4GzzyNQhlhBxTK4n4ADF0DUlIMKpemDuOfOR1lA/1578072890</a>', '2019-12-27 14:34:50', NULL),
(99, 'no_responder@simplerest.mapapulque.ro', 'No responder', 'putazo@g.c', ' ', 'Email confirmation', 'Please confirm your account by following the link bellow:<br/><a href=\'http://simplerest.lan/login/confirm_email/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCIsImlhdCI6MTU3OTEzNjIwNSwiZXhwIjoxNTc5NzQxMDA1LCJlbWFpbCI6InB1dGF6b0BnLmMifQ.R6r08tcLBgR2i4gw5kp04wSYlgnSQQXM28mv0k0zrG4/1579741005\'>http://simplerest.lan/login/confirm_email/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCIsImlhdCI6MTU3OTEzNjIwNSwiZXhwIjoxNTc5NzQxMDA1LCJlbWFpbCI6InB1dGF6b0BnLmMifQ.R6r08tcLBgR2i4gw5kp04wSYlgnSQQXM28mv0k0zrG4/1579741005</a>', '2020-01-15 21:56:45', NULL),
(100, 'no_responder@simplerest.mapapulque.ro', 'No responder', 'simplon@g.c', ' ', 'Email confirmation', 'Please confirm your account by following the link bellow:<br/><a href=\'http://simplerest.lan/login/confirm_email/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCIsImlhdCI6MTU3OTEzNzcyNSwiZXhwIjoxNTc5NzQyNTI1LCJlbWFpbCI6InNpbXBsb25AZy5jIn0.fBmO5BRuhvASi2S6QvOE_E6fI8KP5xO3r0drRwXCUAM/1579742525\'>http://simplerest.lan/login/confirm_email/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCIsImlhdCI6MTU3OTEzNzcyNSwiZXhwIjoxNTc5NzQyNTI1LCJlbWFpbCI6InNpbXBsb25AZy5jIn0.fBmO5BRuhvASi2S6QvOE_E6fI8KP5xO3r0drRwXCUAM/1579742525</a>', '2020-01-15 22:22:05', NULL),
(101, 'no_responder@simplerest.mapapulque.ro', 'No responder', 'jjjjj@g.c', ' ', 'Email confirmation', 'Please confirm your account by following the link bellow:<br/><a href=\'http://simplerest.lan/login/confirm_email/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCIsImlhdCI6MTU3OTM4MTYwNiwiZXhwIjoxNTc5OTg2NDA2LCJpcCI6IjEyNy4wLjAuMSJ9.ngOefJiwZl20v57cIJSZokOZplypNusooa5ZbOk4UrM/1579986406\'>http://simplerest.lan/login/confirm_email/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCIsImlhdCI6MTU3OTM4MTYwNiwiZXhwIjoxNTc5OTg2NDA2LCJpcCI6IjEyNy4wLjAuMSJ9.ngOefJiwZl20v57cIJSZokOZplypNusooa5ZbOk4UrM/1579986406</a>', '2020-01-18 18:06:46', NULL),
(102, 'no_responder@simplerest.mapapulque.ro', 'No responder', 'jj@g.c', ' ', 'Email confirmation', 'Please confirm your account by following the link bellow:<br/><a href=\'http://simplerest.lan/login/confirm_email/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCIsImlhdCI6MTU3OTM4MTg0NSwiZXhwIjoxNTc5OTg2NjQ1LCJpcCI6IjEyNy4wLjAuMSJ9.2yZxZpL2gL5Ib7-Gf4hhEd_FhtmKC3zBRk6wpcHUtLw/1579986645\'>http://simplerest.lan/login/confirm_email/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCIsImlhdCI6MTU3OTM4MTg0NSwiZXhwIjoxNTc5OTg2NjQ1LCJpcCI6IjEyNy4wLjAuMSJ9.2yZxZpL2gL5Ib7-Gf4hhEd_FhtmKC3zBRk6wpcHUtLw/1579986645</a>', '2020-01-18 18:10:45', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `other_permissions`
--

CREATE TABLE `other_permissions` (
  `id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `belongs_to` int(11) NOT NULL,
  `guest` tinyint(4) NOT NULL DEFAULT 0,
  `r` tinyint(4) NOT NULL,
  `w` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `other_permissions`
--

INSERT INTO `other_permissions` (`id`, `folder_id`, `belongs_to`, `guest`, `r`, `w`) VALUES
(1, 4, 90, 0, 0, 0),
(2, 5, 87, 0, 1, 1),
(4, 6, 90, 1, 1, 0),
(5, 9, 4, 1, 1, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `tb` varchar(80) COLLATE utf16_spanish_ci NOT NULL,
  `can_create` tinyint(4) NOT NULL DEFAULT 0,
  `can_read` tinyint(4) NOT NULL DEFAULT 0,
  `can_update` tinyint(4) NOT NULL DEFAULT 0,
  `can_delete` tinyint(4) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_spanish_ci;

--
-- Volcado de datos para la tabla `permissions`
--

INSERT INTO `permissions` (`id`, `tb`, `can_create`, `can_read`, `can_update`, `can_delete`, `user_id`, `created_at`, `updated_at`) VALUES
(2, 'products', 0, 1, 1, 1, 168, '0000-00-00 00:00:00', NULL),
(3, 'foo', 0, 1, 0, 1, 90, '2020-01-14 00:00:00', NULL),
(37, 'foo', 0, 1, 1, 1, 168, '2020-01-14 23:09:37', '2020-01-15 06:30:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(240) DEFAULT NULL,
  `size` varchar(30) NOT NULL,
  `cost` int(11) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `active` tinyint(11) DEFAULT 1,
  `locked` tinyint(4) DEFAULT 0,
  `workspace` varchar(40) DEFAULT NULL,
  `belongs_to` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `size`, `cost`, `created_at`, `created_by`, `updated_at`, `updated_by`, `deleted_at`, `active`, `locked`, `workspace`, `belongs_to`) VALUES
(100, 'Vodka', NULL, '2 1/4 L', 200, '2019-07-04 00:00:00', NULL, '2019-11-24 22:46:44', NULL, '2019-11-25 02:46:44', 1, 1, '', 90),
(103, 'Juguito ric0', 'Delicious juic333333', '1 Litros', 150, '2019-09-13 00:00:00', NULL, '2019-11-24 22:46:46', NULL, '2019-11-25 02:46:46', 1, 1, NULL, 90),
(105, 'Agua mineral', 'De Córdoba', '1L', 525, '2019-03-15 00:00:00', NULL, '2019-11-24 22:46:48', NULL, '2019-11-25 02:46:48', 1, 1, 'lista publica', 90),
(106, 'Vodka', 'Rusiaaaaaa', '1L', 400, '2019-02-16 00:00:00', NULL, '2019-11-24 22:46:50', NULL, '2019-11-25 02:46:50', 1, 1, NULL, 4),
(113, 'Vodka', 'URU', '1L', 550, '2019-03-31 00:00:00', NULL, '2019-11-24 22:46:52', NULL, '2019-11-25 02:46:52', 1, 1, NULL, 86),
(114, 'AAABBBCCCcccD', 'cccccC', '29', 200, '2019-01-23 00:00:00', NULL, '2019-11-24 22:46:54', NULL, '2019-11-25 02:46:54', 1, 1, NULL, 4),
(119, 'CocaCola', 'gaseosa', '1L', 44, '2018-10-15 00:00:00', NULL, '2019-11-24 22:46:56', NULL, '2019-11-25 02:46:56', 1, 1, 'lista2', 90),
(120, 'MiBebida', 'Rica Rica', '1L', 50, '2018-12-23 00:00:00', NULL, '2020-01-18 19:55:35', NULL, '2019-11-23 17:59:32', 1, 0, NULL, 90),
(121, 'OtraBebida', 'gaseosa', '1L', 25, '2019-09-28 00:00:00', NULL, '2019-11-24 22:46:58', NULL, '2019-11-25 02:46:58', 1, 1, 'lista2', 90),
(122, 'Cerveza de malta', 'Pichu', '1L', 100, '2018-12-29 00:00:00', NULL, '2019-11-24 22:47:00', NULL, '2019-11-25 02:47:00', 1, 1, NULL, 90),
(123, 'PesiLoca', 'x_x', '2L', 30, '2018-12-16 00:00:00', NULL, '2019-11-17 07:48:25', NULL, '2019-11-17 07:48:25', 1, 0, 'mylist', 90),
(125, 'Vodka', '', '3L', 350, '2017-01-10 00:00:00', NULL, '2019-12-13 08:54:23', NULL, '2019-12-13 12:54:23', 1, 0, 'lista publica', 90),
(126, 'Uvas fermentadas', 'Espectacular', '5L', 50, '2019-06-24 00:00:00', NULL, '2020-01-18 19:02:46', 90, NULL, 1, 0, 'lista publica', 90),
(131, 'Vodka', 'de Estados Unidos!', '1L', 499, '2019-06-04 00:00:00', NULL, '2020-01-03 21:18:16', 90, NULL, 1, 0, 'secreto', 4),
(132, 'Ron venezolano', 'Rico rico', '1L', 100, '2019-10-03 00:00:00', NULL, '2019-12-22 10:11:31', NULL, '2019-12-22 14:11:31', 1, 0, NULL, 90),
(133, 'Vodka venezolano', 'de Vzla', '1.15L', 100, '2019-09-19 00:00:00', NULL, '2020-01-03 21:18:00', 90, NULL, 1, 0, NULL, 90),
(137, 'Agua ardiente', 'Si que arde!', '1L', 125, '2019-07-16 00:00:00', NULL, '2019-12-22 14:38:12', NULL, NULL, 1, 0, 'lista', 90),
(143, 'Agua ', '--', '1L', 100, '2019-06-03 00:00:00', NULL, '2019-11-27 16:51:42', NULL, '2019-11-27 20:51:42', 1, 0, NULL, 90),
(145, 'Juguito XII', 'de manzanas exprimidas', '1L', 350, '2019-02-09 00:00:00', NULL, '2020-01-16 21:27:43', 168, NULL, 1, 0, 'lista24', 168),
(146, 'Wisky', '', '2L', 230, '2019-08-31 00:00:00', NULL, '2019-11-27 16:57:21', NULL, NULL, 1, 0, 'lista24', 90),
(147, 'Aqua fresh', 'Rico', '1L', 105, '2019-03-20 00:00:00', NULL, '2019-11-30 19:21:07', NULL, '2019-11-30 23:21:07', 1, 0, 'comparto', 90),
(148, 'Alcohol etílico', '', '1L', 100, '2019-04-21 00:00:00', NULL, '2019-11-03 21:37:48', NULL, NULL, 1, 0, 'comparto', 90),
(151, 'Juguito XIII', 'Rico', '1L', 355, '2019-10-03 00:00:00', NULL, '2019-10-15 17:00:58', NULL, NULL, 1, 0, 'lista24', 90),
(155, 'Super-jugo', 'BBB', '12', 100, '2019-09-22 00:00:00', NULL, '2020-01-17 11:15:24', NULL, '2020-01-17 15:15:24', 1, 1, NULL, 90),
(159, 'Agua minerale', 'x_x', '2L', 90, '2019-10-14 18:08:45', NULL, '2020-01-17 01:56:24', 4, NULL, 1, 0, NULL, 90),
(160, 'Limonada', 'Rica', '500ML', 210, '2019-10-23 14:05:30', NULL, '2019-12-22 11:58:48', NULL, '2019-12-12 00:00:00', 1, 0, NULL, 90),
(162, 'Juguito de Mabelita', 'de manzanas exprimidas', '2L', 250, '2019-10-25 08:36:26', NULL, '2019-11-12 12:49:52', NULL, NULL, 1, 0, NULL, 113),
(163, 'ABC', 'XYZ', '6L', 600, '2019-10-26 10:05:00', NULL, '2019-11-07 00:29:25', NULL, NULL, 1, 1, NULL, 1),
(164, 'Vodka', 'de Holanda', '33L', 333, '2019-10-26 19:48:26', NULL, '2019-10-29 18:33:57', NULL, NULL, 1, 0, NULL, 112),
(165, 'Vodka', 'de Suecia', '0.5L', 105, '2019-10-26 22:38:39', NULL, '2020-01-17 22:47:48', 90, '2020-01-18 02:47:48', 1, 0, NULL, 90),
(166, 'UUU', 'uuu uuu uu u', '0.5L', 100, '2019-10-26 22:38:39', NULL, '2019-11-04 12:57:49', NULL, NULL, 1, 1, NULL, 90),
(167, 'Vodka', 'de Francia', '10L', 100, '2019-11-02 08:14:46', NULL, '2019-11-03 23:16:17', NULL, NULL, 1, 1, NULL, 90),
(169, 'Clavos de techo', 'largos', '12 cm', 25, '2019-11-02 16:06:31', NULL, '2019-11-03 20:46:12', NULL, NULL, 1, 0, NULL, 125),
(170, 'Escalera', 'para electricista', '2 metros', 200, '2019-11-02 16:07:10', NULL, NULL, NULL, NULL, 1, 0, NULL, 125),
(171, 'Ruedas', 'plastico', '20 cm', 50, '2019-11-02 16:07:51', NULL, '2019-12-07 00:39:42', NULL, NULL, 1, 0, NULL, 125),
(172, 'Clavos para madera', 'bronce', '2.5 cm', 10, '2019-11-02 16:08:35', NULL, '2019-11-03 20:46:12', NULL, NULL, 1, 0, NULL, 125),
(173, 'Escalera pintor', 'metal', '5 metros', 80, '2019-11-02 20:41:55', NULL, '2019-12-07 00:38:05', NULL, NULL, 1, 0, NULL, 125),
(174, 'Caja de herramientas', 'metal', 'M', 90, '2019-11-02 20:42:52', NULL, '2019-12-07 00:38:47', NULL, NULL, 1, 0, NULL, 125),
(175, 'Caja de herramientas', 'plastico', 'M', 30, '2019-11-02 20:43:14', NULL, '2019-12-07 00:39:18', NULL, NULL, 1, 0, NULL, 125),
(176, 'Alambre', 'Precio por kilo', '1 mm', 400, '2019-11-02 20:44:28', NULL, NULL, NULL, NULL, 1, 0, NULL, 125),
(177, 'Cable de 2 hilos telefónico', 'Por metro', '', 10, '2019-11-02 20:45:10', NULL, '2019-12-07 10:32:49', NULL, NULL, 1, 0, 'electricos', 125),
(178, 'Agua destilada', '', '1L', 0, '2019-11-02 20:46:05', NULL, '2019-12-07 10:32:07', NULL, NULL, 1, 0, NULL, 125),
(179, 'Agua mineral', '', '1L', 10, '2019-11-02 20:46:20', NULL, '2019-11-03 20:46:12', NULL, NULL, 1, 0, NULL, 125),
(180, 'Pintura blanca exteriores', '', '5L', 200, '2019-11-02 21:19:06', NULL, NULL, NULL, NULL, 1, 0, NULL, 125),
(181, 'Pintura blanca exteriores', '', '2L', 100, '2019-11-02 21:19:22', NULL, '2019-11-03 20:46:12', NULL, NULL, 1, 0, NULL, 125),
(182, 'Pintura blanca interiores', NULL, '2L', 80, '2019-11-02 21:20:00', NULL, '2019-11-03 20:46:12', NULL, NULL, 1, 0, NULL, 125),
(183, 'Tuercas', '', '', 0, '2019-11-03 21:33:20', NULL, NULL, NULL, NULL, 1, 0, NULL, 125),
(185, 'ABC', '', '', 0, '2019-11-03 23:55:18', NULL, NULL, NULL, NULL, 1, 0, NULL, 132),
(186, 'Toma-corrientes hembra pared', 'color: blanco', '', 20, '2019-11-04 09:26:55', NULL, NULL, NULL, NULL, 1, 0, 'electricos', 125),
(187, 'Crush', 'x_x', '1L', 20, '2019-11-04 13:06:04', NULL, '2019-11-11 13:15:58', NULL, NULL, 1, 1, NULL, 90),
(189, 'AAAAAAAAAAAAAaaaaa', '', '', 0, '2019-11-04 17:04:51', NULL, '2019-11-04 17:05:00', NULL, NULL, 1, 0, NULL, 87),
(191, 'Wisky', '', '1.15L', 100, '2019-11-05 21:36:40', NULL, '2019-12-22 19:56:57', 90, NULL, 1, 0, NULL, 113),
(192, 'Jugo Naranjin', 'Delicious juicEEEE', '1 L', 350, '2019-11-06 23:45:29', NULL, NULL, NULL, NULL, 1, 0, NULL, 4),
(193, 'Re-Jugo', 'Delicious juicEEEEXXX', '1 L', 350, '2019-11-07 00:18:25', NULL, NULL, NULL, NULL, 1, 0, NULL, 4),
(194, 'Re-Jugo', 'Delicious juicEEEEXXXYZ', '1 L', 350, '2019-11-07 00:20:53', NULL, NULL, NULL, NULL, 1, 0, NULL, 135),
(195, 'Boo', 'Delicious juicEEEEXXXYZ4444444444444444444444444', '1 L', 350, '2019-11-07 01:31:43', NULL, NULL, NULL, NULL, 1, 0, NULL, 4),
(196, 'NaranjAAAAAAA', 'OK', '', 0, '2019-11-07 22:58:51', NULL, '2019-11-24 17:49:34', NULL, NULL, 1, 0, NULL, 137),
(197, 'HEYYYYYY', '', '', 0, '2019-11-10 00:57:51', NULL, NULL, NULL, NULL, 1, 0, NULL, 150),
(198, 'AAAAA', 'x_x', '', 22, '2019-11-11 10:38:19', NULL, '2019-11-11 13:15:58', NULL, NULL, 1, 0, NULL, 90),
(199, 'cuzbgmbhiudjqvrmzwqf', 'x_x', '1L', 70, '2019-11-11 10:42:57', NULL, '2020-01-17 22:37:14', 90, '2020-01-18 02:37:14', 1, 0, 'lista publica', 90),
(200, 'AAA', '', '', 0, '2019-11-11 11:44:51', NULL, NULL, NULL, NULL, 1, 0, NULL, 156),
(201, 'vzukvnjjhzintijexhjd', 'x_x', '1L', 66, '2019-11-11 11:48:37', NULL, '2019-11-11 13:15:58', NULL, NULL, 1, 0, NULL, 90),
(202, 'VVVBBB', '', '', 0, '2019-11-11 11:59:23', NULL, '2019-11-11 13:10:30', NULL, NULL, 1, 0, NULL, 148),
(203, 'Super-gas', '', '2L', 50, '2019-11-11 14:00:47', NULL, NULL, NULL, NULL, 1, 0, NULL, 87),
(204, 'Gas2', '', '', 0, '2019-11-11 14:01:49', NULL, NULL, NULL, NULL, 1, 0, 'lista', 87),
(205, 'Supreme jugooo', 'de manzanas exprimidas', '1L', 250, '2019-11-11 14:09:52', NULL, NULL, NULL, NULL, 1, 0, 'lista', 87),
(206, 'Juguito de tomate de árbol', 'Ecuador', '1L', 200, '2019-11-11 15:14:36', NULL, '2019-11-11 16:26:34', NULL, NULL, 1, 0, 'lista publica', 90),
(207, 'Juguito de tomate papaya', NULL, '1L', 150, '2019-11-11 15:15:05', NULL, '2019-11-11 15:41:32', NULL, NULL, 1, 0, 'lista', 87),
(208, 'Juguito de tomate pitaya', NULL, '1L', 450, '2019-11-11 15:15:16', NULL, NULL, NULL, NULL, 1, 0, 'lista', 87),
(209, 'AAA', '', '', 0, '2019-11-12 12:50:01', NULL, '2019-11-12 12:50:04', NULL, '2019-11-12 12:50:04', 1, 0, NULL, 113),
(211, 'EEEE', '', '', 50, '2019-11-27 17:06:24', NULL, '2019-11-30 23:22:41', NULL, '2019-12-01 03:22:41', 1, 0, NULL, 159),
(212, 'RRR', '', '', 0, '2019-11-27 18:01:44', NULL, '2019-11-27 18:01:50', NULL, '2019-11-27 22:01:50', 1, 0, NULL, 160),
(213, 'E%$', '', '', 0, '2019-11-27 18:02:17', NULL, NULL, NULL, NULL, 1, 0, NULL, 160),
(214, 'TTT', '', '', 0, '2019-11-28 00:01:02', NULL, NULL, NULL, NULL, 1, 0, NULL, 167),
(215, 'Vino tinto', '', '', 100, '2019-11-30 11:13:05', NULL, '2019-11-30 11:26:13', NULL, NULL, 1, 0, NULL, 168),
(216, 'BBB', '', '', 0, '2019-11-30 23:23:29', NULL, NULL, NULL, NULL, 1, 0, NULL, 159),
(218, 'Caja organizadora', 'plastico', 'M', 100, '2019-12-07 10:25:46', NULL, NULL, NULL, NULL, 1, 0, NULL, 125),
(219, 'Vodka', 'de Canada', '2L', 250, '2019-12-13 08:29:11', NULL, '2019-12-13 08:29:28', NULL, NULL, 1, 0, NULL, 90),
(220, 'Agua', 'sabor limón', '', 0, '2019-12-13 18:35:19', NULL, NULL, NULL, NULL, 1, 0, NULL, 90),
(221, 'Agua', 'sabor lima', '', 20, '2019-12-13 18:35:35', NULL, NULL, NULL, NULL, 1, 0, NULL, 90),
(222, 'Agua', 'mineral', '', 15, '2019-12-13 18:35:49', NULL, '2020-01-17 22:28:02', NULL, '2020-01-18 02:28:02', 1, 0, NULL, 90),
(223, 'Agua', 'sabor pomelo', '', 20, '2019-12-13 18:36:21', NULL, '2019-12-13 18:36:39', NULL, '2019-12-13 22:36:39', 1, 0, NULL, 90),
(224, 'Ron', 'caribeño', '', 0, '2019-12-13 18:37:16', NULL, '2019-12-13 18:38:05', NULL, '2019-12-13 22:38:05', 1, 0, NULL, 90),
(225, 'Ron', 'de Trinidad', '', 0, '2019-12-13 18:37:34', NULL, '2019-12-13 18:38:02', NULL, '2019-12-13 22:38:02', 1, 0, NULL, 90),
(226, 'Ron', 'de Cuba', '', 0, '2019-12-13 18:37:47', NULL, '2019-12-13 18:37:54', NULL, '2019-12-13 22:37:54', 1, 0, NULL, 90),
(256, 'XXX', NULL, '', 10, '2019-12-22 19:51:19', 90, NULL, NULL, NULL, 1, 0, NULL, 90),
(257, 'Koke', 'Rica', '0.5L', 20, '2020-01-03 21:27:41', 90, '2020-01-03 21:28:45', 90, NULL, 1, 0, NULL, 90),
(259, 'Wiksy', 'from Bielorussia', '1L', 100, '2020-01-04 01:31:42', 90, NULL, NULL, NULL, 1, 0, NULL, 90),
(271, 'Wisky', NULL, '', 400, '2020-01-16 01:19:08', 323, NULL, NULL, NULL, 1, 0, NULL, 323),
(272, 'Wisky Super', NULL, '', 500, '2020-01-16 01:19:41', 324, '2020-01-16 01:31:31', 323, NULL, 1, 0, 'VVV', 324),
(273, 'EEE90', NULL, '', 50, '2020-01-16 16:09:22', 323, NULL, NULL, NULL, 1, 0, NULL, 323),
(274, 'EEE4', NULL, '', 50, '2020-01-16 16:10:34', 4, '2020-01-18 19:32:19', 90, '2020-01-18 23:32:19', 1, 0, NULL, 90),
(275, 'Wisky Monumental', 'rico', '3L', 1200, '2020-01-17 11:04:27', 4, '2020-01-17 11:07:51', NULL, '2020-01-17 15:07:51', 1, 1, NULL, 324);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `super_cool_table`
--

CREATE TABLE `super_cool_table` (
  `id` int(11) NOT NULL,
  `name` varchar(60) COLLATE utf16_spanish_ci NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 0,
  `belongs_to` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `locked` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_spanish_ci;

--
-- Volcado de datos para la tabla `super_cool_table`
--

INSERT INTO `super_cool_table` (`id`, `name`, `active`, `belongs_to`, `created_by`, `created_at`, `updated_at`, `updated_by`, `deleted_at`, `deleted_by`, `locked`) VALUES
(1, 'algo', 0, 90, 90, '2020-01-19 14:00:49', NULL, 4, NULL, 4, 1),
(2, 'otra cosa', 1, 4, 4, '2020-01-19 21:25:01', NULL, NULL, NULL, 4, 1),
(3, 'xxx', 1, 90, 90, '2020-01-19 23:18:05', NULL, 90, NULL, 90, 0),
(4, 'zzz', 0, 90, 90, '2020-01-19 23:29:51', NULL, 90, NULL, 90, 0),
(5, 'cervezaaaaa fria', 0, 90, 90, '2020-01-20 00:36:15', '2020-01-20 01:06:09', 90, NULL, 90, 0),
(6, 'cervezaaaaa negra', 0, 90, 90, '2020-01-20 00:57:38', NULL, 90, '2020-01-20 01:05:17', 90, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(15) NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 0,
  `locked` tinyint(4) NOT NULL DEFAULT 0,
  `email` varchar(60) NOT NULL,
  `confirmed_email` tinyint(4) NOT NULL DEFAULT 0,
  `firstname` varchar(50) DEFAULT NULL,
  `lastname` varchar(80) DEFAULT NULL,
  `password` varchar(60) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `belongs_to` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `active`, `locked`, `email`, `confirmed_email`, `firstname`, `lastname`, `password`, `deleted_at`, `belongs_to`) VALUES
(1, 'boctulus1', 1, 0, 'boctulus1@gmail.com', 1, '', '0', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 1),
(4, 'pbozzolo', 1, 0, 'pbozzolo@gmail.com', 1, 'Paulinoxxxy', 'Bozzoxxxy', '$2y$10$jAKcStnGqtcOslt1Std7ceYqq3mMIh6Lis/Ug4Z6IDQV65tyyP2Xe', NULL, 4),
(5, 'pepe', 1, 0, 'pepe@gmail.com', 1, 'Pepe', 'Gonzalez', '$2y$10$J.KPjyFukfxcKg83TvQGaeCTrLN9XyYXTgtTDZdZ91DJTdE73VIDK', NULL, 5),
(9, 'dios', 1, 0, 'dios@gmail.com', 1, 'Paulino', 'Bozzoxx000555zZ', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 9),
(11, 'diosdado', 1, 0, 'diosdado@gmail.com', 1, 'Sr', 'Z', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 11),
(13, 'diosdado2', 1, 0, 'diosdado2@gmail.com', 1, 'Sr', 'D', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 13),
(14, 'juancho', 1, 0, 'juancho@aaa.com', 1, 'Juan', 'Perez', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 14),
(15, 'juancho11', 1, 0, 'juancho11@aaa.com', 1, 'Juan XI', 'Perez 10', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 15),
(16, 'mabel', 1, 0, 'mabel@aaa.com', 1, 'Mabel', 'S', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 16),
(17, 'a', 1, 0, 'a@b.commmm', 1, 'Nicos', 'Buzzi', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 17),
(20, 'a1', 1, 0, 'a@b.commmmmmmmmmmm', 1, 'Nicos', 'AAA', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 20),
(34, 'peter', 1, 0, 'peter@abc.com', 1, 'Nicos', 'Buzzi', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 34),
(38, 'udcsoqjyrdg', 1, 0, 'udcsoqjyrdgnhqqtukhupeter@abc.com', 1, 'Nicos', 'Buzzi', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 38),
(39, 'qbosmfvwezo', 1, 0, 'qbosmfvwezohbutpifbopeter@abc.com', 1, 'Nicos', 'Buzzi', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 39),
(40, 'gjappgiduiq', 1, 0, 'gjappgiduiqczagnousspeter@abc.com', 1, 'Nicos', 'Buzzi', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 40),
(41, 'ymcshlekdzh', 1, 0, 'ymcshlekdzhugvmwbjpipeter@abc.com', 1, 'Nicos', 'Buzzi', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 41),
(43, 'vydqkgqszpn', 1, 0, 'vydqkgqszpncijwhxeiapeter@abc.com', 1, 'Nicos', 'Buzzi', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 43),
(44, 'itbrknzsfna', 1, 0, 'itbrknzsfnawnhxgmockpeter@abc.com', 1, 'Nicos', 'Buzzi', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 44),
(45, 'cproifnsfxv', 1, 0, 'cproifnsfxvkxtppbgdupeter@abc.com', 1, 'Nicos', 'Buzzi', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 45),
(46, 'xxxxxxxxxxx', 1, 0, 'xxxxxxxxxxxxxxxxyz@abc.com', 1, 'Nicolayyyy', 'Buzzi', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 46),
(48, 'gates', 1, 0, 'gates@outlook.com', 1, 'Bill', 'Gates', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 48),
(51, 'kkk', 1, 0, 'kkk@bbbbbb.com', 1, 'Sr K', 'NS/NC', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 51),
(52, 'tito', 1, 0, 'tito@gmail.com', 1, 'Tito', 'El Grande', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 52),
(53, 'ooooiiii', 1, 0, 'ooooiiii@gmail.com', 1, 'Oooo', 'iiiiiiii', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 53),
(54, 'booooiiii', 1, 0, 'booooiiii@gmail.com', 1, 'AAA', 'BBB', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 54),
(55, 'iooobooooii', 1, 0, 'iooobooooiiii@gmail.com', 1, 'IIoo', 'ahaha', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 55),
(56, 'iooobooooii5', 1, 0, 'iooobooooiiioooi@gmail.com', 1, 'IIoo', 'ahaha', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 56),
(57, 'iooobooooii57', 1, 0, 'iooobooooiiioooi@gmail.commmm', 1, 'IIoo', 'ahaha', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 57),
(58, 'kkk1', 1, 0, 'kkk@bbbbbb.commmm', 1, 'Sr K', 'NS/NC', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 58),
(59, 'kkkbooooiii', 1, 0, 'kkkbooooiiii@gmail.com', 1, 'Ooookkk', 'kkkk', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 59),
(60, 'aaa', 1, 0, 'aaa@bbbb.com', 1, 'Jjjj', 'kkk', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 60),
(61, 'aaa7', 1, 0, 'aaa@bbbb.commmm', 1, 'Jjjj', 'kkk', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 61),
(62, 'kkk6', 1, 0, 'kkk@bbbbbb.commmmmmm', 1, 'Sr K', 'NS/NC', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 62),
(63, 'aaa5', 1, 0, 'aaa@bbbb.commmmmmm', 1, 'Jjjj', 'kkk', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 63),
(64, 'booooiiiixx', 1, 0, 'booooiiiixxxx@gmail.com', 1, 'xxx', 'xxxxxxxxxx', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 64),
(65, 'aaa9', 1, 0, 'aaa@bbbb.commmmmmmuuuuu', 1, 'Jjjj', 'kkk', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 65),
(66, 'aaa54', 1, 0, 'aaa@dgdgd.cococ', 1, 'ajajaj', 'ajajaj', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 66),
(67, 'booooiiifer', 1, 0, 'booooiiiferfr@gmail.com', 1, 'BillY', 'GGGG', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 67),
(68, 'aaa78', 1, 0, 'aaa@dgdgd.cococo', 1, 'ajajaj', 'ajajaj', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 68),
(69, 'test', 1, 0, 'test@gmail.com', 1, 'TEST', '---', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 69),
(70, 'kkk65', 1, 0, 'kkk@bbJJJJJJJ', 1, 'Sr K', 'NS/NC', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 70),
(72, 'aie', 1, 0, 'aie@b.c', 1, 'Sr K', 'NS/NC', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 72),
(73, 'mabelf450', 1, 0, 'mabelf450@gmail.com', 1, 'Mabel', 'F', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 73),
(74, 'abc', 1, 0, 'abc@def.com', 1, 'Sr K', 'NS/NC', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 74),
(75, 'abc4', 1, 0, 'abc@def.commm', 1, 'Sr K', 'NS/NC', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 75),
(76, 'abc6', 1, 0, 'abc@def.net', 1, 'Sr K', 'NS/NC', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 76),
(77, 'abc62', 1, 0, 'abc@def.co', 1, 'Sr K', 'NS/NC', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 77),
(78, 'abc9', 1, 0, 'abc@def.cox', 1, 'Sr K', 'NS/NC', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 78),
(79, 'feli', 1, 0, 'feli@', 1, 'Sr K', 'NS/NC', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 79),
(80, 'feli3', 1, 0, 'feli@casa', 1, 'Sr K', 'NS/NC', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 80),
(81, 'feli5', 1, 0, 'feli@casa.com', 1, 'Sr K', 'NS/NC', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 81),
(82, 'feli4', 1, 0, 'feli@casa.net', 1, 'Sr K', 'NS/NC', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 82),
(83, 'feli9', 1, 0, 'feli@casa.neto', 1, 'Sr K', 'NS/NC', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 83),
(84, 'pablo', 1, 0, 'pablo@', 1, 'Nicos', 'AAA', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 84),
(85, 'feli6', 1, 0, 'feli@teamo', 1, 'Felipe', 'Bozzolo', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 85),
(86, 'nuevo', 1, 0, 'nuevo@gmail.com', 1, 'Norberto', 'Nullo', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 86),
(87, 'pedro', 1, 0, 'pedro@gmail.com', 1, 'Pedro', 'Picapiedras', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 87),
(88, 'feli8', 1, 0, 'feli@abc', 1, 'Felipe', 'Bozzzolo', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 88),
(89, 'h', 1, 0, 'h@', 1, 'Sr H', 'J', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 89),
(90, 'nano', 1, 0, 'nano@g.c', 1, 'NA', 'Bzz', '$2y$10$qmCo8ZeT1XJWPZ1kuSeFjuj7rEDT9J/YDV4yD3BsVoE.pz0ryfhE2', NULL, 90),
(102, 'feli61', 1, 0, 'feli@delacasita', 1, 'Sr K', 'NS/NC', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 102),
(103, 'feli1', 1, 0, 'feli@delacasita2', 1, 'Sr K', 'NS/NC', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 103),
(104, 'feli7', 1, 0, 'feli@delacasita5', 1, 'Sr K', 'NS/NC', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 104),
(105, 'feli80', 1, 0, 'feli@delacasita50.com', 1, 'Sr K', 'NS/NC', '$2y$10$N6fTRdVfyusWVkAchTWmSO1OAscI/X.ZU5YU14imTrfR0gLlbncVO', NULL, 105),
(106, 'feli72', 1, 0, 'feli@delacasita50000', 1, 'Sr K', 'NS/NC', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 106),
(107, 'feli36', 1, 0, 'feli@delacasita50000700', 1, 'Sr K', 'NS/NC', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 107),
(108, 'feli31', 1, 0, 'feli@delacasita50000700800', 1, 'Sr K', 'NS/NC', '$2y$10$/ehgjdS8p8IbRKMW4AVVOuX38p8yMIZinciIWsj79rDUfRfKH6/56', NULL, 108),
(109, 'feli34', 1, 0, 'feli@compeon', 1, 'Sr K', 'NS/NC', '$2y$10$ocJZwzelZ3W.3Eas5ig/q.qaBm79ottSuJ8ee2wXv9584INHL2RpW', NULL, 109),
(110, 'feli67', 1, 0, 'feli@compeon_mundial', 1, 'Sr K', 'NS/NC', '$2y$10$k4kFWXmQacW4LDS.j4gVk./LqKPUtOc9XaObNWojsAzmFnIxbLZ8u', NULL, 110),
(111, 'feli76', 1, 0, 'feli@compeon_dios', 1, 'Sr K', 'NS/NC', '$2y$10$nYh5nGXM6mVwwAP93G4Nv.m8P8aQmJKLm5fODQBqdmzzSCEZzDiOC', NULL, 111),
(112, 'superpepe', 1, 0, 'superpepe@', 1, 'Super Pepe', '', '$2y$10$hITAKY1zsMPMIe0KCO6YuuG5Xke8FeZlw00Uw1Mz4J57LU0lLfvja', NULL, 112),
(113, 'mabelsusana', 1, 0, 'mabelsusanaf@gmail.com', 1, NULL, NULL, NULL, NULL, 113),
(114, 'xyz', 1, 0, 'xyz@', 1, 'XYZ', 'ZZZ', '$2y$10$mMaJuMmAt7hgTo8KljlDRuavoOg4F0ZGw.hAK/vmpNQJ0xvA/ZEmG', NULL, 114),
(115, 'x', 1, 0, 'x@', 1, 'Sr K', 'NS/NC', '$2y$10$K64bJLscpFCH7Geal..vhuKeMzZOP9MWD21eXPj7VpfYiHw4AaEMC', NULL, 115),
(116, 'xy', 1, 0, 'xy@', 1, 'Sr K', 'NS/NC', '$2y$10$86ztfpWglgqvK/MyJf12VOYY8rqjJ4Qhi1XhpOsNW0u.BMM3odtUG', NULL, 116),
(117, 'aaa3', 1, 0, 'aaa@', 1, 'A', 'AB', '$2y$10$KP8rEs5DracAVvcdMY/ATuB3xwEz7Rjwqj5DilPiszMi8wMRKNAhK', NULL, 117),
(118, 'aaaC', 1, 0, 'aaaC@', 1, 'A', 'AB', '$2y$10$T59OjL8Rxb/QZeArKW.PK.GVVG7V3Ao846KSimwt6xGbf7tx0oik.', NULL, 118),
(119, 'aaaCD', 1, 0, 'aaaCD@', 1, 'A', 'AB', '$2y$10$SvAYgvxsszn1Z/cfMW/w9eopOcih3CzADDEDd2q0wKwqwTBWVPZoi', NULL, 119),
(120, 'xyza', 1, 0, 'xyza@', 1, 'XYZA', 'ZZZA', '$2y$10$rp40xaVPNtHqSsDYXVBNCespVSLBpvwdp1oyV7NY6nJk011q2/Iri', NULL, 120),
(121, 'nono', 1, 0, 'nono@', 1, 'Nono', 'viecco', '$2y$10$W5HaPfOxbAE9rRb04XCzeO/sS0tlHQ4ZTnmXzZPnd2s1qZt26OFdS', NULL, 121),
(122, 'sss', 1, 0, 'sss@', 1, 'Regular', 'Tester', '$2y$10$8FiysajEjA0.5eyXuLNUZeqQkUBgxCYVonwk1Z5k4rTf/rPMLu.2y', NULL, 122),
(123, 'ppp', 1, 0, 'ppp@', 1, 'Regular', 'Tester', '$2y$10$J3C1J2pqvJbuQXEekf6vf.AL0lEJbpGrhPtZCujXRfyv16nEzQvdm', NULL, 123),
(124, 'w', 1, 0, 'w@', 1, 'AA', 'BB', '$2y$10$899qRrlzAXbnyE/5CHLZVezdK9beIDJqrUmb/TcdgepkPHYTMLJTK', NULL, 124),
(125, 'tester3', 1, 0, 'tester3@g.c', 1, 'Tester', '', '$2y$10$hfB4/MQ8ULXY4fhaeAhXqOw7V.U1ifhJeLe5/Xx7mwXA.uFbHBwai', NULL, 125),
(126, 'jk', 1, 0, 'jk@', 1, 'J', 'K', '$2y$10$irie63zGURJ/JQiJuyph/uVkVmAJRiSvvDRGaBokyvVLpi8tqganW', NULL, 126),
(127, 'aaaCDEF', 1, 0, 'aaaCDEF@', 1, 'A', 'AB', '$2y$10$juP31/p3B.P7F/b2MXxGF.kiN/HG1zwIyehkNWjef5yhFYSGq.YwC', NULL, 127),
(128, 'aaaCDEFG', 1, 0, 'aaaCDEFG@', 1, 'A', 'AB', '$2y$10$nkIhbPrL4Y/oJJOe6JdOO.57U8Njn57IRM5cYG7FtPDA2jffOsFNm', NULL, 128),
(129, 'aaaCDEFGI', 1, 0, 'aaaCDEFGI@', 1, 'A', 'AB', '$2y$10$VeyVsSP/.2SgfBJq25FXIOPq2iXceFiEgRiEVuOTi5oPIMWc8Vteq', NULL, 129),
(130, 'aaaCDEFGIJK', 1, 0, 'aaaCDEFGIJK@', 1, 'A', 'AB', '$2y$10$ann6qT5V/SkaYk/InT27ouVPhrkNaVOlvwgY2nf27lhlT5hm0hnTK', NULL, 130),
(131, 'aaaCDEFGIJK9', 1, 0, 'aaaCDEFGIJKLM@', 1, 'A', 'AB', '$2y$10$N2hoKh4E9.aYnAPmMPuSb.mgkLAJA1mB0pAnpjsLRU7DuyX1.b1ZC', NULL, 131),
(132, 'tt', 1, 0, 'tt@', 1, 'T', 'TT', '$2y$10$qXJ25mY64hef.47EjJkBNeYDgG.zHNH8QsodGK3OW13EYQdpaifFG', NULL, 132),
(133, 'jc', 1, 0, 'jc@', 1, 'Juan', 'Carlos', '$2y$10$gMgqAHr1A5.phZ2/n.RuVuJEM8QnfPVAiTGMVqvCOf7mlWXrGNjXK', NULL, 133),
(135, 'b', 1, 0, 'b@gmail.com', 1, 'Pablo', 'Bozzolo', '$2y$10$k0dfh9fPueuBlpPr0zC6V.kEr0CR4uHjEc3IUKipfRR3sDnnSvieu', NULL, 135),
(136, 'bill', 1, 0, 'bill@', 1, 'Bill', 'O', '$2y$10$GHiCUTrFu01EiVcVgTvRluGweRBx8rF6V2qgbhNH82Oi86ATE0RO2', NULL, 136),
(137, 'san', 1, 0, 'san@', 1, 'San', 'Pepe', '$2y$10$OVUu5rTU1JPSTv2HeXq/puWq86vZT24VAuYTW.sAT.pqzxZEJPFou', NULL, 137),
(138, 'j', 1, 0, 'j@', 1, 'AJ', 'J', '$2y$10$TOUciinPf3DEBTjALBLnaOhMLzHrwBRkvczOWYD5OsZOy9aVGVfnC', NULL, 138),
(139, 'aaaCDEFGIJK6', 1, 0, 'aaaCDEFGIJKLMSSS@', 1, 'A', 'AB', '$2y$10$0YDH5aE9l3lQwxFp25bLXepmGQF8fM4XcK4zt/lNzS/2.M7mVgz4S', NULL, 139),
(140, 'ZZZ', 1, 0, 'ZZZ@', 1, 'A', 'AB', '$2y$10$QosozVg0npjSEimSeOZ74OPyXXZO0SwPsZevPOJlQ0GnHDK7DcsR6', NULL, 140),
(141, 'ZZZ5', 1, 0, 'ZZZ@PEPE.com', 1, 'A', 'AB', '$2y$10$XIpys1..n4XcQ9CWP89vce9j7NeAiMYYeOqonYtGgUi.9nZcaccB2', NULL, 141),
(142, 'A5', 1, 0, 'A@PEPE.COM', 1, 'A', 'AB', '$2y$10$Fn5Wkt8masdaOdqBHIaU0uDuRbi7mEVDSzlHgAK8wfJSesSlQWAZu', NULL, 142),
(143, 'Ab', 1, 0, 'Ab@PEPE.COM', 1, 'A', 'AB', '$2y$10$ehfF6Uwrvdl3NoqYkqhnIexzzCX49pCdbEXOYKrI1O2jsvdHLEpCa', NULL, 143),
(147, 'ABCDEF', 1, 0, 'ABCDEF@PEPE.COM', 1, 'A', 'AB', '$2y$10$67kVEbzj5C7eGl8b9f01jeMvbU6Cy2xGYADhYl5PvMmUAXAenmmb2', NULL, 147),
(148, 'ABCDEFG', 1, 0, 'ABCDEFG@PEPE.COM', 1, 'A', 'AB', '$2y$10$p/KGmzQsMmlDDJlYmhr9GOUmkcO4A.A8sefXd7bN3GthEIEA4YCSO', NULL, 148),
(149, 'X8', 1, 0, 'X@PEPE.COM', 1, 'A', 'AB', '$2y$10$R9EtFdhCWUjMMFkLZxLiA.8XJ2hd2SE8Q3eDuyUa8KOV0TsTGNRX.', NULL, 149),
(150, 'Y', 1, 0, 'Y@PEPE.COM', 1, 'A', 'AB', '$2y$10$WeO4iScCcFe/yV4d7iUbFeYOfTR6H3TGDjunJLskrJg/.9raTP7IG', NULL, 150),
(151, 'F', 1, 0, 'F@PEPE.COM', 1, 'A', 'AB', '$2y$10$LDtPjcxYtrwMVfae.8MsNuRaoRgsnVtagH2fbYG3m3MIYOeAWB4fC', NULL, 151),
(152, 'G', 1, 0, 'G@PEPE.COM', 1, 'A', 'AB', '$2y$10$TY9RyLYn.E24qAeBn9LCzuy8bRT1/UReSDDodfdHouQ7jRN6GqMTq', NULL, 152),
(153, 'GG', 1, 0, 'GG@PEPE.COM', 1, 'A', 'AB', '$2y$10$qt79FFFbCbQNya/a8fMMn.tcstyJYUHRXIw36pGDTLExLueDKbJby', NULL, 153),
(154, 'GGG', 1, 0, 'GGG@PEPE.COM', 1, 'A', 'AB', '$2y$10$bIqxFs1bOo.CkImQj5t26.R6fnp5UO8913B5NkxNDy5gP0bhNoP2O', NULL, 154),
(155, 'elpiojo', 1, 0, 'elpiojo@', 1, 'Ojo', '', '$2y$10$UvEXPDfjiZ/PPits.tThwurSYCz844ZMh9mCVBC5Y9s7Hp8KwlR.2', NULL, 155),
(156, 'elpiojo2', 1, 0, 'elpiojo2@', 1, 'Ojo', '', '$2y$10$aPWXX4L3MNnPcz0Q.zLFRO97ChkGYQoUdyvk.RzVDuwRaQDiMxkUS', NULL, 156),
(159, 'boo', 1, 0, 'boo@', 1, 'Boo', '', '$2y$10$eZfHERLi3AlNU7zsPzV.OOqF0Hs7jkh.LXRKdM/3XVABpLujf/f.G', NULL, 159),
(160, 'uub', 1, 0, 'uub@', 1, 'Uub', '', '$2y$10$ylqsjqzOYFRrfRNYGaruteJ1uLnqcDLyyJTE6If1wb3GNCvEwTk/u', NULL, 160),
(163, 'asdfgh', 1, 0, 'asdfgh', 0, NULL, NULL, '$2y$10$3qyTA2frHg.CNo2VQTh/cenZoi4y4dtoKhGQNe6P8lqL.u5jS3MFu', NULL, 163),
(164, 'asdfgh2', 1, 0, 'asdfgh2', 0, NULL, NULL, '$2y$10$lWC.2LcTeHNX65n1NDwDsuSekR0zYC0WNBTOzXEesuRSkKt3krTxa', NULL, 164),
(165, 'asdfgh23', 1, 0, 'asdfgh23', 0, NULL, NULL, '$2y$10$3ehTASOEPlBjoNYdrZE.WeoKNoV35.DvjpDW1S7IkihN3ByKlCRse', NULL, 165),
(166, 'asdfgh234', 1, 0, 'asdfgh234', 0, NULL, NULL, '$2y$10$AgMfAavv9tAZjJNPZWAceeEq6gBnBgbKiQRrnqtBIioMk8zMvZrKi', NULL, 166),
(167, 'pepem', 1, 0, 'pepe@', 1, NULL, NULL, '$2y$10$E7MLf1GxIdRnT4uwOYr03e6mrs3BXd1SApL6EzvzqTs4EkyzttjKm', NULL, 167),
(168, 'boctulus', 1, 0, 'boctulus@gmail.com', 0, 'Pablo', 'Bozzolo', '$2y$10$266K2AVhSao58S1J2VJBmOQgTikkuNtRJUUJL3jjb0kcNrbDtaoLe', NULL, 168),
(170, 'uva@g.c', 1, 0, 'uva@g.c', 0, NULL, NULL, '$2y$10$tqA8gO2X8m8aNJoWkqjlJObThy4ZkzTxrV0V0srP7o8QWm/VFsQyO', NULL, 170),
(196, 'doe1979', 1, 0, 'testing_create@g.com', 0, 'Jhon', 'Doe', 'pass', NULL, NULL),
(200, 'doe1980', 1, 0, 'testing1@g.com', 0, 'Jhon', 'Doe', 'pass', NULL, NULL),
(223, 'ffff1', 1, 0, 'ffff1@g.c', 0, NULL, NULL, '$2y$10$FmWYwubCrUXNoyUC16zsquFyCn0R8OzE6eicAgCd6cnDUNXqr2fIa', NULL, 223),
(224, 'newuser97865wxy', 1, 0, 'newuser97865wxy@g.c', 0, NULL, NULL, '$2y$10$E0bUrCzhD12YWQWtRPE4CeQspqlKWhJXNyLuBi3iFzPLmiZ6fW10W', NULL, 224),
(225, 'nnn', 1, 0, 'nnn@g.c', 0, NULL, NULL, '$2y$10$C0sO.yRmK1xm./jm.OTMw.citGYFci11M/BHecY/pBYY9BplqsYg2', NULL, 225),
(226, 'nn_j23483j401wx', 1, 0, 'nn_j23483j401wx@g.c', 0, NULL, NULL, '$2y$10$vHi9kf2VLIIyZUn8P8ACQuQ0SIT7REfVTPxnMvGwY7g.MrMUi2Sn2', NULL, 226),
(301, 'nano111', 1, 0, 'asdffffffffffff@g.c', 0, NULL, NULL, '$2y$10$Xu6Lv.AjKrwYV4NOfJIce.l1HTwHUkSlOcMQQGkATFLwmDt2i5K9i', NULL, 301),
(302, 'nano111__$', 1, 0, 'asdfggg@g.c', 0, NULL, NULL, '$2y$10$PMVMWFDhKnaITfqHimZ1JOyaYnqOA6ne26AQPHVMQAUFEYA.vXEc6', NULL, 302),
(303, 'nano111__$u', 1, 0, 'asdfggtttg@g.c', 0, NULL, NULL, '$2y$10$Ioz50NWbl86r9ZC8F/tXoOZHbiArx07z51quWp4smvsn.3sNXLXb6', NULL, 303),
(304, 'nandddo111__$u', 1, 0, 'asdfggtdddddtdddtg@g.c', 0, NULL, NULL, '$2y$10$w/DJt2PJB0jjUe3piamNNeUN2.JzMGfkcW69boizhjVhrbO75anOK', NULL, 304),
(305, 'no1fff11__$u', 1, 0, 'asdfggtdtdddtg@g.c', 0, NULL, NULL, '$2y$10$.YHFBcObFtZo72RWZ54hj.7qAQqGghb4/He5YZA5xTqL8ZXxZk9c6', NULL, 305),
(306, 'no51fff11__$u', 1, 0, 'asdfgg5tdftdddtg@g.c', 0, NULL, NULL, '$2y$10$MU4hhwbx2qDQp8J/M7lUSOiU/q0mPfwfmz3bZ6lVBc5Ofymo41fmu', NULL, 306),
(307, 'no51fuff11u', 1, 0, 'asdfgg5tudftdddtg@g.c', 0, NULL, NULL, '$2y$10$eIIdO34JhpW4hlmrssn7e.ZvnuQLph8yN/IhZAnEb7nzv/ZvgpjuG', NULL, 307),
(308, 'no1fuff11u', 1, 0, 'asdfgg5dftdddtg@g.c', 0, NULL, NULL, '$2y$10$UP.z8IaCnMC5lZRAvVy/tetjg4ql5LLXubwIzZwrnY7QGE8Vi4vGu', NULL, 308),
(313, 'no1fuff11uy', 1, 0, 'asdfgg5dftdddtyg@g.c', 0, NULL, NULL, '$2y$10$mxcd133mEJ7PuG2Ctjqs4e5E1skwi5kApWPhuB.0eiZNCFfOXipg.', NULL, 313),
(321, 'nano3', 1, 0, 'nano3@g.c', 1, 'Nano III', NULL, '$2y$10$BrmMGV6U0eqsDipgeEhKjeqAeWWuTPfRNLVODR04Jg1DBKiLJOXZO', NULL, 90),
(322, 'nano4', 1, 0, 'nano4@g.c', 0, NULL, NULL, '$2y$10$Jf4Ne9pxFP4eSZUysP8bR.QkyMAI3QXGVsubQQm4AV9p/XEHDPaQC', NULL, 90),
(323, 'maerik', 1, 0, 'putazo@g.c', 0, NULL, 'XXX', '$2y$10$vkbU4FHYT7jYzce4xFlqZesflLr/030EXsBBUPOlR03RKfRH/XG6m', NULL, 323),
(324, 'simplon', 1, 0, 'simplon@g.c', 0, NULL, NULL, '$2y$10$XBd2ZIJgqIVRPDxi8.6Rzu8nQfDdHZpfMP//.bj5o3NwQOG4fkHYu', NULL, 324),
(327, 'xxx', 0, 0, 'xxx@g.c', 1, 'yyy', 'ZZ', '$2y$10$k/GsKcFuucJ/MvfBTtUBS.oBY7450u.o0M0gEDGI.Aduz9RctrddW', NULL, 90),
(328, 'juanjo', 0, 0, 'jjjjj@g.c', 0, NULL, NULL, '$2y$10$kOyfUThkzFnWzsU1Ts6EguIUANb9ObJ.lQaflYZCNgsj10Ey90AcS', NULL, 328),
(329, 'jj', 0, 0, 'jj@g.c', 0, NULL, NULL, '$2y$10$UoNz79u8eEQ.n4j4DM0Q5.Xu2nKSqvDQKvJlqInLmffBB4RLCwcAK', NULL, 329);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_roles`
--

CREATE TABLE `user_roles` (
  `id` int(11) NOT NULL,
  `belongs_to` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `modification_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `user_roles`
--

INSERT INTO `user_roles` (`id`, `belongs_to`, `role_id`, `created_at`, `modification_date`) VALUES
(1, 5, 2, '2019-10-18 21:58:10', '2019-10-18 21:58:10'),
(2, 1, 100, '2019-10-18 21:58:10', '2019-10-18 21:58:10'),
(3, 4, 100, '2019-10-18 21:58:10', '2019-10-18 21:58:10'),
(5, 9, 100, '2019-10-18 21:58:10', '2019-10-18 21:58:10'),
(6, 11, 100, '2019-10-18 21:58:10', '2019-10-18 21:58:10'),
(7, 13, 100, '2019-10-18 21:58:10', '2019-10-18 21:58:10'),
(9, 4, 2, '2019-10-18 21:58:10', '2019-10-18 21:58:10'),
(10, 48, 2, '2019-10-18 21:58:10', '2019-10-18 21:58:10'),
(11, 86, 2, '2019-10-18 21:58:10', '2019-10-18 21:58:10'),
(12, 86, 3, '2019-10-18 21:58:10', '2019-10-18 21:58:10'),
(14, 86, 100, '2019-10-18 21:58:10', '2019-10-18 21:58:10'),
(15, 87, 3, '2019-10-18 21:58:10', '2019-10-18 21:58:10'),
(16, 89, 3, '2019-10-18 21:58:10', '2019-10-18 21:58:10'),
(17, 4, 3, '2019-10-18 21:58:10', '2019-10-18 21:58:10'),
(18, 1, 3, '2019-10-18 21:58:10', '2019-10-18 21:58:10'),
(20, 90, 3, '2019-10-18 21:58:10', '2019-10-18 21:58:10'),
(21, 85, 2, '2019-10-18 21:58:10', '2019-10-18 21:58:10'),
(22, 48, 3, '2019-10-18 21:58:10', '2019-10-18 21:58:10'),
(23, 105, 2, '2019-10-18 22:12:34', NULL),
(24, 106, 2, '2019-10-19 11:06:17', NULL),
(25, 107, 3, '2019-10-19 11:31:55', NULL),
(26, 108, 3, '2019-10-19 11:37:13', NULL),
(35, 113, 3, '2019-10-24 11:34:25', NULL),
(36, 112, 3, '2019-10-26 19:47:48', NULL),
(40, 122, 3, '2019-11-02 15:49:04', NULL),
(41, 123, 3, '2019-11-02 15:53:03', NULL),
(42, 124, 3, '2019-11-02 16:03:44', NULL),
(43, 125, 3, '2019-11-02 16:05:58', NULL),
(44, 129, 3, '2019-11-03 23:46:30', NULL),
(45, 130, 3, '2019-11-03 23:49:30', NULL),
(46, 131, 3, '2019-11-03 23:50:11', NULL),
(47, 132, 3, '2019-11-03 23:55:09', NULL),
(48, 133, 2, '2019-11-04 17:05:47', NULL),
(49, 135, 3, '2019-11-05 21:04:03', NULL),
(50, 136, 3, '2019-11-07 00:54:05', NULL),
(51, 137, 3, '2019-11-07 22:57:33', NULL),
(53, 139, 3, '2019-11-08 20:43:38', NULL),
(54, 140, 3, '2019-11-09 10:46:12', NULL),
(55, 141, 3, '2019-11-09 10:46:39', NULL),
(59, 147, 3, '2019-11-09 21:18:10', NULL),
(62, 149, 3, '2019-11-10 00:45:45', NULL),
(63, 150, 3, '2019-11-10 00:50:39', NULL),
(64, 151, 3, '2019-11-10 22:03:23', NULL),
(65, 152, 3, '2019-11-10 22:04:17', NULL),
(66, 153, 3, '2019-11-10 22:08:29', NULL),
(67, 154, 3, '2019-11-10 22:08:41', NULL),
(68, 156, 3, '2019-11-11 11:42:16', NULL),
(69, 148, 3, '2019-11-11 11:58:20', NULL),
(70, 159, 2, '2019-11-27 16:45:34', NULL),
(71, 160, 3, '2019-11-27 17:59:13', NULL),
(72, 164, 3, '2019-11-27 23:50:16', NULL),
(73, 165, 3, '2019-11-27 23:53:18', NULL),
(74, 166, 3, '2019-11-27 23:55:28', NULL),
(75, 167, 3, '2019-11-27 23:59:54', NULL),
(76, 168, 3, '2019-11-30 11:12:33', NULL),
(78, 170, 3, '2019-12-04 09:17:45', NULL),
(79, 223, 3, '2019-12-06 10:36:42', NULL),
(80, 224, 3, '2019-12-06 10:49:24', NULL),
(81, 225, 3, '2019-12-06 11:08:08', NULL),
(82, 226, 3, '2019-12-06 11:14:26', NULL),
(111, 301, 3, '2019-12-17 14:00:39', NULL),
(112, 302, 3, '2019-12-17 14:01:06', NULL),
(113, 303, 3, '2019-12-17 14:02:01', NULL),
(114, 304, 3, '2019-12-17 14:03:04', NULL),
(115, 305, 3, '2019-12-17 14:04:26', NULL),
(116, 306, 3, '2019-12-17 14:05:47', NULL),
(117, 307, 3, '2019-12-17 14:10:53', NULL),
(118, 308, 3, '2019-12-17 14:13:20', NULL),
(119, 313, 3, '2019-12-22 14:27:28', NULL),
(123, 323, 3, '2020-01-15 21:56:45', NULL),
(129, 321, 2, '2020-01-16 16:14:04', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `emails`
--
ALTER TABLE `emails`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `folders`
--
ALTER TABLE `folders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `resource_table` (`tb`,`name`,`belongs_to`),
  ADD KEY `owner` (`belongs_to`);

--
-- Indices de la tabla `foo`
--
ALTER TABLE `foo`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `group_permissions`
--
ALTER TABLE `group_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `folder_id` (`folder_id`,`member`),
  ADD KEY `member` (`member`),
  ADD KEY `belongs_to` (`belongs_to`);

--
-- Indices de la tabla `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `other_permissions`
--
ALTER TABLE `other_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `folder_id` (`folder_id`),
  ADD KEY `belongs_to` (`belongs_to`);

--
-- Indices de la tabla `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_table` (`tb`,`user_id`) USING BTREE,
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`belongs_to`),
  ADD KEY `created_by_2` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indices de la tabla `super_cool_table`
--
ALTER TABLE `super_cool_table`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `belongs_to` (`belongs_to`);

--
-- Indices de la tabla `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id_2` (`belongs_to`,`role_id`),
  ADD KEY `user_id` (`belongs_to`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `folders`
--
ALTER TABLE `folders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `foo`
--
ALTER TABLE `foo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `group_permissions`
--
ALTER TABLE `group_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT de la tabla `other_permissions`
--
ALTER TABLE `other_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=276;

--
-- AUTO_INCREMENT de la tabla `super_cool_table`
--
ALTER TABLE `super_cool_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=330;

--
-- AUTO_INCREMENT de la tabla `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `emails`
--
ALTER TABLE `emails`
  ADD CONSTRAINT `fk_emails_users1` FOREIGN KEY (`user_id`) REFERENCES `superpos`.`users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `folders`
--
ALTER TABLE `folders`
  ADD CONSTRAINT `folders_ibfk_1` FOREIGN KEY (`belongs_to`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `group_permissions`
--
ALTER TABLE `group_permissions`
  ADD CONSTRAINT `group_permissions_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`),
  ADD CONSTRAINT `group_permissions_ibfk_2` FOREIGN KEY (`member`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `group_permissions_ibfk_3` FOREIGN KEY (`belongs_to`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `other_permissions`
--
ALTER TABLE `other_permissions`
  ADD CONSTRAINT `other_permissions_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`),
  ADD CONSTRAINT `other_permissions_ibfk_2` FOREIGN KEY (`belongs_to`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `permissions`
--
ALTER TABLE `permissions`
  ADD CONSTRAINT `user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`belongs_to`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
