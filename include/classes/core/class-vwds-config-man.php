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
        $config = get_option(JGB_VWDS_OPTION_NAME);
        $config[ $opt_nm ] = $opt_vl;
        return update_option( JGB_VWDS_OPTION_NAME, $config );
    }

    public function receiveOptSetReq( WP_REST_Request $r ){
        $o = $r->get_json_params();
        $res = [];
        $res['opt_update_result'] = $this->set_option($o['nm'],$o['vl']);

        if( $res['opt_update_result'] !== false){
            $res['err_status'] = 0;
        } else {
            $res['err_status'] = 1;
        }

        $response = new WP_REST_Response($res);

        return $response;
    }
}