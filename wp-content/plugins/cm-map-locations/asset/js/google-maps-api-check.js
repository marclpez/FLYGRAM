/**
 * Testing the Google Maps API key.
 * 
 * @requires: jQuery
 * 
 * ----------------------------------------------------------------------------------
 * Manual
 * ----------------------------------------------------------------------------------
 * 1. Create a button with class="cminds-google-maps-api-check-btn", for example:
 *    <a href="#" class="button cminds-google-maps-api-check-btn">Test configuration</a>
 * 2. Pass the API key by setting the data-* attribute on the button. You can do it in 3 ways:
 *    a) Directly by setting a data-api-key attribute on the button, for example: data-api-key="abcd"
 *    b) By passing a text input field's selector to get the API key from, for example:
 *       data-api-key-field-selector="form#settings input[name=api_key]"
 *    c) By passing a HTML element's selector to get its text from, for example:
 *       data-api-text-element-selector="#configuration-table .google_maps_api_key"
 * 3. Basically that's it.
 * ----------------------------------------------------------------------------------
 */
jQuery(function($) {
	
	
	// ==========================================================
	// Configuration
	// ==========================================================
	
	var overlayId = 'cminds_google_maps_api_check_overlay';
	var mapCallbackName = 'cminds_google_maps_api_check_callback';
	
	var loaderImage = "data:image/gif;base64,R0lGODlhEAAQAPIAAP///wAAAMLCwkJCQgAAAGJiYoKCgpKSkiH/C05FVFNDQVBFMi4wAwEAAAAh/hpDcmVhdGVkIHdpdGggYW"
		+ "pheGxvYWQuaW5mbwAh+QQJCgAAACwAAAAAEAAQAAADMwi63P4wyklrE2MIOggZnAdOmGYJRbExwroUmcG2LmDEwnHQLVsYOd2mBzkYDAdKa+dIAAAh+QQJCgAAACwAAAAAEA"
		+ "AQAAADNAi63P5OjCEgG4QMu7DmikRxQlFUYDEZIGBMRVsaqHwctXXf7WEYB4Ag1xjihkMZsiUkKhIAIfkECQoAAAAsAAAAABAAEAAAAzYIujIjK8pByJDMlFYvBoVjHA70GU7"
		+ "xSUJhmKtwHPAKzLO9HMaoKwJZ7Rf8AYPDDzKpZBqfvwQAIfkECQoAAAAsAAAAABAAEAAAAzMIumIlK8oyhpHsnFZfhYumCYUhDAQxRIdhHBGqRoKw0R8DYlJd8z0fMDgsGo/"
		+ "IpHI5TAAAIfkECQoAAAAsAAAAABAAEAAAAzIIunInK0rnZBTwGPNMgQwmdsNgXGJUlIWEuR5oWUIpz8pAEAMe6TwfwyYsGo/IpFKSAAAh+QQJCgAAACwAAAAAEAAQAAADMwi"
		+ "6IMKQORfjdOe82p4wGccc4CEuQradylesojEMBgsUc2G7sDX3lQGBMLAJibufbSlKAAAh+QQJCgAAACwAAAAAEAAQAAADMgi63P7wCRHZnFVdmgHu2nFwlWCI3WGc3TSWhUF"
		+ "GxTAUkGCbtgENBMJAEJsxgMLWzpEAACH5BAkKAAAALAAAAAAQABAAAAMyCLrc/jDKSatlQtScKdceCAjDII7HcQ4EMTCpyrCuUBjCYRgHVtqlAiB1YhiCnlsRkAAAOwAAAAAAAAAAAA==";
	
	
	// ==========================================================
	// Helper functions
	// ==========================================================
	
	var createOverlay = function() {
		var overlay = $('<div>', {id: overlayId, style: 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);z-index:999;'})
		var content = $('<div>', {style: 'position:absolute;max-width:800px;height:50%;top:15%;margin:0 auto;left:0;right:0;background:white;'
			+ 'border:solid 1px #cccccc;padding:2em;max-height:90%;overflow:auto;text-align:center;font-size:120%;'});
		var loader = $('<div>', {style: "margin-bottom:1em;padding-left:30px;display:inline-block;background:url('"+ loaderImage +"') left center no-repeat;"});
		var loaderText = 'Testing configuration';
		loader.text(loaderText);
		content.append(loader);
		overlay.append(content);
		$('body').append(overlay);
		
		overlay.click(function(ev) {
			if (ev.target == overlay[0]) {
				overlay.fadeOut('fast', function() {
					overlay.remove();
				});
			}
		});
		
		return overlay;
		
	};
	
	
	var getApiKey = function(btn) {
		var apiKey = btn.data('apiKey'); // you can pass api key directly
		if (!apiKey) {
			var selector = btn.data('apiKeyFieldSelector'); // or give a selector to the input field
			if (selector) {
				apiKey = $(selector).val();
			} else {
				selector = btn.data('apiKeyTextElementSelector'); // or give a selector to an element with text
				apiKey = $(selector).text();
			}
		}
		return apiKey;
	};
	
	
//	var createIframe = function(content, apiKey) {
//		var iframe = document.createElement('iframe');
//		var html = '<html><head><style>html, body {margin: 0; padding: 0;}</style></head><body><div id="map" style="width:100%;height:100%;"></div>'
//			+ '<script>function initMap() { var map = new google.maps.Map(document.getElementById("map")); }</script>'
//			+ '<script src="https://maps.googleapis.com/maps/api/js?key='+ encodeURIComponent(apiKey) +'&libraries=places,geometry&callback=initMap"></script>'
//			+ '</body></html>';
//		iframe.src = 'data:text/html;charset=utf-8,' + encodeURI(html);
//		content.append(iframe);
//		console.log('iframe.contentWindow =', iframe.contentWindow.document);
//		$(iframe).css({width: '100%', height: '100%'});
//		return iframe;
//	};
	
	
	var createMap = function(content, apiKey, successCallback, errorCallback) {
		
		var mapContainer = $('<div>', {style: 'width:100%;height:85%;'});
		content.append(mapContainer);
		var script = document.createElement('script');
		
		window[mapCallbackName] = function() {
			try {
				// Create Google map instance
				var map = new google.maps.Map(mapContainer[0]);
				map.setCenter({lat: -34.397, lng: 150.644});
				map.setZoom(8);
				setTimeout(function() {
					// Check if Google displayed an error message since cannot catch it with a Javascript...
					if (document.querySelectorAll('.gm-err-container').length == 1) {
						errorCallback('Invalid API key');
					} else {
						successCallback();
					}
				}, 2000);
			} catch (e) {
				errorCallback(e);
			}
			
			// Clean
			window[mapCallbackName] = null;
			script.parentNode.removeChild(script);
			
		};
		
		script.src = 'https://maps.googleapis.com/maps/api/js?key='+ encodeURIComponent(apiKey) +'&libraries=places,geometry&callback='+ mapCallbackName;
		$('body').append(script);
		
		
	};
	
	
	var createMessage = function(type, msg, content) {
		var color = 'green';
		if (type == 'error') color = 'red';
		var container = $('<div>', {"style": 'font-weight:bold;color:' + color});
		container.text(msg);
		content.html('');
		content.append(container);
		
	};
	
	
	
	
	// ==============================================================================================================================
	// Okay, let's start!
	// ==============================================================================================================================
	
	$('.cminds-google-maps-api-check-btn').click(function(ev) {
		ev.preventDefault();
		ev.stopPropagation();
		
		var btn = $(this);
		var overlay = createOverlay();
		var content = overlay.find('div').first();
		var apiKey = getApiKey(btn);
		
		createMap(content, apiKey, function() {
			createMessage('success', 'Google Maps API loaded successfully', content);
		}, function(error) {
			createMessage('error', 'Google Maps API does not work: ' + error, content);
		});
		
	});
	
});