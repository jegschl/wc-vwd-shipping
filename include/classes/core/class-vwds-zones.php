<?php

if ( ! defined( 'WPINC' ) ) {
 
    die;
 
}

class JGBVWDSZones{
    public function get_zones($params){
        global $wpdb;
        $res = [];

        $select  = "SELECT 
                        id,
                        code,
                        `desc`,
                        active
                    FROM wp_wc_vwds_zones zones ";

        $where = $this->get_where_clausule($params);

        $join = "LEFT JOIN wp_wc_vwds_locations prnt ON locts.parent = prnt.location_code ";

        $select_prepare_count = "SELECT SQL_CALC_FOUND_ROWS * FROM wp_wc_vwds_zones zones ";
        
        $select_get_count = "SELECT FOUND_ROWS() AS total_rcds";

        $limit = $this->get_sql_limit_clausule($params);

        $orderby = "ORDER BY `desc` ASC ";

        $isql_scount = $select_prepare_count . $where;
        $isql_gcount = $select_get_count;
        $isql        = $select . $join . $where . $orderby . $limit;

        $wpdb->get_results( $isql_scount );

        $rec_count = intval( $wpdb->get_row($isql_gcount)->total_rcds );

        $zones = $wpdb->get_results( $isql );

        $res[0] = $zones;
        $res[1] = $rec_count;

        return $res;
    }

    public function get_where_clausule($params){
        $where = "WHERE zones.`deleted` = 0 ";
        if(isset($params['search']) && !empty($params['search']) && !empty($params['search']['value'])){
            $sv = $params['search']['value'];
            $where  .= "AND `desc` LIKE '%$sv%' ";
        }

        return $where;
    }

    public function get_sql_limit_clausule($params){
        if(isset($params['length']) && $params['length']>0)
            $limit = ' LIMIT ' . $params['start'] . ',' . $params['length'];
        else 
            $limit = ' LIMIT 10';
        return $limit;
    }

    public function sendZones(){
 
        [$zones, $rec_count] = $this->get_zones($_GET);

        $zones_raw = [];
        //$row_data = [];
        foreach( $zones as $l ){
            //$row_data [ 'parent-zone-code' ] = $l->plc;

            $zones_raw[] = [
                'DT_RowId'         => $l->id,
                //'DT_RowData'       => $row_data,
                'zone_code'        => $l->code,
                'name'             => $l->desc,
                'active'           => $l->active == 0 ? 'No' : 'Si'
            ];
        }

        $res = [];

        $res['draw']            = $_GET['draw'];
        $res['recordsTotal']    = $rec_count;
        $res['recordsFiltered'] = $rec_count; //count( $zones_raw );
        $res['data']            = $zones_raw;

        $response = new WP_REST_Response( $res );
        $response->set_status( 200 );

        return $response;

    }
}