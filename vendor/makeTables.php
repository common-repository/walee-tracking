<?php


    /**
     * @package WaleeTrackingPlugin
     */

     /*
      Plugin Name: Walee Tracking
      Plugin URL: http://walee.pk/wordpress-plugin
      Description: This plugin tracks the performance of influencers on SME's website
      Version: 1.0.0
      Author: Techlets Pvt Ltd
      Author URL: https://techletspk.com/
      License: GLPv2 or later
      Text Domain: Walee Tracking Wordpress Plugin
      */

      /*
        This program is free software: you can redistribute it and/or modify
        it under the terms of the GNU General Public License as published by
        the Free Software Foundation, either version 3 of the License, or
        (at your option) any later version.

        This program is distributed in the hope that it will be useful,
        but WITHOUT ANY WARRANTY; without even the implied warranty of
        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
        GNU General Public License for more details.

        You should have received a copy of the GNU General Public License
        along with this program.  If not, see <https://www.gnu.org/licenses/>.
      */


global $wpdb;
$charset_collate = $wpdb->get_charset_collate();
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

$createConfigSQL = "
    CREATE TABLE `".$wpdb->prefix."wt_config` (
    `domain` VARCHAR(500) NOT NULL,
    `username` VARCHAR(45) NULL,
    `password` VARCHAR(45) NULL,
    `is_view_tracking` TINYINT(1) NULL,
    `is_sales_tracking` TINYINT(1) NULL,
    `is_add_cart_tracking` TINYINT(1) NULL,
    `createdOn` TIMESTAMP NULL DEFAULT NOW(),
    `installed_version` VARCHAR(50) NULL,
    `last_synced` TIMESTAMP NULL DEFAULT NOW()) $charset_collate;";

$createViewsSQL = "
    CREATE TABLE `".$wpdb->prefix."wt_views` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `referrer` VARCHAR(45) NULL,
    `page` VARCHAR(245) NULL,
    `type` VARCHAR(30) NOT NULL,
    `is_synced` TINYINT(1) NULL DEFAULT 0,
    `createdOn` TIMESTAMP NULL DEFAULT NOW(),
    `synced_on` TIMESTAMP NULL,
    PRIMARY KEY (`id`)) $charset_collate;";

$formSubmission = "
    CREATE TABLE `".$wpdb->prefix."wt_formsubmittion` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `referrer` VARCHAR(45) NULL,
    `ip` VARCHAR(245) NULL,
    `formData` VARCHAR(700) NOT NULL,
    `is_synced` TINYINT(1) NULL DEFAULT 0,
    `createdOn` TIMESTAMP NULL DEFAULT NOW(),
    `synced_on` TIMESTAMP NULL,
    PRIMARY KEY (`id`)) $charset_collate;";

$createCartsSQL = "
    CREATE TABLE `".$wpdb->prefix."wt_carts` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `referrer` VARCHAR(45) NULL,
    `is_synced` TINYINT(1) NULL DEFAULT 0,
    `createdOn` TIMESTAMP NULL DEFAULT NOW(),
    `synced_on` TIMESTAMP NULL,
    `proId` VARCHAR(45) NULL,
    `proSku` VARCHAR(45) NULL,
    `proName` VARCHAR(45) NULL,
    `proPrice` INT NULL,
    `proPriceSale` INT NULL,
    PRIMARY KEY (`id`)) $charset_collate;";

$createSalesSQL = "
    CREATE TABLE `".$wpdb->prefix."wt_sales` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `referrer` VARCHAR(45) NULL,
    `is_synced` TINYINT(1) NULL DEFAULT 0,
    `orderId` INT NOT NULL,
    `createdOn` TIMESTAMP NULL DEFAULT NOW(),
    `synced_on` TIMESTAMP NULL,
    `userPhone` VARCHAR(45) NULL,
    `currency` VARCHAR(45) NULL,
    `paymentMethod` VARCHAR(45) NULL,
    `userMail` VARCHAR(145) NULL,
    `totalItems` INT NULL,
    `totalPrice` INT NULL,
    `order_status` VARCHAR(45) NULL,
    `lastUpdated` TIMESTAMP NULL,
    PRIMARY KEY (`id`)) $charset_collate;";

$createSalesLinesSQL = "
    CREATE TABLE `".$wpdb->prefix."wt_sale_line` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `sale_id` INT NULL,
    `proName` VARCHAR(45) NULL,
    `proId` VARCHAR(45) NULL,
    `proSku` VARCHAR(45) NULL,
    `proPrice` INT NULL,
    `proPriceSale` INT NULL,
    `qty` INT NULL,
    PRIMARY KEY (`id`)) $charset_collate;";

$createMatchingSQL = "
    CREATE TABLE `".$wpdb->prefix."wt_match_data` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `referrer` VARCHAR(45) NULL,
    `ip` VARCHAR(45) NULL,
    `createdOn` TIMESTAMP NULL DEFAULT NOW(),
    PRIMARY KEY (`id`)) $charset_collate;";

dbDelta( $createConfigSQL );
dbDelta( $createViewsSQL );
dbDelta( $createCartsSQL );
dbDelta( $createSalesSQL );
dbDelta( $createSalesLinesSQL );
dbDelta( $createMatchingSQL );
dbDelta( $formSubmission );
// create indexes here