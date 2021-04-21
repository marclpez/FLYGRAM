jQuery(function($) {

	var loadUrl = function(filter) {
		var atts = filter.data('atts');
		atts.category = filter.find('.cmloc-business-filter-category').val();
		atts.s = filter.find('.cmloc-input-search').val();
		var data = {atts: atts, action: 'cmloc_business_map_filter'};
		$.post(filter.data('url'), data, function(response) {
			var wrapper = filter.parents('.cmloc-business-shortcode');
			var responseDoc = $(response);
			wrapper.html(responseDoc.html());
			initHandlers(wrapper);
		});
	};
	
	
	var initHandlers = function(container) {
		
		$('.cmloc-business-filter form', container).submit(function(ev) {
			ev.stopPropagation();
			ev.preventDefault();
			var filter = $(this).parents('.cmloc-business-filter');
			loadUrl(filter);
		});
		
		
		$('.cmloc-business-filter-category', container).change(function() {
			$(this).parents('form').first().submit();
		});
		
	};
	
	initHandlers($('body'));
	
	$('.cmloc-bd-index-map-wrapper .cmloc-show-map-btn').click(function(ev) {
		ev.stopPropagation();
		ev.preventDefault();
		var btn = $(this);
		var wrapper = btn.parents('.cmloc-bd-index-map-wrapper');
		btn.hide();
		wrapper.find('.cmloc-hide-map-btn').show();
		var shortcode = wrapper.find('.cmloc-business-shortcode');
		shortcode.fadeIn('medium', function() {
			var mapObj = document.getElementById('cmloc-location-index-map-canvas').mapObj;
			google.maps.event.trigger(mapObj.map, "resize");
			mapObj.center();
		});
		
	});
	
	$('.cmloc-bd-index-map-wrapper .cmloc-hide-map-btn').click(function(ev) {
		ev.stopPropagation();
		ev.preventDefault();
		var btn = $(this);
		var wrapper = btn.parents('.cmloc-bd-index-map-wrapper');
		btn.hide();
		wrapper.find('.cmloc-show-map-btn').show();
		var shortcode = wrapper.find('.cmloc-business-shortcode');
		shortcode.fadeOut('medium');
	});

});

