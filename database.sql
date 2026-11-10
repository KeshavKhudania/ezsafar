-- EzSafar Automotive Mobility Platform Database Schema
-- Character set utf8mb4 for full emoji and international support

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `inquiries`;
DROP TABLE IF EXISTS `sell_requests`;
DROP TABLE IF EXISTS `rental_bookings`;
DROP TABLE IF EXISTS `used_cars`;
DROP TABLE IF EXISTS `rental_cars`;
DROP TABLE IF EXISTS `hubs`;
DROP TABLE IF EXISTS `admins`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Admins Table
CREATE TABLE `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `role` ENUM('admin', 'manager') DEFAULT 'admin',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin: admin / admin123
INSERT INTO `admins` (`username`, `email`, `password_hash`, `full_name`, `role`) VALUES
('admin', 'admin@ezsafar.in', '$2y$10$wK6nE8.f2qS8cZkF8JquseB/f11v3Jg4h65z63F7Y6pZeq3YhR.yq', 'EzSafar Admin', 'admin');

-- 2. Hubs Table
CREATE TABLE `hubs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `city` VARCHAR(50) NOT NULL,
    `hub_name` VARCHAR(100) NOT NULL,
    `address` TEXT NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `hubs` (`city`, `hub_name`, `address`, `phone`, `is_active`) VALUES
('Jind', 'Jind Central Mobility Hub', 'Opp. Railway Station Road, Near Patiala Chowk, Jind, Haryana 126102', '+91 9729226643', 1),
('Jind', 'Safidon Road Self-Drive Depot', 'Shop 14-16, Urban Estate, Safidon Road, Jind, Haryana 126102', '+91 9729226643', 1),
('Narwana', 'Narwana Outstation Desk', 'Main Bus Stand Circle, Narwana, Jind, Haryana 126116', '+91 9729226643', 1),
('Rohtak', 'Rohtak Express Exchange', 'Delhi Road, Near Model Town, Rohtak, Haryana 124001', '+91 9729226643', 1);

-- 3. Rental Cars Fleet
CREATE TABLE `rental_cars` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `category` ENUM('Hatchback', 'Sedan', 'Compact SUV', 'Luxury SUV', 'MUV / 7-Seater') NOT NULL,
    `fuel_type` ENUM('Petrol', 'Diesel', 'CNG', 'Electric') NOT NULL,
    `transmission` ENUM('Manual', 'Automatic') NOT NULL,
    `seats` INT NOT NULL DEFAULT 5,
    `price_per_day` DECIMAL(10, 2) NOT NULL,
    `price_per_hour` DECIMAL(10, 2) DEFAULT NULL,
    `security_deposit` DECIMAL(10, 2) DEFAULT 0.00,
    `free_km_per_day` INT DEFAULT 250,
    `extra_km_charge` DECIMAL(6, 2) DEFAULT 12.00,
    `image_url` VARCHAR(500) NOT NULL,
    `features` TEXT,
    `location_hub` VARCHAR(100) DEFAULT 'Jind Central Mobility Hub',
    `rating` DECIMAL(2, 1) DEFAULT 4.9,
    `trips_count` INT DEFAULT 120,
    `is_available` TINYINT(1) DEFAULT 1,
    `is_featured` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `rental_cars` (`id`, `name`, `category`, `fuel_type`, `transmission`, `seats`, `price_per_day`, `price_per_hour`, `security_deposit`, `free_km_per_day`, `extra_km_charge`, `image_url`, `features`, `location_hub`, `rating`, `trips_count`, `is_available`, `is_featured`) VALUES
