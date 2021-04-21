jQuery(function($) {
	
	$('.cmloc-rating li').click(function() {
		
		var obj = $(this);
		var container = obj.parents('ul').first();
		
		if (container.attr('data-can-rate') == '0') return;
		
		$.post(CMLOC_Rating.url, {
			action: 'cmloc_route_rating',
			nonce: CMLOC_Rating.nonce,
			routeId: obj.parents('.cmloc-location-single').data('routeId'),
			rate: obj.data('rate')
		}, function(response) {
			if (response.success == '1') {
				container.attr('data-rating', Math.round(response.rate));
				container.attr('data-can-rate', 0);
			}
		});
		
	});
	
});