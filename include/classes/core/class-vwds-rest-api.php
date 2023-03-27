<?php

if ( ! defined( 'WPINC' ) ) {
 
    die;
 
}


class JGBVWDSRestApi{

    private $locations;

    private $config;

    private $zones;

    function __construct(
        JGBVWDSLocations $locations, 
        JGBVWDSCfgManager $config,
        JGBVWDSZones $zones
    ){
        $this->locations = $locations;
        $this->config = $config;
        $this->zones = $zones;
    }

    public function set_endpoints(){
        register_rest_route(
            'wc-vwd-sipping/',
            '/comunas-por-region/(?P<region_id>\d+)',
            array(
                'methods'  => 'GET',
                'callback' => [$this,'getComunasByRegion'],
                'permission_callback' => '__return_true',
                'args' => array(
                    'region_id' => array(
                        'validate_callback' => function($param, $request, $key) {
                            return is_numeric( $param );
                        }
                    ),
                )
            )
        );

        register_rest_route(
            'wc-vwd-sipping/',
            '/locations/',
            array(
                'methods'  => 'GET',
                'callback' => [$this->locations,'sendLocations'],
                'permission_callback' => '__return_true',
            )
        );

        register_rest_route(
            'wc-vwd-sipping/',
            '/locations/',
            array(
                'methods'  => 'POST',
                'callback' => [$this->locations,'receiveNewLocation'],
                'permission_callback' => '__return_true',
            )
        );

        register_rest_route(
            'wc-vwd-sipping/',
            '/rem-locations/',
            array(
                'methods'  => 'POST',
                'callback' => [$this->locations,'removeLocations'],
                'permission_callback' => '__return_true',
            )
        );

        register_rest_route(
            'wc-vwd-sipping/',
            '/option/',
            array(
                'methods'  => 'POST',
                'callback' => [$this->config,'receiveOptSetReq'],
                'permission_callback' => '__return_true',
            )
        );

        register_rest_route(
            'wc-vwd-sipping/',
            '/zones/',
            array(
                'methods'  => 'GET',
                'callback' => [$this->zones,'sendZones'],
                'permission_callback' => '__return_true',
            )
        );

        ///wc-vwd-sipping/zones-by-wr/
        register_rest_route(
            'wc-vwd-sipping/',
            '/zones-by-wr/',
            array(
                'methods'  => 'POST',
                'callback' => [$this->zones,'receiveZonesByWR'],
                'permission_callback' => '__return_true',
            )
        );

    }

    

    public function get_endpoint_base($endpoint_name){
        switch($endpoint_name){
            case 'remove-location':
                return rest_url('/wc-vwd-sipping/rem-locations/');
                break;

            case 'locations':
                return rest_url('/wc-vwd-sipping/locations/');
                break;
            
            case 'comunas':
                return rest_url('/wc-vwd-sipping/comunas-por-region/');
                break;

            case 'option':
                return rest_url('/wc-vwd-sipping/option/');
                break;

            case 'get-zones':
                return rest_url('/wc-vwd-sipping/zones/');

            case 'set-zones-by-wr':
                return rest_url('/wc-vwd-sipping/zones-by-wr/');

        }

        return null;
    }

    
    
    public function getComunasByRegion( $request ){
        $region_id = $request->get_param( 'region_id' );
        if( isset($region_id) ){
            $comunas_dpa = $this->get_comunas_by_region(intval($region_id));
            function cmp($a, $b) {
                return strcmp($a['name'], $b['name']);
            }
            usort($comunas_dpa, "cmp");
            if($comunas_dpa){
                $res = array(
                    'comunas' => $comunas_dpa,
                    //'region'  => $this->emp_dpa->get_dpa_data($region_id)
                );
                $response = new WP_REST_Response( $res );
                $response->set_status( 200 );
                return $response;
            } else {
                return new WP_Error( 'cant-read-comunas', __( 'Can\'t get comunas', 'emp-inspecthm' ), array( 'status' => 500 ) );
              }
            
        } else {
            return new WP_Error( 'invalid-region-id', __( 'Invalid region ID', 'emp-inspecthm' ), array( 'status' => 404 ) );
        }
        
    }
    
    public function get_comunas_by_region($parent_id){
        global $wpdb;
        $qry = 'SELECT * FROM wp_wc_vwds_locations WHERE type = "comuna" AND parent = "'.$parent_id.'" ORDER BY "desc"';
        $comunas = $wpdb->get_results($qry, OBJECT);
        $vl = array();
        foreach($comunas as $cm){
            $key = $cm->location_code ."-".$cm->base_zone;
            $vl[$key] = array('id'=>$key,'name' => $cm->desc);
        }
        return $vl;
    }
    
    public function get_regiones(){
        global $wpdb;
        $qry = 'SELECT * FROM wp_wc_vwds_locations WHERE type = "region" ORDER BY "desc"';
        $regiones = $wpdb->get_results($qry, OBJECT);
        $vl = array();
        foreach($regiones as $rg){
            $key = $rg->location_code;
            //$vl[$key] = array('id'=>$key,'name' => $rg->desc);
            $vl[$key] = $rg->desc;
        }
        return $vl;
    }
}