(1, 'Maruti Suzuki Swift VXi 2024', 'Hatchback', 'Petrol', 'Manual', 5, 1499.00, 149.00, 0.00, 250, 9.00, 'https://lh3.googleusercontent.com/aida-public/AB6AXuD603gtExq3dy-6OAqJMZrG-wccOdyZBtUHl_hcppYugJOWaQNabp6jUovNZHqHcglQf_dPDBYDS-1LSqy9BhE1FR6shdVziOzjNRmpU5hbTLqdl-HUY3vDFMi72V6uX2nU4dHSTIDLeZ2h3USt3n6uUB1igZPQogL4-xK7vMLWRCdvlcymWHImfXjm_uq1hOkGGi7Bc7iivqKce5pjmm0JrLY13WTkX2GB1D6MEuk', 'Touchscreen Infotainment, Bluetooth, Fastag, Chilled AC, Airbags, Reverse Parking Sensors', 'Jind Central Mobility Hub', 4.9, 214, 1, 1),
(2, 'Honda City 5th Gen ZX', 'Sedan', 'Petrol', 'Automatic', 5, 2399.00, 219.00, 0.00, 300, 13.00, 'https://lh3.googleusercontent.com/aida-public/AB6AXuBFH-wKfCDqj7Y-BrL4yJAP8gaKT2am-WuipV4r65F36ZG1hxfwM_ukAXIDcFjNCQvmnjCPYmS09TSkEU_qir_WAPkH82cMYcmhOE6MoVG8UOk57EFA2xFZ_sQNukz6R70NtU4ARG8TFBBKC0l5Nl7whsWKDk-tGIqeB7NgsbkkYrEPJyoLYref-xE69ET-ERqz8X4QyBnDsNNbnzd2djYiZxIeFoOaIojGzFKazwI', 'Electric Sunroof, Leather Seats, LaneWatch Camera, Cruise Control, Android Auto / Apple CarPlay, Fastag', 'Jind Central Mobility Hub', 4.95, 186, 1, 1),
(3, 'Hyundai Creta SX (O) Panoramic', 'Compact SUV', 'Diesel', 'Automatic', 5, 2799.00, 249.00, 0.00, 300, 14.00, 'https://lh3.googleusercontent.com/aida-public/AB6AXuAidmGkSiiqtZG5_IbXUH8GPcbb18lJOwQG7_jgp6zaEPa3scIv2vCOvxSF2K5PDQI124PGr5ERo3SOIBG9ZL9Q4ajgIaF9FW1imV6BK_5jLQy2LSpRjLkog38OtrcFQVuxMVJKOHI3J-pfBN1iBgfE8FQC3BbedQbxDD9LWkcedLZ_bVHoLVCYQ3MXeghNgGye0-eGF06jlx-_baqKzRf-sbtxinp5SpHmA3wYaVo', 'Panoramic Sunroof, Ventilated Seats, Bose 8-Speaker Audio, Wireless Charger, 6 Airbags, Drive Modes', 'Jind Central Mobility Hub', 4.9, 178, 1, 1),
(4, 'Mahindra Thar 4x4 Hard Top', 'Luxury SUV', 'Diesel', 'Manual', 4, 3499.00, 299.00, 0.00, 250, 16.00, 'https://lh3.googleusercontent.com/aida-public/AB6AXuDRMXCAUemB7-bstL5wNRUYLsLJbOP4D4hKwEuwVYQ7vvvnchj7_NmRgNdfprpP8pDVfPPXgTPXbO1XNciF5bvUwy3isUxW1GHqYYLPrcwV5VQpOCMoF_JL4CqMJ69CW947EILzrDpUp_-OMtS5Ixj4ZyMEJsmJKOdjRF5DVjYvm1Aii0oRSfubBNK4kAjsVLJc152hJxeWOz-iHbLWSAKWk5uxtgwyk5o3upxghVQ', 'Mechanical Locking Differential, 4WD High/Low, All-Terrain Rims, Roll Cage, Water Resistant Interior', 'Safidon Road Self-Drive Depot', 4.92, 290, 1, 1),
(5, 'Toyota Innova Crysta 2.4 VX 7-Seater', 'MUV / 7-Seater', 'Diesel', 'Manual', 7, 3699.00, 320.00, 0.00, 300, 16.00, 'https://lh3.googleusercontent.com/aida-public/AB6AXuDkJ45pf5AWI5mfpMjYy6FIQV4hv9H-ACfwqAWwDn83ZkKQCFZtmi7liFKmcSm7zkIb8YEyCm_cI9LsmYWmQJ2RxjPPRuWc8TIZcJEkbvwysniLsc8ILSRohJY15SmPmMz7kZ0ZVR7OkEPlx02K6xsvYucbwSoF3dbx5uI-sT9SqeIFtce1eDG-ifvxCUbgHrTfIqygyReIc4k1IwHksCOeJEifmMAxKxl03XKzwg0', 'Captain Seats, Automatic Climate Control rear, High Highway Stability, Huge Luggage Boot, Fastag Enabled', 'Jind Central Mobility Hub', 4.98, 340, 1, 1),
(6, 'Maruti Suzuki Baleno Alpha', 'Hatchback', 'Petrol', 'Automatic', 5, 1699.00, 159.00, 0.00, 250, 10.00, 'https://lh3.googleusercontent.com/aida-public/AB6AXuD603gtExq3dy-6OAqJMZrG-wccOdyZBtUHl_hcppYugJOWaQNabp6jUovNZHqHcglQf_dPDBYDS-1LSqy9BhE1FR6shdVziOzjNRmpU5hbTLqdl-HUY3vDFMi72V6uX2nU4dHSTIDLeZ2h3USt3n6uUB1igZPQogL4-xK7vMLWRCdvlcymWHImfXjm_uq1hOkGGi7Bc7iivqKce5pjmm0JrLY13WTkX2GB1D6MEuk', '360 View Camera, Head Up Display, SmartPlay Pro+, 6 Airbags, Premium Arkamys Surround Sound', 'Jind Central Mobility Hub', 4.88, 142, 1, 0);

