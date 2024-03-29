<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class JGBVWDSDbInitializator{
    public static function initializeTables(){
        global $wpdb;

        ob_start();
        $isql  = "CREATE TABLE IF NOT EXISTS `wp_wc_vwds_locations` (
                    `id` int unsigned NOT NULL AUTO_INCREMENT,
                    `location_code` varchar(24) NOT NULL,
                    `desc` varchar(256) NOT NULL,
                    `parent` varchar(24) DEFAULT NULL,
                    `type` varchar(12) NOT NULL,
                    `deleted` tinyint(1) NOT NULL DEFAULT '0',
                    `active` tinyint(1) NOT NULL DEFAULT '1',
                    UNIQUE KEY `wp_wc_vwds_locations_id_IDX` (`id`) USING BTREE,
                    KEY `wp_wc_vwds_locations_location_code_IDX` (`location_code`) USING BTREE,
                    KEY `wp_wc_vwds_locations_parent_IDX` (`parent`) USING BTREE,
                    KEY `wp_wc_vwds_locations_type_IDX` (`type`) USING BTREE
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;";
        $wpdb->query( $isql );

        $isql = " CREATE TABLE IF NOT EXISTS `wp_wc_vwds_rules` (
                    `id` int unsigned NOT NULL AUTO_INCREMENT,
                    `mode` enum('OU','WU','WR') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'OU: Solo unitario, sin multiplicar el peso de cada producto, no importa que el producto no tenga peso. WU: Se multiplicará el peso del producto por el precio. WR: Se multiplicará el peso por el precio según el rango de peso.',
                    `min_weight` float NOT NULL,
                    `max_weight` float NOT NULL,
                    `unit_price` float NOT NULL,
                    `min_price` float DEFAULT NULL,
                    `destination_zone_code` varchar(6) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
                    `origin_zone_code` varchar(6) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'En versiones posteriores se utilizará para señalar el orignen',
                    `deleted` tinyint(1) NOT NULL DEFAULT '0',
                    `active` tinyint(1) NOT NULL DEFAULT '1',
                    UNIQUE KEY `wp_wc_vwds_rules_id_IDX` (`id`) USING BTREE,
                    KEY `wp_wc_vwds_rules_min_weight_IDX` (`min_weight`) USING BTREE,
                    KEY `wp_wc_vwds_rules_max_weight_IDX` (`max_weight`) USING BTREE,
                    KEY `wp_wc_vwds_rules_dzc_IDX` (`destination_zone_code`) USING BTREE,
                    KEY `wp_wc_vwds_rules_ozc_IDX` (`origin_zone_code`) USING BTREE,
                    KEY `wp_wc_vwds_rules_mode_IDX` (`mode`) USING BTREE
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;";
        $wpdb->query( $isql );

        $isql = " CREATE TABLE IF NOT EXISTS `wp_wc_vwds_zones` (
                    `id` int unsigned NOT NULL AUTO_INCREMENT,
                    `code` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                    `desc` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                    `deleted` tinyint(1) NOT NULL DEFAULT '0',
                    `active` tinyint(1) NOT NULL DEFAULT '1',
                    UNIQUE KEY `wp_wc_vwds_zones_id_IDX` (`id`) USING BTREE,
                    UNIQUE KEY `wp_wc_vwds_zones_code_IDX` (`code`) USING BTREE
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $wpdb->query( $isql );

        $isql = " CREATE TABLE IF NOT EXISTS `wp_wc_vwds_zones_locations` (
                    `id` int unsigned NOT NULL AUTO_INCREMENT,
                    `zone_code` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                    `location_code` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                    UNIQUE KEY `wp_wc_vwds_zones_locations_id_IDX` (`id`) USING BTREE
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $wpdb->query( $isql );
        
        
        write_log("Salida de inicialización de tablas de wp_wc_vwd_shipping...");
        write_log( ob_get_clean() );
    }

    public static function poblateLocations($country='CL'){
      require_once __DIR__ . "/config_locations_$country.php";
      $locations_data = JGB_get_locations_data();
      
    }
}