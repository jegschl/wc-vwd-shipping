<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

define('JGB_VWDS_NOZONES_AN_CODE','zones-weights-disabled');
define('JGB_VWDS_NOZONES_AN_DESC','Zones Weights disabled');
define('JGB_VWDS_LOCATIONS_NONCE_KEY_NM','vwds-locations');

class JGBVWDS_Manager{
    /**
	 * Core singleton class
	 * @var self - pattern realization
	 */
	private static $instance;

    /**
	 * List of paths.
	 *
	 * @since 1.0
	 * @var array
	 */
	private $paths = array();

    /**
	 * @var string
	 */
	private $plugin_name = 'wc-vwds-shipping/wc-vwd-shipping.php';

    private $productFlds;

    private $checkoutFlds;

    private $restApi;

	private $dbInitzr;

	private $adminMan;

	private $locations;

    /**
	 * Constructor loads API functions, defines paths and adds required wp actions
	 *
	 * @since 1.0
	 */
	private function __construct() {
		$dir = JGB_VWDS_PLUGIN_DIR;
		/**
		 * Define path settings for WPBakery Page Builder.
		 *
		 * APP_ROOT        - plugin directory.
		 * WP_ROOT         - WP application root directory.
		 * APP_DIR         - plugin directory name.
		 * CONFIG_DIR      - configuration directory.
		 * ASSETS_DIR      - asset directory full path.
		 * ASSETS_DIR_NAME - directory name for assets. Used from urls creating.
		 * CORE_DIR        - classes directory for core vc files.
		 * HELPERS_DIR     - directory with helpers functions files.
		 * SHORTCODES_DIR  - shortcodes classes.
		 * SETTINGS_DIR    - main dashboard settings classes.
		 * TEMPLATES_DIR   - directory where all html templates are hold.
		 * EDITORS_DIR     - editors for the post contents
		 * PARAMS_DIR      - complex params for shortcodes editor form.
		 * UPDATERS_DIR    - automatic notifications and updating classes.
		 */
		$this->setPaths( array(
			'APP_ROOT' => $dir,
			'WP_ROOT' => preg_replace( '/$\//', '', ABSPATH ),
			'APP_DIR' => basename( plugin_basename( $dir ) ),
			'CONFIG_DIR' => $dir . '/config',
			'ASSETS_DIR' => $dir . '/assets',
			'ASSETS_DIR_NAME' => 'assets',
			'AUTOLOAD_DIR' => $dir . '/include/autoload',
			'CORE_DIR' => $dir . '/include/classes/core',
			'HELPERS_DIR' => $dir . '/include/helpers',
			'SHORTCODES_DIR' => $dir . '/include/classes/shortcodes',
			'SETTINGS_DIR' => $dir . '/include/classes/settings',
			'TEMPLATES_DIR' => $dir . '/include/templates',
			'EDITORS_DIR' => $dir . '/include/classes/editors',
			'PARAMS_DIR' => $dir . '/include/params',
			'UPDATERS_DIR' => $dir . '/include/classes/updaters',
			'VENDORS_DIR' => $dir . '/include/classes/vendors',
			'DEPRECATED_DIR' => $dir . '/include/classes/deprecated',
			'ADMIN' => $dir . '/include/admin'
		) );
		// Load API

        if( $this->CheckDependenciesOk() ){

            // Load php code
            $this->load_php_api();

            // Create objects
            $this->create_objects();

            // Add hooks
            add_action( 'plugins_loaded', array(
                $this,
                'pluginsLoaded',
            ), 9 );

            add_action( 'init', array(
                $this,
                'init',
            ), 11 );

            add_action( 'jgb_vwds_activation_hook', array(
                $this->dbInitzr,
                'JGBVWDSDbInitializator::initializeTables',
            ), 11 );

            $this->admin_hooks();

            $this->public_hooks();

            $this->api_hooks();

        }
		
		$this->setPluginName( $this->path( 'APP_DIR', 'genomalab.php' ) );
		
        register_activation_hook( JGB_VWDS_PLUGIN_FILE, array(
			$this,
			'activationHook',
		) );
	}

    public function CheckDependenciesOk(){
        if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            return true;
        }