-- 4. Used Cars Table (Certified & 140-Point Inspected)
CREATE TABLE `used_cars` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `brand` VARCHAR(50) NOT NULL,
    `model` VARCHAR(50) NOT NULL,
    `variant` VARCHAR(50),
    `year` INT NOT NULL,
    `price` DECIMAL(12, 2) NOT NULL,
    `original_price` DECIMAL(12, 2) DEFAULT NULL,
    `emi_per_month` DECIMAL(10, 2) DEFAULT NULL,
    `kms_driven` INT NOT NULL,
    `fuel_type` ENUM('Petrol', 'Diesel', 'CNG', 'Electric') NOT NULL,
    `transmission` ENUM('Manual', 'Automatic') NOT NULL,
    `ownership` VARCHAR(20) DEFAULT '1st Owner',
    `registration_state` VARCHAR(20) DEFAULT 'HR-31 (Jind)',
    `body_type` ENUM('Hatchback', 'Sedan', 'SUV', 'MUV') NOT NULL,
    `color` VARCHAR(30) DEFAULT 'Arctic White',
    `insurance_validity` VARCHAR(50) DEFAULT 'Comprehensive till Dec 2026',
    `inspection_points` INT DEFAULT 140,
    `inspection_status` VARCHAR(50) DEFAULT 'Passed (Tier-1 Certified)',
    `image_url` VARCHAR(500) NOT NULL,
    `gallery_images` TEXT DEFAULT NULL,
    `description` TEXT,
    `highlights` TEXT,
    `status` ENUM('Available', 'Reserved', 'Sold') DEFAULT 'Available',
    `is_featured` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `used_cars` (`id`, `title`, `brand`, `model`, `variant`, `year`, `price`, `original_price`, `emi_per_month`, `kms_driven`, `fuel_type`, `transmission`, `ownership`, `registration_state`, `body_type`, `color`, `insurance_validity`, `inspection_points`, `inspection_status`, `image_url`, `description`, `highlights`, `status`, `is_featured`) VALUES
