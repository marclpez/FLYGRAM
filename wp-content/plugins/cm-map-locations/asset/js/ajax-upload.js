jQuery(function($) {

	$('.cmloc-upload-wrapper input[type=file]')
		.click(function() {
//			console.log('click');
			$(this).val("");
		})
		.change(function() {
			
//			console.log('change');
			
			var input = $(this);
			var loader = $('<div/>', {"class":"cmloc-loader-inline"});
			input.parents('.cmloc-upload-wrapper').append(loader);
			
			var isMultiple = input.attr('multiple');
			var files = this.files;
			var formData = new FormData();
			// Loop through each of the selected files.
			for (var i = 0; i < files.length; i++) {
				var file = files[i];

				// Check the file type.
				if (!file.type.match('image.*')) {
					continue;
				}

				// Add the file to the request.
				formData.append(input.attr('name') + (isMultiple ? '[]' : ''), file, file.name);
			}
			
			formData.append('action', input.data('action'));
			formData.append('nonce', input.data('nonce'));
			
			var xhr = new XMLHttpRequest();
			xhr.open('POST', input.data('url'), true);
			xhr.onload = function () {
				loader.remove();
				console.log(xhr);
				if (xhr.status === 200) {
					var response = JSON.parse(xhr.response);
					if (response.success) {
						var wrapper = input.parents('.cmloc-field-icon');
						wrapper.find('input[name=icon]').val(response.iconUrl);
						wrapper.find('img.cmloc-current-icon').attr('src', response.iconUrl);
					}
				} else {
					alert('An error occurred!');
				}
			};
			xhr.send(formData);
			
		});

});
