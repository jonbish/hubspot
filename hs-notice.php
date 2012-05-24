<?php
class WPHubspotNotice {
        
	public  $admin_notice_text = '';
        public  $default_notice_text = '';
        
        //=============================================
	// Initiate new notice with default text if no errors
	//=============================================
	function WPHubspotNotice($default_notice_text = '') {
                $this->admin_notice_text = '';
                $this->default_notice_text = $default_notice_text;
	}
	
	//=============================================
	// Add Configuration Warning
	//=============================================	
	function configuration_warning(){
                $hs_settings = get_option('hs_settings');
		if(!WPHubspot::hs_is_customer($hs_settings['hs_portal'], $hs_settings['hs_appdomain'])){
                    if(!$hs_settings['hs_config_notice']){
                        if(!(isset($_GET['page']) && $_GET['page'] == 'hubspot_settings')){
                            $this->admin_notice('configuration-warning',10);
                        }
                    }
		}	
	}
	
	//=============================================
	// Display notice
	//=============================================	
	function admin_notice($notice = 'default-error', $fadetime = 0) {
		$notice_text = "";
		
		switch ($notice){
			case 'main-settings-update':
				$notice_text = "HubSpot settings updated.";
				break;
			case 'shortcode-settings-update':
				$notice_text = "HubSpot shortcode settings updated.";
				break;
			case 'configuration-warning':
				$notice_text = "Please go to the <a href='".HUBSPOT_ADMIN."/admin.php?page=hubspot_settings'>HubSpot settings page</a> and insert your Portal ID, Application Domain and Feedburner feed to begin collecting website statistics or to hide this warning.
                                    <br /><br />
                                    Not using HubSpot marketing software yet? <a href='http://bit.ly/HSWPTrial'>Try it for free.</a>.";
				break;
			case 'default-error':
				$notice_text = "An error occurred, please try again or contact support.";
				break;
                        default:
                                $notice_text = $notice;
                                break;
		}
		
		echo "<div id=\"msg-" . $notice . "\" class=\"updated fade\"><p>" . $notice_text . "</p></div>\n";
		if ($fadetime != 0){
			echo "<script type=\"text/javascript\">setTimeout(function(){jQuery('#msg-" . $notice . "').hide('slow');}, " . $fadetime * 1000 . ");</script>";	
		}
	}
        
	//=============================================
	// Add new notice to instance
	//=============================================
        function add_notice($new_admin_notice_text){
            if(trim($this->admin_notice_text)!=''){
                $this->admin_notice_text .= '<br />';
            }
            $this->admin_notice_text .= $new_admin_notice_text;
        }
        
	//=============================================
	// Display current state of notice
	//=============================================
        function display_notice($fadetime = 0){
            if(trim($this->admin_notice_text)!=''){
                $this->admin_notice($this->admin_notice_text, $fadetime);
            } else {
                $this->admin_notice($this->default_notice_text, $fadetime);
            }
        }
}
?>