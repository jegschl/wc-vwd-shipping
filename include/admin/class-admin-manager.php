<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class JGBVWDSAdminManager {

    private $assetsPath;
    private $assetsUrlPrfx;
    
    private $restAPIer;

    private $img_path_spinner;

    private $config;

    function __construct($config){
        $this->assetsPath       = isset( $config['assetsPath'] ) ? $config['assetsPath'] : plugins_url(__FILE__) . '/assets';
        $this->assetsUrlPrfx    = isset( $config['assetsUrlPrfx'] ) ? $config['assetsUrlPrfx'] : plugins_url(__FILE__) . '/assets';
        
        $this->restAPIer        = $config['restAPIer'];
        $this->img_path_spinner = $this->get_img_path_spinner();

        $this->config           = $config[ 'config' ];
    }

    public function get_img_path_spinner(){
        return $this->assetsUrlPrfx . '/imgs/spinner.gif';
    }

    public function menu(){
        add_menu_page( 
			'VWD Shipping', 
			'VWD Shipping', 
			'manage_options', 
			'jgb-vwds-settings',  
			array($this,'admin_page'), 
			'dashicons-forms', 
			11
		);
    }

    public function admin_page(){
        $tabs = $this->get_registered_tabs();
        $current_tab = $this->get_current_tab();
        $path = __DIR__ . '/views/html-admin-settings.php';
        include $path;
        
    }

    public function get_current_tab(){
        if( isset( $_GET['tab' ]) && !empty( $_GET['tab'] ) ){
            return $_GET['tab'];
        }

        return 'locations';
    }

    public function get_registered_tabs(){
        $tabs = array();
        
        $tabs['zones'] = [
            'slug'  => 'zones',
            'label' => 'Zonas y tarifas'
        ];

        $tabs['locations'] = [
            'slug'  => 'locations',
            'label' => 'Locaciones'
        ];

        $tabs['settings'] = [
            'slug'  => 'prices',
            'label' => 'Ajustes'
        ];

        return apply_filters('JGB/VWDS/admin_settings_tabs',$tabs);
    }

    public function zones_adm_html_render(){
        $mode_price = $this->config->get_option(JGB_VWDS_OPTION_NAME_MODE_PRICE);
        $path = __DIR__ . '/views/html-adm-settings-zones.php';
        include $path;
    }

    public function zones_price_mode_html_render(){
        $mode_price = $this->config->get_option(JGB_VWDS_OPTION_NAME_MODE_PRICE);
        $path = __DIR__ . '/views/html-adm-zones-price-mode-selection.php';
        include $path;
    }

    public function zones_price_importer_html_render(){
        $mode_price = $this->config->get_option(JGB_VWDS_OPTION_NAME_MODE_PRICE);
        $path = __DIR__ . '/views/html-adm-zones-price-importer.php';
        include $path;
    }

    public function locations_list_html_render(){

        $path = __DIR__ . '/views/html-adm-locations-list.php';
        include $path;

    }

    public function locations_form_add_new_html_render(){
        $img_path_spinner = $this->img_path_spinner;
        $nonce = wp_create_nonce( JGB_VWDS_LOCATIONS_NONCE_ACT_NM );
        $path = __DIR__ . '/views/html-adm-locations-add-new.php';
        include $path;
    }

    public function locations_list_actions_html_render(){
        $img_path_spinner = $this->img_path_spinner;
        $path = __DIR__ . '/views/html-adm-locations-item-actions.php';
        include $path;
    }

    public function enqueue_js_zones(){
        if( $this->is_admin_setting_zones() ){
            $script_fl = 'https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js';
            wp_enqueue_script(
                'jgb_vwds_jquery_datatable', 
                $script_fl,
                array('jquery'),
                null,
                false
            );

            wp_deregister_script('jquery-ui');
            wp_register_script('jquery-ui', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js');

            ob_start();
            $this->locations_list_actions_html_render();
            $actsHtml = ob_get_clean();
            $script_data = [
                'urlGetZones'     => $this->restAPIer->get_endpoint_base('zones'),
                'urlDelZones'     => $this->restAPIer->get_endpoint_base('remove-location'),
                'urlGetZones'     => $this->restAPIer->get_endpoint_base('get-zones'),
                'urlSetOpts'      => $this->restAPIer->get_endpoint_base('option'),
                'urlSetZoneByWR'  => $this->restAPIer->get_endpoint_base('set-zones-by-wr'),
                'actionsHtml'     => $actsHtml,
                'zfMsgAddMode'    => 'Agregar una nueva Zona',
                'zfMsgModMode'    => 'Modificar Zona con id %i',
                'priceMode'       => $this->config->get_option(JGB_VWDS_OPTION_NAME_MODE_PRICE)
            ];

            $script_fl  = '/js/admin-zones.js';
            $tversion = filemtime($this->assetsPath . $script_fl);
            $script_url = $this->assetsUrlPrfx . $script_fl;
            wp_enqueue_script(
                'jgb_vwds-admin-zones-js',
                $script_url,
                [
                    'jquery',
                    'jquery-ui'
                ],
                $tversion,
                false
            );

            wp_localize_script('jgb_vwds-admin-zones-js','JGB_VWDS',$script_data);

            if( $this->config->get_option(JGB_VWDS_OPTION_NAME_MODE_PRICE) == 'WR'){
                
                $script_fl  = '/js/admin-zones-importer.js';
                $tversion = filemtime($this->assetsPath . $script_fl);
                $script_url = $this->assetsUrlPrfx . $script_fl;
                wp_enqueue_script(
                    'jgb_vwds-admin-zones-importer-js',
                    $script_url,
                    [
                        'jquery',
                        'jquery-ui'
                    ],
                    $tversion,
                    false
                );

                $script_fl  = '/js/math-11.7.0/math.js';
                $tversion = filemtime($this->assetsPath . $script_fl);
                $script_url = $this->assetsUrlPrfx . $script_fl;
                wp_enqueue_script(
                    'jgb_vwds-math-js',
                    $script_url,
                    [],
                    $tversion,
                    false
                );

            }
        }
    }

    public function enqueue_js_locations(){
        if( $this->is_admin_setting_locations() ){
            $script_fl = 'https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js';
            wp_enqueue_script(
                'jgb_vwds_jquery_datatable', 
                $script_fl,
                array('jquery'),
                null,
                false
            );

            wp_deregister_script('jquery-ui');
            wp_register_script('jquery-ui', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js');

            ob_start();
            $this->locations_list_actions_html_render();
            $actsHtml = ob_get_clean();
            $script_data = [
                'urlGetLocations' => $this->restAPIer->get_endpoint_base('locations'),
                'urlDelLocations' => $this->restAPIer->get_endpoint_base('remove-location'),
                'actionsHtml'     => $actsHtml,
                'lfMsgAddMode'    => 'Agregar una nueva locación',
                'lfMsgModMode'    => 'Modificar locación con id %i'
            ];

            $script_fl  = '/js/admin-locations.js';
            $tversion = filemtime($this->assetsPath . $script_fl);
            $script_url = $this->assetsUrlPrfx . $script_fl;
            wp_enqueue_script(
                'jgb_vwds-admin-locations-js',
                $script_url,
                [
                    'jquery',
                    'jquery-ui'
                ],
                $tversion,
                false
            );

            wp_localize_script('jgb_vwds-admin-locations-js','JGB_VWDS',$script_data);
        }
    }

    public function enqueue_css_locations(){
        if( $this->is_admin_setting_locations() ){
            $script_fl  = '/css/admin-locations.css';
            $tversion = filemtime($this->assetsPath . $script_fl);
            $script_url = $this->assetsUrlPrfx . $script_fl;
            wp_enqueue_style(
                'jgb_vwds-admin-locations-css',
                $script_url,
                array(),
                $tversion
            );

            wp_enqueue_style(
                'jgb_vwds-datatable-css',
                'https://cdn.datatables.net/1.13.3/css/jquery.dataTables.min.css',
                array()
            );

        }
    }

    public function is_admin_setting_locations(){
        // page=jgb-vwds-settings&tab=locations

        if( !isset( $_GET['page']) ){
            return false;
        }

        if( $_GET['page'] != 'jgb-vwds-settings' ){
            return false;
        }

        if( isset( $_GET['tab'] ) && $_GET['tab'] != 'locations' ){
            return false;
        }

        return true;
    }

    public function is_admin_setting_zones(){
        // page=jgb-vwds-settings&tab=locations

        if( !isset( $_GET['page']) ){
            return false;
        }

        if( $_GET['page'] != 'jgb-vwds-settings' ){
            return false;
        }

        if( isset( $_GET['tab'] ) && $_GET['tab'] != 'zones' ){
            return false;
        }

        return true;
    }
}