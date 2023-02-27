<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class JGBVWDSDbInitializator{
    public static function initializeTables(){
        global $wpdb;

        $isql  = "CREATE TABLE IF NOT EXISTS `wp_wc_vwds_locations` (
                    `id` int unsigned NOT NULL AUTO_INCREMENT,
                    `location_code` varchar(24) NOT NULL,
                    `desc` varchar(256) NOT NULL,
                    `parent` varchar(24) DEFAULT NULL,
                    `type` varchar(12) NOT NULL,
                    UNIQUE KEY `wp_wc_vwds_locations_id_IDX` (`id`) USING BTREE,
                    KEY `wp_wc_vwds_locations_location_code_IDX` (`location_code`) USING BTREE,
                    KEY `wp_wc_vwds_locations_parent_IDX` (`parent`) USING BTREE,
                    KEY `wp_wc_vwds_locations_type_IDX` (`type`) USING BTREE
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;";

        $isql .= "CREATE TABLE IF NOT EXISTS `wp_wc_vwds_rules` (
                    `id` int unsigned NOT NULL AUTO_INCREMENT,
                    `min_weight` float NOT NULL,
                    `max_weight` float NOT NULL,
                    `unit_price` float NOT NULL,
                    `min_price` float DEFAULT NULL,
                    `zone_code` varchar(6) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
                    UNIQUE KEY `wp_wc_vwds_rules_id_IDX` (`id`) USING BTREE,
                    KEY `wp_wc_vwds_rules_min_weight_IDX` (`min_weight`) USING BTREE,
                    KEY `wp_wc_vwds_rules_max_weight_IDX` (`max_weight`) USING BTREE,
                    KEY `wp_wc_vwds_rules_zone_code_IDX` (`zone_code`) USING BTREE
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;";

        $isql .= "CREATE TABLE `wp_wc_vwds_zones` (
                    `id` int unsigned NOT NULL AUTO_INCREMENT,
                    `code` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                    `desc` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                    UNIQUE KEY `wp_wc_vwds_zones_id_IDX` (`id`) USING BTREE,
                    UNIQUE KEY `wp_wc_vwds_zones_code_IDX` (`code`) USING BTREE
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $isql .= "CREATE TABLE IF NOT EXISTS `wp_wc_vwds_zones_locations` (
                    `id` int unsigned NOT NULL AUTO_INCREMENT,
                    `zone_code` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                    `location_code` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                    UNIQUE KEY `wp_wc_vwds_zones_locations_id_IDX` (`id`) USING BTREE
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";


    }

    public static function poblateLocations($country='CL'){
      require_once __DIR__ . "/config_locations_$country.php";
      $locations_data = JGB_get_locations_data();

    }
}