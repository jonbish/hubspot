<?php
class WPHubspotSocial {
	
	function WPHubspotSocial() {
		// FeedBurner Integration Hooks
		add_filter('feed_link', array(&$this, 'hs_feedburner_feed_link'), 1, 2);
		if(is_admin()){
			//
		} else {
			add_action('template_redirect', array(&$this, 'hs_feed_redirect'));
		}
	}	
	//=============================================
	// Replace RSS with Feedburner
	//=============================================
	function hs_feed_redirect() {
		global $feed;
		// Do nothing if not a feed
		if (!is_feed()) {
			return;
		}
		// Do nothing if feedburner is the user-agent
		if (preg_match('/feedburner/i', $_SERVER['HTTP_USER_AGENT'])){
			return;
		}
                if (preg_match('/googlebot/i', $_SERVER['HTTP_USER_AGENT'])){
                        return;
                }
		$hs_settings=get_option('hs_settings');
		if ($feed != 'comments-rss2' && trim($hs_settings['hs_feedburner_url']) != '') {
			if (function_exists('status_header')) status_header( 302 );
			header("Location:" . trim($hs_settings['hs_feedburner_url']));
			header("HTTP/1.1 302 Temporary Redirect");
			exit();
		}
	}
	
	//=============================================
	// Feedburner filter URL
	//=============================================
	function hs_feedburner_feed_link($output, $feed){
		$hs_settings=get_option('hs_settings');
		$feed_url=$hs_settings['hs_feedburner_url'];
		//preg_match("/rss2|atom|rdf/i", $feed)
		if(trim($feed_url) != '' && $feed!='comments-rss2'){
			$feed_array = array('rss' => $feed_url, 'rss2' => $feed_url, 'atom' => $feed_url, 'rdf' => $feed_url, 'comments_rss2' => '');
			$feed_array[$feed] = $feed_url;
			$output = $feed_array[$feed];
		}
		return $output;
	}
}
?>