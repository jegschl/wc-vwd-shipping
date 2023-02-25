<?php

if ( ! defined( 'WPINC' ) ) {
 
    die;
 
}


function wc_vwds_validate_order( $posted )   {
 
    $packages = WC()->shipping->get_packages();

    $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
     
    if( is_array( $chosen_methods ) && in_array( 'wc_vwds', $chosen_methods ) ) {
         
        foreach ( $packages as $i => $package ) {

            if ( $chosen_methods[ $i ] != "wc_vwds" ) {
                         
                continue;
                         
            }

            $wc_vwds_Shipping_Method = new wc_vwds_Shipping_Method();
            $weightLimit = (int) $wc_vwds_Shipping_Method->settings['weight'];
            $weight = 0;

            foreach ( $package['contents'] as $item_id => $values ) 
            { 
                $_product = $values['data']; 
                $weight = $weight + $_product->get_weight() * $values['quantity']; 
            }

            $weight = wc_get_weight( $weight, 'kg' );
            
            if( $weight > $weightLimit ) {

                    $message = sprintf( __( 'Sorry, %d kg exceeds the maximum weight of %d kg for %s', 'wc_vwds' ), $weight, $weightLimit, $wc_vwds_Shipping_Method->title );
                         
                    $messageType = "error";

                    if( ! wc_has_notice( $message, $messageType ) ) {
                     
                        wc_add_notice( $message, $messageType );
                  
                    }
            }
        }       
    } 
}

//add_action( 'woocommerce_review_order_before_cart_contents', 'wc_vwds_validate_order' , 10 );
//add_action( 'woocommerce_after_checkout_validation', 'wc_vwds_validate_order' , 10 );



//add_filter('woocommerce_states','emp_set_state_list',99);
function emp_set_state_list($vl){
	global $wpdb;
	$qry = 'SELECT * FROM wp_wc_vwds_locations WHERE type = "region" ORDER BY "desc"';
	$states = $wpdb->get_results($qry, OBJECT);
	$vl = array();
	foreach($states as $st){
        $key = $st->location_code ."-".$st->base_zone;
		$vl[$key] = $st->desc;
	}
	return array('CL' => $vl);
}