        return false;
    }

    private function admin_hooks(){
        // Agregar campos para configurar parámetros en cada producto.
        add_action( 
            'woocommerce_product_options_shipping', 
            array(
                $this->productFlds,
                'emp_add_product_shipping_fields'
            )
        );
        

        // Save the custom fields values as meta data
        add_action( 
            'woocommerce_process_product_meta', 
            array(
                $this->productFlds,
                'save_product_vol_weight_shipping_opts'
            )
        );

		add_action( 'admin_menu', [$this->adminMan,'menu'] );

		add_action('admin_enqueue_scripts',[$this->adminMan,'enqueue_js_locations']);
		add_action( 'JGB/VWDS/sections_locations', [$this->adminMan,'locations_list_html_render']);
		add_action( 'JGB/VWDS/admin_settings_locations_before_datatable', [$this->adminMan,'locations_form_add_new_html_render']);
		add_action( 'JGB/VWDS/admin_settings_locations_after_datatable', [$this->adminMan,'locations_form_add_new_html_render']);
    }

    private function public_hooks(){
        // Agrega el ,método de envío de este plugin.
        add_filter( 
            'woocommerce_shipping_methods', 
            'wc_vwds_Shipping_Method::add_wc_vwds_shipping_method'
        );

        /* Agrega campos alternativos del plugin para el checkout */
        add_filter(
            'woocommerce_checkout_fields',
            array(
                $this->checkoutFlds,
                'emp_configure_checkout_city_field'
            )
        );


        add_action(
            'wp_enqueue_scripts',
            array(
                $this->checkoutFlds,
                'enqueue_scripts_on_checkout'
            ),
            99
        );


        add_action(
            'wp_footer',
            array(
                $this->checkoutFlds,
                'add_js_locations'
            )
        );
    }

    private function api_hooks(){
        // Initialize the rest API.
        add_action( 'rest_api_init', [$this->restApi, 'set_endpoints']);
    }

    private function load_php_api(){
        //require_once $this->path( 'HELPERS_DIR', 'functions-vwds-helpers.php' );
        
        require_once $this->path( 'HELPERS_DIR', 'functions-jgb-wp.php' );
        require_once $this->path( 'CORE_DIR', 'class-vwds-product-flds.php' );
        require_once $this->path( 'CORE_DIR', 'class-vwds-checkout-flds.php' );
        require_once $this->path( 'CORE_DIR', 'class-vwds-rest-api.php' );
		require_once $this->path( 'CORE_DIR', 'class-vwds-locations.php' );
        require_once $this->path( 'CONFIG_DIR', 'class-db-config.php' );
		require_once $this->path( 'ADMIN', 'class-admin-manager.php' );

		/* la clase de wc_vwds_Shipping_Method hay que cargarla después
		 * de que inicialice el sistema de envíos de WC. */
		add_action( 'woocommerce_shipping_init', function(){
			require_once $this->path( 'CORE_DIR', 'class-vwds-shipping.php' );
		} );
		
    }

    private function create_objects(){
        $this->productFlds = new JGBVWDSProductsFields;

        $this->checkoutFlds = new JGBVWDSCheckoutFields;

		$this->locations = new JGBVWDSLocations;

        $this->restApi = new JGBVWDSRestApi( $this->locations );

        $this->dbInitzr = new JGBVWDSDbInitializator;

		$cfg = [
			'assetsPath'		=> $this->path('ASSETS_DIR'),
			'assetsUrlPrfx'		=> JGB_VWDS_PLUGIN_URL . '/assets',
			'restAPIer'			=> $this->restApi
		];
		
		$this->adminMan = new JGBVWDSAdminManager ($cfg);
    }

    /**
	 * Get the instane of GnmLab_Manager 
	 *
	 * @return self
	 */
	public static function getInstance() {
		if ( ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

    /**
	 * Callback function WP plugin_loaded action hook. Loads locale
	 *
	 * @since 1.0
	 * @access public
	 */
	public function pluginsLoaded() {
		// Setup locale
		do_action( 'jgb_vwds_plugins_loaded' );
		load_plugin_textdomain( 'jgb_vwds', false, $this->path( 'APP_DIR', 'locale' ) );
	}

    /**
	 * Callback function for WP init action hook. Sets Vc mode and loads required objects.
	 *
	 * @return void
	 * @throws \Exception
	 * @since 1.0
	 * @access public
	 */
	public function init() {
		
		ob_start();
		do_action( 'jgb_vwds_before_init' );
		ob_end_clean(); // FIX for whitespace issues (#76147)
		
		/**
		 * Set version of GNLB if required.
		 */
		$this->setVersion();
		
		
		do_action( 'jgb_vwds_after_init' );

	}

    /**
	 * Enables to add hooks in activation process.
	 * @param $networkWide
	 * @since 1.0.0
	 *
	 */
	public function activationHook( $networkWide = false ) {
		do_action( 'jgb_vwds_activation_hook', $networkWide );
	}

    protected function setPaths( $paths ) {
		$this->paths = $paths;
	}

    /**
	 * Sets version of the GNLB in DB as option `gnlb_version`
	 *
	 * @return void
	 * @since 1.0.0
	 * @access protected
	 *
	 */
	protected function setVersion() {
		$version = get_option( 'jgb_vwds_version' );
		if ( ! is_string( $version ) || version_compare( $version, JGB_VWDS_VERSION ) !== 0 ) {
			update_option( 'jgb_vwds_version', JGB_VWDS_VERSION );
		}
	}

    /**
	 * Getter for plugin name variable.
	 * @return string
	 * @since 1.0.0
	 *
	 */
	public function pluginName() {
		return $this->plugin_name;
	}

    /**
	 * @param $name
	 * @since 1.0.0
	 */
	public function setPluginName( $name ) {
		$this->plugin_name = $name;
	}

    /**
	 * Gets absolute path for file/directory in filesystem.
	 *
	 * @param $name - name of path dir
	 * @param string $file - file name or directory inside path
	 *
	 * @return string
	 * @since  1.0.0
	 * @access public
	 *
	 */
	public function path( $name, $file = '' ) {
		$path = $this->paths[ $name ] . ( strlen( $file ) > 0 ? '/' . preg_replace( '/^\//', '', $file ) : '' );

		return apply_filters( 'jgb_vwds_path_filter', $path );
	}

    /**
	 * Get absolute url for asset file.
	 *
	 * Assets are css, javascript, less files and images.
	 *
	 * @param $file
	 *
	 * @return string
	 * @since 1.0.0
	 *
	 */
	public function assetUrl( $file ) {
		return preg_replace( '/\s/', '%20', plugins_url( $this->path( 'ASSETS_DIR_NAME', $file ), JGB_VWDS_PLUGIN_FILE ) );
	}
}