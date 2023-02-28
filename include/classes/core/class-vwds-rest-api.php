<?php

if ( ! defined( 'WPINC' ) ) {
 
    die;
 
}


class JGBVWDSRestApi{
    private function set_endpoints(){
        register_rest_route(
            'wc-vwd-sipping/',
            '/comunas-por-region/(?P<region_id>\d+)',
            array(
                'methods'  => 'GET',
                'callback' => 'getComunasByRegion',
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
            '/locations',
            array(
                'methods'  => 'GET',
                'callback' => 'sendLocations',
                'permission_callback' => '__return_true',
            )
        );


    }

    public function sendLocations( $request ){
        global $wpdb;

        $select  = "SELECT * FROM wp_wc_vwds_locations ";

        $select_prepare_count = "SELECT SQL_CALC_FOUND_ROWS * FROM wp_wc_vwds_locations ";
        
        $select_get_count = "SELECT FOUND_ROWS() AS total_rcds";

        $where = '';
        if(isset($_GET['search']) && !empty($_GET['search'])){
            $sv = $_GET['search']['value'];
            $where = "WHERE `desc`LIKE '%$sv%' ";
        }

        if(isset($_GET['length']) && $_GET['length']>0)
            $limit = ' LIMIT ' . $_GET['start'] . ',' . $_GET['length'];
        else 
            $limit = ' LIMIT 10';


        $orderby = "ORDER BY `desc` ASC ";

        $isql_scount = $select_prepare_count . $where;
        $isql_gcount = $select_get_count;
        $isql        = $select . $where . $orderby . $limit;

        $wpdb->get_results( $isql_scount );

        $rec_count = $wpdb->get_row($isql_gcount);

        $locations = $wpdb->get_results( $isql );

        $locations_raw = [];
        foreach( $locations as $l ){
            $locations_raw[] = [
                'DT_RowId'         => $l->id,
                'location_code'    => $l->location_code,
                'type'             => $l->type,
                'title'            => $l->desc,
                'parent'           => $l->parent    
            ];
        }

    }
    
    public function getComunasByRegion( $request ){
        $region_id = $request->get_param( 'region_id' );
        if( isset($region_id) ){
            $comunas_dpa = get_comunas_by_region(intval($region_id));
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