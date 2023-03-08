<?php

if ( ! defined( 'WPINC' ) ) {
 
    die;
 
}

class JGBVWDSLocations{
    public function receiveNewLocation( $r ){
        global $wpdb;
        $nldt = $r->get_json_params();
        $nln = JGB_VWDS_LOCATIONS_NONCE_KEY_NM.'-nonce';
        $nonce = $nldt[ $nln ];

        $res = [];

        if( empty($nonce) ){
            $res['err_status']  = JGB_VWDS_LOCATIONS_UPSERT_ERR_NONCE;
            $res['err_msg']     = 'Sesión inválida';
            return new WP_REST_Response( $res );
        }

        if( !isset( $nldt['code'] ) || empty( $nldt['code'] ) ){
            $res['err_status']  = JGB_VWDS_LOCATIONS_UPSERT_ERR_CTT_EMPTY;
            $res['err_msg']     = 'Código vacío';
            return new WP_REST_Response( $res );
        }

        if( !isset( $nldt['type'] )  || empty( $nldt['type'] ) ){
            $res['err_status']  = JGB_VWDS_LOCATIONS_UPSERT_ERR_CTT_EMPTY;
            $res['err_msg']     = 'Tipo vacío';
            return new WP_REST_Response( $res );
        }

        if( !isset( $nldt['title'] ) || empty( $nldt['title'] ) ){
            $res['err_status']  = JGB_VWDS_LOCATIONS_UPSERT_ERR_CTT_EMPTY;
            $res['err_msg']     = 'Título vacío';
            return new WP_REST_Response( $res );
        }

        if( !empty( $nldt['parent'] ) ){
            $ssql  = "SELECT COUNT(*) AS coincidencias ";
            $ssql .= "FROM wp_wc_vwds_locations ";
            $ssql .= "WHERE `location_code` = '" . $nldt['parent'] . "'";
            $ssql .= "  AND `deleted` = 0";

            $coincidencias = intval( $wpdb->get_results($ssql)[0]->coincidencias );
            if( $coincidencias < 1 ){
                $res['err_status']  = JGB_VWDS_LOCATIONS_UPSERT_ERR_NVLDPRNT;
                $res['err_msg']     = 'No existe superior';
                return new WP_REST_Response( $res );
            }
        }

        $ssql  = "SELECT COUNT(*) AS coincidencias ";
        $ssql .= "FROM wp_wc_vwds_locations ";
        $ssql .= "WHERE `location_code` = '" . $nldt['code'] . "'";
        $ssql .= "  AND `deleted` = 0";

        $coincidencias = intval( $wpdb->get_results($ssql)[0]->coincidencias );
        if( $coincidencias < 1 ){
            $isql  = "INSERT INTO wp_wc_vwds_locations ( ";
            $isql .= "  location_code,";
            $isql .= "  `desc`,";
            $isql .= "  `parent`,";
            $isql .= "  `type`";
            $isql .= ") ";
            $isql .= "VALUES (";
            $isql .= "  '". $nldt['code'] ."',";
            $isql .= "  '". $nldt['title'] ."',";
            $isql .= "  '". $nldt['parent'] ."',";
            $isql .= "  '". $nldt['type'] ."'";
            $isql .= ")";

            $res['sql_oprtn']  = 'insert';
            $res['sql_result'] = $wpdb->query($isql);
        } else {
            $usql  = "UPDATE wp_wc_vwds_locations ";
            $usql .= "SET ";
            $usql .= "  `desc` = '" . $nldt['title'] ."',";
            $usql .= "  `parent` = '" . $nldt['parent'] ."',";
            $usql .= "  `type` = '" . $nldt['type'] ."' ";
            $usql .= "WHERE `location_code` = '" . $nldt['code'] ."'";
            
            $res['sql_oprtn']  = 'update';
            $res['sql_result'] = $wpdb->query($usql);
        }

        $response = new WP_REST_Response( $res );
        return $response;
    }

    public function removeLocations( WP_REST_Request $r ){
        global $wpdb;
        $locations_ids_to_remove = $r->get_json_params();
        
        try{
            $i = 0;
            foreach( $locations_ids_to_remove as $idtr){
                $ir = $wpdb->update(
                    'wp_wc_vwds_locations',
                    ['deleted' => 1],
                    ['id' => $idtr]
                );
                if( $ir ){
                    $i++;
                }
            }
            $res = [
                'err_status'   => 0,
                'deleted_rows' => $i
            ];
        } catch ( Exception $e ){
            $res['err_status'] = 1;
            $res['err_msg'] = $e->getMessage();
        }

        $response = new WP_REST_Response( $res );
        return $response;
    }

    public function sendLocations(){
        global $wpdb;

        $select  = "SELECT * FROM wp_wc_vwds_locations ";

        $select_prepare_count = "SELECT SQL_CALC_FOUND_ROWS * FROM wp_wc_vwds_locations ";
        
        $select_get_count = "SELECT FOUND_ROWS() AS total_rcds";

        //$where = "WHERE code NOT IN (\"". JGB_VWDS_NOZONES_AN_CODE ."\") ";
        $where = "WHERE `deleted` = 0 ";
        if(isset($_GET['search']) && !empty($_GET['search']) && !empty($_GET['search']['value'])){
            $sv = $_GET['search']['value'];
            $where  .= "AND `desc` LIKE '%$sv%' ";
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

        $rec_count = intval( $wpdb->get_row($isql_gcount)->total_rcds );

        $locations = $wpdb->get_results( $isql );

        $locations_raw = [];
        foreach( $locations as $l ){
            $locations_raw[] = [
                'DT_RowId'         => $l->id,
                'location_code'    => $l->location_code,
                'type'             => $l->type,
                'title'            => $l->desc,
                'parent'           => $l->parent,
                'active'           => $l->active == 0 ? 'No' : 'Si'   
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