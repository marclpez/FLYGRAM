jQuery(function($) {
	
	var getYouTubeVideoId = function(url) {
		var match = url.match(/youtu\.be\/(.+)$/);
		if (match && match.length == 2) {
			return match[1];
		}
		match = url.match(/[\?\&]v=([^&]+)/);
		if (match && match.length == 2) {
			return match[1];
		}
	};
	
	var overlay = $('<div id="cmloc-gallery-overlay">');
	var overlayShadow = $('<div id="cmloc-gallery-overlay-shadow"></div>');
	var overlayContent = $('<div id="cmloc-gallery-overlay-content"></div>');
	overlay.append(overlayShadow);
	overlay.append(overlayContent);
	$('body').append(overlay);
	overlay.hide();
	overlayShadow.click(function() {
		overlay.hide();
		overlayContent.html('');
	});
	
	var galleryStack = $('a.cmloc-gallery');
	for (var i=0; i<galleryStack.length; i++) {
		$(galleryStack[i]).data('galleryIndex', i);
	}
	var currentGalleryIndex = null;
	
	galleryStack.click(function(ev) {
		ev.stopPropagation();
		ev.preventDefault();
		
		var obj = $(this);
		currentGalleryIndex = obj.data('galleryIndex');
		
		overlayContent.html('');
		overlay.show();
		
		var url = obj.attr('href');
		if (url.match(/https?:\/\/(www\.)?(youtube\.com|youtu\.be)\//)) {
			var width = $(window).width()-100;
			var height = Math.floor(width * 315/560);
			if (height > $(window).height()-100) {
				height = $(window).height()-100;
				console.log(height);
				width = Math.floor(height * 560/315);
			}
			var content = $('<iframe frameborder="0" allowfullscreen></iframe>');
			content.attr('src', 'https://www.youtube.com/embed/' + getYouTubeVideoId(url));
			content.css('width', width + 'px');
			content.css('height', height + 'px');
			overlayContent.css('left', (($(window).width()-width)/2) + 'px');
			overlayContent.css('top', (($(window).height()-height)/2) + 'px');
			overlayContent.append(content);
		}
		else if (url.match(new RegExp('\.(jpe?g|png|gif|bmp|webp|bpg)$', 'i'))) {
			var content = $('<img/>', {src: url});
			content.css('max-width', $(window).width()+'px');
			content.css('display', 'none');
			overlayContent.append(content);
			setTimeout(function() {
				var url_arr = url.split("-");
				var image_size = url_arr[url_arr.length-1];
				var image_width_arr = image_size.split("x");
				var image_width = image_width_arr[0];
				if (content.width()) {
					overlayContent.css('left', Math.floor(($(window).width() - content.width())/2) + 'px');
				} else {
					if(image_width) {
						overlayContent.css('left', Math.floor(($(window).width() - image_width)/2) + 'px');
					} else {
						content.css('width', '50%');
						overlayContent.css('left', '25%');
					}
				}
				content.fadeIn('slow');
			}, 100);
		}
		else if (url.match(new RegExp('\.(mp4|avi|mpe?g|ogg|webm|mkv|flv|vob|ogv|mov|wmv|rm|rmvb|m4p|m4v|3gp)$', 'i'))) {
			content = $('<video>Your browser does not support video.</video>');
//			, {src: url, controls: 'controls', autoplay: 'autoplay'});
			content.attr('src', url);
			content.attr('controls', 'controls');
			content.attr('autoplay', 'autoplay');
			overlayContent.append(content);
			content[0].play();
			
			content.attr('width', Math.floor($(window).width()/2));
			overlayContent.css('left', '25%');
			
		}
		
		
		
	});
	
	$(document).bind('keydown', function(e) {
		if (!overlay.is(':visible')) return;
		if ( 39 == e.which ) { // next
			if (currentGalleryIndex < galleryStack.length-1) {
				currentGalleryIndex++;
				galleryStack[currentGalleryIndex].click();
			}
		}
		else if ( 37 == e.which ) { // prev
			if (currentGalleryIndex > 0) {
				currentGalleryIndex--;
				galleryStack[currentGalleryIndex].click();
			}
		}
		else if ( 27 == e.which ) { // escape
			overlay.hide();
			overlayContent.html('');
		}
	});
	
});
