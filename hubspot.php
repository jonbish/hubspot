<?php
/*
Plugin Name: HubSpot for WordPress
Description: The HubSpot for WordPress plugin integrates the power of HubSpot with your WordPress site
Author: Jon Bishop
Version: 1.6
Requires at least: 2.9
Author URI: http://www.hubspot.com
License: GPL
*/
/*  Copyright 2011  HubSpot  (email : jonbish@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//=============================================
// Define constants
//=============================================
if ( ! defined( 'HUBSPOT_URL' ) ){
	define('HUBSPOT_URL', plugin_dir_url(__FILE__));
}
if ( ! defined( 'HUBSPOT_PATH' ) ){
	define('HUBSPOT_PATH', plugin_dir_path(__FILE__));
}
if ( ! defined( 'HUBSPOT_BASENAME' ) ){
	define('HUBSPOT_BASENAME', plugin_basename( __FILE__ ));
}
if ( ! defined( 'HUBSPOT_ADMIN' ) ){
	define('HUBSPOT_ADMIN', get_bloginfo('url')."/wp-admin");
}
//=============================================
// Include needed files
//=============================================
require_once(HUBSPOT_PATH."inc/hs-social.php");
require_once(HUBSPOT_PATH."inc/hs-action.php");
require_once(HUBSPOT_PATH."inc/hs-analytics.php");
require_once(HUBSPOT_PATH."inc/hs-contact.php");
require_once(HUBSPOT_PATH."inc/hs-team.php");
require_once(HUBSPOT_PATH."inc/hs-leads.php");
require_once(HUBSPOT_PATH."hs-admin.php");
require_once(HUBSPOT_PATH."hs-widgets.php");
require_once(HUBSPOT_PATH."hs-wysiwyg.php");
require_once(HUBSPOT_PATH."hs-notice.php");
require_once(HUBSPOT_PATH."hs-usage.php");

//=============================================
// WPHubspot Class
//=============================================
class WPHubspot {
	//=============================================
	// Hooks and Filters
	//=============================================
	function WPHubspot(){
		global $myhubspotwp_admin, $myhubspotwp_analytics, $myhubspotwp_contact, $myhubspotwp_leads, $myhubspotwp_social, $myhubspotwp_team, $myhubspotwp_action, $myhubspotwp_notice, $myhubspotusage;
		
		$myhubspotwp_admin = new WPHubspotAdmin();
                $myhubspotwp_editor = new WPHubspotCustomEditor();
		
		$myhubspotwp_analytics = new WPHubspotAnalytics();
		$myhubspotwp_contact = new WPHubspotContact();
		$myhubspotwp_leads = new WPHubspotLeads();
		$myhubspotwp_social = new WPHubspotSocial();
		$myhubspotwp_team = new WPHubspotTeam();

                $myhubspotusage = new WPHubspotUsage();

                // Display config notice
		$myhubspotwp_notice = new WPHubspotNotice();
                add_action('admin_notices', array(&$myhubspotwp_notice, 'configuration_warning'));
                
		if ((float)substr(get_bloginfo('version'), 0, 3) >= '3.0') {
			$myhubspotwp_action = new WPHubspotAction();
			
			if($myhubspotwp_action->hs_actions_enabled()){
				add_action('widgets_init', create_function('', 'return register_widget("HubSpot_Action_Widget");'));
			}
		}
		if($this->hs_is_customer()){
			add_action('widgets_init', create_function('', 'return register_widget("HubSpot_Social_Widget");'));
		}
		add_action('widgets_init', create_function('', 'return register_widget("HubSpot_EmailSubscribe_Widget");'));
                add_action('widgets_init', create_function('', 'return register_widget("HubSpot_WSGrader_Widget");'));
                add_action('widgets_init', create_function('', 'return register_widget("HubSpot_TwitterGrader_Widget");'));
	}

	//=============================================
	// Add default settings
	//=============================================
	function add_defaults_hs() {
		$tmp = get_option('hs_settings');
		
		if(($tmp['hs_installed']!='on')||(!is_array($tmp))) {
			$hs_settings = array(
						"hs_installed"			=>"on",
						"hs_company_name"		=>"",
						"hs_company_address"		=>"",
						"hs_company_citystate"		=>"",
						"hs_company_phone"		=>"",
						"hs_portal"			=>"",
						"hs_appdomain"			=>"",
						"hs_feedburner_url"		=>"",
						"hs_feedburner_comments_url"    =>"",
						"hs_team_avatars"		=>'on',
						"hs_team_admin"			=>'',
						"hs_actions_disabled"		=>'',
						"hs_actions_stats_disabled"	=>'',
						"hs_leads_enabled"  		=>'on',
						"hs_hubfeed_url"		=>'',
						"hs_config_notice"		=>'',
                                                "hs_email_sent"                 =>''
						);
			// Upgrade and delete old options
			if($options = get_option('HubSpotAnalyticsPP')){
				$hs_settings['hs_portal'] = $options['portal'];
				$hs_settings['hs_appdomain'] = $options['appdomain'];
				delete_option('HubSpotAnalyticsPP');
			}
			update_option('hs_settings', $hs_settings);
			$tmp = $hs_settings;
		}
		// 1.2.1 upgrade
		if (!$tmp['hs_config_notice']){
			$tmp['hs_config_notice'] = '';
			update_option('hs_settings', $tmp);
		}
                // 1.5 upgrade
                if(isset($tmp['hs_leads_html'])){
                        $formid = 0;
                        foreach($tmp['hs_leads_html'] as $form_option){
                            update_option("hs_form_settings_" . $formid, $form_option);
                            $formid++;
                        }
                        unset($tmp['hs_leads_html']);
                        $tmp['hs_email_sent'] = false;
                        update_option('hs_settings', $tmp);
                }
	}
	
	//=============================================
	// Test if user is HubSpot cusomter
	//=============================================
	function hs_is_customer($hs_portal = "", $hs_appdomain = ""){
		if(trim($hs_portal) != "" && trim($hs_appdomain) !=""){
			return true;
		} else {
			$hs_settings = get_option('hs_settings');
			if(trim($hs_settings['hs_portal']) != "" && trim($hs_settings['hs_appdomain']) !=""){
				return true;
			} else {
				return false;	
			}
		}
	}
	
	//=============================================
	// Format text for WordPress output
	//=============================================
	function hs_format_text($content, $br = true){
		//$content = wpautop($content);
		//$content = wptexturize($content);
    	return $content;
	}
}

//=============================================
// HubSpot Init
//=============================================

global $myhubspotwp;
$myhubspotwp = new WPHubspot();
// RegisterDefault settings
register_activation_hook(__FILE__, array( $myhubspotwp, 'add_defaults_hs'));
?>