<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class JGBVWDSAdminManager {

    private $assetsPath;
    private $assetsUrlPrfx;
    private $urlGetLoctns;

    function __constructor($config){
        $this->assetsPath    = $config['assetsPath'];
        $this->assetsUrlPrfx = $config['assetsUrlPrfx'];
        $this->urlGetLoctns  = $config['urlGetLocations'];
    }

    public function menu(){
        add_menu_page( 
			'VWD Shipping', 
			'VWD Shipping', 
			'manage_options', 
			'jgb-vwds-settings',  //'dosf/dosf-admin.php', 
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

        add_action('admin_enqueue_scripts',[$this,'enqueue_js_locations']);

        $path = __DIR__ . '/views/html-adm-locations-list.php';
        include $path;

    }

    public function enqueue_js_locations(){
        $script_fl = 'https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js';
		wp_enqueue_script(
			'jgb_vwds_jquery_datatable', 
			$script_fl,
			array('jquery'),
			null,
			false
		);

        $script_data = [
            'urlGetLocations' => $this->urlGetLoctns
        ];

        $script_fl  = '/admin-locations.js';
        $tversion = filemtime($this->assetsPath . $script_fl);
        $script_url = plugin_dir_url( __FILE__ ) . $script_fl;
        wp_enqueue_script(
			'jgb_vwds-admin-locations',
			$script_url,
			array('jquery'),
			null,
			false
		);

        wp_localize_script('jgb_vwds-admin-locations','JGB_VWDS',$script_data);
    }
}