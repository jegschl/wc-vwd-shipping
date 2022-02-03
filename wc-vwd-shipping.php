<?php
 
/**
 * Plugin Name: Woocommerce Volume-Weight-Destination Shipping
 * Plugin URI: https://empdigital.cl/plugins/wc-vwd-shipping
 * Description: Plugin de método de envío por Volumen-Peso-Destino
 * Version: 1.0.0
 * Author: Jorge Garrido / EMP Digital
 * Author URI: https://empdigital.cl/devteam/jegschl
 * License: GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path: /lang
 * Text Domain: wc-vwd-shipping
 */
 
if ( ! defined( 'WPINC' ) ) {
 
    die;
 
}
 
/*
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
 
    function wc_vwds_shipping_method() {
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
							$weight = $weight + $_product->get_weight() * $qty;
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
					
					global $wpdb;
                    $locationTypeForCost = apply_filters('wc_vwds_location_type_for_cost','region');
                    $WCCheckoutFieldForLTFC = apply_filters('wc_vwds_location_type_wc_chk_field_map','state');
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
					$qry = 'SELECT * FROM wp_wc_vwds_rules WHERE zone_code="%s" AND (%f BETWEEN min_weight AND max_weight)';
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


            }
        }
    }
 
    add_action( 'woocommerce_shipping_init', 'wc_vwds_shipping_method' );
 
    add_filter( 'woocommerce_shipping_methods', 'add_wc_vwds_shipping_method' );
    function add_wc_vwds_shipping_method( $methods ) {
        $methods['wc_vwds'] = 'wc_vwds_Shipping_Method';
        return $methods;
    }
 
    
 
    function wc_vwds_validate_order( $posted )   {
 
        $packages = WC()->shipping->get_packages();
 
        $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
         
        if( is_array( $chosen_methods ) && in_array( 'wc_vwds', $chosen_methods ) ) {
             
            foreach ( $packages as $i => $package ) {
 
                if ( $chosen_methods[ $i ] != "wc_vwds" ) {
                             
                    continue;
                             
                }
 
                $wc_vwds_Shipping_Method = new wc_vwds_Shipping_Method();
                $weightLimit = (int) $wc_vwds_Shipping_Method->settings['weight'];
                $weight = 0;
 
                foreach ( $package['contents'] as $item_id => $values ) 
                { 
                    $_product = $values['data']; 
                    $weight = $weight + $_product->get_weight() * $values['quantity']; 
                }
 
                $weight = wc_get_weight( $weight, 'kg' );
                
                if( $weight > $weightLimit ) {
 
                        $message = sprintf( __( 'Sorry, %d kg exceeds the maximum weight of %d kg for %s', 'wc_vwds' ), $weight, $weightLimit, $wc_vwds_Shipping_Method->title );
                             
                        $messageType = "error";
 
                        if( ! wc_has_notice( $message, $messageType ) ) {
                         
                            wc_add_notice( $message, $messageType );
                      
                        }
                }
            }       
        } 
    }
 
    //add_action( 'woocommerce_review_order_before_cart_contents', 'wc_vwds_validate_order' , 10 );
    //add_action( 'woocommerce_after_checkout_validation', 'wc_vwds_validate_order' , 10 );

	// Add custom fields to product shipping tab
add_action( 'woocommerce_product_options_shipping', 'emp_add_product_shipping_fields');
function emp_add_product_shipping_fields(){
	global $post;
	global $product_object;


	echo '</div><div class="emp_opts_group_vol_weight">'; // New option group
	
	$vwe = $product_object->get_meta('_vol_weight_enabled');
	woocommerce_wp_checkbox( array(
        'id'          => '_vol_weight_enabled',
        'label'       => __( 'Activar peso volumétrico', 'woocommerce' ),
        'desc_tip'    => 'false',
		'value'       => empty($vwe) ? 'no' : $vwe,
		
		
    ) );

    woocommerce_wp_text_input( array(
        'id'          => '_vol_weight_mult_factor',
        'label'       => __( 'Factor de multiplicación', 'woocommerce' ),
        'placeholder' => 'Ingrese el factor de multiplicación para el peso volumétrico',
        'desc_tip'    => 'true',
        'description' => __( 'Ingrese el factor de multiplicación para el peso volumétrico.', 'woocommerce' ),
		'value'       => get_post_meta( $post->ID, '_vol_weight_mult_factor', true ),
		
    ) );

    woocommerce_wp_text_input( array(
        'id'          => '_vol_weight_min_qty',
        'label'       => __( 'Cantidad mínima para altura máxima', 'woocommerce' ),
        'placeholder' => 'Ingrese la cantidad mínima para altura máxima',
        'desc_tip'    => 'true',
        'description' => __( 'Cantidad mínima para altura máxima.', 'woocommerce' ),
        'value'       => get_post_meta( $post->ID, '_vol_weight_min_qty', true ),
    ) );
	
	woocommerce_wp_text_input( array(
        'id'          => '_vol_weight_max_height',
        'label'       => __( 'Altura máxima', 'woocommerce' ),
        'placeholder' => 'Ingrese la altura máxima en cm',
        'desc_tip'    => 'true',
        'description' => __( 'Altura máxima', 'woocommerce' ),
        'value'       => get_post_meta( $post->ID, '_vol_weight_max_height', true ),
    ) );
	
	woocommerce_wp_text_input( array(
        'id'          => '_vol_weight_max_qty_per_pkg',
        'label'       => __( 'Cantidad máxima por unidad de empaque (pallet, bulto, etc.)', 'woocommerce' ),
        'placeholder' => 'Ingrese la cantidad máxima por unidad de empaque para envío',
        'desc_tip'    => 'true',
        'description' => __( 'Cantidad máxima por unidad de empaque para el cálculo del peso volumétrico.', 'woocommerce' ),
        'value'       => get_post_meta( $post->ID, '_vol_weight_max_qty_per_pkg', true ),
    ) );
}

// Save the custom fields values as meta data
add_action( 'woocommerce_process_product_meta', 'save_product_vol_weight_shipping_opts' );
function save_product_vol_weight_shipping_opts( $post_id ){

	$vol_weight_enabled = isset($_POST['_vol_weight_enabled']) ? 'yes' : 'no';
    if( isset( $vol_weight_enabled ) )
        update_post_meta( $post_id, '_vol_weight_enabled', esc_attr( $vol_weight_enabled ) );

    $vol_weight_mult_factor = $_POST['_vol_weight_mult_factor'];
    if( isset( $vol_weight_mult_factor ) )
        update_post_meta( $post_id, '_vol_weight_mult_factor', esc_attr( $vol_weight_mult_factor ) );

    $vol_weight_min_qty = $_POST['_vol_weight_min_qty'];
    if( isset( $vol_weight_min_qty ) )
        update_post_meta( $post_id, '_vol_weight_min_qty', esc_attr( $vol_weight_min_qty ) );

	$vol_weight_max_qty_per_pkg = $_POST['_vol_weight_max_qty_per_pkg'];
	if( isset( $vol_weight_max_qty_per_pkg ) )
		update_post_meta( $post_id, '_vol_weight_max_qty_per_pkg', esc_attr( $vol_weight_max_qty_per_pkg ) );
	
	$vol_weight_max_height = $_POST['_vol_weight_max_height'];
	if( isset( $vol_weight_max_height ) )
		update_post_meta( $post_id, '_vol_weight_max_height', esc_attr( $vol_weight_max_height ) );
}
}

//add_filter('woocommerce_states','emp_set_state_list',99);
function emp_set_state_list($vl){
	global $wpdb;
	$qry = 'SELECT * FROM wp_wc_vwds_locations WHERE type = "region" ORDER BY "desc"';
	$states = $wpdb->get_results($qry, OBJECT);
	$vl = array();
	foreach($states as $st){
        $key = $st->location_code ."-".$st->base_zone;
		$vl[$key] = $st->desc;
	}
	return array('CL' => $vl);
}

//add_action('woocommerce_checkout_update_order_review','emp_set_shipping_state');
function emp_set_shipping_state($data){
	return;
}

//add_action('woocommerce_before_get_rates_for_package','emp_on_calculate_shipping_set_state_dest',10,2);
function emp_on_calculate_shipping_set_state_dest($package, $shipping_method ){
    $address_state_dest_code = explode('-',$package['destination']['state'])[1];
    $package['destination']['state'] = $address_state_dest_code;
}

add_action( 'rest_api_init', 'set_endpoints');
function set_endpoints(){
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
}

function getComunasByRegion( $request ){
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

function get_comunas_by_region($parent_id){
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

function get_regiones(){
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


/* Agrega campos alternativos del plugin para el checkout */
add_filter('woocommerce_checkout_fields','emp_configure_checkout_city_field');
function emp_configure_checkout_city_field($ckfs){
    

    $regiones = get_regiones();

    $ckfs['billing']['billing_vwds_region']['type'] = 'select';
    $ckfs['billing']['billing_vwds_region']['label'] = 'Región';
    $ckfs['billing']['billing_vwds_region']['class'][] = 'form-row-first';
    $ckfs['billing']['billing_vwds_region']['options'] = $regiones;


    $ckfs['billing']['billing_vwds_comuna']['type'] = 'select';
    $ckfs['billing']['billing_vwds_comuna']['label'] = 'Comuna/Localidad';
    $ckfs['billing']['billing_vwds_comuna']['class'][] = 'form-row-last';
    $ckfs['billing']['billing_vwds_comuna']['options'] = array('Selecciona una comuna/localidad');

    $ckfs['billing']['billing_vwds_region']['priority'] = apply_filters('wc_vwd_field_priority_region',22);
    $ckfs['billing']['billing_vwds_comuna']['priority'] = apply_filters('wc_vwd_field_priority_comuna',23);
    return $ckfs;
}

