function CMLOC_Editor(locations) {
	
	var $ = jQuery;
	
	this.mapId = 'cmloc-editor-map-canvas';
	this.locationsCounter = 0;
	this.mapElement = document.getElementById(this.mapId);
	this.containerElement = $(this.mapElement).parents('.cmloc-location-editor').first();
	this.editorMode = 'location';
	this.lastLocation = null;
	this.suspendAddWaypoints = false;
	
	CMLOC_Map.call(this, 'cmloc-editor-map-canvas', locations);
	
	this.map.setOptions({
		draggableCursor: 'crosshair',
	});
	
	var mapObj = this;
	
	// Create or move location
	google.maps.event.addListener(this.map, 'click', function(ev) {
		if (mapObj.suspendAddWaypoints) return;
		if (mapObj.locations.length > 0) {
			mapObj.locations[0].marker.setPosition(ev.latLng);
		} else {
			var location = {lat: ev.latLng.lat(), long: ev.latLng.lng(), id: null};
			location.name = 'Location';
			location.type = 'location';
			mapObj.addLocation(location);
		}
	});
	
	
	$('.cmloc-editor-instructions-btn').click(function(ev) {
		ev.stopPropagation();
		ev.preventDefault();
		mapObj.containerElement.find('.cmloc-editor-instructions').slideDown();
	});
	
	$('.cmloc-choose-icon-btn').click(function(ev) {
		ev.stopPropagation();
		ev.preventDefault();
		var obj = $(this);
		var list = obj.parents('.cmloc-field-icon').find('.cmloc-icons-list');
		if (list.css('display') == 'none') list.show();
		else list.hide();
	});
	
	$('.cmloc-icons-list img').click(function() {
		var obj = $(this);
		var container = obj.parents('.cmloc-field-icon');
		container.find('.cmloc-icons-list').hide();
		container.find('.cmloc-current-icon').attr('src', obj.attr('src'));
		container.find('input[type=hidden]').val(obj.attr('src'));
	});
	
	
	var searchBoxInput = $('.cmloc-find-location', this.containerElement);
	searchBoxInput.keypress(function(e) {
		e = e || event;
		 var txtArea = /textarea/i.test((e.target || e.srcElement).tagName);
		 var result = txtArea || (e.keyCode || e.which || e.charCode || 0) !== 13;
		 if (!result) this.blur();
		 return result;
	})
	this.searchBox = new google.maps.places.SearchBox(searchBoxInput[0]);
	this.searchBox.addListener('places_changed', function() {
		var places = mapObj.searchBox.getPlaces();
		if (places.length == 0) return;
		var bounds = new google.maps.LatLngBounds();
		places.forEach(function(place) {
			if (place.geometry.viewport) {
		        // Only geocodes have viewport.
		        bounds.union(place.geometry.viewport);
		      } else {
		        bounds.extend(place.geometry.location);
		      }
		});
		mapObj.map.fitBounds(bounds);
	});
	
	
	$('.cmloc-locations-editor-mode a', this.containerElement).click(function(ev) {
		ev.stopPropagation();
		ev.preventDefault();
		var obj = $(this);
		mapObj.editorMode = obj.data('mode');
		obj.parents('ul').find('li.current').removeClass('current');
		obj.parents('li').first().addClass('current');
	});
	
	var mapObj = this;
	setTimeout(function() {
		mapObj.center();	
	}, 500);
	
};


CMLOC_Editor.prototype = Object.create(CMLOC_Map.prototype);
CMLOC_Editor.prototype.contructor = CMLOC_Editor;




CMLOC_Editor.prototype.createDirectionsRenderer = function() {
	var directionsDisplay = CMLOC_Map.prototype.createDirectionsRenderer.call(this);
//	directionsDisplay.setOptions({draggable: true, suppressMarkers: true});
	return directionsDisplay;
};



CMLOC_Editor.prototype.addLocation = function(location, index) {
	location.item = this.addLocationViewItem(location, index).get(0);
	CMLOC_Map.prototype.addLocation.call(this, location, index);
	this.locationsCounter++;
};



