CMLOC_Tooltip.prototype = new google.maps.OverlayView();

function CMLOC_Tooltip(widget, position, content, style) {

	this.widget = widget;
	this.content = content;
	this.position = position;
	
	this.offsetTop = 0;
	this.offsetLeft = 0;
	
	if (typeof style != 'object') style = {};
	this.style = style;
	
//	this.setMap(widget.map.map);

}

CMLOC_Tooltip.prototype.onAdd = function() {

//	console.log('CMLOC_Tooltip.prototype.onAdd');
	
	var div = document.createElement('div');
	div.setAttribute('class', 'cmloc-map-tooltip');
	div.style.borderStyle = 'solid';
	div.style.borderColor = 'black';
	div.style.borderWidth = '1px';
	div.style.backgroundColor = '#ffff66';
	div.style.padding = '0.2em 0.5em';
	div.style.zIndex = '9999999';
	div.style.position = 'absolute';
	
	for (var param in this.style) {
		div.style[param] = this.style[param];
	}
	
	div.style.color = this.getContrastColor(div.style.backgroundColor);
	
	div.innerHTML = this.content;

	this.div_ = div;

	// Add the element to the "overlayLayer" pane.
	var panes = this.getPanes();
	panes.overlayLayer.appendChild(div);
	
};

CMLOC_Tooltip.prototype.draw = function() {

//	console.log('CMLOC_Tooltip.prototype.draw');
	var pos = this.getProjection().fromLatLngToDivPixel(this.position);
	this.div_.style.left = (pos.x + this.offsetLeft) + 'px';
	this.div_.style.top = (pos.y + this.offsetTop) + 'px';
	return this;
	
};

// The onRemove() method will be called automatically from the API if
// we ever set the overlay's map property to 'null'.
CMLOC_Tooltip.prototype.onRemove = function() {
	this.div_.parentNode.removeChild(this.div_);
	this.div_ = null;
};


CMLOC_Tooltip.prototype.getContrastColor = function(color) {
    var d = 0;
    
    var hexToRgb = function(hex) {
	    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
	    return result ? {
	        R: parseInt(result[1], 16),
	        G: parseInt(result[2], 16),
	        B: parseInt(result[3], 16)
	    } : null;
	};
    
    var componentToHex = function(c) {
	    var hex = c.toString(16);
	    return hex.length == 1 ? "0" + hex : hex;
	};

	var rgbToHex = function(r, g, b) {
	    return "#" + componentToHex(r) + componentToHex(g) + componentToHex(b);
	}
    
	if (color.substr(0, 1) == '#') {
		color = hexToRgb(color);
		if (!color) {
			return '#000000';
		}
	} else {
//		console.log(color);
		var result = color.match(/[0-9]+/g);
		if (result) {
			color = {R: result[0], G: result[1], B: result[2]};
		} else {
			return '#000000';
		}
	}

    // Counting the perceptive luminance - human eye favors green color... 
    var a = 1 - ( 0.299 * color.R + 0.587 * color.G + 0.114 * color.B)/255;

    if (a < 0.5)
       d = 0; // bright colors - black font
    else
       d = 255; // dark colors - white font

    return  rgbToHex(d, d, d);
    
};