(1, '2023 Maruti Suzuki Swift ZXi Dual Tone', 'Maruti Suzuki', 'Swift', 'ZXi Dual Tone', 2023, 645000.00, 710000.00, 11890.00, 18500, 'Petrol', 'Manual', '1st Owner', 'HR-31 (Jind)', 'Hatchback', 'Arctic White with Black Roof', 'Comprehensive till Oct 2026', 140, 'Passed 140-Pt Inspection', 'https://lh3.googleusercontent.com/aida-public/AB6AXuBzZL2Syr3wkpbGA7RfIPIgITss-dAqytNI4Bvy1JvfP8nmG0dm2V5suvjLL6PxIFf-o5VZu4NDypnJTZ6BIqF1astLg6AfoM2ptsMnPOXt5caZ2dhwuIgv2qvQrCNaai6vrocWtc4HUGlUR6dl7rbPINjvETUQ_JIz_CUQuxl8_HRUBvpHBnQYD4N5m73BNiOKVI_fC49K78i9lN6dwHFIgMviPsbjrNguIkLFUGQ', 'Immaculately maintained single-owner Swift ZXi. Full service history at Authorized Nexa Jind. Non-accidental, original paint, zero meter tampering guaranteed.', 'Non-Accidental Guaranteed, Zero Meter Tampering, 1-Year Comprehensive Warranty, Free RC Transfer', 'Available', 1),

(2, '2022 Hyundai Creta SX Executive Panoramic', 'Hyundai', 'Creta', 'SX Executive', 2022, 1275000.00, 1380000.00, 22450.00, 32000, 'Diesel', 'Manual', '1st Owner', 'HR-33 (Narwana)', 'SUV', 'Phantom Black', 'Zero Depreciation till Aug 2026', 140, 'Passed 140-Pt Inspection', 'https://lh3.googleusercontent.com/aida-public/AB6AXuCtrUKhgpXN-T86ED-iHsGtg058fgfNw48licaHxNvFu2MZk525QVd-gFoKbYBNLQOqwDn0ZIjG5QWSFLW58eDRPMnXcys-1Lrq0vaIWJDTUwui0Lf0vbv1HrEV-TeSaaexIm2RXMucrsNBN1rN8nugFUTJiERnsu3jD4jya1KAw619lUgNeiSZ2EwZeehrt_hKQF1-kWcylwJKwaT32SgdQuTXfN8FlU3wLeXw-to', 'Top-selling mid-size SUV in pristine condition. Panoramic sunroof, wireless Android Auto, all 4 disc brakes, brand new Goodyear tyres.', 'Panoramic Sunroof, 140-Point Inspected, 7-Day Money Back Guarantee, Free RC Transfer', 'Available', 1),

(3, '2021 Mahindra Thar LX 4x4 Hard Top', 'Mahindra', 'Thar', 'LX 4x4 Hard Top', 2021, 1320000.00, 1450000.00, 24100.00, 28000, 'Diesel', 'Manual', '1st Owner', 'HR-31 (Jind)', 'SUV', 'Napoli Black', 'Comprehensive till Dec 2026', 140, 'Passed 140-Pt Inspection', 'https://lh3.googleusercontent.com/aida-public/AB6AXuCZAMtCpYqqSx6I6UyQA6IeImLLG4i88y5f_KNoFW9vUvRIHhndBDpmpxfpxNjBgRN7sxcxF980Tc-QU7QM7qIPTXUBb67aK7GvDLcTx6O5zHfAUuGVxmH0P3byLKiigNsASUfjatc51BAhrp2E4mIO7a2SwNKeM9dZsiAdfJDU61GtokpUOrFSqea_xCzuugGt_9Rs1llrOsOFKvbLnJvDQOjHTTZjm4Fmo-5sOT4', 'The authentic adventure icon. Rarely driven off-road, mostly city and NH-152D highway runs. Equipped with upgraded speakers, genuine Mahindra alloy wheels.', 'Authentic 4x4, Certified Non-Flood Vehicle, Free RC Transfer, 1-Year Engine & Gearbox Warranty', 'Available', 1),