//add_action('woocommerce_checkout_process','emp_process_city_name',10,1);
function emp_process_city_name(){
	write_log('================= Post: ================');
	write_log($_POST);
    global $wpdb;
	$bc_code = $_POST['billing_city'];
    $loc_code = explode('-',$bc_code)[0];
    $bs_zone  = explode('-',$bc_code)[1];
    $qry = 'SELECT * FROM wp_wc_vwds_locations WHERE location_code = "'. $loc_code .'" AND base_zone = "'.$bs_zone.'" ORDER BY "desc"';
    $location = $wpdb->get_results($qry, OBJECT);
    $city_name = $location[0]->desc;
	$_POST['billing_city'] = $_POST['shipping_city'] = $city_name;
	
}

add_action('wp_enqueue_scripts','emp_enqueue_scripts_on_checkout',99);
function emp_enqueue_scripts_on_checkout(){
    if(is_checkout()){
        wp_enqueue_script('emp-select2', plugin_dir_url( __FILE__ ) . 'assets/js/select2-4.0.13/dist/js/select2.full.min.js', ['jquery'], false);
    }
}

add_action('wp_footer','emp_add_js_locations');
function emp_add_js_locations(){
    if(is_checkout()){
        
        $reg_com_url = rest_url( '/wc-vwd-sipping/comunas-por-region/' );
        ?>

        <script>
            (function( $ ) {
                const reg_com_url = '<?= $reg_com_url ?>';
                $(document).ready(function ($) {
                    const select2_options = {
                        sorter: function(data) {
                            return data.sort(function(a, b) {
                                return a.text < b.text ? -1 : a.text > b.text ? 1 : 0;
                            });
                        }
                    };

                    <?php $slct2opt = apply_filters('vwds_slct2opts_comuna','select2_options'); ?>
                    $('#billing_vwds_comuna').select2(<?= $slct2opt ?>);
                    <?php $slct2opt = apply_filters('vwds_slct2opts_region','select2_options'); ?>
                    $('#billing_vwds_region').select2(<?= $slct2opt ?>);

                    $("#billing_vwds_region").on("change", function(){
                        let region_id = $('#billing_vwds_region').val();
                        //region_id = region_id.split('-')[0];
                        let region_nm = $('#billing_vwds_region  option:selected').text();
                        $('#billing_state').val(region_nm);
                        console.log('===== Valor de #billing_state: ' + $('#billing_state').val());

                        const blkCnf = {
                            message: 'Cargando comunas...'
                        };
                        $('#billing_vwds_comuna_field').block(blkCnf);

                        $.ajax({
                            type: "GET",
                            url: reg_com_url + region_id,
                            headers: {
                                'Content-Type': 'application/json; charset=utf-8',
                                'Accept': 'application/json'
                            },
                            success: function(res){
                                var thoc = ""; // Temporal html option code.
                                var newOpt = {};
                                $('#billing_vwds_comuna').find('option').remove();
                                //thoc = '<option value="0">Seleccine una comuna</option>';
                                $('#billing_vwds_comuna').append(thoc);
                                for(var i = 0; i < res.comunas.length; i++){
                                    newOpt = new Option(res.comunas[i].name, res.comunas[i].id, false, false);
                                    $('#billing_vwds_comuna').append(newOpt).trigger('change');
                                    
                                }
                                
                            },
                            complete: function(jqXHR, textStatus){
                                $('#billing_vwds_comuna_field').unblock();
                            }
                        });
                    });

                    $('#billing_vwds_comuna').on('change', function(){
                        let comuna_nm = $('#billing_vwds_comuna  option:selected').text();
                        $('#billing_city').val(comuna_nm);
                        console.log('===== Valor de #billing_city: ' + $('#billing_city').val());
                        $( document.body ).trigger( 'update_checkout' );
                    });
                    
                });

            })( jQuery );
        </script>

        <?php
    }
}

//add_filter('woocommerce_package_rates','emp_wvds_add_location_code_field_to_package');
function emp_wvds_add_location_code_field_to_package($r){
    return $r;
}

