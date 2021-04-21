jQuery(function($) {
	
	if (typeof CMLOC_Map_Settings == 'undefined') return;
	
	var getPosition = function(callback, errorCallback, highAccuracy) {
		if ("geolocation" in navigator) {
			if (typeof highAccuracy != 'boolean') highAccuracy = true;
			var geo_options = {
			  enableHighAccuracy: highAccuracy, 
			  maximumAge        : 1000 * 60 * 1, // 1 minute
			  timeout           : 1000 * 60 * 10 // 10 minutes
			};
			errorCallback = function(err) {
				console.log(err);
				if (CMLOC_Map_Settings.showGeolocationErrors == '1') {
					window.CMLOC.Utils.toast('Geolocation error: [' + err.code + '] ' + err.message, null, Math.ceil(err.message.length/5));
				}
			};
			return navigator.geolocation.getCurrentPosition(callback, errorCallback, geo_options);
		}
	};
	
	
	var askReload = function() {
		if (confirm(CMLOC_Map_Settings.proximityOrderReloadMsg)) {
			location.reload();
		}
	};
	
	
	getPosition(function(pos) {
		
		var data = {action: 'cmloc_register_user_geolocation', nonce: CMLOC_Map_Settings.geolocationNonce,
				lat: pos.coords.latitude, long: pos.coords.longitude};
		$.post(CMLOC_Map_Settings.ajaxurl, data, function() {
			if (CMLOC_Map_Settings.userLastPositionLat.length == 0 || CMLOC_Map_Settings.userLastPositionLong.length == 0) {
				askReload();
				console.log('empty last pos');
			} else {
				console.log('current coords = ', CMLOC_Map_Settings.userLastPositionLat, CMLOC_Map_Settings.userLastPositionLong);
				console.log('new coords = ', pos.coords.latitude, pos.coords.longitude);
				var p1 = new google.maps.LatLng(CMLOC_Map_Settings.userLastPositionLat, CMLOC_Map_Settings.userLastPositionLong);
				var p2 = new google.maps.LatLng(pos.coords.latitude, pos.coords.longitude);
				var distance = CMLOC_Map.prototype.calculateDistance(p1, p2);
				console.log('distance = ', distance);
				if (distance > 1000) {
					askReload();
				}
			}
		});
		
	}, null, true);
	
});