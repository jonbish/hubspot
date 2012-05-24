<?php
class WPHubspotAnalytics {
	
	function WPHubspotAnalytics() {
		// Analytics Hook
		if(is_admin()){
			//
		} else {
			add_action('wp_footer', array(&$this, 'hs_analytics_insert'));
		}
	}
	//=============================================
	// Insert tracking code
	//=============================================
	function hs_analytics_insert() {	
		$hs_settings = array();
		$hs_settings = get_option('hs_settings');
		if ( $hs_settings["hs_portal"] != "" && $hs_settings["hs_appdomain"] != "" ) { 
			echo "\n".'<!-- HubSpot Analytics for WordPress | http://success.hubspot.com/ -->'."\n";
			echo '<script type="text/javascript" language="javascript">'."\n";
			echo "\t".'var hs_portalid = '.$hs_settings["hs_portal"].';'."\n";
			echo "\t".'var hs_salog_version = "2.00";'."\n";
			echo "\t".'var hs_ppa = "'.$hs_settings["hs_appdomain"].'";'."\n";	
			echo "\t"."document.write(unescape(\"%3Cscript src='\" + document.location.protocol + \"//\" + hs_ppa + \"/salog.js.aspx' type='text/javascript'%3E%3C/script%3E\"));"."\n";							
			echo '</script>'."\n";
			echo '<!-- End of HubSpot Analytics code -->'."\n";
		}
	}
}
?>