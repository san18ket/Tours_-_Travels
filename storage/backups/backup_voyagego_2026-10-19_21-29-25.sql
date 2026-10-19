-- VoyageGo Database Backup
-- Created: 2026-10-19 21:29:25
-- Host: 127.0.0.1

SET FOREIGN_KEY_CHECKS=0;

-- Table structure for `audit_logs`
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_audit_entity` (`entity_type`,`entity_id`),
  KEY `idx_audit_user` (`user_id`),
  KEY `idx_audit_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `audit_logs`
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('1', '1', 'customer_registered', 'customers', '1', NULL, '{\"customer_code\":\"VG-CUST-0001\",\"status\":\"active\",\"created_by\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('2', '1', 'booking_created', 'bookings', '1', NULL, '{\"booking_code\":\"VG-2026-5AFF2E\",\"total_amount\":9975}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('3', '1', 'employee_created', 'employees', '1', NULL, '{\"code\":\"VG-EMP-0001\",\"name\":\"Virat Kholi\",\"department\":\"fleet_chauffeur\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('4', '1', 'vehicle_created', 'vehicles', '1', NULL, '{\"vehicle_number\":\"MH40AL1891\",\"make_model\":\"BUS\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('5', NULL, 'booking_created', 'bookings', '2', NULL, '{\"booking_code\":\"VG-2026-55DA80\",\"total_amount\":15750}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('6', NULL, 'invoice_issued', 'invoices', '1', NULL, '{\"invoice_number\":\"INV-2026-00102\",\"total_amount\":\"15750.00\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('7', NULL, 'payment_received', 'payments', '1', NULL, '{\"invoice_id\":1,\"amount\":5000,\"method\":\"upi\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('8', '1', 'auth_login_success', 'users', '1', NULL, '{\"username\":\"owner\",\"email\":\"owner@voyagego.com\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('9', '3', 'auth_login_success', 'users', '3', NULL, '{\"username\":\"admin\",\"email\":\"admin@voyagego.com\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('10', '3', 'auth_login_success', 'users', '3', NULL, '{\"username\":\"admin\",\"email\":\"admin@voyagego.com\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('11', '1', 'booking_created', 'bookings', '3', NULL, '{\"booking_code\":\"VG-2026-2E039F\",\"total_amount\":15750}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('12', '1', 'booking_created', 'bookings', '4', NULL, '{\"booking_code\":\"VG-2026-43CFAC\",\"total_amount\":23100}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('13', '1', 'dispatch_assigned', 'dispatches', '1', NULL, '{\"booking_id\":4,\"vehicle_id\":4,\"driver_id\":1}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('14', '1', 'trip_accepted_by_driver', 'dispatches', '1', NULL, NULL, '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('15', '1', 'dispatch_cancelled', 'dispatches', '1', '{\"status\":\"driver_accepted\"}', '{\"status\":\"cancelled\",\"reason\":\"VIP itinerary modified\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('16', NULL, 'user_created', 'users', '9', NULL, '{\"name\":\"Custom Executive\",\"username\":\"custom.exec\",\"email\":\"custom.exec@voyagego.com\",\"role\":\"dispatcher\",\"status\":\"active\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('17', NULL, 'booking_created', 'bookings', '5', NULL, '{\"booking_code\":\"VG-2026-84639E\",\"total_amount\":15750}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('18', NULL, 'invoice_issued', 'invoices', '2', NULL, '{\"invoice_number\":\"INV-2026-00105\",\"total_amount\":\"15750.00\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('19', NULL, 'payment_received', 'payments', '2', NULL, '{\"invoice_id\":2,\"amount\":5000,\"method\":\"upi\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('20', '1', 'auth_login_success', 'users', '1', NULL, '{\"username\":\"owner\",\"email\":\"owner@voyagego.com\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('21', '3', 'auth_login_success', 'users', '3', NULL, '{\"username\":\"admin\",\"email\":\"admin@voyagego.com\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('22', '3', 'auth_login_success', 'users', '3', NULL, '{\"username\":\"admin\",\"email\":\"admin@voyagego.com\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('23', '1', 'booking_created', 'bookings', '6', NULL, '{\"booking_code\":\"VG-2026-B112CB\",\"total_amount\":15750}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('24', '1', 'booking_created', 'bookings', '7', NULL, '{\"booking_code\":\"VG-2026-70E926\",\"total_amount\":23100}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('25', '1', 'dispatch_assigned', 'dispatches', '2', NULL, '{\"booking_id\":7,\"vehicle_id\":7,\"driver_id\":2}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('26', '1', 'trip_accepted_by_driver', 'dispatches', '2', NULL, NULL, '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('27', '1', 'dispatch_cancelled', 'dispatches', '2', '{\"status\":\"driver_accepted\"}', '{\"status\":\"cancelled\",\"reason\":\"VIP itinerary modified\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('28', NULL, 'booking_created', 'bookings', '8', NULL, '{\"booking_code\":\"VG-2026-552D5D\",\"total_amount\":15750}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('29', NULL, 'invoice_issued', 'invoices', '3', NULL, '{\"invoice_number\":\"INV-2026-00108\",\"total_amount\":\"15750.00\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('30', NULL, 'payment_received', 'payments', '3', NULL, '{\"invoice_id\":3,\"amount\":5000,\"method\":\"upi\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('31', '1', 'auth_login_success', 'users', '1', NULL, '{\"username\":\"owner\",\"email\":\"owner@voyagego.com\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('32', '3', 'auth_login_success', 'users', '3', NULL, '{\"username\":\"admin\",\"email\":\"admin@voyagego.com\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('33', '3', 'auth_login_success', 'users', '3', NULL, '{\"username\":\"admin\",\"email\":\"admin@voyagego.com\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('34', '1', 'booking_created', 'bookings', '9', NULL, '{\"booking_code\":\"VG-2026-B7E4A4\",\"total_amount\":15750}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('35', '1', 'booking_created', 'bookings', '10', NULL, '{\"booking_code\":\"VG-2026-A9CB3C\",\"total_amount\":23100}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('36', '1', 'dispatch_assigned', 'dispatches', '3', NULL, '{\"booking_id\":10,\"vehicle_id\":10,\"driver_id\":3}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('37', '1', 'trip_accepted_by_driver', 'dispatches', '3', NULL, NULL, '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('38', '1', 'dispatch_cancelled', 'dispatches', '3', '{\"status\":\"driver_accepted\"}', '{\"status\":\"cancelled\",\"reason\":\"VIP itinerary modified\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('39', NULL, 'role_created', 'roles', '5', NULL, '{\"name\":\"VIP Concierge Executive\",\"slug\":\"vip_concierge_executive\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('40', NULL, 'booking_created', 'bookings', '11', NULL, '{\"booking_code\":\"VG-2026-A7B024\",\"total_amount\":15750}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('41', NULL, 'invoice_issued', 'invoices', '4', NULL, '{\"invoice_number\":\"INV-2026-00111\",\"total_amount\":\"15750.00\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('42', NULL, 'payment_received', 'payments', '4', NULL, '{\"invoice_id\":4,\"amount\":5000,\"method\":\"upi\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('43', '1', 'auth_login_success', 'users', '1', NULL, '{\"username\":\"owner\",\"email\":\"owner@voyagego.com\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('44', '3', 'auth_login_success', 'users', '3', NULL, '{\"username\":\"admin\",\"email\":\"admin@voyagego.com\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('45', '3', 'auth_login_success', 'users', '3', NULL, '{\"username\":\"admin\",\"email\":\"admin@voyagego.com\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('46', '1', 'booking_created', 'bookings', '12', NULL, '{\"booking_code\":\"VG-2026-302852\",\"total_amount\":15750}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('47', '1', 'booking_created', 'bookings', '13', NULL, '{\"booking_code\":\"VG-2026-BC8DA6\",\"total_amount\":23100}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('48', '1', 'dispatch_assigned', 'dispatches', '4', NULL, '{\"booking_id\":13,\"vehicle_id\":13,\"driver_id\":4}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('49', '1', 'trip_accepted_by_driver', 'dispatches', '4', NULL, NULL, '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('50', '1', 'dispatch_cancelled', 'dispatches', '4', '{\"status\":\"driver_accepted\"}', '{\"status\":\"cancelled\",\"reason\":\"VIP itinerary modified\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('51', '1', 'role_created', 'roles', '6', NULL, '{\"name\":\"driver\",\"slug\":\"driver_09a5\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('52', NULL, 'booking_created', 'bookings', '14', NULL, '{\"booking_code\":\"VG-2026-63A708\",\"total_amount\":15750}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('53', NULL, 'invoice_issued', 'invoices', '5', NULL, '{\"invoice_number\":\"INV-2026-00114\",\"total_amount\":\"15750.00\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('54', NULL, 'payment_received', 'payments', '5', NULL, '{\"invoice_id\":5,\"amount\":5000,\"method\":\"upi\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('55', '1', 'auth_login_success', 'users', '1', NULL, '{\"username\":\"owner\",\"email\":\"owner@voyagego.com\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('56', '3', 'auth_login_success', 'users', '3', NULL, '{\"username\":\"admin\",\"email\":\"admin@voyagego.com\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('57', '3', 'auth_login_success', 'users', '3', NULL, '{\"username\":\"admin\",\"email\":\"admin@voyagego.com\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('58', '1', 'booking_created', 'bookings', '15', NULL, '{\"booking_code\":\"VG-2026-E0A2F3\",\"total_amount\":15750}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('59', '1', 'booking_created', 'bookings', '16', NULL, '{\"booking_code\":\"VG-2026-7BC57D\",\"total_amount\":23100}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('60', '1', 'dispatch_assigned', 'dispatches', '5', NULL, '{\"booking_id\":16,\"vehicle_id\":16,\"driver_id\":5}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('61', '1', 'trip_accepted_by_driver', 'dispatches', '5', NULL, NULL, '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('62', '1', 'dispatch_cancelled', 'dispatches', '5', '{\"status\":\"driver_accepted\"}', '{\"status\":\"cancelled\",\"reason\":\"VIP itinerary modified\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('63', NULL, 'booking_created', 'bookings', '17', NULL, '{\"booking_code\":\"VG-2026-3422FD\",\"total_amount\":15750}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('64', NULL, 'invoice_issued', 'invoices', '6', NULL, '{\"invoice_number\":\"INV-2026-00117\",\"total_amount\":\"15750.00\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('65', NULL, 'payment_received', 'payments', '6', NULL, '{\"invoice_id\":6,\"amount\":5000,\"method\":\"upi\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('66', '1', 'auth_login_success', 'users', '1', NULL, '{\"username\":\"owner\",\"email\":\"owner@voyagego.com\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('67', '3', 'auth_login_success', 'users', '3', NULL, '{\"username\":\"admin\",\"email\":\"admin@voyagego.com\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('68', '3', 'auth_login_success', 'users', '3', NULL, '{\"username\":\"admin\",\"email\":\"admin@voyagego.com\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('69', '1', 'booking_created', 'bookings', '18', NULL, '{\"booking_code\":\"VG-2026-5636A3\",\"total_amount\":15750}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('70', '1', 'booking_created', 'bookings', '19', NULL, '{\"booking_code\":\"VG-2026-E43036\",\"total_amount\":23100}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('71', '1', 'dispatch_assigned', 'dispatches', '6', NULL, '{\"booking_id\":19,\"vehicle_id\":19,\"driver_id\":6}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('72', '1', 'trip_accepted_by_driver', 'dispatches', '6', NULL, NULL, '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('73', '1', 'dispatch_cancelled', 'dispatches', '6', '{\"status\":\"driver_accepted\"}', '{\"status\":\"cancelled\",\"reason\":\"VIP itinerary modified\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('74', NULL, 'booking_created', 'bookings', '20', NULL, '{\"booking_code\":\"VG-2026-741CA8\",\"total_amount\":15750}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('75', NULL, 'invoice_issued', 'invoices', '7', NULL, '{\"invoice_number\":\"INV-2026-00120\",\"total_amount\":\"15750.00\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('76', NULL, 'payment_received', 'payments', '7', NULL, '{\"invoice_id\":7,\"amount\":5000,\"method\":\"upi\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('77', '1', 'auth_login_success', 'users', '1', NULL, '{\"username\":\"owner\",\"email\":\"owner@voyagego.com\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('78', '3', 'auth_login_success', 'users', '3', NULL, '{\"username\":\"admin\",\"email\":\"admin@voyagego.com\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('79', '3', 'auth_login_success', 'users', '3', NULL, '{\"username\":\"admin\",\"email\":\"admin@voyagego.com\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('80', '1', 'booking_created', 'bookings', '21', NULL, '{\"booking_code\":\"VG-2026-D83D25\",\"total_amount\":15750}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('81', '1', 'booking_created', 'bookings', '22', NULL, '{\"booking_code\":\"VG-2026-C0E6DF\",\"total_amount\":23100}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('82', '1', 'dispatch_assigned', 'dispatches', '7', NULL, '{\"booking_id\":22,\"vehicle_id\":22,\"driver_id\":7}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('83', '1', 'trip_accepted_by_driver', 'dispatches', '7', NULL, NULL, '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('84', '1', 'dispatch_cancelled', 'dispatches', '7', '{\"status\":\"driver_accepted\"}', '{\"status\":\"cancelled\",\"reason\":\"VIP itinerary modified\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('85', '1', 'role_created', 'roles', '11', NULL, '{\"name\":\"VIP Concierge Ops a20a\",\"slug\":\"vip_concierge_ops_a20a\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('86', NULL, 'booking_created', 'bookings', '23', NULL, '{\"booking_code\":\"VG-2026-3733E6\",\"total_amount\":15750}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('87', NULL, 'invoice_issued', 'invoices', '8', NULL, '{\"invoice_number\":\"INV-2026-00123\",\"total_amount\":\"15750.00\"}', '127.0.0.1', '', '0000-00-00 00:00:00');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES ('88', NULL, 'payment_received', 'payments', '8', NULL, '{\"invoice_id\":8,\"amount\":5000,\"method\":\"upi\"}', '127.0.0.1', '', '0000-00-00 00:00:00');

-- Table structure for `bookings`
DROP TABLE IF EXISTS `bookings`;
CREATE TABLE `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_code` varchar(30) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_profile_id` int(11) DEFAULT NULL,
  `tour_id` int(11) DEFAULT NULL,
  `pickup_address` varchar(255) NOT NULL,
  `drop_address` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `pickup_time` time NOT NULL DEFAULT '09:00:00',
  `passenger_count` int(11) NOT NULL DEFAULT 1,
  `special_requests` text DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','assigned','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending',
  `idempotency_token` varchar(64) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `booking_code` (`booking_code`),
  UNIQUE KEY `idempotency_token` (`idempotency_token`),
  KEY `idx_booking_status` (`status`),
  KEY `idx_booking_customer` (`customer_id`),
  KEY `idx_booking_dates` (`start_date`,`end_date`),
  KEY `fk_booking_tour` (`tour_id`),
  KEY `idx_booking_customer_profile` (`customer_profile_id`),
  CONSTRAINT `fk_booking_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_booking_customer_profile` FOREIGN KEY (`customer_profile_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_booking_tour` FOREIGN KEY (`tour_id`) REFERENCES `tours` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `bookings`
INSERT INTO `bookings` (`id`, `booking_code`, `customer_id`, `customer_profile_id`, `tour_id`, `pickup_address`, `drop_address`, `start_date`, `end_date`, `pickup_time`, `passenger_count`, `special_requests`, `subtotal`, `tax_amount`, `discount_amount`, `total_amount`, `status`, `idempotency_token`, `created_at`, `updated_at`) VALUES ('1', 'VG-2026-5AFF2E', NULL, '1', NULL, 'nagpur', 'mumbai', '2026-10-20', '2026-10-23', '08:00:00', '20', '', '10000.00', '475.00', '500.00', '9975.00', 'pending', 'db419a6a8d77b3f4186edc9b3a3a17a7', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `bookings` (`id`, `booking_code`, `customer_id`, `customer_profile_id`, `tour_id`, `pickup_address`, `drop_address`, `start_date`, `end_date`, `pickup_time`, `passenger_count`, `special_requests`, `subtotal`, `tax_amount`, `discount_amount`, `total_amount`, `status`, `idempotency_token`, `created_at`, `updated_at`) VALUES ('2', 'VG-2026-55DA80', '2', NULL, NULL, 'New Delhi Airport T3', 'Civil Lines, Jaipur', '2026-10-21', '2026-10-24', '09:00:00', '3', NULL, '15000.00', '750.00', '0.00', '15750.00', 'pending', 'test_idemp_6ad633081523b', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `bookings` (`id`, `booking_code`, `customer_id`, `customer_profile_id`, `tour_id`, `pickup_address`, `drop_address`, `start_date`, `end_date`, `pickup_time`, `passenger_count`, `special_requests`, `subtotal`, `tax_amount`, `discount_amount`, `total_amount`, `status`, `idempotency_token`, `created_at`, `updated_at`) VALUES ('5', 'VG-2026-84639E', '2', NULL, NULL, 'New Delhi Airport T3', 'Civil Lines, Jaipur', '2026-10-21', '2026-10-24', '09:00:00', '3', NULL, '15000.00', '750.00', '0.00', '15750.00', 'pending', 'test_idemp_6ad633448adb1', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `bookings` (`id`, `booking_code`, `customer_id`, `customer_profile_id`, `tour_id`, `pickup_address`, `drop_address`, `start_date`, `end_date`, `pickup_time`, `passenger_count`, `special_requests`, `subtotal`, `tax_amount`, `discount_amount`, `total_amount`, `status`, `idempotency_token`, `created_at`, `updated_at`) VALUES ('8', 'VG-2026-552D5D', '2', NULL, NULL, 'New Delhi Airport T3', 'Civil Lines, Jaipur', '2026-10-21', '2026-10-24', '09:00:00', '3', NULL, '15000.00', '750.00', '0.00', '15750.00', 'pending', 'test_idemp_6ad6348ba4b28', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `bookings` (`id`, `booking_code`, `customer_id`, `customer_profile_id`, `tour_id`, `pickup_address`, `drop_address`, `start_date`, `end_date`, `pickup_time`, `passenger_count`, `special_requests`, `subtotal`, `tax_amount`, `discount_amount`, `total_amount`, `status`, `idempotency_token`, `created_at`, `updated_at`) VALUES ('11', 'VG-2026-A7B024', '2', NULL, NULL, 'New Delhi Airport T3', 'Civil Lines, Jaipur', '2026-10-21', '2026-10-24', '09:00:00', '3', NULL, '15000.00', '750.00', '0.00', '15750.00', 'pending', 'test_idemp_6ad637ad0da4b', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `bookings` (`id`, `booking_code`, `customer_id`, `customer_profile_id`, `tour_id`, `pickup_address`, `drop_address`, `start_date`, `end_date`, `pickup_time`, `passenger_count`, `special_requests`, `subtotal`, `tax_amount`, `discount_amount`, `total_amount`, `status`, `idempotency_token`, `created_at`, `updated_at`) VALUES ('14', 'VG-2026-63A708', '2', NULL, NULL, 'New Delhi Airport T3', 'Civil Lines, Jaipur', '2026-10-21', '2026-10-24', '09:00:00', '3', NULL, '15000.00', '750.00', '0.00', '15750.00', 'pending', 'test_idemp_6ad63a5e29b37', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `bookings` (`id`, `booking_code`, `customer_id`, `customer_profile_id`, `tour_id`, `pickup_address`, `drop_address`, `start_date`, `end_date`, `pickup_time`, `passenger_count`, `special_requests`, `subtotal`, `tax_amount`, `discount_amount`, `total_amount`, `status`, `idempotency_token`, `created_at`, `updated_at`) VALUES ('17', 'VG-2026-3422FD', '2', NULL, NULL, 'New Delhi Airport T3', 'Civil Lines, Jaipur', '2026-10-21', '2026-10-24', '09:00:00', '3', NULL, '15000.00', '750.00', '0.00', '15750.00', 'pending', 'test_idemp_6ad63d31dc1df', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `bookings` (`id`, `booking_code`, `customer_id`, `customer_profile_id`, `tour_id`, `pickup_address`, `drop_address`, `start_date`, `end_date`, `pickup_time`, `passenger_count`, `special_requests`, `subtotal`, `tax_amount`, `discount_amount`, `total_amount`, `status`, `idempotency_token`, `created_at`, `updated_at`) VALUES ('20', 'VG-2026-741CA8', '2', NULL, NULL, 'New Delhi Airport T3', 'Civil Lines, Jaipur', '2026-10-21', '2026-10-24', '09:00:00', '3', NULL, '15000.00', '750.00', '0.00', '15750.00', 'pending', 'test_idemp_6ad63e0e2bd0f', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `bookings` (`id`, `booking_code`, `customer_id`, `customer_profile_id`, `tour_id`, `pickup_address`, `drop_address`, `start_date`, `end_date`, `pickup_time`, `passenger_count`, `special_requests`, `subtotal`, `tax_amount`, `discount_amount`, `total_amount`, `status`, `idempotency_token`, `created_at`, `updated_at`) VALUES ('23', 'VG-2026-3733E6', '2', NULL, NULL, 'New Delhi Airport T3', 'Civil Lines, Jaipur', '2026-10-21', '2026-10-24', '09:00:00', '3', NULL, '15000.00', '750.00', '0.00', '15750.00', 'pending', 'test_idemp_6ad63e5d81203', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- Table structure for `customers`
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_code` varchar(30) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(20) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','non_binary','prefer_not_to_say','other') DEFAULT NULL,
  `phone` varchar(30) NOT NULL,
  `alternate_phone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address_line` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) NOT NULL DEFAULT 'India',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `marketing_consent` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_code` (`customer_code`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `idx_customers_phone` (`phone`),
  KEY `idx_customers_email` (`email`),
  KEY `idx_customers_status` (`status`),
  CONSTRAINT `fk_customer_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `customers`
INSERT INTO `customers` (`id`, `customer_code`, `user_id`, `title`, `name`, `date_of_birth`, `gender`, `phone`, `alternate_phone`, `email`, `address_line`, `city`, `state`, `postal_code`, `country`, `status`, `marketing_consent`, `created_at`, `updated_at`) VALUES ('1', 'VG-CUST-0001', NULL, 'Mr', 'Sanket Sidurkar', '2026-10-19', 'male', '8799845236', NULL, NULL, 'New Bhangeaon Khaperkheda Near Sai mandir', 'Nagpur', 'Mahārāshtra', '441102', 'India', 'active', '0', '2026-10-19 20:22:43', '2026-10-19 20:22:43');

-- Table structure for `dispatches`
DROP TABLE IF EXISTS `dispatches`;
CREATE TABLE `dispatches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dispatch_code` varchar(30) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `assigned_by` int(11) NOT NULL,
  `status` enum('assigned','driver_accepted','en_route_pickup','trip_started','completed','cancelled') NOT NULL DEFAULT 'assigned',
  `start_odometer` int(11) DEFAULT NULL,
  `end_odometer` int(11) DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `driver_notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dispatch_code` (`dispatch_code`),
  UNIQUE KEY `booking_id` (`booking_id`),
  KEY `idx_dispatch_status` (`status`),
  KEY `idx_dispatch_driver` (`driver_id`),
  KEY `idx_dispatch_vehicle` (`vehicle_id`),
  KEY `fk_dispatch_assigner` (`assigned_by`),
  CONSTRAINT `fk_dispatch_assigner` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_dispatch_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  CONSTRAINT `fk_dispatch_driver` FOREIGN KEY (`driver_id`) REFERENCES `driver_profiles` (`id`),
  CONSTRAINT `fk_dispatch_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `driver_profiles`
DROP TABLE IF EXISTS `driver_profiles`;
CREATE TABLE `driver_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `license_number` varchar(50) NOT NULL,
  `license_expiry` date NOT NULL,
  `experience_years` int(11) NOT NULL DEFAULT 1,
  `emergency_contact` varchar(30) DEFAULT NULL,
  `duty_status` enum('on_duty','off_duty','on_trip') NOT NULL DEFAULT 'off_duty',
  `rating` decimal(3,2) NOT NULL DEFAULT 5.00,
  `current_latitude` decimal(10,8) DEFAULT NULL,
  `current_longitude` decimal(11,8) DEFAULT NULL,
  `last_gps_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `license_number` (`license_number`),
  UNIQUE KEY `employee_id` (`employee_id`),
  KEY `idx_driver_duty` (`duty_status`),
  CONSTRAINT `fk_driver_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_driver_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `employees`
DROP TABLE IF EXISTS `employees`;
CREATE TABLE `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_code` varchar(30) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(80) NOT NULL,
  `last_name` varchar(80) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `alternate_phone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `department` enum('operations','dispatch','fleet_chauffeur','management','administration','maintenance') NOT NULL DEFAULT 'operations',
  `designation` varchar(100) NOT NULL DEFAULT 'Operations Executive',
  `employment_type` enum('full_time','part_time','contract','probation') NOT NULL DEFAULT 'full_time',
  `joining_date` date NOT NULL,
  `status` enum('active','on_leave','terminated') NOT NULL DEFAULT 'active',
  `emergency_contact` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_code` (`employee_code`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `idx_employee_department` (`department`),
  KEY `idx_employee_status` (`status`),
  KEY `idx_employee_phone` (`phone`),
  CONSTRAINT `fk_employee_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `employees`
INSERT INTO `employees` (`id`, `employee_code`, `user_id`, `first_name`, `last_name`, `phone`, `alternate_phone`, `email`, `department`, `designation`, `employment_type`, `joining_date`, `status`, `emergency_contact`, `address`, `created_at`, `updated_at`) VALUES ('1', 'VG-EMP-0001', NULL, 'Virat', 'Kholi', '8799845236', NULL, 'virat@gmail.com', 'fleet_chauffeur', 'Driver', 'full_time', '2026-10-19', 'active', NULL, 'New Bhangeaon Khaperkheda Near Sai mandir', '2026-10-19 20:25:32', '2026-10-19 20:25:32');

-- Table structure for `gps_logs`
DROP TABLE IF EXISTS `gps_logs`;
CREATE TABLE `gps_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dispatch_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `speed_kmh` decimal(5,2) DEFAULT 0.00,
  `heading_degrees` decimal(5,2) DEFAULT NULL,
  `battery_level` int(11) DEFAULT NULL,
  `recorded_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_gps_dispatch_time` (`dispatch_id`,`recorded_at`),
  KEY `idx_gps_driver` (`driver_id`),
  CONSTRAINT `fk_gps_dispatch` FOREIGN KEY (`dispatch_id`) REFERENCES `dispatches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_gps_driver` FOREIGN KEY (`driver_id`) REFERENCES `driver_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `invoices`
DROP TABLE IF EXISTS `invoices`;
CREATE TABLE `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_profile_id` int(11) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 5.00,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('unpaid','partially_paid','paid') NOT NULL DEFAULT 'unpaid',
  `due_date` date NOT NULL,
  `is_immutable` tinyint(1) NOT NULL DEFAULT 1,
  `issued_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  UNIQUE KEY `booking_id` (`booking_id`),
  KEY `idx_invoice_status` (`payment_status`),
  KEY `idx_invoice_customer` (`customer_id`),
  KEY `idx_invoice_customer_profile` (`customer_profile_id`),
  CONSTRAINT `fk_invoice_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  CONSTRAINT `fk_invoice_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_invoice_customer_profile` FOREIGN KEY (`customer_profile_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `invoices`
INSERT INTO `invoices` (`id`, `invoice_number`, `booking_id`, `customer_id`, `customer_profile_id`, `subtotal`, `tax_rate`, `tax_amount`, `discount_amount`, `total_amount`, `paid_amount`, `payment_status`, `due_date`, `is_immutable`, `issued_at`, `created_at`) VALUES ('1', 'INV-2026-00102', '2', '2', NULL, '15000.00', '5.00', '750.00', '0.00', '15750.00', '5000.00', 'partially_paid', '2026-10-21', '1', '2026-10-19 20:41:04', '0000-00-00 00:00:00');
INSERT INTO `invoices` (`id`, `invoice_number`, `booking_id`, `customer_id`, `customer_profile_id`, `subtotal`, `tax_rate`, `tax_amount`, `discount_amount`, `total_amount`, `paid_amount`, `payment_status`, `due_date`, `is_immutable`, `issued_at`, `created_at`) VALUES ('2', 'INV-2026-00105', '5', '2', NULL, '15000.00', '5.00', '750.00', '0.00', '15750.00', '5000.00', 'partially_paid', '2026-10-21', '1', '2026-10-19 20:42:04', '0000-00-00 00:00:00');
INSERT INTO `invoices` (`id`, `invoice_number`, `booking_id`, `customer_id`, `customer_profile_id`, `subtotal`, `tax_rate`, `tax_amount`, `discount_amount`, `total_amount`, `paid_amount`, `payment_status`, `due_date`, `is_immutable`, `issued_at`, `created_at`) VALUES ('3', 'INV-2026-00108', '8', '2', NULL, '15000.00', '5.00', '750.00', '0.00', '15750.00', '5000.00', 'partially_paid', '2026-10-21', '1', '2026-10-19 20:47:31', '0000-00-00 00:00:00');
INSERT INTO `invoices` (`id`, `invoice_number`, `booking_id`, `customer_id`, `customer_profile_id`, `subtotal`, `tax_rate`, `tax_amount`, `discount_amount`, `total_amount`, `paid_amount`, `payment_status`, `due_date`, `is_immutable`, `issued_at`, `created_at`) VALUES ('4', 'INV-2026-00111', '11', '2', NULL, '15000.00', '5.00', '750.00', '0.00', '15750.00', '5000.00', 'partially_paid', '2026-10-21', '1', '2026-10-19 21:00:53', '0000-00-00 00:00:00');
INSERT INTO `invoices` (`id`, `invoice_number`, `booking_id`, `customer_id`, `customer_profile_id`, `subtotal`, `tax_rate`, `tax_amount`, `discount_amount`, `total_amount`, `paid_amount`, `payment_status`, `due_date`, `is_immutable`, `issued_at`, `created_at`) VALUES ('5', 'INV-2026-00114', '14', '2', NULL, '15000.00', '5.00', '750.00', '0.00', '15750.00', '5000.00', 'partially_paid', '2026-10-21', '1', '2026-10-19 21:12:22', '0000-00-00 00:00:00');
INSERT INTO `invoices` (`id`, `invoice_number`, `booking_id`, `customer_id`, `customer_profile_id`, `subtotal`, `tax_rate`, `tax_amount`, `discount_amount`, `total_amount`, `paid_amount`, `payment_status`, `due_date`, `is_immutable`, `issued_at`, `created_at`) VALUES ('6', 'INV-2026-00117', '17', '2', NULL, '15000.00', '5.00', '750.00', '0.00', '15750.00', '5000.00', 'partially_paid', '2026-10-21', '1', '2026-10-19 21:24:25', '0000-00-00 00:00:00');
INSERT INTO `invoices` (`id`, `invoice_number`, `booking_id`, `customer_id`, `customer_profile_id`, `subtotal`, `tax_rate`, `tax_amount`, `discount_amount`, `total_amount`, `paid_amount`, `payment_status`, `due_date`, `is_immutable`, `issued_at`, `created_at`) VALUES ('7', 'INV-2026-00120', '20', '2', NULL, '15000.00', '5.00', '750.00', '0.00', '15750.00', '5000.00', 'partially_paid', '2026-10-21', '1', '2026-10-19 21:28:06', '0000-00-00 00:00:00');
INSERT INTO `invoices` (`id`, `invoice_number`, `booking_id`, `customer_id`, `customer_profile_id`, `subtotal`, `tax_rate`, `tax_amount`, `discount_amount`, `total_amount`, `paid_amount`, `payment_status`, `due_date`, `is_immutable`, `issued_at`, `created_at`) VALUES ('8', 'INV-2026-00123', '23', '2', NULL, '15000.00', '5.00', '750.00', '0.00', '15750.00', '5000.00', 'partially_paid', '2026-10-21', '1', '2026-10-19 21:29:25', '0000-00-00 00:00:00');

-- Table structure for `payments`
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_reference` varchar(64) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('upi','net_banking','card','cash') NOT NULL DEFAULT 'upi',
  `transaction_id` varchar(100) DEFAULT NULL,
  `status` enum('success','failed','refunded') NOT NULL DEFAULT 'success',
  `idempotency_key` varchar(64) NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `paid_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_reference` (`payment_reference`),
  UNIQUE KEY `idempotency_key` (`idempotency_key`),
  KEY `idx_payment_invoice` (`invoice_id`),
  KEY `idx_payment_status` (`status`),
  CONSTRAINT `fk_payment_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `payments`
INSERT INTO `payments` (`id`, `payment_reference`, `invoice_id`, `amount`, `payment_method`, `transaction_id`, `status`, `idempotency_key`, `notes`, `paid_at`, `created_at`) VALUES ('1', 'PAY-20261019-EA6183', '1', '5000.00', 'upi', 'TXN-6ad633081a0ec', 'success', 'pay_idemp_6ad633081a0e8', 'Advance payment', '2026-10-19 20:41:04', '0000-00-00 00:00:00');
INSERT INTO `payments` (`id`, `payment_reference`, `invoice_id`, `amount`, `payment_method`, `transaction_id`, `status`, `idempotency_key`, `notes`, `paid_at`, `created_at`) VALUES ('2', 'PAY-20261019-97114E', '2', '5000.00', 'upi', 'TXN-6ad633448e54f', 'success', 'pay_idemp_6ad633448e54d', 'Advance payment', '2026-10-19 20:42:04', '0000-00-00 00:00:00');
INSERT INTO `payments` (`id`, `payment_reference`, `invoice_id`, `amount`, `payment_method`, `transaction_id`, `status`, `idempotency_key`, `notes`, `paid_at`, `created_at`) VALUES ('3', 'PAY-20261019-DC512C', '3', '5000.00', 'upi', 'TXN-6ad6348ba89f1', 'success', 'pay_idemp_6ad6348ba89ec', 'Advance payment', '2026-10-19 20:47:31', '0000-00-00 00:00:00');
INSERT INTO `payments` (`id`, `payment_reference`, `invoice_id`, `amount`, `payment_method`, `transaction_id`, `status`, `idempotency_key`, `notes`, `paid_at`, `created_at`) VALUES ('4', 'PAY-20261019-4208AD', '4', '5000.00', 'upi', 'TXN-6ad637ad12810', 'success', 'pay_idemp_6ad637ad1280b', 'Advance payment', '2026-10-19 21:00:53', '0000-00-00 00:00:00');
INSERT INTO `payments` (`id`, `payment_reference`, `invoice_id`, `amount`, `payment_method`, `transaction_id`, `status`, `idempotency_key`, `notes`, `paid_at`, `created_at`) VALUES ('5', 'PAY-20261019-66139D', '5', '5000.00', 'upi', 'TXN-6ad63a5e3029b', 'success', 'pay_idemp_6ad63a5e30297', 'Advance payment', '2026-10-19 21:12:22', '0000-00-00 00:00:00');
INSERT INTO `payments` (`id`, `payment_reference`, `invoice_id`, `amount`, `payment_method`, `transaction_id`, `status`, `idempotency_key`, `notes`, `paid_at`, `created_at`) VALUES ('6', 'PAY-20261019-F32505', '6', '5000.00', 'upi', 'TXN-6ad63d31e2078', 'success', 'pay_idemp_6ad63d31e2072', 'Advance payment', '2026-10-19 21:24:25', '0000-00-00 00:00:00');
INSERT INTO `payments` (`id`, `payment_reference`, `invoice_id`, `amount`, `payment_method`, `transaction_id`, `status`, `idempotency_key`, `notes`, `paid_at`, `created_at`) VALUES ('7', 'PAY-20261019-C02657', '7', '5000.00', 'upi', 'TXN-6ad63e0e2fb44', 'success', 'pay_idemp_6ad63e0e2fb41', 'Advance payment', '2026-10-19 21:28:06', '0000-00-00 00:00:00');
INSERT INTO `payments` (`id`, `payment_reference`, `invoice_id`, `amount`, `payment_method`, `transaction_id`, `status`, `idempotency_key`, `notes`, `paid_at`, `created_at`) VALUES ('8', 'PAY-20261019-572C0E', '8', '5000.00', 'upi', 'TXN-6ad63e5d84896', 'success', 'pay_idemp_6ad63e5d84894', 'Advance payment', '2026-10-19 21:29:25', '0000-00-00 00:00:00');

-- Table structure for `permissions`
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `module` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_permissions_module` (`module`),
  KEY `idx_permissions_action` (`action`)
) ENGINE=InnoDB AUTO_INCREMENT=316 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `permissions`
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('1', 'all_access', 'all', 'view', 'Full administrative command center authority', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('2', 'dashboard.view', 'dashboard', 'view', 'Access Executive Command Center Page', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('3', 'dashboard.refresh', 'dashboard', 'view', 'Poll live updates across all modules', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('4', 'dashboard.export', 'dashboard', 'view', 'Download executive metrics report', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('5', 'dashboard.view_revenue', 'dashboard', 'view', 'Inspect GST invoices and net revenue totals', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('6', 'live_tracking.view', 'live_tracking', 'view', 'Access Real-Time Fleet Live Radar Page', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('7', 'live_tracking.locate_all', 'live_tracking', 'view', 'Poll instant coordinates for all deployed vehicles', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('8', 'live_tracking.inspect_unit', 'live_tracking', 'view', 'Focus camera on specific vehicle telemetry', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('9', 'live_tracking.telemetry_api', 'live_tracking', 'view', 'Authorize telemetry data feed stream', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('10', 'customers.view', 'customers', 'view', 'Access Customer Directory Page', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('11', 'customers.create', 'customers', 'add', 'Register new customer profile', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('12', 'customers.show', 'customers', 'view', 'Inspect client history and contact details', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('13', 'customers.edit', 'customers', 'edit', 'Modify customer name, mobile, and preferences', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('14', 'customers.delete', 'customers', 'delete', 'Decommission customer profile', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('15', 'customers.create_page', 'customers', 'view', 'Access Register New Customer Page', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('16', 'customers.store', 'customers', 'add', 'Commit new customer details into system', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('17', 'bookings.view', 'bookings', 'view', 'Access Reservations Directory Page', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('18', 'bookings.create', 'bookings', 'add', 'Initiate new luxury tour reservation', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('19', 'bookings.show', 'bookings', 'view', 'Inspect full itinerary and billing details', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('20', 'bookings.edit', 'bookings', 'edit', 'Update reservation dates, passenger counts, or pricing', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('21', 'bookings.cancel', 'bookings', 'delete', 'Cancel booking and release reserved units', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('22', 'bookings.invoice', 'bookings', 'view', 'Download and print statutory GST tax invoice', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('23', 'bookings.create_page', 'bookings', 'view', 'Access Create Reservation Page', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('24', 'bookings.store', 'bookings', 'add', 'Save reservation and generate tracking manifest', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('25', 'vehicles.view', 'vehicles', 'view', 'Access Fleet Directory Page', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('26', 'vehicles.create', 'vehicles', 'add', 'Add new luxury transport unit', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('27', 'vehicles.show', 'vehicles', 'view', 'View vehicle history, maintenance, and assigned chauffeur', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('28', 'vehicles.edit', 'vehicles', 'edit', 'Update vehicle model, registration, or specs', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('29', 'vehicles.toggle_status', 'vehicles', 'edit', 'Toggle vehicle between Available and In Maintenance', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('30', 'vehicles.delete', 'vehicles', 'delete', 'Permanently retire vehicle from fleet', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('31', 'vehicles.create_page', 'vehicles', 'view', 'Access Register Vehicle Page', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('32', 'vehicles.store', 'vehicles', 'add', 'Commit new vehicle specs into inventory', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('33', 'employees.view', 'employees', 'view', 'Access Employee Directory Page', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('34', 'employees.create', 'employees', 'add', 'Add new employee or chauffeur profile', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('35', 'employees.show', 'employees', 'view', 'View complete employee dossier', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('36', 'employees.edit', 'employees', 'edit', 'Update employee designation, contact, or salary', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('37', 'employees.toggle_status', 'employees', 'edit', 'Toggle employee between Active and On Leave', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('38', 'employees.delete', 'employees', 'delete', 'Archive personnel record from active directory', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('39', 'employees.create_page', 'employees', 'view', 'Access Onboard Employee Page', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('40', 'employees.store', 'employees', 'add', 'Save employee details into personnel register', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('41', 'trip_operations.assign_page', 'trip_operations', 'view', 'Access Assign Trip Duty Page', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('42', 'trip_operations.store', 'trip_operations', 'add', 'Confirm and dispatch duty assignment', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('43', 'trip_operations.view', 'trip_operations', 'view', 'Access Trip Transit History Page', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('44', 'trip_operations.export', 'trip_operations', 'view', 'Download transit history manifest', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('45', 'trip_operations.show', 'trip_operations', 'view', 'Inspect timestamped dispatch milestones', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('46', 'trip_operations.update_status', 'trip_operations', 'edit', 'Advance trip milestone (Driver Accepted, En Route, Completed)', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('47', 'trip_operations.cancel', 'trip_operations', 'delete', 'Cancel active dispatch and release assigned assets', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('48', 'users.view', 'users', 'view', 'Access Users Directory Page', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('49', 'users.create', 'users', 'add', 'Provision new staff login account', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('50', 'users.show', 'users', 'view', 'Inspect login history and access level', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('51', 'users.edit', 'users', 'edit', 'Update user username, role, or linked employee', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('52', 'users.toggle_status', 'users', 'edit', 'Activate or suspend login access', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('53', 'users.reset_pwd', 'users', 'edit', 'Trigger immediate password reset modal', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('54', 'users.delete', 'users', 'delete', 'Permanently delete user account', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('55', 'users.create_page', 'users', 'view', 'Access Create Login Account Page', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('56', 'users.store', 'users', 'add', 'Save credentials and activate user account', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('57', 'roles.view', 'roles', 'view', 'Access Roles & Permissions Page', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('58', 'roles.create', 'roles', 'add', 'Open modal to create a new custom role', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('59', 'roles.edit', 'roles', 'edit', 'Update role title and scope description', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('60', 'roles.manage_permissions', 'roles', 'edit', 'Launch permission matrix editor for role', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('61', 'roles.delete', 'roles', 'delete', 'Remove custom role (system roles protected)', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('62', 'role_permissions.assign', 'roles', 'edit', 'Open permission checklist matrix', '2026-10-19 21:14:18');
INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `created_at`) VALUES ('63', 'role_permissions.store', 'roles', 'edit', 'Commit role and user permission assignments', '2026-10-19 21:14:18');

-- Table structure for `role_permissions`
DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_role_perm` (`role_id`,`permission_id`),
  KEY `idx_rp_role_id` (`role_id`),
  KEY `idx_rp_permission_id` (`permission_id`)
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `role_permissions`
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('1', '7', '1', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('2', '7', '21', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('3', '7', '18', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('4', '7', '23', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('5', '7', '20', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('6', '7', '22', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('7', '7', '19', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('8', '7', '24', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('9', '7', '17', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('10', '7', '11', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('11', '7', '15', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('12', '7', '14', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('13', '7', '13', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('14', '7', '12', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('15', '7', '16', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('16', '7', '10', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('17', '7', '4', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('18', '7', '3', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('19', '7', '2', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('20', '7', '5', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('21', '7', '34', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('22', '7', '39', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('23', '7', '38', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('24', '7', '36', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('25', '7', '35', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('26', '7', '40', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('27', '7', '37', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('28', '7', '33', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('29', '7', '8', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('30', '7', '7', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('31', '7', '9', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('32', '7', '6', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('33', '7', '62', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('34', '7', '63', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('35', '7', '58', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('36', '7', '61', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('37', '7', '59', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('38', '7', '60', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('39', '7', '57', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('40', '7', '41', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('41', '7', '47', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('42', '7', '44', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('43', '7', '45', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('44', '7', '42', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('45', '7', '46', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('46', '7', '43', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('47', '7', '49', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('48', '7', '55', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('49', '7', '54', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('50', '7', '51', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('51', '7', '53', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('52', '7', '50', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('53', '7', '56', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('54', '7', '52', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('55', '7', '48', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('56', '7', '26', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('57', '7', '31', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('58', '7', '30', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('59', '7', '28', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('60', '7', '27', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('61', '7', '32', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('62', '7', '29', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('63', '7', '25', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('64', '8', '2', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('65', '8', '3', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('66', '8', '4', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('67', '8', '5', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('68', '8', '6', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('69', '8', '7', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('70', '8', '8', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('71', '8', '9', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('72', '8', '10', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('73', '8', '11', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('74', '8', '15', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('75', '8', '16', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('76', '8', '12', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('77', '8', '17', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('78', '8', '18', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('79', '8', '23', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('80', '8', '24', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('81', '8', '19', '2026-10-19 21:15:04');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('82', '8', '20', '2026-10-19 21:15:05');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('83', '8', '22', '2026-10-19 21:15:05');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('84', '8', '25', '2026-10-19 21:15:05');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('85', '8', '27', '2026-10-19 21:15:05');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('86', '8', '33', '2026-10-19 21:15:05');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('87', '8', '35', '2026-10-19 21:15:05');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('88', '8', '41', '2026-10-19 21:15:05');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('89', '8', '42', '2026-10-19 21:15:05');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('90', '8', '43', '2026-10-19 21:15:05');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('91', '8', '45', '2026-10-19 21:15:05');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('92', '8', '46', '2026-10-19 21:15:05');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('93', '8', '47', '2026-10-19 21:15:05');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('94', '8', '44', '2026-10-19 21:15:05');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('95', '9', '6', '2026-10-19 21:15:05');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('96', '9', '43', '2026-10-19 21:15:05');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('97', '9', '45', '2026-10-19 21:15:05');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('98', '9', '46', '2026-10-19 21:15:05');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('99', '10', '17', '2026-10-19 21:15:05');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('100', '10', '18', '2026-10-19 21:15:05');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('101', '10', '23', '2026-10-19 21:15:05');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('102', '10', '24', '2026-10-19 21:15:05');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('103', '10', '19', '2026-10-19 21:15:05');
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES ('104', '10', '22', '2026-10-19 21:15:05');

-- Table structure for `roles`
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `slug` varchar(80) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_roles_slug` (`slug`),
  KEY `idx_roles_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `roles`
INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `is_system`, `status`, `created_at`, `updated_at`) VALUES ('6', 'driver', 'driver_09a5', 'sdfsdfsd', '0', 'active', '2026-10-19 21:01:46', '2026-10-19 21:01:46');
INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `is_system`, `status`, `created_at`, `updated_at`) VALUES ('7', 'Administrator', 'admin', 'Full command center authority, system settings, and administrative management', '1', 'active', '2026-10-19 21:15:04', '2026-10-19 21:15:04');
INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `is_system`, `status`, `created_at`, `updated_at`) VALUES ('8', 'Dispatcher', 'dispatcher', 'Trip operations dispatch, chauffeur duty deployment, and fleet live radar', '1', 'active', '2026-10-19 21:15:04', '2026-10-19 21:15:04');
INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `is_system`, `status`, `created_at`, `updated_at`) VALUES ('9', 'Chauffeur / Driver', 'driver', 'Mobile chauffeur duty portal, trip manifests, and transit execution', '1', 'active', '2026-10-19 21:15:04', '2026-10-19 21:15:04');
INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `is_system`, `status`, `created_at`, `updated_at`) VALUES ('10', 'Customer', 'customer', 'Verified guest client portal, reservation booking, and invoice receipts', '1', 'active', '2026-10-19 21:15:04', '2026-10-19 21:15:04');
INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `is_system`, `status`, `created_at`, `updated_at`) VALUES ('11', 'VIP Concierge Ops a20a', 'vip_concierge_ops_a20a', 'Dedicated luxury itinerary and chauffeur coordination', '0', 'active', '2026-10-19 21:29:18', '2026-10-19 21:29:18');

-- Table structure for `schema_migrations`
DROP TABLE IF EXISTS `schema_migrations`;
CREATE TABLE `schema_migrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'success',
  `executed_at` datetime NOT NULL,
  `execution_time_ms` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `migration` (`migration`),
  KEY `idx_batch` (`batch`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `schema_migrations`
INSERT INTO `schema_migrations` (`id`, `migration`, `batch`, `status`, `executed_at`, `execution_time_ms`) VALUES ('1', '001_create_users_table.php', '1', 'success', '2026-10-19 20:22:15', '13');
INSERT INTO `schema_migrations` (`id`, `migration`, `batch`, `status`, `executed_at`, `execution_time_ms`) VALUES ('2', '002_create_fleet_and_drivers_table.php', '1', 'success', '2026-10-19 20:22:15', '27');
INSERT INTO `schema_migrations` (`id`, `migration`, `batch`, `status`, `executed_at`, `execution_time_ms`) VALUES ('3', '003_create_tours_table.php', '1', 'success', '2026-10-19 20:22:15', '16');
INSERT INTO `schema_migrations` (`id`, `migration`, `batch`, `status`, `executed_at`, `execution_time_ms`) VALUES ('4', '004_create_bookings_table.php', '1', 'success', '2026-10-19 20:22:15', '21');
INSERT INTO `schema_migrations` (`id`, `migration`, `batch`, `status`, `executed_at`, `execution_time_ms`) VALUES ('5', '005_create_dispatches_table.php', '1', 'success', '2026-10-19 20:22:15', '16');
INSERT INTO `schema_migrations` (`id`, `migration`, `batch`, `status`, `executed_at`, `execution_time_ms`) VALUES ('6', '006_create_invoices_and_payments_table.php', '1', 'success', '2026-10-19 20:22:15', '27');
INSERT INTO `schema_migrations` (`id`, `migration`, `batch`, `status`, `executed_at`, `execution_time_ms`) VALUES ('7', '007_create_gps_logs_table.php', '1', 'success', '2026-10-19 20:22:15', '12');
INSERT INTO `schema_migrations` (`id`, `migration`, `batch`, `status`, `executed_at`, `execution_time_ms`) VALUES ('8', '008_create_audit_logs_table.php', '1', 'success', '2026-10-19 20:22:15', '14');
INSERT INTO `schema_migrations` (`id`, `migration`, `batch`, `status`, `executed_at`, `execution_time_ms`) VALUES ('9', '009_add_username_to_users_table.php', '1', 'success', '2026-10-19 20:22:16', '511');
INSERT INTO `schema_migrations` (`id`, `migration`, `batch`, `status`, `executed_at`, `execution_time_ms`) VALUES ('10', '010_create_vehicle_maintenance_table.php', '1', 'success', '2026-10-19 20:22:16', '11');
INSERT INTO `schema_migrations` (`id`, `migration`, `batch`, `status`, `executed_at`, `execution_time_ms`) VALUES ('11', '011_create_trip_expenses_table.php', '1', 'success', '2026-10-19 20:22:16', '10');
INSERT INTO `schema_migrations` (`id`, `migration`, `batch`, `status`, `executed_at`, `execution_time_ms`) VALUES ('12', '012_create_customers_table.php', '1', 'success', '2026-10-19 20:22:16', '227');
INSERT INTO `schema_migrations` (`id`, `migration`, `batch`, `status`, `executed_at`, `execution_time_ms`) VALUES ('13', '013_remove_travel_profile_and_preferred_channel_from_customers.php', '1', 'success', '2026-10-19 20:22:16', '117');
INSERT INTO `schema_migrations` (`id`, `migration`, `batch`, `status`, `executed_at`, `execution_time_ms`) VALUES ('14', '014_create_employees_table.php', '1', 'success', '2026-10-19 20:22:16', '104');
INSERT INTO `schema_migrations` (`id`, `migration`, `batch`, `status`, `executed_at`, `execution_time_ms`) VALUES ('15', '015_add_employee_id_to_driver_profiles.php', '1', 'success', '2026-10-19 20:22:17', '16');
INSERT INTO `schema_migrations` (`id`, `migration`, `batch`, `status`, `executed_at`, `execution_time_ms`) VALUES ('16', '016_create_trip_status_history_table.php', '1', 'success', '2026-10-19 20:22:17', '18');
INSERT INTO `schema_migrations` (`id`, `migration`, `batch`, `status`, `executed_at`, `execution_time_ms`) VALUES ('17', '017_create_roles_table.php', '2', 'success', '2026-10-19 20:55:39', '63');
INSERT INTO `schema_migrations` (`id`, `migration`, `batch`, `status`, `executed_at`, `execution_time_ms`) VALUES ('18', '018_create_permissions_and_role_permissions_tables.php', '3', 'success', '2026-10-19 21:14:18', '152');

-- Table structure for `tours`
DROP TABLE IF EXISTS `tours`;
CREATE TABLE `tours` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(150) NOT NULL,
  `title` varchar(200) NOT NULL,
  `category` enum('heritage','hill_station','pilgrimage','wildlife','beach','custom') NOT NULL DEFAULT 'heritage',
  `duration_days` int(11) NOT NULL DEFAULT 1,
  `duration_nights` int(11) NOT NULL DEFAULT 0,
  `pickup_city` varchar(100) NOT NULL,
  `destination_city` varchar(100) NOT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `itinerary_json` text DEFAULT NULL,
  `inclusions` text DEFAULT NULL,
  `exclusions` text DEFAULT NULL,
  `max_passengers` int(11) NOT NULL DEFAULT 10,
  `image_url` varchar(255) DEFAULT NULL,
  `status` enum('published','draft','archived') NOT NULL DEFAULT 'published',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_tour_category` (`category`),
  KEY `idx_tour_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `trip_expenses`
DROP TABLE IF EXISTS `trip_expenses`;
CREATE TABLE `trip_expenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dispatch_id` int(11) NOT NULL,
  `expense_category` enum('fuel','toll','parking','driver_allowance','permit','maintenance','misc') NOT NULL DEFAULT 'fuel',
  `amount` decimal(10,2) NOT NULL,
  `receipt_number` varchar(50) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `logged_by` int(11) NOT NULL,
  `incurred_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_expense_dispatch` (`dispatch_id`),
  KEY `idx_expense_category` (`expense_category`),
  KEY `fk_expense_logger` (`logged_by`),
  CONSTRAINT `fk_expense_dispatch` FOREIGN KEY (`dispatch_id`) REFERENCES `dispatches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_expense_logger` FOREIGN KEY (`logged_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `trip_status_history`
DROP TABLE IF EXISTS `trip_status_history`;
CREATE TABLE `trip_status_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dispatch_id` int(11) NOT NULL,
  `from_status` varchar(50) DEFAULT NULL,
  `to_status` varchar(50) NOT NULL,
  `changed_by_user_id` int(11) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tsh_dispatch` (`dispatch_id`),
  KEY `idx_tsh_created` (`created_at`),
  KEY `fk_tsh_user` (`changed_by_user_id`),
  CONSTRAINT `fk_tsh_dispatch` FOREIGN KEY (`dispatch_id`) REFERENCES `dispatches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tsh_user` FOREIGN KEY (`changed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `user_permissions`
DROP TABLE IF EXISTS `user_permissions`;
CREATE TABLE `user_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `is_granted` tinyint(1) NOT NULL DEFAULT 1,
  `expires_at` datetime DEFAULT NULL,
  `max_usage_count` int(11) DEFAULT NULL,
  `usage_count` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_perm` (`user_id`,`permission_id`),
  KEY `idx_up_user_id` (`user_id`),
  KEY `idx_up_permission_id` (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `users`
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'customer',
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `idx_users_username` (`username`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `users`
INSERT INTO `users` (`id`, `name`, `username`, `email`, `phone`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES ('1', 'System Owner', 'owner', 'owner@voyagego.com', '+91 98000 00000', '$2y$12$Q0BkcO/w.6RTE15PbF/JFO.hzdF19TCe.mC/opOwt.srEMvIN7a9S', 'admin', 'active', '2026-10-19 20:22:16', '2026-10-19 20:22:16');
INSERT INTO `users` (`id`, `name`, `username`, `email`, `phone`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES ('2', 'Test Customer', '', 'test.cust.6ad63308144b1@example.com', NULL, 'secret', 'customer', 'active', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `users` (`id`, `name`, `username`, `email`, `phone`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES ('3', 'Aditya Singhania', 'admin', 'admin@voyagego.com', '+91 98111 22334', '$2y$12$tedl8KgINACJpthrVIW/SecV1OcHZvKV.s/qXGWVIJ34t1KKscKdy', 'admin', 'active', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `users` (`id`, `name`, `username`, `email`, `phone`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES ('4', 'Operations Lead', 'ops.lead.6ad6330a1dff4', 'ops.lead.6ad6330a1dff6@voyagego.com', '+91 99999 11111', '$2y$12$YgvtVjj/5jgsvdeyWt27Ue1TS7Ed4ZswZL1s6oXYB6n2GRkcLn2me', 'dispatcher', 'active', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `users` (`id`, `name`, `username`, `email`, `phone`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES ('7', 'Temp Admin', 'temp_admin_6ad6330b8bc3f', 'temp_admin_6ad6330b8bc42@voyagego.com', NULL, '$2y$10$2rtPqzaLbZ6IOa6BAhCOLuEYPs88dxugkX8zzL94hTUwkye2KoCFu', 'admin', 'active', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `users` (`id`, `name`, `username`, `email`, `phone`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES ('10', 'Operations Lead', 'ops.lead.6ad6334665de8', 'ops.lead.6ad6334665dea@voyagego.com', '+91 99999 11111', '$2y$12$.Bi3TQwANrK8x5mFW2qgbeSVprcfkGBjxRRcfbNs4fKeAXWREvdYa', 'dispatcher', 'active', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `users` (`id`, `name`, `username`, `email`, `phone`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES ('13', 'Temp Admin', 'temp_admin_6ad63347c50fc', 'temp_admin_6ad63347c5103@voyagego.com', NULL, '$2y$10$9OEPpZSesOKBBDgEBfxLQeUkMb2dj5wo4DvCWn3RsysyKoMWLGUDW', 'admin', 'active', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `users` (`id`, `name`, `username`, `email`, `phone`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES ('15', 'Operations Lead', 'ops.lead.6ad6348d81b97', 'ops.lead.6ad6348d81b99@voyagego.com', '+91 99999 11111', '$2y$12$iZuBxFUSp7yzAn5io4DpFuxmYMyXlW7Dd3sFlfze20upOFCie/Boy', 'dispatcher', 'active', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `users` (`id`, `name`, `username`, `email`, `phone`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES ('18', 'Temp Admin', 'temp_admin_6ad6348eecccd', 'temp_admin_6ad6348eeccd0@voyagego.com', NULL, '$2y$10$Z8SMQ1hMJkVDPqe3YpVQfu.tJ5hZoPXqsEAgTgp4I.xY08UKTfPvy', 'admin', 'active', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `users` (`id`, `name`, `username`, `email`, `phone`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES ('20', 'Operations Lead', 'ops.lead.6ad637aecb34e', 'ops.lead.6ad637aecb351@voyagego.com', '+91 99999 11111', '$2y$12$vD8nWu3SoYK.1rN4Om33N.kpH.wtSFy7IeHxIHCRDbQki6uUt57nO', 'dispatcher', 'active', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `users` (`id`, `name`, `username`, `email`, `phone`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES ('23', 'Temp Admin', 'temp_admin_6ad637b049276', 'temp_admin_6ad637b049278@voyagego.com', NULL, '$2y$10$9v7khNAxqluMsutc2WTSBOawrQF4atkWt9XIev/YakAthnRcKmHI.', 'admin', 'active', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `users` (`id`, `name`, `username`, `email`, `phone`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES ('25', 'Operations Lead', 'ops.lead.6ad63a5ff15d2', 'ops.lead.6ad63a5ff15d5@voyagego.com', '+91 99999 11111', '$2y$12$bAAyEOXy.CGFTQ52TWem2ei7L4ESsmw9POpDTFwOgElS1pnXYw/CS', 'dispatcher', 'active', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `users` (`id`, `name`, `username`, `email`, `phone`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES ('28', 'Temp Admin', 'temp_admin_6ad63a61731fb', 'temp_admin_6ad63a61731fe@voyagego.com', NULL, '$2y$10$HUXDEfkkyFqT79yqhVQsAejZdCHzEdjy3k434DH6Q20E0Lvwd2.32', 'admin', 'active', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `users` (`id`, `name`, `username`, `email`, `phone`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES ('30', 'Operations Lead', 'ops.lead.6ad63d3417504', 'ops.lead.6ad63d3417508@voyagego.com', '+91 99999 11111', '$2y$12$oVMn/DLBkTxOi8uvS4dwjePjO08LCPgho2omAaScTnJTSR18p4tBK', 'dispatcher', 'active', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `users` (`id`, `name`, `username`, `email`, `phone`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES ('33', 'Temp Admin', 'temp_admin_6ad63d357b798', 'temp_admin_6ad63d357b79b@voyagego.com', NULL, '$2y$10$c4MSeIypDISTwJxEDbnAF.drqOwCgDF0KHimM4zWYZf4/tGLlU.kG', 'admin', 'active', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `users` (`id`, `name`, `username`, `email`, `phone`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES ('35', 'Operations Lead', 'ops.lead.6ad63e1072e8c', 'ops.lead.6ad63e1072e90@voyagego.com', '+91 99999 11111', '$2y$12$UhsqvxCNqpYB1sd8YxQBHuEPNT9SDEWJvLoEX7rRZ793ERnvscKum', 'dispatcher', 'active', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `users` (`id`, `name`, `username`, `email`, `phone`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES ('38', 'Temp Admin', 'temp_admin_6ad63e120114d', 'temp_admin_6ad63e1201152@voyagego.com', NULL, '$2y$10$nF76zV/lObA.Do.czTQBte2lq4bp5olCd.WNC4tQ1txvzPAAQhdoG', 'admin', 'active', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- Table structure for `vehicle_maintenance`
DROP TABLE IF EXISTS `vehicle_maintenance`;
CREATE TABLE `vehicle_maintenance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vehicle_id` int(11) NOT NULL,
  `maintenance_type` enum('scheduled_service','repair','inspection','tyre_replacement','bodywork') NOT NULL DEFAULT 'scheduled_service',
  `odometer_km` int(11) NOT NULL,
  `cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `service_center` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `performed_at` date NOT NULL,
  `next_service_due_km` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_maint_vehicle` (`vehicle_id`),
  KEY `idx_maint_date` (`performed_at`),
  KEY `fk_maint_user` (`created_by`),
  CONSTRAINT `fk_maint_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_maint_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `vehicles`
DROP TABLE IF EXISTS `vehicles`;
CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vehicle_number` varchar(30) NOT NULL,
  `make_model` varchar(100) NOT NULL,
  `vehicle_type` enum('sedan','suv','tempo_traveller','luxury_bus','van') NOT NULL DEFAULT 'suv',
  `seating_capacity` int(11) NOT NULL DEFAULT 4,
  `fuel_type` enum('diesel','petrol','cng','electric') NOT NULL DEFAULT 'diesel',
  `status` enum('available','on_trip','maintenance','retired') NOT NULL DEFAULT 'available',
  `odometer_km` int(11) NOT NULL DEFAULT 0,
  `registration_expiry` date DEFAULT NULL,
  `insurance_expiry` date DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vehicle_number` (`vehicle_number`),
  KEY `idx_vehicle_status` (`status`),
  KEY `idx_vehicle_type` (`vehicle_type`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `vehicles`
INSERT INTO `vehicles` (`id`, `vehicle_number`, `make_model`, `vehicle_type`, `seating_capacity`, `fuel_type`, `status`, `odometer_km`, `registration_expiry`, `insurance_expiry`, `created_at`, `updated_at`) VALUES ('1', 'MH40AL1891', 'BUS', 'suv', '50', 'diesel', 'available', '0', '2027-10-19', '2027-10-19', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

SET FOREIGN_KEY_CHECKS=1;