(4, '2022 Mahindra Scorpio-N Z8 L 4x4 7-Seater', 'Mahindra', 'Scorpio-N', 'Z8 L Luxury', 2022, 1890000.00, 2050000.00, 32500.00, 38000, 'Diesel', 'Automatic', '1st Owner', 'HR-31 (Jind)', 'SUV', 'Dazzling Silver', 'Comprehensive till Nov 2026', 140, 'Passed 140-Pt Inspection', 'https://lh3.googleusercontent.com/aida-public/AB6AXuAD61R0eW3okFG3kYMqR1EcZkN5FJK_HDAlXicacqP0nWdlTSiWqYWn7iXbfNxTe5A-rGIf0v-VBDYL5Af6ltrIcJPhvoWBBFfB65vbTwp3_PmRSm2se17fN7m0RVaHEbTQmfe8mWNeDbYmUxo6-NnEqNFpjIh6-O5LzMAoUVPZ4ydV4YUaWNMFl0xrd-xOj4b-l3rAhmw7JtXz9_hXms50rF3mn1q9KsCeuQN6ang', 'Commanding road presence with luxurious captain seats. 12-speaker Sony 3D sound system, dual front and rear cameras, 4XPLOR terrain modes.', 'Sony 3D Audio, AdrenoX Connected Tech, 140-Pt Quality Certified, Free RC Transfer', 'Available', 1),

(5, '2020 Honda City 5th Gen ZX CVT', 'Honda', 'City', 'ZX i-VTEC', 2020, 985000.00, 1100000.00, 17200.00, 45000, 'Petrol', 'Automatic', '1st Owner', 'HR-12 (Rohtak)', 'Sedan', 'Platinum White Pearl', 'Comprehensive till Mar 2026', 140, 'Passed 140-Pt Inspection', 'https://lh3.googleusercontent.com/aida-public/AB6AXuBFH-wKfCDqj7Y-BrL4yJAP8gaKT2am-WuipV4r65F36ZG1hxfwM_ukAXIDcFjNCQvmnjCPYmS09TSkEU_qir_WAPkH82cMYcmhOE6MoVG8UOk57EFA2xFZ_sQNukz6R70NtU4ARG8TFBBKC0l5Nl7whsWKDk-tGIqeB7NgsbkkYrEPJyoLYref-xE69ET-ERqz8X4QyBnDsNNbnzd2djYiZxIeFoOaIojGzFKazwI', 'Executive luxury sedan known for supreme comfort. Zero mechanical defects, full Honda service records, pristine interior upholstery.', 'Electric Sunroof, Automatic CVT, Complete Service History, Free RC Transfer', 'Available', 1),

(6, '2021 Maruti Suzuki Dzire ZXi Plus CNG', 'Maruti Suzuki', 'Dzire', 'ZXi Plus Factory CNG', 2021, 680000.00, 750000.00, 12200.00, 52000, 'CNG', 'Manual', '1st Owner', 'HR-31 (Jind)', 'Sedan', 'Magma Grey', 'Comprehensive till May 2026', 140, 'Passed 140-Pt Inspection', 'https://lh3.googleusercontent.com/aida-public/AB6AXuBzZL2Syr3wkpbGA7RfIPIgITss-dAqytNI4Bvy1JvfP8nmG0dm2V5suvjLL6PxIFf-o5VZu4NDypnJTZ6BIqF1astLg6AfoM2ptsMnPOXt5caZ2dhwuIgv2qvQrCNaai6vrocWtc4HUGlUR6dl7rbPINjvETUQ_JIz_CUQuxl8_HRUBvpHBnQYD4N5m73BNiOKVI_fC49K78i9lN6dwHFIgMviPsbjrNguIkLFUGQ', 'Incredible mileage (31+ km/kg). Original company fitted CNG kit tested and endorsed on RC. Best economy family car.', 'Factory Fitted CNG, 31+ km/kg Mileage, Non-Accidental, Free RC Transfer', 'Available', 0);

