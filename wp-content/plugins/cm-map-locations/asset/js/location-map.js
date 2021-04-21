function CMLOC_Route(mapId, locations, pathColor) {
	
	var $ = jQuery;
	
	this.mapElement = document.getElementById(mapId);
	this.containerElement = $(this.mapElement).parents('.cmloc-location-single').first();
	this.isFullscreen = false;
	this.markersCounter = 0;
	this.pathColor = pathColor;
	
	CMLOC_Map.call(this, mapId, locations);
	
	var mapObj = this;
	
	$('.cmloc-show-terrain input', this.containerElement).change(function(ev) {
		mapObj.map.setMapTypeId(this.checked ? google.maps.MapTypeId.TERRAIN : google.maps.MapTypeId.ROADMAP);
	});
	
	var fullscreen = $('<div/>', {"class":"cmloc-fullscreen"}).hide().appendTo($('body'));
	fullscreen.height($(window).height());
	$('.cmloc-map-fullscreen-btn', this.containerElement).click(function(ev) {
		ev.stopPropagation();
		ev.preventDefault();
		mapObj.isFullscreen = true;
		jQuery('html, body').scrollTop(0);
		fullscreen.show();
		var obj = $(mapObj.mapElement);
		obj.data('height', obj.height());
		obj.height('100%');
		obj.appendTo(fullscreen);
		google.maps.event.trigger(mapObj.map, "resize");
		mapObj.center();
	});
	$(window).keydown(function(ev) { // Close fullscreen
		if (mapObj.isFullscreen && ev.keyCode == 27) {
			mapObj.isFullscreen = false;
			var obj = fullscreen.children().first();
			mapObj.containerElement.find('.cmloc-location-map-canvas-outer').prepend(obj);
//			obj.appendTo();
			obj.height(obj.data('height'));
			fullscreen.hide();
			google.maps.event.trigger(mapObj.map, "resize");
			mapObj.center();
		}
	});
	
	this.initWheelScrollZoom();
	
	var mapObj = this;
	setTimeout(function() {
		mapObj.center();	
//		google.maps.event.trigger(mapObj.map, "resize");
	}, 500);
	
	
}


CMLOC_Route.prototype = Object.create(CMLOC_Map.prototype);
CMLOC_Route.prototype.contructor = CMLOC_Route;


CMLOC_Route.prototype.addLocation = function(location) {
	location.container = jQuery('.cmloc-location-single[data-map-id="'+ this.mapId +'"] .cmloc-location-details[data-id='+ location.id +']');
	CMLOC_Map.prototype.addLocation.call(this, location);
	if (location.type == 'location') {
		this.requestLocationWeather(location);
	}
};



CMLOC_Route.prototype.createMarker = function(location) {
	
	var label = (this.locations.length+1) +". "+ name;
	var marker = new CMLOC_Marker(this, new google.maps.LatLng(location.lat, location.long),
			   {draggable: false, style: 'cursor:pointer;', icon: location.icon, iconSize: location.iconSize},
			   {text: CMLOC_Location_Map_Settings.showLabels=='1' ? location.name : '', style: 'cursor:pointer;'}
			 );
	var mapObj = this;
	
	google.maps.event.addListener(marker, 'click', function() {
		if (mapObj.isFullscreen) return false;
		var index = mapObj.getLocationIndexByMarker(marker);
	    if (index !== false) {
	    	var location = mapObj.locations[index];
	    	var container = mapObj.containerElement;
	    	jQuery('html, body').animate({
		        scrollTop: container.find('.cmloc-location-details[data-id='+ location.id +']').offset().top-30
		    }, 500);
	    }
	});
	
	return marker;
	
};


CMLOC_Route.prototype.createWaypointMarker = function(location) {
	var marker = CMLOC_Map.prototype.createWaypointMarker.call(this, location);
	marker.setMap(null);
	return marker;
};


