<?php
/**
  Plugin Name: EMChimp
  Plugin URI: 
  Description: A WordPress - MailChimp plugin for customized WordPress based campaigns.
  Author: Erik Mitchell
  Version: 0.1.0
  Author URI: 
  Text Domain: emchimp
  Domain Path: /languages/
 */	

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class EMChimp {

	public $version='0.1.0';

	protected static $_instance = null;
	
	public $settings='';
	
	public $api='';

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}

	private function define_constants() {
		$upload_dir = wp_upload_dir();

		$this->define('EMCHIMP_PATH', plugin_dir_path(__FILE__));
		$this->define('EMCHIMP_URL', plugin_dir_url(__FILE__));
		$this->define('EMCHIMP_VERSION', $this->version);
	}

	public function includes() {
		include_once(EMCHIMP_PATH.'mailchimp-api.php');
		include_once(EMCHIMP_PATH.'admin.php');
		
		new EMChimp_Admin();
	}

	private function init_hooks() {
		add_action('init', array($this, 'init'), 0);
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name  Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	public function init() {
		$this->api=new EMChimp_MailChimp_API();
		$this->settings='get admin settings';
	}

}

function emchimp() {
	return EMChimp::instance();
}

// Global for backwards compatibility.
$GLOBALS['emchimp'] = emchimp();
?>