Number.prototype.toRadians = function() {
   return this * Math.PI / 180;
}

Number.prototype.toDegrees = function() {
	   return this * 180 /  Math.PI;
}


function CMLOC_Map(mapId, locations) {
	
	if (typeof locations != 'object' || typeof locations.length == 'undefined') locations = [];
	this.mapId = mapId;
	
	var styles = [];
	if (CMLOC_Map_Settings.mapShowGooglePlaces != '1') {
			styles.push({
				featureType: "poi",
				stylers: [
					{ visibility: "off" }
				]
		    });
	}
	this.map = new google.maps.Map(this.mapElement, {gestureHandling: 'greedy', styles: styles});
	
	this.map.setMapTypeId(CMLOC_Map_Settings.mapType);
	this.locations = [];
//	this.directionsService = this.createDirectionsService();
//	this.directionsDisplay = this.createDirectionsRenderer();
	this.trailPolylines = [];
	this.trailResponse = null;
	this.totalDistance = 0;
	this.totalDuration = 0;
	this.travelMode = google.maps.TravelMode.WALKING;
	this.requestsCount = 0;
	this.geocoder = new google.maps.Geocoder;
	
	var mapObj = this;
	
	// Add locations
	for (var i=0; i<locations.length; i++) {
		mapObj.addLocation(locations[i]);
	}
	
	mapObj.createViewPointBound();
	
	setTimeout(function() {
		mapObj.center();	
	}, 500);
	
};


CMLOC_Map.prototype.createDirectionsService = function() {
	return new google.maps.DirectionsService;
};


CMLOC_Map.prototype.createDirectionsRenderer = function() {
	var directionsDisplay = new google.maps.DirectionsRenderer;
	directionsDisplay.setMap(this.map);
	directionsDisplay.setOptions({suppressMarkers: true, preserveViewport: true, suppressBicyclingLayer: true, draggable: false});
	return directionsDisplay;
};


CMLOC_Map.prototype.createViewPointBound = function() {
//	console.log(this.locations);
	this.bounds = new google.maps.LatLngBounds();
	for (var i=0; i<this.locations.length; i++) {
		this.bounds.extend(new google.maps.LatLng(this.locations[i].lat, this.locations[i].long));
	}
};


CMLOC_Map.prototype.addLocation = function(location, index) {
//	location.marker = this.createMarker(location);
	if( this.checkDuplicates(location, index) ) {
		var min = -0.00005;
		var max = 0.00005;
		location.lat = parseFloat( location.lat ) + parseFloat( ( Math.random() * ( max - min ) + min ).toFixed(6) );
		location.long = parseFloat( location.long ) + parseFloat( ( Math.random() * ( max - min ) + min ).toFixed(6) );
	}
	if (location.type == 'location') { // Location
		location.marker = this.createMarker(location);
	} else { // Waypoint
		location.marker = this.createWaypointMarker(location);
	}
	this.pushLocation(location, index);
};


CMLOC_Map.prototype.pushLocation = function(location, index) {
	if (typeof index == 'undefined') {
		this.locations.push(location);
	} else {
		this.locations.splice(index, 0, location);
	}
};


CMLOC_Map.prototype.checkDuplicates = function(location, index) {
	var flag = false;
	for( k in this.locations ) {
		if( typeof index !== 'undefined' && index === k ) continue;
		if( this.locations[k].lat === location.lat && this.locations[k].long === location.long ) {
            flag = true;
        }
	}
	return flag;
};


CMLOC_Map.prototype.requestLocationWeather = function(location) {
	if (!CMLOC_Map_Settings.openweathermapAppKey) return;
	var units = ('temp_f' == CMLOC_Map_Settings.temperatureUnits ? 'imperial' : 'metric');
	var url = '//api.openweathermap.org/data/2.5/weather?APPID='+ CMLOC_Map_Settings.openweathermapAppKey
				+'&lat='+ location.lat + '&lon=' + location.long + '&units=' + units;
	this.pushRequest(url, function(response) {
//		console.log(response);
		if (200 == response.cod) {
			var iconUrl = 'https://openweathermap.org/img/w/'+ response.weather[0].icon +'.png';
			var container = location.container.find('.cmloc-weather');
			var tempUnit = ('temp_f' == CMLOC_Map_Settings.temperatureUnits ? 'F' : 'C');
			container.attr('href', 'https://openweathermap.org/city/' + response.id);
			container.append(jQuery('<img/>', {src: iconUrl}));
			container.append(jQuery('<div/>', {"class" : "cmlocr-weather-temperature"}).html(Math.round(response.main.temp) + "&deg;"+ tempUnit));
			container.append(jQuery('<div/>', {"class" : "cmlocr-weather-pressure"}).html(Math.round(response.main.pressure) + " hPa"));
		}
	});
};


