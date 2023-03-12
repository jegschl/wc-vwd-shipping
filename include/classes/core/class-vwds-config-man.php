<?php

if ( ! defined( 'WPINC' ) ) {
 
    die;
 
}

define( 'JGB_VWDS_OPTION_NAME','jgb_wvds_config');
define( 'JGB_VWDS_OPTION_NAME_MODE_PRICE','mode_price');

class JGBVWDSCfgManager{
    public function get_option( $opt_nm, $default = null ){
        $config = get_option(JGB_VWDS_OPTION_NAME);
        if( $config === false ){
            $config = $this->get_default_config();
        }

        if( isset( $config[ $opt_nm ]) ){

            return $config[ $opt_nm ];

        } elseif( !is_null( $default ) ){

            return $default;

        } else {

            return null;

        }
    }

    public function get_default_config(){
        $dc = [
            JGB_VWDS_OPTION_NAME_MODE_PRICE => 'OU' // solo toma en cuenta el valor de 'unit_price' para calcular el costo de envío (sin importar el peso de cada producto en el CdC).
        ];

        return $dc;
    }

    public function set_option( $opt_nm, $opt_vl ){

    }
}