-- 5. Rental Bookings Table
CREATE TABLE `rental_bookings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `booking_number` VARCHAR(30) NOT NULL UNIQUE,
    `car_id` INT NOT NULL,
    `rental_type` ENUM('Self-Drive', 'With Chauffeur') DEFAULT 'Self-Drive',
    `customer_name` VARCHAR(100) NOT NULL,
    `customer_phone` VARCHAR(20) NOT NULL,
    `customer_email` VARCHAR(100) DEFAULT NULL,
    `customer_city` VARCHAR(50) DEFAULT 'Jind',
    `pickup_date` DATE NOT NULL,
    `pickup_time` TIME NOT NULL,
    `return_date` DATE NOT NULL,
    `return_time` TIME NOT NULL,
    `pickup_location` VARCHAR(100) DEFAULT 'Jind Central Mobility Hub',
    `drop_location` VARCHAR(100) DEFAULT 'Same as Pickup',
    `total_days` INT DEFAULT 1,
    `total_amount` DECIMAL(10, 2) NOT NULL,
    `special_requests` TEXT DEFAULT NULL,
    `booking_status` ENUM('Pending', 'Confirmed', 'Active', 'Completed', 'Cancelled') DEFAULT 'Pending',
    `payment_status` ENUM('Pay at Pickup', 'Deposit Paid', 'Fully Paid') DEFAULT 'Pay at Pickup',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_booking_car` FOREIGN KEY (`car_id`) REFERENCES `rental_cars`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `rental_bookings` (`booking_number`, `car_id`, `rental_type`, `customer_name`, `customer_phone`, `customer_email`, `customer_city`, `pickup_date`, `pickup_time`, `return_date`, `return_time`, `pickup_location`, `drop_location`, `total_days`, `total_amount`, `special_requests`, `booking_status`, `payment_status`) VALUES
('EZB-2025-1001', 4, 'Self-Drive', 'Vikas Sharma', '+91 9812345678', 'vikas.sharma@gmail.com', 'Jind', CURDATE() + INTERVAL 1 DAY, '09:00:00', CURDATE() + INTERVAL 3 DAY, '20:00:00', 'Jind Central Mobility Hub', 'Same as Pickup', 2, 6998.00, 'Trip to Manali. Need clean vehicle with fastag recharge.', 'Confirmed', 'Pay at Pickup'),
('EZB-2025-1002', 2, 'With Chauffeur', 'Deepak Verma', '+91 9729001122', 'deepak@vermaholdings.in', 'Narwana', CURDATE() + INTERVAL 2 DAY, '07:30:00', CURDATE() + INTERVAL 2 DAY, '22:00:00', 'Narwana Outstation Desk', 'Delhi Airport T3 Drop', 1, 3800.00, 'Airport drop off, flight at 1 PM.', 'Confirmed', 'Deposit Paid');