CMLOC_Map.prototype.pushRequest = function(url, callback) {
	var callbackName = 'cmloc_callback_' + Math.floor(Math.random()*99999999);
	window[callbackName] = callback;
	var script = document.createElement('script');
	script.type = 'text/javascript';
	script.src = url + '&callback=' + callbackName;
	document.getElementsByTagName('body')[0].appendChild(script);
};


CMLOC_Map.prototype.createMarker = function(location) {
	
	return new CMLOC_Marker(this, new google.maps.LatLng(location.lat, location.long),
			   {draggable: false, style: 'cursor:pointer;', icon: location.icon, iconSize: location.iconSize},
			   {text: location.name, style: 'cursor:pointer;'}
			 );
	
//	var marker = new MarkerWithLabel({
//		   position: new google.maps.LatLng(location.lat, location.long),
//		   draggable: false,
////		   raiseOnDrag: true,
//		   map: this.map,
//		   cursor: 'pointer',
//		   labelContent: location.name,
//		   labelAnchor: new google.maps.Point(this.getTextWidth(location.name, 10), 0),
//		   labelClass: "cmloc-map-label" // the CSS class for the label
//		 });
	
	
	
	return marker;
};


CMLOC_Map.prototype.createWaypointMarker = function(location) {
	
	var marker = new google.maps.Marker({
		position: new google.maps.LatLng(location.lat, location.long),
		map: this.map,
		icon: 'https://maps.gstatic.com/mapfiles/dd-via.png',
		draggable: false,
	});
	
	return marker;
	
};


CMLOC_Map.prototype.getLocationIndexByMarker = function(marker) {
	for (var i=0; i<this.locations.length; i++) {
		if (this.locations[i].marker == marker) {
			return i;
		}
	}
	return false;
};


CMLOC_Map.prototype.getLocationIndexByItem = function(item) {
	for (var i=0; i<this.locations.length; i++) {
		if (this.locations[i].item == item) {
			return i;
		}
	}
	return false;
};


CMLOC_Map.prototype.getLocationIndexById = function(id) {
	for (var i=0; i<this.locations.length; i++) {
		if (this.locations[i].id == id) {
			return i;
		}
	}
	return false;
};


CMLOC_Map.prototype.center = function() {
	if (this.locations.length > 0) {
		if ( CMLOC_Map_Settings.defaultZoom > 0 ) {
			this.map.panTo(new google.maps.LatLng(this.locations[0].lat, this.locations[0].long));
			this.map.setZoom( parseInt( CMLOC_Map_Settings.defaultZoom ) );
        } else {
            if (this.locations.length == 1) {
                this.map.panTo(new google.maps.LatLng(this.locations[0].lat, this.locations[0].long));
                this.map.setZoom(12);
            } else {
                this.map.fitBounds(this.bounds);
            }
		}
//		google.maps.event.trigger(this.map, "resize");
	}
};


CMLOC_Map.prototype.getMapElement = function() {
	return jQuery(this.mapElement);
};


CMLOC_Map.prototype.getTextWidth = function(text, fontSize) {
	var narrow = '1tiIfjJl';
	var wide = 'WODGKXZBM';
	var result = 0;
	for (var i=0; i<text.length; i++) {
		var letter = text.substr(i, 1);
		var rate = 1.0 + (0.5*(wide.indexOf(letter) >= 0 ? 1 : 0)) - (0.5*(narrow.indexOf(letter) >= 0 ? 1 : 0));
//		console.log(letter +' : '+ rate);
		result += rate;
	}
	return result * fontSize*0.7/2;
};





CMLOC_Map.prototype.calculateDistance = function(p1, p2) {
	
	var R = 6371000; // metres
	var k = p1.lat().toRadians();
	var l = p2.lat().toRadians();
	var m = (p2.lat() - p1.lat()).toRadians();
	var n = (p2.lng() - p1.lng()).toRadians();
	
	var a = Math.sin(m/2) * Math.sin(m/2) +
    	Math.cos(k) * Math.cos(l) *
    	Math.sin(n/2) * Math.sin(n/2);
	var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
	
	return R * c;
	
};


