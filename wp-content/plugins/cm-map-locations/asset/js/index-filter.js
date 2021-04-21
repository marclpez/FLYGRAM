jQuery(function($) {
	
	$('.cmloc-location-filter-category').change(function() {
		var obj = $(this);
		var filter = obj.parents('.cmloc-location-index-filter');
		var s = filter.find('.cmloc-input-search').val();
		location.href = obj.val() + (s ? '?s=' + encodeURIComponent(s) : '');
	});
	
	
	$('.cmloc-zip-filter select').change(function() {
		
		$(this).parents('form').submit();
		return;
		
		var container = $(this).parents('.cmloc-zip-filter');
		var mapObj = document.getElementById('cmloc-location-index-map-canvas').mapObj;
		var zipcode = container.find('.cmloc-zip-filter-code input').val();
		var radius = parseInt(container.find('.cmloc-zip-filter-radius select').val());
		if (zipcode.length > 0 && radius > 0) {
			if ('feet' == CMLOC_Map_Settings.lengthUnits) { // change miles to meters
				var radiusFeet = radius * CMLOC_Map_Settings.feetInMile;
				radius = radiusFeet * CMLOC_Map_Settings.feetToMeter;
			} else { // change km to meters
				radius *= 1000;
			}
			var findAddr = zipcode + " " + CMLOC_Map_Settings.zipFilterCountry;
			mapObj.findLocationByAddress(findAddr, function(zipCoords) {
				for (var i=0; i<mapObj.locations.length; i++) {
					var location = mapObj.locations[i];
					var locationCoords = new google.maps.LatLng(location.lat, location.long);
					var dist = mapObj.calculateDistance(zipCoords, locationCoords);
					if (dist <= radius) {
						location.marker.setVisible(true);
					} else {
						location.marker.setVisible(false);
					}
				}
				mapObj.center();
			});
		}
	});
	
	
	// ZIP filter
	if (typeof CMLOC_Index_Map_Settings == 'object' && typeof document.getElementById('cmloc-location-index-map-canvas') != 'null'
			&& CMLOC_Index_Map_Settings.zipFilterGeolocation == '1' && location.search.indexOf('zipcode=') == -1) {
		var canvas = document.getElementById('cmloc-location-index-map-canvas');
		jQuery(canvas).bind('MapObject:ready', function() {
			var mapObj = this.mapObj;
			
			var redirect = function(postal_code) {
				var url = location.href;
				url.replace(/\&?zip(code|radius)=\w+/g, '');
				url += (url.indexOf('?') > -1 ? '&' : '?');
				url += 'zipcode=' + encodeURIComponent(postal_code);
				url += '&zipradius=' + encodeURIComponent($('.cmloc-zip-filter-radius select').val());
				location.href = url;
			};
			
			var callback = function(pos) { // success
				var coords = new google.maps.LatLng(pos.coords.latitude, pos.coords.longitude);
				mapObj.findAddress(coords, function(address) {
					console.log(address);
					if (address.results.length == 0) return;
					var addr = address.results[0];
					for (var i=0; i<addr.address_components.length; i++) {
						if (addr.address_components[i].short_name == CMLOC_Index_Map_Settings.zipFilterCountry) {
							redirect(address.postal_code);
							return;
						}
					}
					
				});
			};
			
	//		callback({coords: {latitude: 54.382006986174, longitude: 18.6004114151}});
	//		callback({coords: {latitude: 47.595977104738, longitude: -122.32830047607}});
			mapObj.geolocationGetPosition(callback,
				function(err) { // error callback
					console.log(err);
				}
			);
			
		});
	}
	
	
});