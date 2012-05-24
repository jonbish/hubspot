jQuery(document).ready(function($) {

	// TOGGLE SCRIPT
	$(".hubfeed_content").hide();

	$("a.hubfeed_content_link").click(function(event){
		$(this).parents("#hubspot_hubfeed_widget li").find(".hubfeed_content").toggle("fast");
		return false;
	});

   });