-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 13, 2026 at 06:51 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `inventory_sales_star`
--

-- --------------------------------------------------------

--
-- Table structure for table `dim_customer`
--

CREATE TABLE `dim_customer` (
  `customer_key` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dim_date`
--

CREATE TABLE `dim_date` (
  `date_key` int(11) NOT NULL,
  `full_date` date DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `quarter` int(11) DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `month_name` varchar(20) DEFAULT NULL,
  `day` int(11) DEFAULT NULL,
  `weekday` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dim_product`
--

CREATE TABLE `dim_product` (
  `product_key` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(100) DEFAULT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `star_fact_sales`
--

CREATE TABLE `star_fact_sales` (
  `sale_key` int(11) NOT NULL,
  `date_key` int(11) DEFAULT NULL,
  `customer_key` int(11) DEFAULT NULL,
  `product_key` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dim_customer`
--
ALTER TABLE `dim_customer`
  ADD PRIMARY KEY (`customer_key`);

--
-- Indexes for table `dim_date`
--
ALTER TABLE `dim_date`
  ADD PRIMARY KEY (`date_key`);

--
-- Indexes for table `dim_product`
--
ALTER TABLE `dim_product`
  ADD PRIMARY KEY (`product_key`);

--
-- Indexes for table `star_fact_sales`
--
ALTER TABLE `star_fact_sales`
  ADD PRIMARY KEY (`sale_key`),
  ADD KEY `date_key` (`date_key`),
  ADD KEY `customer_key` (`customer_key`),
  ADD KEY `product_key` (`product_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dim_customer`
--
ALTER TABLE `dim_customer`
  MODIFY `customer_key` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dim_product`
--
ALTER TABLE `dim_product`
  MODIFY `product_key` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `star_fact_sales`
--
ALTER TABLE `star_fact_sales`
  MODIFY `sale_key` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `star_fact_sales`
--
ALTER TABLE `star_fact_sales`
  ADD CONSTRAINT `star_fact_sales_ibfk_1` FOREIGN KEY (`date_key`) REFERENCES `dim_date` (`date_key`),
  ADD CONSTRAINT `star_fact_sales_ibfk_2` FOREIGN KEY (`customer_key`) REFERENCES `dim_customer` (`customer_key`),
  ADD CONSTRAINT `star_fact_sales_ibfk_3` FOREIGN KEY (`product_key`) REFERENCES `dim_product` (`product_key`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