CMLOC_Map.prototype.calculateMidpoint = function(p1, p2) {
	
	var lat1 = p1.lat().toRadians();
	var lon1 = p1.lng().toRadians();
	var lat2 = p2.lat().toRadians();
	var lon2 = p2.lng().toRadians();
	
	var bx = Math.cos(lat2) * Math.cos(lon2 - lon1);
	var by = Math.cos(lat2) * Math.sin(lon2 - lon1);
	var lat3 = Math.atan2(Math.sin(lat1) + Math.sin(lat2), Math.sqrt((Math.cos(lat1) + bx) * (Math.cos(lat1) + bx) + by*by));
	var lon3 = lon1 + Math.atan2(by, Math.cos(lat1) + Bx);
	
	return new google.maps.LatLng(lat3.toDegrees(), lon3.toDegrees());
	
};


CMLOC_Map.prototype.calculateMidpoints = function(p1, p2, maxDist) {
	var dist = this.calculateDistance(p1, p2);
	if (dist <= maxDist) return [];
	var num = dist / maxDist;
	
	
	
};



CMLOC_Map.prototype.parseDuration = function(val) {
	val = val.replace(/[^0-9hms]/g, '').match(/([0-9]+h)?([0-9]+m)?([0-9]+s)?/);
	for (var i=1; i<=3; i++) {
		val[i] = parseInt(val[i]);
		if (isNaN(val[i])) val[i] = 0;
	}
	console.log(val);
	return val[1] * 3600 + val[2] * 60 + val[3];
};


CMLOC_Map.prototype.findAddress = function(pos, successCallback) {
	this.geocoder.geocode({'location': pos}, function(results, status) {
		if (status === google.maps.GeocoderStatus.OK) {
			
			var findPostalCode = function(results) {
				for (var j=0; j<results.length; j++) {
					var address = results[j];
					var components = address.address_components;
//					console.log(components);
					for (var i=0; i<components.length; i++) {
						var component = components[i];
						if (component.types[0]=="postal_code"){
					        return component.short_name;
					    }
					}
				}
				return "";
			};
			
			if (results.length > 0) {
				var address = results[0];
				successCallback({
					results: results,
					postal_code: findPostalCode(results),
					formatted_address: address.formatted_address,
				});
			}
		}
	});
};


CMLOC_Map.prototype.findLocationByAddress = function(full_address, successCallback) {
	
	jQuery.getJSON('https://maps.googleapis.com/maps/api/geocode/json?address='+ encodeURIComponent(full_address) +'&sensor=false', null, function (data) {
		if (data.results.length > 0) {
			var p = data.results[0].geometry.location;
			successCallback(new google.maps.LatLng(p.lat, p.lng));
		}
	});
	
	return;
	
	this.geocoder.geocode({'address': full_address}, function(results, status) {
		if (status === google.maps.GeocoderStatus.OK) {
			successCallback(results[0].geometry.location);
		}
	});
};


CMLOC_Map.prototype.shouldRecalculate = function() {
	return (location.search.indexOf('recalculate=1') >= 0);
};


CMLOC_Map.prototype.geolocationGetPosition = function(callback, errorCallback, highAccuracy) {
	if ("geolocation" in navigator) {
		if (typeof highAccuracy != 'boolean') highAccuracy = true;
		var geo_options = {
				  enableHighAccuracy: highAccuracy, 
				  maximumAge        : 1000 * 60 * 1, // 1 minute 
				  timeout           : 1000 * 60 * 10 // 10 minutes
				};
		if (typeof errorCallback != 'function') errorCallback = function(err) {
			console.log(err);
			window.CMLOC.Utils.toast('Geolocation error: [' + err.code + '] ' + err.message, null, Math.ceil(err.message.length/5));
		};
		return navigator.geolocation.getCurrentPosition(callback, errorCallback, geo_options);
	}
};


CMLOC_Map.prototype.initWheelScrollZoom = function() {
	var that = this;
	if (CMLOC_Map_Settings.scrollZoom == 'after_click') {
		this.map.set('scrollwheel', false);
		google.maps.event.addListener(this.map, 'click', function(ev) {
			this.set('scrollwheel', true);
		});
	} else {
		this.map.set('scrollwheel', (CMLOC_Map_Settings.scrollZoom == 'enable'));
	}
};