CMLOC_Editor.prototype.addLocationViewItem = function(location, index) {
	
	var mapObj = this;
	var container = jQuery('#cmloc-editor-locations .cmloc-locations-list');
	var item = container.find('li:first-child').first().clone();
	
	if (typeof index == 'number') {
		container.children('li:nth-child('+ (index+1) +')').after(item);
	} else {
		container.append(item);
	}
	
	item.attr('data-id', location.id ? location.id : 0);
	item.find('input[type=hidden][name*=id]').val(location.id ? location.id : 0);
	item.find('.location-name').val(location.name).change(function() { location.name = this.value; location.marker.setTitle(this.value); });
	item.find('.location-lat').val(location.lat).change(function() { location.lat = this.value; location.marker.setPosition(new google.maps.LatLng(this.value, location.marker.getPosition().lng())); });;
	item.find('.location-long').val(location.long).change(function() { location.lat = this.value; location.marker.setPosition(new google.maps.LatLng(location.marker.getPosition().lat(), this.value)); });;
	item.find('.location-description').val(location.description ? location.description : '');
	item.find('.location-type').val(location.type);
	
	if (location.type == 'location') {
		this.findAddress(new google.maps.LatLng(location.lat, location.long), function(result) {
			item.find('.location-address').val(location.address ? location.address : result.formatted_address);
			item.find('.location-postal-code').val(location.postal_code ? location.postal_code : result.postal_code);
		});
	} else {
		if (typeof location.address == 'string') {
			item.find('.location-address').val(location.address);
		}
		if (typeof location.postal_code == 'string') {
			item.find('.location-postal-code').val(location.postal_code);
		}
	}
	
	if (typeof location.phone_number == 'string') {
		item.find('.location-phone-number').val(location.phone_number);
	}
	if (typeof location.website == 'string') {
		item.find('.location-website').val(location.website);
	}
	if (typeof location.email == 'string') {
		item.find('.location-email').val(location.email);
	}
	
//	item.find('input[type=hidden][name*=images]').val(location.images ? location.images.join(',') : '');
	
	if (location.type == 'location') {
		item.show();
	}
		
	item.find('.cmloc-images').each(CMLOC_Editor_Images_init);
	

	if (location.images && location.images.length > 0) {
		var imageFileInput = item.find('input[type=hidden][name*=images]');
		var imageFileList = item.find('.cmloc-images-list');
		for (var i=0; i<location.images.length; i++) {
			var image = location.images[i];
			CMLOC_Editor_Images_add(imageFileInput, imageFileList, image.id, image.thumb, image.url);
		}
	}
	
	jQuery('.cmloc-location-remove', item).click(function(ev) {
		ev.stopPropagation();
		ev.preventDefault();
		var item = jQuery(this).parents('li').first();
		var index = mapObj.getLocationIndexByItem(item.get(0));
		if (index !== false) {
			mapObj.removeLocation(index);
		}
	});
	
	return item;
	
};



CMLOC_Editor.prototype.createMarker = function(location) {
	
	var label = location.name;
	
	var marker = new CMLOC_Marker(this, new google.maps.LatLng(location.lat, location.long),
		   {draggable: true, style: 'cursor:pointer;'},
		   {text: label, style: 'cursor:pointer;'}
		 );
	
	var mapObj = this;
	
	google.maps.event.addListener(marker, 'positionUpdated', function() {
	    var pos = marker.getPosition();
	    var index = mapObj.getLocationIndexByMarker(marker);
	    if (index !== false) {
	    	mapObj.updateLocationPosition(index, pos.lat(), pos.lng());
	    }
	    mapObj.findAddress(new google.maps.LatLng(location.lat, location.long), function(result) {
	    	var item = jQuery(location.item);
			item.find('.location-address').val(result.formatted_address);
			item.find('.location-postal-code').val(result.postal_code);
		});
	});
	
	google.maps.event.addListener(marker, 'click', function() {
		if (mapObj.suspendAddWaypoints) return;
		var index = mapObj.getLocationIndexByMarker(marker);
	    if (index !== false) {
	    	var nameInput = jQuery(mapObj.locations[index].item).find('.location-name');
	    	nameInput.select();
	    	jQuery('html, body').animate({
		        scrollTop: nameInput.offset().top
		    }, 500);
	    }
	});
	
	return marker;
	
};


CMLOC_Editor.prototype.updateLocationPosition = function(index, lat, long) {
	var location = this.locations[index];
	var item = jQuery(location.item);
	location.lat = lat;
	location.long = long;
	if (item.length > 0) {
		item.find('input[class=location-lat]').val(lat);
		item.find('input[class=location-long]').val(long);
	}
};


CMLOC_Editor.prototype.removeLocation = function(index) {
	var location = this.locations[index];
	if (location.item) location.item.remove();
	if (location.marker) location.marker.setMap(null);
	this.locations.splice(index, 1);
};



CMLOC_Editor.prototype.center = function() {
//	console.log('CMLOC_Editor.prototype.center');
	if (this.locations.length == 0) {
		this.map.panTo(new google.maps.LatLng(CMLOC_Editor_Settings.defaultLat, CMLOC_Editor_Settings.defaultLong));
		this.map.setZoom(parseInt(CMLOC_Editor_Settings.defaultZoom));
	} else {
		CMLOC_Map.prototype.center.call(this);
	}
};


