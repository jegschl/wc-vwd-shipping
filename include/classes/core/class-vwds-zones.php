<?php

if ( ! defined( 'WPINC' ) ) {
 
    die;
 
}

define('JGB_VWDS_ZNCD_GEN_MODE_LOW_ZONE_COUNT',0);
define('JGB_VWDS_ZNCD_GEN_MODE_SECUENTIAL',1);
class JGBVWDSZones{
    private $price_mode;

    private $last_generated_zone_code;

    private $zone_code_generation_mode;

    function __construct()
    {
        $this->zone_code_generation_mode = JGB_VWDS_ZNCD_GEN_MODE_LOW_ZONE_COUNT;
    }

    public function get_zones($params){
        global $wpdb;
        $res = [];

        $select  = "SELECT 
                        zones.id,
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

    private function resetZoneTable(){
        global $wpdb;

        $isql = "TRUNCATE TABLE wp_wc_vwds_zones";

        $wpdb->query( $isql );

        $isql = "ALTER TABLE wp_wc_vwds_zones AUTO_INCREMENT=1";

        $wpdb->query( $isql );
    }

    private function resetRulesTable(){
        global $wpdb;

        $isql = "TRUNCATE TABLE wp_wc_vwds_rules";

        $wpdb->query( $isql );

        $isql = "ALTER TABLE wp_wc_vwds_rules AUTO_INCREMENT=1";

        $wpdb->query( $isql );
    }

    public function receiveZonesByWR( WP_REST_Request $r ){
        $dt = $r->get_json_params();

        $this->resetZoneTable();

        $this->resetRulesTable();

        $zir = $this->insertZones( $dt['zones'] );
        
        $rir = $this->insertRules( $zir, $dt['weight'],$dt['prices'] );

        $response = new WP_REST_Response( $rir );
        $response->set_status( 200 );
        
        return $response;
    }

    private function insertRules( $zonesInfo, $weights, $prices ){
        $rir = [];
        $i = 0;
        foreach( $zonesInfo as $zk => $zi ){
            $j = 0;
            foreach($weights as $w ){
                [$mnw,$mxw] = explode('-',$w);
                $ri = [
                    'mode'                  => $this->price_mode,
                    'min_weight'            => $mnw,
                    'max_weight'            => $mxw,
                    'unit_price'            => $prices[$j][$i],
                    'min_price'             => $prices[$j][$i],
                    'destination_zone_code' => $zi['code']
                ];
                $tr = $this->insertRule( $ri );
                if($tr['insert_err']){
                    $tr['zone_desc'] = $zk;
                    $tr['zone_code'] = $zi['code'];
                    $tr['weight_range'] = $w;
                }
                $rir[] = $tr;
                $j++;
            }
            $i++;
        }

        return $rir;
    }

    private function insertRule($ruleInfo){
        global $wpdb;
       
        $ir = $wpdb->insert(
            'wp_wc_vwds_rules',
            $ruleInfo
        );

        if( $ir !== false ){
            $result = [
                'insert_err' => false,
                'rule_id' => $wpdb->insert_id
            ];
        } else {
            $result = [
                'insert_err' => true,
                'insert_err_msg' => $wpdb->last_error
            ];
        }

        return $result;
    }

    private function insertZones( $zones ){

        $zones_inserted = [];

        foreach( $zones as $z ){

            $zones_inserted[$z] = $this->insertZone( $z );
            
        }

        return $zones_inserted;
    }

    private function insertZone( $zone ){
        global $wpdb;
        $zone_code = $this->generate_code( $zone );
        $ir = $wpdb->insert(
            'wp_wc_vwds_zones',
            [
                'code' => $zone_code,
                'desc' => $zone
            ]
        );

        if( $ir !== false ){
            $result = [
                'insert_err' => false,
                'zone_id' => $wpdb->insert_id,
                'code'    => $zone_code
            ];
        } else {
            $result = [
                'insert_err' => true,
                'insert_err_msg' => $wpdb->last_error
            ];
        }

        return $result;
    }

    public function  generate_code( $zone ){
        
        $zdis = explode(' ',$zone);
        $zone_code = '';

        if( $this->zone_code_generation_mode == JGB_VWDS_ZNCD_GEN_MODE_LOW_ZONE_COUNT ){
            foreach($zdis as $zdi){
                $zone_code .= substr($zdi,0,1);
            }
        }

        if( ( $this->zone_code_generation_mode == JGB_VWDS_ZNCD_GEN_MODE_SECUENTIAL ) && !is_null($this->last_generated_zone_code)){
            $last_sec = intval($this->last_generated_zone_code);
            $zone_code = $last_sec++;
        }

        $zone_code = apply_filters('JGB/VWDS/zones/generate_code',$zone_code,$zone);

        if( $this->zone_code_generation_mode == JGB_VWDS_ZNCD_GEN_MODE_SECUENTIAL ){
            $this->last_generated_zone_code = $zone_code;
        }

        return $zone_code;

        
    }

    public function set_price_mode( $mode ){
        $this->price_mode = $mode;
    }
}