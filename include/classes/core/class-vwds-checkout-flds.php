<?php

if ( ! defined( 'WPINC' ) ) {
 
    die;
 
}

class JGBVWDSCheckoutFields{
    public function configure_checkout_city_field($ckfs){
    

        $regiones = JGBVWDSLocations::get_regiones();
    
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

    public function enqueue_scripts_on_checkout(){
        if(is_checkout()){
            wp_enqueue_script('emp-select2', plugin_dir_url( __FILE__ ) . 'assets/js/select2-4.0.13/dist/js/select2.full.min.js', ['jquery'], false);
        }
    }

    private function get_default_vw_states_map(){
        return [
            'XV' => 'CL-AP',
            'I' => 'CL-TA',
            'II' => 'CL-AN',
            'III' => 'CL-AT',
            'IV' => 'CL-CO',
            'V' => 'CL-VS',
            'VI' => 'CL-LI',
            'VII' => 'CL-ML',
            'VIII' => 'CL-BI',
            'IX' => 'CL-AR',
            'XIV' => 'CL-LR',
            'X' => 'CL-LL',
            'XI' => 'CL-AI',
            'XII' => 'CL-MA',
            'RM' => 'CL-RM',
            'XVI' => 'CL-NB'
        ];
    }

    private function get_vwsm(){
        $vwsDefaultMap = $this->get_default_vw_states_map();
        return apply_filters('vwds_state_map', $vwsDefaultMap );
    }

    public function add_js_locations(){
        if(is_checkout()){
            $vwsm = $this->get_vwsm();
            $reg_com_url = rest_url( '/wc-vwd-sipping/comunas-por-region/' );
            ?>
    
            <script>
                const vwds_wc_states_map = <?= json_encode( $vwsm ) ?>;
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
                            let region_nm = $('#billing_vwds_region  option:selected').val();
                            region_nm = vwds_wc_states_map[region_nm];
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

                        $("#billing_vwds_region").trigger('change');

                        /* Retira select2 de campo de región de WC */
                        setTimeout(function () {
                            //$('#billing_state').hide();
                            $('#billing_state').select2('destroy');
                            $('#billing_state_field').hide();
                            $('#billing_city_field').hide();
                        }, 1500);

                        
                        
                    });
    
                })( jQuery );
            </script>
    
            <?php
        }
    }
}