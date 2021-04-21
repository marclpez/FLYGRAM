jQuery(function($) {
	
	$('.cmloc-map-category-filter input').change(function() {
		var input = $(this);
		var filter = input.parents('.cmloc-map-category-filter').first();
		var wrapper = input.parents('.cmloc-map-shortcode').first();
		var mapCanvas = wrapper.find('#cmloc-location-index-map-canvas');
		var mapObj = mapCanvas[0].mapObj;
		
		var filteredNames = filter.find('input:checked').map(function() {
			return this.value;
		});
		
		var isVisible = function(location) {
			if (filteredNames.length == 0) return true;
			var names = $.map(location.categories, function(val, key) {
				return val;
			});
//			console.log(names);
			var intersection = $(names).filter(filteredNames);
			return (intersection.length > 0);
		};
		
		for (var i=0; i<mapObj.locations.length; i++) {
			var visible = isVisible(mapObj.locations[i]);
			mapObj.locations[i].marker.setVisible(visible);
			if (mapObj.locations[i].pathPolyline) {
				mapObj.locations[i].pathPolyline.setMap(visible ? mapObj.map : null);
			}
		}
		
		return;
		
		var items = filter.find('input');
		for (var i=0; i<items.length; i++) {
			
		}
	});
	
});