<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class JGBVWDSAdminManager {

    private $assetsPath;
    private $assetsUrlPrfx;
    
    private $restAPIer;

    private $img_path_spinner;

    function __construct($config){
        $this->assetsPath       = isset( $config['assetsPath'] ) ? $config['assetsPath'] : plugins_url(__FILE__) . '/assets';
        $this->assetsUrlPrfx    = isset( $config['assetsUrlPrfx'] ) ? $config['assetsUrlPrfx'] : plugins_url(__FILE__) . '/assets';
        
        $this->restAPIer        = $config['restAPIer'];
        $this->img_path_spinner = $this->get_img_path_spinner();
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
        
        $tabs['locations'] = [
            'slug'  => 'locations',
            'label' => 'Locaciones'
        ];

        $tabs['zones'] = [
            'slug'  => 'zones',
            'label' => 'Zonas'
        ];

        $tabs['prices'] = [
            'slug'  => 'prices',
            'label' => 'Tarifas'
        ];

        return apply_filters('JGB/VWDS/admin_settings_tabs',$tabs);
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
        $path = __DIR__ . '/views/html-adm-locations-actions.php';
        include $path;
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
                'actionsHtml'     => $actsHtml
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
}