<?php
 
/**
 * Plugin Name: Woocommerce Volume-Weight-Destination Shipping
 * Plugin URI: https://empdigital.cl/plugins/wc-vwd-shipping
 * Description: Plugin de método de envío por Volumen-Peso-Destino
 * Version: 1.0.1
 * Author: Jorge Garrido / EMP Digital
 * Author URI: https://empdigital.cl/devteam/jegschl
 * License: GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path: /lang
 * Text Domain: wc-vwd-shipping
 */
 
if ( ! defined( 'WPINC' ) ) {
 
    die;
 
}

/**
 * Current Woocommerce Volume-Weight-Destination Shipping
 */
if ( ! defined( 'JGB_VWDS_VERSION' ) ) {
	/**
	 *
	 */
	define( 'JGB_VWDS_VERSION', '1.0.1' );
}

$dir = dirname( __FILE__ );
define( 'JGB_VWDS_PLUGIN_DIR', $dir );
define( 'JGB_VWDS_PLUGIN_FILE', __FILE__ );
define( 'JGB_VWDS_PLUGIN_URL', "/".substr($dir,strlen(ABSPATH)) );

require_once $dir . '/include/class-vwds-manager.php';

/**
 * Main Genoma Lab manager.
 * @var GnmLab_Manager $gnlb_manager - instance of composer management.
 * @since 4.2
 */
global $jgb_vwds_manager;
if ( ! $jgb_vwds_manager ) {
	$jgb_vwds_manager = JGBVWDS_Manager::getInstance();
	// Load components
	//$gnlb_manager->loadComponents();
}


/*
 * Check if WooCommerce is active
 */
/* if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
 
    function wc_vwds_shipping_method() {
        include_once __DIR__ . "/class-vwds-shipping.php";
    }
 
    add_action( 'woocommerce_shipping_init', 'wc_vwds_shipping_method' );
    
} */









