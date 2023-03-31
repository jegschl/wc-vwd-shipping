<?php

if ( ! defined( 'WPINC' ) ) {
 
    die;
 
}

if ( ! class_exists( 'wc_vwds_Shipping_Method' ) ) {
    class wc_vwds_Shipping_Method extends WC_Shipping_Method {
        /**
         * Constructor for your shipping class
         *
         * @access public
         * @return void
         */
        public function __construct($instance_id = 0) {
            $this->id                   = 'wc_vwds'; 
            $this->instance_id          = absint( $instance_id );
            $this->method_title         = __( 'WC Volume-WEight-Destination Shipping', 'wc_vwds' );  
            $this->method_description   = __( 'Custom Shipping Method for Volume-Weight-Destination', 'wc_vwds' ); 
            $this->supports             = array(
                'shipping-zones',
                'instance-settings',
                'instance-settings-modal',
            );

            // Availability & Countries
            $this->availability = 'including';
            $this->countries = array(
                'CL' // Chile
                );

            $this->init();

            $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
            $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'WC Volume-WEight-Destination Shipping', 'wc_vwds' );
        }

        /**
         * Init your settings
         *
         * @access public
         * @return void
         */
        function init() {
            // Load the settings API
            $this->init_form_fields(); 
            $this->init_settings(); 

            // Save settings in admin if you have any defined
            add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
            
            global $wc_vwds;
            $wc_vwds = array();
        }

        /**
         * Define settings field for this shipping
         * @return void 
         */
        function init_form_fields() { 

            $this->form_fields = array(

             'enabled' => array(
                  'title' => __( 'Enable', 'wc_vwds' ),
                  'type' => 'checkbox',
                  'description' => __( 'Enable this shipping.', 'wc_vwds' ),
                  'default' => 'yes'
                  ),

             'title' => array(
                'title' => __( 'Title', 'wc_vwds' ),
                  'type' => 'text',
                  'description' => __( 'Title to be display on site', 'wc_vwds' ),
                  'default' => __( 'WC Volume-Weight-Destination Shipping', 'wc_vwds' )
                  )
             );

        }

        /**
         * Get setting form fields for instances of this shipping method within zones.
         *
         * @return array
         */
        public function get_instance_form_fields() {
            return parent::get_instance_form_fields();
        }

        /**
         * Always return shipping method is available
         *
         * @param array $package Shipping package.
         * @return bool
         */
        public function is_available( $package ) {
            $is_available = true;
            return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', $is_available, $package, $this );
        }

        /**
         * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
         *
         * @access public
         * @param mixed $package
         * @return void
         */
        public function calculate_shipping( $package = array() ) {
            write_log('===================== Llamada a calculate_shipping de  wc-vwd-shipping =======================');
            $weight = 0;
            $cost = 0;
            $country = $package["destination"]["country"];
            global $wc_vwds;
             
            /*write_log('Datos de $package:');
            write_log($package);*/
            foreach ( $package['contents'] as $item_id => $values ) 
            { 
                $_product = $values['data']; 
                $qty = $values['quantity'];
                $productVolMngm = $_product->get_meta('_vol_weight_enabled');
                $productVolMngm = empty($productVolMngm) ? "no" : $productVolMngm;
                /* write_log('Producto maneja peso volumétrico: '. $productVolMngm); */
                if($productVolMngm == "no"){
                    $originalWeightByUnit = apply_filters('wc_vwds_weight_no_volumetric_by_unit',$_product->get_weight(),$_product);
                    $originalWeightByPack = apply_filters('wc_vwds_weight_no_volumetric_by_pack',$originalWeightByUnit * $qty, $qty, $_product);
                    $weight += $originalWeightByPack;
                } else {

                    $largo = (float)$_product->get_length();
                    $ancho = (float)$_product->get_width();
                    $vw_mult_factor =  (float)$_product->get_meta('_vol_weight_mult_factor' );
                    $max_qty_per_pkg = $_product->get_meta('_vol_weight_max_qty_per_pkg');
                    $min_qty_for_max_height =  $_product->get_meta('_vol_weight_min_qty');
                    $weight = 0;
                    /* Se verifica la cantidad de unidades, para determinar la cantidad de paquetes. */
                    if($max_qty_per_pkg>0){
                        $alto_pallet = apply_filters('wc_vwd_shipping_pallet_height',14.4,$_product);
                        $vol_packages_count = intdiv($qty, $max_qty_per_pkg);
                        $vol_packages_rest = $qty % $max_qty_per_pkg;
                        $vol_packages_rest_weight = 0; //inicializando el peso volumétrico del resto.
                        /* Verificando si hay un resto mayor a la cantidad mmínima para paquetes con altura máxima. Y
                           si es así se incrementa en una unodad el conteo de paquetes. Si no se calcula el peso
                           volumétrico del resto obtenido considerando la altura de cada panel por la cantida obtenida
                           del resto. */
                        if($vol_packages_rest >= $min_qty_for_max_height){
                            $vol_packages_count++;
                        } else {
                            $alto = (float)$_product->get_height();
                            $vol_packages_rest_weight = $largo * $ancho * ($alto_pallet+($alto * $vol_packages_rest)) * $vw_mult_factor;
                        }
                        $alto = $_product->get_meta('_vol_weight_max_height');
                        $vol_packages_count_weight = $largo * $ancho * ($alto_pallet + $alto) * $vw_mult_factor * $vol_packages_count;
                        $weight = $vol_packages_rest_weight + $vol_packages_count_weight;
                    }
                    
                }
                
            }

            
            $weight = wc_get_weight( $weight, 'kg' );
             write_log('Peso acumulado hasta el momento: ' .$weight . ' kg.');
            
            $weight = apply_filters('wc_vwds_total_contents_weight_before_get_rule',$weight,$package['contents']);
            
            global $wpdb;
            $locationTypeForCost = apply_filters('wc_vwds_location_type_for_cost','comuna');
            $WCCheckoutFieldForLTFC = apply_filters('wc_vwds_location_type_wc_chk_field_map','billing_vwds_comuna');
            /* $destination_location = explode('-',$package["destination"][$WCCheckoutFieldForLTFC])[0];
            $destination_base_zone = explode('-',$package["destination"][$WCCheckoutFieldForLTFC])[1]; */
            $post_data = array();
            parse_str( $_POST['post_data'] ,$post_data );
            /*write_log('========= datos en $post_data =========');
            write_log($post_data);*/
            if( isset($post_data[$WCCheckoutFieldForLTFC]) && !empty($post_data[$WCCheckoutFieldForLTFC]) ){
                $pd_location_code = explode('-',$post_data[$WCCheckoutFieldForLTFC]);
                $destination_location = $pd_location_code[0];
                $destination_base_zone = $pd_location_code[1];
            }
            write_log('========= datos en $destination_location =========');
            write_log($destination_location);
            write_log('========= datos en $destination_base_zone =========');
            write_log($destination_base_zone);
            $values = array(
                $destination_location					
            );
            $qry = 'SELECT * FROM wp_wc_vwds_zones_locations WHERE location_code="%s"';
            $inst_sql = $wpdb->prepare($qry,$values);
            $zone_match_reg = $wpdb->get_row($inst_sql, OBJECT);
            write_log('============== $zone_match_reg =============');
            write_log($zone_match_reg);
            $zone_code = '';
            if( !is_null( $zone_match_reg ) ) {
                $zone_code = $zone_match_reg->zone_code;
            }
            $qry = 'SELECT * FROM wp_wc_vwds_zones WHERE code="%s"';
            $inst_sql = $wpdb->prepare($qry,array($zone_code));
            $zone_match = $wpdb->get_row($inst_sql, OBJECT);
            $values = array(
                $zone_code,
                $weight
            );
            $qry = 'SELECT * FROM wp_wc_vwds_rules WHERE destination_zone_code="%s" AND (%f BETWEEN min_weight AND max_weight)';
            $inst_sql = $wpdb->prepare($qry,$values);
            //$rule_match = $wpdb->get_row($wpdb->prepare($qry,$values), OBJECT);
            $rule_match = $wpdb->get_row($inst_sql, OBJECT);
            write_log('$rule_match: ');
            
            
            $qry = 'SELECT * FROM wp_wc_vwds_locations WHERE location_code="%s"';
            $inst_sql = $wpdb->prepare($qry,$destination_location);
            $location_match = $wpdb->get_row($inst_sql, OBJECT);
            $serv_esp_match_term = apply_filters('wc_vwds_espcial_srv_match_term','SERVICIO ESPECIAL');
            $serv_esp = $location_match->transito_servicio == $serv_esp_match_term;
            $wc_vwds['serv_esp'] = $serv_esp;
            
            if( !is_null( $rule_match ) ) {
                $useMinShippingMinPrice = apply_filters('wc_vwds_use_global_minimum_price',false);
                 if(!$serv_esp){
                    $rule_match_unit_price_rndd = round($rule_match->unit_price);
                    $cost = $weight * $rule_match_unit_price_rndd;
                    $min_cost = $rule_match->min_price;
                    $cost = $min_cost > $cost ? $min_cost : $cost;
                    write_log('Regla con match sin SERVICIO ESPECIAL para el destino:');
                    write_log($rule_match);
                } elseif($useMinShippingMinPrice){
                    $cost = $rule_match->min_price;
                    write_log('Regla con match de uso de precio mnimo:');
                    write_log($rule_match);
                }else {
                    $cost = 0;
                    write_log('Regla con match con SERVICIO ESPECIAL para el destino:');
                    write_log($rule_match);
                }
                
            } else {
                 write_log('No hay reglas con match.');
                $cost = 0;

            }

            $l = $zone_match->desc . " " . $location_match->desc;
         
            $rate = apply_filters(
                            'wc_vwd_shipping_rates',
                            array(
                                'id' => $this->id,
                                'label' => $l,
                                'cost' => $cost,
                                'weight' => $weight
                            ),
                            $package,
                            $rule_match,
                            $location_match,
                            $zone_match
                        );
             write_log('Datos de shipping rate:');
            write_log($rate);
            if($rate['cost']>0){
                $this->add_rate( $rate );
            }
            
            $wc_vwds['rule_match'] = $rule_match;
        }

        public static function add_wc_vwds_shipping_method( $methods ) {
            $methods['wc_vwds'] = 'wc_vwds_Shipping_Method';
            return $methods;
        }
    }
}