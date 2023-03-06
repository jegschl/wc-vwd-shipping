<?php

if ( ! defined( 'WPINC' ) ) {
 
    die;
 
}

class JGBVWDSLocations{
    public function receiveNewLocation( $r ){
        global $wpdb;
    }

    public function sendLocations(){
        global $wpdb;

        $select  = "SELECT * FROM wp_wc_vwds_locations ";

        $select_prepare_count = "SELECT SQL_CALC_FOUND_ROWS * FROM wp_wc_vwds_locations ";
        
        $select_get_count = "SELECT FOUND_ROWS() AS total_rcds";

        $where = "WHERE code NOT IN (\"". JGB_VWDS_NOZONES_AN_CODE ."\") ";
        if(isset($_GET['search']) && !empty($_GET['search'])){
            $sv = $_GET['search']['value'];
            $where  = "AND `desc` LIKE '%$sv%' ";
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

        $res = [];

        $res['draw']            = $_GET['draw'];
        $res['recordsTotal']    = $rec_count;
        $res['recordsFiltered'] = count( $locations_raw );
        $res['data']            = $locations_raw;

        $response = new WP_REST_Response( $res );
        $response->set_status( 200 );

        return $response;
    }
}