-- 6. Sell Your Car Requests
CREATE TABLE `sell_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `request_number` VARCHAR(30) NOT NULL UNIQUE,
    `reg_number` VARCHAR(30) NOT NULL,
    `brand` VARCHAR(50) NOT NULL,
    `model` VARCHAR(50) NOT NULL,
    `year` INT NOT NULL,
    `fuel_type` VARCHAR(20) NOT NULL,
    `transmission` VARCHAR(20) NOT NULL,
    `kms_driven` INT NOT NULL,
    `car_condition` ENUM('Excellent', 'Good', 'Fair', 'Needs Work') DEFAULT 'Good',
    `expected_price` DECIMAL(12, 2) DEFAULT NULL,
    `estimated_range_min` DECIMAL(12, 2) DEFAULT NULL,
    `estimated_range_max` DECIMAL(12, 2) DEFAULT NULL,
    `seller_name` VARCHAR(100) NOT NULL,
    `seller_phone` VARCHAR(20) NOT NULL,
    `seller_city` VARCHAR(50) DEFAULT 'Jind',
    `inspection_pref_date` DATE DEFAULT NULL,
    `status` ENUM('Pending Review', 'Inspection Scheduled', 'Offer Generated', 'Deal Closed', 'Rejected') DEFAULT 'Pending Review',
    `admin_notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sell_requests` (`request_number`, `reg_number`, `brand`, `model`, `year`, `fuel_type`, `transmission`, `kms_driven`, `car_condition`, `expected_price`, `estimated_range_min`, `estimated_range_max`, `seller_name`, `seller_phone`, `seller_city`, `inspection_pref_date`, `status`) VALUES
('EZS-2025-5001', 'HR-31-AJ-4921', 'Hyundai', 'i20 Asta', 2021, 'Petrol', 'Manual', 34000, 'Excellent', 650000.00, 620000.00, 660000.00, 'Kuldeep Singh', '+91 9416012345', 'Jind (Urban Estate)', CURDATE() + INTERVAL 2 DAY, 'Inspection Scheduled'),
('EZS-2025-5002', 'HR-33-C-8899', 'Maruti Suzuki', 'Brezza ZDi', 2019, 'Diesel', 'Manual', 62000, 'Good', 580000.00, 540000.00, 590000.00, 'Ramesh Mor', '+91 9896112233', 'Narwana', CURDATE() + INTERVAL 3 DAY, 'Pending Review');

-- 7. Inquiries & Test Drive Requests
CREATE TABLE `inquiries` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `inquiry_type` ENUM('General', 'Test Drive', 'Used Car Purchase', 'Rental Inquiry', 'Corporate') DEFAULT 'General',
    `car_id` INT DEFAULT NULL,
    `name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `email` VARCHAR(100) DEFAULT NULL,
    `preferred_date` DATE DEFAULT NULL,
    `message` TEXT,
    `status` ENUM('New', 'Contacted', 'Resolved', 'Closed') DEFAULT 'New',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `inquiries` (`inquiry_type`, `car_id`, `name`, `phone`, `email`, `preferred_date`, `message`, `status`) VALUES
('Test Drive', 2, 'Anil Dhull', '+91 9812981298', 'anil.dhull@gmail.com', CURDATE() + INTERVAL 1 DAY, 'Interested in test driving the Creta 2022 Phantom Black at Jind hub.', 'New'),
('Rental Inquiry', 5, 'Sunil Malik', '+91 9728554433', 'sunil@maliktransport.com', CURDATE() + INTERVAL 4 DAY, 'Need Innova Crysta for 4 days wedding trip to Jaipur.', 'New');



