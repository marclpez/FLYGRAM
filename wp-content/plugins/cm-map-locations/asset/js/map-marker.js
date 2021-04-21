CMLOC_Marker.prototype = new google.maps.OverlayView();

function CMLOC_Marker(mapObj, position, markerOptions, labelOptions) {
	
	this.mapObj = mapObj;
	var map = mapObj.map;

	if (typeof markerOptions != 'object')
		markerOptions = {};
	if (typeof markerOptions.color == 'undefined')
		markerOptions.color = '#ff6666';
	if (typeof markerOptions.style == 'undefined')
		markerOptions.style = '';
	if (typeof markerOptions.draggable == 'undefined')
		markerOptions.draggable = false;
	if (typeof markerOptions.icon != 'string')
		markerOptions.icon = '';

	if (typeof labelOptions != 'object')
		labelOptions = {};
	if (typeof labelOptions.style == 'undefined')
		labelOptions.style = '';
	if (typeof labelOptions.text == 'undefined')
		labelOptions.text = '';

	this.set('position', position);
	this._labelOptions = labelOptions;
	this._markerOptions = markerOptions;
	this.setMap(map);
	
	this._createContainer();

};


CMLOC_Marker.prototype._createContainer = function() {
	var that = this;
	var container = document.createElement('div');
	google.maps.event.addDomListener(container, 'click', function(ev) {
		google.maps.event.trigger(that, 'click', ev);
	});
	google.maps.event.addDomListener(container, 'mouseenter', function(ev) {
		google.maps.event.trigger(that, 'mouseenter', ev);
	});
	google.maps.event.addDomListener(container, 'mouseleave', function(ev) {
		google.maps.event.trigger(that, 'mouseleave', ev);
	});
	this.set('container', container);
	return container;
};


/**
 * onAdd is called when the map's panes are ready and the overlay has been added
 * to the map.
 */
CMLOC_Marker.prototype.onAdd = function() {

	var container = this.getContainer();
	container.style.position = 'absolute';
	container.draggable = true;
	
	var markerHTML;
	
	if (this._markerOptions.icon.length > 0) {
		var iconUrl = this._markerOptions.icon;
		if (iconUrl.substr(0, 4) != 'http' && iconUrl.substr(0, 2) != '//') {
			iconUrl = 'https://maps.google.com/mapfiles/kml/shapes/' + iconUrl +'.png';
		}
		var height = 40;
		var size = 'normal';
		if (typeof this._markerOptions.iconSize == 'string' && this._markerOptions.iconSize.length > 0) {
			size = this._markerOptions.iconSize;
		}
		markerHTML = '<img src="'+ iconUrl +'" class="cmloc-marker-icon-size-'+ size +'" style="position:relative;" />';
	} else {
		markerHTML = '<div style="z-index:200"><div class="cmloc-pin" style="background:'
			+ this._markerOptions.color + '"></div>'
			+ '<div class="cmloc-pin-triangle" style="border-top-color:'
			+ this._markerOptions.color + '"></div>'
			+ '<div class="cmloc-pin-dot"></div></div>';
	}
	
	container.innerHTML = '<div class="cmloc-custom-marker" style="'
			+ this._markerOptions.style
			+ '">'+ markerHTML +'</div>';
	if (this._labelOptions.text.length > 0) {
		var labelLeft = 12 - this.getTextWidth(
				this._labelOptions.text, 10);
		container.innerHTML += '<div class="cmloc-map-label" style="left:'
				+ labelLeft + 'px;z-index:300;' + this._labelOptions.style + '">'
				+ this._labelOptions.text + '</div>';
	}
	
	if (this._markerOptions.draggable) {
		this.setDragEvents(container);
	}

//	console.log(this._markerOptions);
	if (typeof this._markerOptions.title == 'string' && this._markerOptions.title.length > 0) {
		container.title = this._markerOptions.title;
	}
//	this.set('container', container)
	this.getPanes().floatPane.appendChild(container);
	
};


CMLOC_Marker.prototype.getTextWidth = function(text, fontSize) {
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




CMLOC_Marker.prototype.setDragEvents = function(container) {

	var dragging = false;
	var that = this;

	google.maps.event.addDomListener(this.get('map').getDiv(), 'mouseleave',
			function() {
				google.maps.event.trigger(container, 'mouseup');
			});

	google.maps.event
			.addDomListener(
					container,
					'mousedown',
					function(e) {
						that.mapObj.suspendAddWaypoints = true;
						dragging = true;
						this.style.cursor = 'move';
						that.map.set('draggable', false);
						that.set('origin', e);

						that.moveHandler = google.maps.event
								.addDomListener(
										that.get('map').getDiv(),
										'mousemove',
										function(e) {
											var origin = that.get('origin')
											var left = origin.clientX - e.clientX;
											var top = origin.clientY - e.clientY;
											var pos = that.getProjection().fromLatLngToDivPixel(that.get('position'));
											var latLng = that.getProjection().fromDivPixelToLatLng(new google.maps.Point(pos.x - left, pos.y - top));
											that.set('origin', e);
											that.set('position', latLng);
											that.draw();
										});

					});

	google.maps.event.addDomListener(container, 'mouseup', function(ev) {
		if (ev) {
			if (ev.preventDefault) {
				ev.preventDefault();
			}
			ev.cancelBubble = true;
			if (ev.stopPropagation) {
				ev.stopPropagation();
			}
		}
		if (that.map) {
			that.map.set('draggable', true);
		}
		this.style.cursor = 'default';
		google.maps.event.removeListener(that.moveHandler);
		google.maps.event.removeListener(that.clickHandler);
		if (dragging) {
			google.maps.event.trigger(that, 'dragend');
			google.maps.event.trigger(that, 'positionUpdated');
		}
		dragging = false;
		setTimeout(function() {
			that.mapObj.suspendAddWaypoints = false;
		}, 500);
	});


};



CMLOC_Marker.prototype.draw = function() {
	var pos = this.getProjection().fromLatLngToDivPixel(this.get('position'));
	this.get('container').style.left = (pos.x - 11) + 'px';
	this.get('container').style.top = (pos.y - 30 - 12) + 'px';
	return this;
};

CMLOC_Marker.prototype.onRemove = function() {
	var container = this.get('container');
	if (container) {
		container.parentNode.removeChild(container);
	}
 	this.set('container', null);
};

CMLOC_Marker.prototype.getPosition = function() {
	return this.get('position');
};

CMLOC_Marker.prototype.setPosition = function(pos) {
	this.set('position', pos);
	this.draw();
	google.maps.event.trigger(this, 'positionUpdated');
	return this;
};

CMLOC_Marker.prototype.setVisible = function(visible) {
	this.get('container').style.display = (visible ? 'block' : 'none');
	return this;
};


CMLOC_Marker.prototype.getContainer = function() {
	var container = this.get('container');
	if (!container) {
		container = this._createContainer();
	}
	return container;
};
