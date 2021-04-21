function CMLOC_Editor_Images_init() {
	
	var $ = jQuery;
	var container = $(this);
	
	CMLOC_Editor_Images_delete_init(container.find('li'));
	
	$('.cmloc-images-upload', container).change(function() {
		
		if (this.files.length == 0) return;
		
		var input = $(this);
		var container = input.parents('.cmloc-images').first();
		var fileList = container.find('.cmloc-images-list');
		var idsInput = container.find('input[type=hidden]');
		
		var loader = $('<li/>', {"class": 'cmloc-ajax-loader'});
		fileList.append(loader);
		
		var data = new FormData();
		data.append('action', 'cmloc_route_image_upload');
		data.append('nonce', CMLOC_Editor_Images.nonce);
		for (var i=0; i<this.files.length; i++) {
			data.append( 'file_'+ i, this.files[i] );
		}
		
		$.ajax({
		  url: CMLOC_Editor_Images.ajax_url,
		  data: data,
		  processData: false,
		  contentType: false,
		  type: 'POST',
		  success: function(response) {
			console.log(response);
			input[0].value = '';
			loader.remove();
		    if (response.success) {
		    	for (var i=0; i<response.files.length; i++) {
		    		var file = response.files[i];
		    		CMLOC_Editor_Images_add(idsInput, fileList, file.id, file.thumb, file.url);
		    	}
		    }
		  }
		});
		
	});
	
	
	$('.cmloc-add-video-btn', container).click(function() {
		
		var input = $(this);
		var container = input.parents('.cmloc-images').first();
		var fileList = container.find('.cmloc-images-list');
		var idsInput = container.find('input[type=hidden]');
		
		var video = prompt('Enter the video file URL or the YouTube URL address:');
		if (video !== false && video !== null) {
			var loader = $('<li/>', {"class": 'cmloc-ajax-loader'});
			fileList.append(loader);
			var data = {action: 'cmloc_route_add_video', nonce: CMLOC_Editor_Images.nonce, url: video};
			$.post(CMLOC_Editor_Images.ajax_url, data, function(response) {
				console.log(response);
				loader.remove();
				if (response.success) {
					CMLOC_Editor_Images_add(idsInput, fileList, response.id, response.thumb, response.url);
				}
			});
		}
	});
	
	
	$('.cmloc-images-add-btn', container).click(function(ev) {
		ev.preventDefault();
		ev.stopPropagation();
		
		tb_show(CMLOC_Editor_Images.title, CMLOC_Editor_Images.url);
		
		var container = $(this).parents('.cmloc-images').first();
		var fileList = container.find('.cmloc-images-list');
		var fileInput = container.find('input[type=hidden]');
		
		window.send_to_editor = function(html) {
			console.log(html);
			
			tb_remove();
			
			var image = $('<div>' + html + '</div>');
			var match = image.find('img').attr('class').match(/wp-image-([0-9]+)/);
			if (match && typeof match[1] == 'string') {
				var response = {id: match[1], thumb: image.find('img').attr('src'), url: image.find('a').attr('href')};
				CMLOC_Editor_Images_add(fileInput, fileList, response.id, response.thumb, response.url);
			}
			
//			var matchHref = html.match(/(a|img) (href|src)=["']([^"']+)["']/);
//			if (matchHref && typeof matchHref[3] == 'string') {
//				
//				var href = matchHref[3];
//				
//				console.log(href);
//				
//				$.post(CMLOC_Editor_Images.ajax_url, {action: 'cmloc_get_image_id', url: href}, function(response) {
//					console.log(response);
//					if (response.success) {
//						CMLOC_Editor_Images_add(fileInput, fileList, response.id, response.thumb, response.url);
//					}
//				});
//				
//			}
		};
		
	});
	
	$('.cmloc-images-list', container).sortable({
		update: function(event, ui) {
			var items = ui.item.parents('.cmloc-images-list').find('li:visible');
			var val = '';
			var input = ui.item.parents('.cmloc-images').find('input[name=images]');
//			console.log(input.val());
//			console.log(items);
			for (var i=0; i<items.length; i++) {
				if (val.length > 0) val += ',';
				val += items[i].getAttribute('data-id');
			}
//			console.log(val);
			input.val(val);
		}
	}).disableSelection();
	
	
}


function CMLOC_Editor_Images_add(fileInput, fileList, id, thumb, url) {
	fileInput.val(fileInput.val() + ',' + id);
	
	var item = fileList.find('li[data-id=0]').first().clone();
	item.data('id', id);
	item.attr('data-id', id);
	item.find('img').first().attr('src', thumb);
	item.find('a').first().attr('href', url);
	fileList.append(item);
	item.fadeIn('slow', function() {
		CMLOC_Editor_Images_delete_init(item);
	});
	fileList.parents('.cmloc-images').first().find('.cmloc-field-desc').show();
}


function CMLOC_Editor_Images_delete_init(items) {
	jQuery('.cmloc-image-delete', items).click(function(ev) {
		ev.preventDefault();
		ev.stopPropagation();
		var obj = jQuery(this);
		var item = obj.parents('li').first();
		var id = item.data('id');
		var container = items.first().parents('.cmloc-images').first();
		var fileInput = container.find('input[type=hidden]');
		console.log(fileInput.val());
		var val = fileInput.val().split(',');
		for (var i=0; i<val.length; i++) {
			if (val[i] == id) {
				val.splice(i, 1);
				break;
			}
		}
		fileInput.val(val.join(','));
		console.log(fileInput.val());
		item.fadeOut('slow', function() {
			item.remove();
		});
	});
}

jQuery(document).ready(function() {
	jQuery('.cmloc-embed-shortcode textarea').click(function() {
		this.select();
	});
});
