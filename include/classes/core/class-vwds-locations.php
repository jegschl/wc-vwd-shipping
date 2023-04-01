<?php

if ( ! defined( 'WPINC' ) ) {
 
    die;
 
}

class JGBVWDSLocations{

    static function get_regiones(){
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

    public function validateNewLocation( $nldt ){
        $nln = JGB_VWDS_LOCATIONS_NONCE_KEY_NM.'-nonce';
        $nonce = $nldt[ $nln ];

        $res = [];
        $res['err_status'] = 0;

        if( empty($nonce) ){
            $res['err_status']  = JGB_VWDS_LOCATIONS_UPSERT_ERR_NONCE;
            $res['err_msg']     = 'Sesión inválida';
            return $res;
        }

        if( !isset( $nldt['code'] ) || empty( $nldt['code'] ) ){
            $res['err_status']  = JGB_VWDS_LOCATIONS_UPSERT_ERR_CTT_EMPTY;
            $res['err_msg']     = 'Código vacío';
            return $res;
        }

        if( !isset( $nldt['type'] )  || empty( $nldt['type'] ) ){
            $res['err_status']  = JGB_VWDS_LOCATIONS_UPSERT_ERR_CTT_EMPTY;
            $res['err_msg']     = 'Tipo vacío';
            return $res;
        }

        if( !isset( $nldt['title'] ) || empty( $nldt['title'] ) ){
            $res['err_status']  = JGB_VWDS_LOCATIONS_UPSERT_ERR_CTT_EMPTY;
            $res['err_msg']     = 'Título vacío';
            return $res;
        }

        if( !empty( $nldt['parent'] ) ){
            global $wpdb;
            $ssql  = "SELECT COUNT(*) AS coincidencias ";
            $ssql .= "FROM wp_wc_vwds_locations ";
            $ssql .= "WHERE `location_code` = '" . $nldt['parent'] . "'";
            $ssql .= "  AND `deleted` = 0";

            $coincidencias = intval( $wpdb->get_results($ssql)[0]->coincidencias );
            if( $coincidencias < 1 ){
                $res['err_status']  = JGB_VWDS_LOCATIONS_UPSERT_ERR_NVLDPRNT;
                $res['err_msg']     = 'No existe superior';
                return $res;
            }
        }

        return $res;
    }

    public function storedLocationMatchByLocationCode( $lc ){
        global $wpdb;
        $ssql  = "SELECT COUNT(*) AS coincidencias ";
        $ssql .= "FROM wp_wc_vwds_locations ";
        $ssql .= "WHERE `location_code` = '" . $lc . "'";
        $ssql .= "  AND `deleted` = 0";

        $coincidencias = intval( $wpdb->get_results($ssql)[0]->coincidencias );

        return $coincidencias < 1 ? false : true;
    }

    public function insertNewLocation( $nldt ){
        global $wpdb;

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

        if( ($res['sql_result'] !== false) && ($res['sql_result'] > 0) ){
            $res['err_status'] = 0;
        } else {
            $res['err_status'] = JGB_VWDS_LOCATIONS_UPSERT_ERR_SQL;
            $res['err_msg'] = $wpdb->last_error;
        }
        return $res;
    }

    public function updateLocation( $nldt ){
        global $wpdb;

        $usql  = "UPDATE wp_wc_vwds_locations ";
        $usql .= "SET ";
        $usql .= is_null( $nldt['updId'] ) ? '' : "  `location_code` = '" . $nldt['code'] . "',";
        $usql .= "  `desc` = '" . $nldt['title'] ."',";
        $usql .= "  `parent` = '" . $nldt['parent'] ."',";
        $usql .= "  `type` = '" . $nldt['type'] ."' ";
        $usql .= "WHERE ";
        if( is_null( $nldt['updId'] ) ){
            $usql .= "`location_code` = '" . $nldt['code'] ."'";
        } else {
            $usql .= "`id` = '" . $nldt['updId'] ."'";
        }

        $res['sql_oprtn']  = 'update';
        $res['sql_result'] = $wpdb->query($usql);

        if( ($res['sql_result'] !== false) && ($res['sql_result'] >= 0) ){
            $res['err_status'] = 0;
        } else {
            $res['err_status'] = JGB_VWDS_LOCATIONS_UPSERT_ERR_SQL;
            $res['err_msg'] = $wpdb->last_error;
        }
        return $res;
    }

    public function receiveNewLocation( WP_REST_Request $r ){

        $nldt = $r->get_json_params();
        
        $res = [];
        $res = $this->validateNewLocation( $nldt );
        if( $res['err_status'] != 0 )
            return new WP_REST_Response( $res );
        
        if( !$this->storedLocationMatchByLocationCode(  $nldt['code'] ) && is_null( $nldt['updId'] ) ){
            $res = $this->insertNewLocation( $nldt );
            
        } else {
            $res = $this->updateLocation( $nldt );
            
        }

        $response = new WP_REST_Response( $res );

        return $response;
        
    }

    public function truncateLocationsTable(){
        global $wpdb;
        $wpdb->query("TRUNCATE TABLE wp_wc_vwds_locations");
        $wpdb->query("ALTER TABLE wp_wc_vwds_locations AUTO_INCREMENT = 1");
    }

    public function receiveNewLocationsForImport( WP_REST_Request $r ){

        $nlsdt = $r->get_json_params(); //New Locations Data

        $res = [];

        $res['err_status'] = 0;
        $res['updates_ok_count'] = 0;
        $res['updates_fail_count'] = 0;
        $res['dtls'] = [];
        $ir = [];

        if( isset($nlsdt['truncateLocations']) && $nlsdt['truncateLocations'] ){
            $this->truncateLocationsTable();
        }

        if( isset($nlsdt['data']) && is_array($nlsdt['data']) && ( count($nlsdt['data']) > 0 )){
            $nldt = [];
            foreach( $nlsdt['data'] as $nlfi ){// NewLocationForImport
                $nldt['code'] = $nlfi['location_code'];
                $nldt['type'] = $nlfi['type'];
                $nldt['title'] = $nlfi['desc'];
                $nldt['parent'] = $nlfi['parent'];
                $nldt['updId'] = null;
                $nldt['vwds-locations-nonce'] = '';
                $ir['nldt'] = $nldt;
                $locMatch = $this->storedLocationMatchByLocationCode(  $nldt['code'] );
                if( $nlsdt['updateExistentLocations'] && $locMatch ){
                    $csor = $this->updateLocation( $nldt );
                    if( $csor['err_status'] == 0 ){
                        $res['updates_ok_count']++;
                    } else {
                        $res['updates_fail_count']++;
                        $ir['err_status'] = $csor['err_status'];
                        $ir['err_msg'] = $csor['err_msg'];
                    }
                } elseif(!$nlsdt['updateExistentLocations'] && $locMatch){
                    $res['updates_fail_count']++;
                } elseif(!$locMatch && !$nlsdt['createNewLocations'] ){
                    $res['inserts_fail_count']++;
                } elseif( !$locMatch && $nlsdt['createNewLocations'] ){
                    $csor = $this->insertNewLocation( $nldt );
                    if( $csor['err_status'] == 0 ){
                        $res['inserts_ok_count']++;
                    } else {
                        $res['inserts_fail_count']++;
                        $ir['err_status'] = $csor['err_status'];
                        $ir['err_msg'] = $csor['err_msg'];
                    }
                }

                $res['dtls'][$nldt['code']] = $ir;
            }
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

        $select  = "SELECT 
                        locts.id, 
                        locts.location_code,
                        locts.type,
                        locts.desc,
                        prnt.desc as parent,
                        locts.parent as plc,
                        locts.active
                    FROM wp_wc_vwds_locations locts ";

        $join = "LEFT JOIN wp_wc_vwds_locations prnt ON locts.parent = prnt.location_code ";

        $select_prepare_count = "SELECT SQL_CALC_FOUND_ROWS * FROM wp_wc_vwds_locations locts ";
        
        $select_get_count = "SELECT FOUND_ROWS() AS total_rcds";

        //$where = "WHERE code NOT IN (\"". JGB_VWDS_NOZONES_AN_CODE ."\") ";
        $where = "WHERE locts.`deleted` = 0 ";
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
        $isql        = $select . $join . $where . $orderby . $limit;

        $wpdb->get_results( $isql_scount );

        $rec_count = intval( $wpdb->get_row($isql_gcount)->total_rcds );

        $locations = $wpdb->get_results( $isql );

        $locations_raw = [];
        $row_data = [];
        foreach( $locations as $l ){
            $row_data [ 'parent-location-code' ] = $l->plc;

            $locations_raw[] = [
                'DT_RowId'         => $l->id,
                'DT_RowData'       => $row_data,
                'selection'        => '',
                'location_code'    => $l->location_code,
                'type'             => $l->type,
                'title'            => $l->desc,
                'parent'           => $l->parent,
                'active'           => $l->active == 0 ? 'No' : 'Si',
                'actions'          => ''
            ];
        }

        $res = [];

        $res['draw']            = $_GET['draw'];
        $res['recordsTotal']    = $rec_count;
        $res['recordsFiltered'] = $rec_count; //count( $locations_raw );
        $res['data']            = $locations_raw;

        $response = new WP_REST_Response( $res );
        $response->set_status( 200 );

        return $response;
    }
}