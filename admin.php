<?php
	
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class EMChimp_Admin {
	
	public $settings=array();
	
	public function __construct() {
		add_action('admin_menu', array($this, 'admin_menu'));
		add_action('admin_init', array($this, 'save_settings'), 1);
		add_action('admin_init', array($this, 'load_settings'), 99);
		add_action('admin_init', array($this, 'create_campaign'));
	}
	
	public function admin_menu() {
		add_menu_page('EMChimp', 'EMChimp', 'manage_options', 'emchimp', array($this, 'admin_page'), 'dashicons-email-alt');
	}
	
	public function admin_page() {
		$html='';
		
		$html.='<div class="emchimp-admin wrap">';
			$html.='<h1>EMChimp</h1>';
			
			$html.='<form action="" method="post">';
				$html.=wp_nonce_field('emchimp_save_settings', 'emchimp_admin_settings', true, false);
			
			
				$html.='<table class="form-table">';
					$html.='<tbody>';
						$html.='<tr>';
							$html.='<th scope="row"><label for="apikey">API Key</label></th>';
							$html.='<td><input name="emchimp[apikey]" type="text" id="apikey" value="'.$this->settings['apikey'].'" class="regular-text code"></td>';
						$html.='</tr>';
					$html.='</tbody>';
				$html.='</table>';


				$html.='<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Settings"></p>';
			$html.='</form>';
			
			$html.='<h2>Create Campaign</h2>';

			$html.='<form action="" method="post">';
				$html.=wp_nonce_field('emchimp_send_campaign', 'emchimp_admin_send_campaign', true, false);
			
			
				$html.='<table class="form-table">';
					$html.='<tbody>';
						$html.='<tr>';
							$html.='<th scope="row"><label for="listid">List ID</label></th>';
							$html.='<td><input name="emchimp_send_campaign[listid]" type="text" id="listid" value="c667b1b26e" class="regular-text code"></td>';
						$html.='</tr>';
						
						$html.='<tr>';
							$html.='<th scope="row"><label for="subject">Subject</label></th>';
							$html.='<td><input name="emchimp_send_campaign[subject]" type="text" id="subject" value="" class="regular-text"></td>';
						$html.='</tr>';
						
						$html.='<tr>';
							$html.='<th scope="row"><label for="templateid">Template ID</label></th>';
							$html.='<td><input name="emchimp_send_campaign[templateid]" type="text" id="templateid" value="317197" class="regular-text code"></td>';
						$html.='</tr>';	
						
						$html.='<tr>';
							$html.='<th scope="row"><label for="preaheader">Preheader Text</label></th>';
							$html.='<td><input name="emchimp_send_campaign[preaheader]" type="text" id="preaheader" value="" class="regular-text"></td>';
						$html.='</tr>';	
						
						$html.='<tr>';
							$html.='<th scope="row"><label for="content">Email Content</label></th>';
							$html.='<td>'.$this->editor('', 'emchimp_send_campaign_content').'</td>';
						$html.='</tr>';	
											
					$html.='</tbody>';
				$html.='</table>';


				$html.='<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Create"></p>';
			$html.='</form>';
			
		$html.='</div>';
		
		echo $html;
	}
	
	protected function editor($content='', $id='foo', $options=array()) {
		ob_start();
		
		wp_editor('', 'emchimp_send_campaign_content');
						
		$editor=ob_get_clean();
		
		return $editor;
	}
	
	public function save_settings() {
		if (!isset($_POST['emchimp_admin_settings']) || !wp_verify_nonce($_POST['emchimp_admin_settings'], 'emchimp_save_settings'))
			return false;
		
		update_option('emchimp_settings', $_POST['emchimp']);
		
		//echo '<div>Settings Updated</div>';
	}
	
	public function load_settings() {
		$default_settings=array(
			'apikey' => ''
		);
		
		$this->settings=wp_parse_args(get_option('emchimp_settings', array()), $default_settings);
	}

	public function create_campaign() {
		if (!isset($_POST['emchimp_admin_send_campaign']) || !wp_verify_nonce($_POST['emchimp_admin_send_campaign'], 'emchimp_send_campaign'))
			return false;
		
		$campaign_id=emchimp()->api->create_campaign($_POST['emchimp_send_campaign']['listid'], $_POST['emchimp_send_campaign']['subject']);

		if ($campaign_id) :
		    // Set the content for this campaign
		    $template_content = array(
		        'template' => array(
					'id' => (int)$_POST['emchimp_send_campaign']['templateid'],
					// Content for the sections of the template. Each key should be the unique mc:edit area name from the template. 
					'sections'  => array(
						//'body_content' => '<h1>Hi *|FNAME|*</h1><p>This is being populated via WP</p>',
						'body_content' => $_POST['emchimp_send_campaign_content'],
						'preheader_leftcol_content' => $_POST['emchimp_send_campaign']['preaheader'],
		            ),
		        ),
		    );

		    $set_campaign_content=emchimp()->api->set_mail_campaign_content($campaign_id, $template_content);
		 
		    // Send the Campaign if the content was set.
		 
		    // NOTE: Campaign will send immediately.
		 
		    if ( $set_campaign_content ) {
		 
		        $send_campaign=emchimp()->api->api_request("campaigns/$campaign_id/actions/send", 'POST');
		 
		        if ( empty( $send_campaign ) ) {
		 
		            echo '<div class="notice notice-success is-dismissible"><p>Campaign was sent!</p></div>';
		 
		        } elseif( isset( $send_campaign->detail ) ) {
		 
		            echo '<div class="notice notice-error"><p>'.$send_campaign->detail.'</p></div>';
		 
		        }
		 
		    }
		 
		endif;		
	}
		
}
?>