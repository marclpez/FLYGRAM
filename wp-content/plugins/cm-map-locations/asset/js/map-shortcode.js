function CMLOC_Map_Shortcode(elemId) {
	
	var $ = jQuery;
	var container = $('#' + elemId);
	shortcodeParams = container.data('shortcodeParams');
	
	var setHandlers = function(container) {
		$('.cmloc-pagination a', container).click(function(ev) {
			ev.stopPropagation();
			ev.preventDefault();
			var link = $(this);
			request(link.data('page'));
		});
		
		// Initialize tooltip click listeners
		var mapCanvas = document.getElementById('cmloc-location-index-map-canvas');
		if (mapCanvas && mapCanvas.mapObj) {
			mapCanvas.mapObj.initLocationsListClickTooltipHandler();
		}
		
	};
	
	var request = function(page) {
		var params = jQuery.extend({}, shortcodeParams);
		params.page = page;
		var postParams = {params: params, action: 'cmloc_map_shortcode'};
		$.post(CMLOC_Map_Shortcode_Settings.ajaxUrl, postParams, function(response) {
			var html = $(response);
			var targetElem = container.find('.cmloc-locations-archive-list-wrapper');
			targetElem.replaceWith(html.find('.cmloc-locations-archive-list-wrapper'));
			targetElem = container.find('.cmloc-locations-archive-list-wrapper');
			setHandlers(targetElem);
		});
	};
	
	setHandlers(container);
	
}