-- 8. Website Configuration & SEO Settings
CREATE TABLE IF NOT EXISTS `settings` (
    `setting_key` VARCHAR(100) PRIMARY KEY,
    `setting_value` TEXT,
    `setting_group` VARCHAR(50) DEFAULT 'general',
    `label` VARCHAR(150),
    `description` VARCHAR(255),
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_group`, `label`, `description`) VALUES
('site_name', 'EzSafar', 'general', 'Website Name', 'Brand name displayed on logo and throughout the portal'),
('site_tagline', 'Automotive Mobility Platform', 'general', 'Website Tagline', 'Short subtitle for brand positioning'),
('primary_city', 'Jind', 'general', 'Primary Hub City', 'Base operating city in Haryana'),
('primary_state', 'Haryana', 'general', 'Primary Hub State', 'Base state in India'),
('primary_hub_address', 'Opp. Railway Station Road, Near Patiala Chowk, Jind, Haryana 126102', 'general', 'Central Hub Address', 'Full physical address shown in footer and contact page'),
('contact_phone', '+91 9729226643', 'general', 'Support Phone Number', 'Click-to-call phone number for customers'),
('contact_whatsapp', '919729226643', 'general', 'WhatsApp Support Number', 'Digits only with country code (e.g. 919729226643)'),
('contact_email', 'care@ezsafar.in', 'general', 'Support Email Address', 'Customer care email'),
('currency_symbol', '₹', 'general', 'Currency Symbol', 'Symbol prefixed before all amounts (e.g. ₹)'),
('working_hours', '24x7 Roadside Assistance & Hub Desk 08:00 AM - 10:00 PM', 'general', 'Working Hours', 'Customer support desk timing'),

('meta_title_default', 'EzSafar - Premium Car Rentals, Buy & Sell Pre-Owned Cars in Jind, Haryana', 'seo', 'Default SEO Meta Title', 'Browser title for search engines and social sharing'),
('meta_description_default', 'Haryana\'s premier self-drive car rental and certified used car trading platform in Jind. Zero security deposit, 140-point inspection, and instant payment for car sellers.', 'seo', 'Default SEO Meta Description', 'Page summary displayed in Google search results'),
('meta_keywords', 'car rental jind, self drive cars haryana, used cars in jind, buy second hand car jind, sell car online jind, 4x4 thar rental jind, innova rental haryana', 'seo', 'SEO Meta Keywords', 'Comma-separated target SEO keywords'),
('meta_author', 'EzSafar Mobility Solutions Pvt. Ltd.', 'seo', 'Meta Author', 'Author tag in page head'),
('meta_robots', 'index, follow', 'seo', 'Meta Robots', 'Search engine indexing directives (index, follow / noindex)'),
('og_image_url', 'https://lh3.googleusercontent.com/aida-public/AB6AXuD603gtExq3dy-6OAqJMZrG-wccOdyZBtUHl_hcppYugJOWaQNabp6jUovNZHqHcglQf_dPDBYDS-1LSqy9BhE1FR6shdVziOzjNRmpU5hbTLqdl-HUY3vDFMi72V6uX2nU4dHSTIDLeZ2h3USt3n6uUB1igZPQogL4-xK7vMLWRCdvlcymWHImfXjm_uq1hOkGGi7Bc7iivqKce5pjmm0JrLY13WTkX2GB1D6MEuk', 'seo', 'Open Graph Share Image', 'Preview image URL when links are shared on WhatsApp/Facebook/X'),
('google_analytics_id', '', 'seo', 'Google Analytics Measurement ID', 'e.g. G-ABC123XYZ or Google Tag Manager ID'),
('custom_head_scripts', '', 'seo', 'Custom Head Scripts', 'Additional HTML/scripts to insert into <head> (e.g. tracking scripts)'),

('default_free_km_per_day', '250', 'pricing', 'Default Free KM / Day', 'Included mileage per rental day'),
('default_extra_km_charge', '12.00', 'pricing', 'Extra KM Charge (₹/km)', 'Default charge per extra kilometer driven'),
('chauffeur_charge_per_day', '600.00', 'pricing', 'Chauffeur Allowance (₹/day)', 'Daily driver fee when chauffeur mode is selected'),
('doorstep_delivery_charge', '250.00', 'pricing', 'Doorstep Delivery Charge (₹)', 'Flat fee for delivering rental car to customer doorstep in Jind'),
('airport_drop_charge', '1800.00', 'pricing', 'Delhi/Chandigarh Airport Drop Fee (₹)', 'Airport drop off surcharge'),

('facebook_url', 'https://facebook.com/ezsafar', 'social', 'Facebook Page URL', 'Link to company Facebook page'),
('instagram_url', 'https://instagram.com/ezsafar', 'social', 'Instagram Profile URL', 'Link to official Instagram profile'),
('youtube_url', '', 'social', 'YouTube Channel URL', 'Link to official YouTube channel')
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- 9. Blog Categories & Blog Posts
CREATE TABLE IF NOT EXISTS `blog_categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(120) NOT NULL UNIQUE,
    `description` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `blog_posts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NULL,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `excerpt` TEXT NULL,
    `content` LONGTEXT NOT NULL,
    `featured_image` VARCHAR(500) NOT NULL,
    `author_name` VARCHAR(100) DEFAULT 'EzSafar Editorial Team',
    `reading_time` INT DEFAULT 5,
    `views_count` INT DEFAULT 0,
    `is_published` TINYINT(1) DEFAULT 1,
    `published_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `meta_title` VARCHAR(255) NULL,
    `meta_description` TEXT NULL,
    `meta_keywords` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_published` (`is_published`, `published_at`),
    FOREIGN KEY (`category_id`) REFERENCES `blog_categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

