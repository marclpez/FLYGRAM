jQuery(function($) {
	
	$('.cmloc_category_icon_choose').click(function() {
		var btn = $(this);
		btn.parents('.cmloc_category_icon').find('.cmloc_category_icon_list').show();
		$('.cmloc_category_icon_list img').css('cursor', 'pointer');
	});
	
	$('.cmloc_category_icon_list img').click(function() {
		var obj = $(this);
		obj.parents('.cmloc_category_icon').find('.cmloc_category_icon_list').hide();
		obj.parents('.cmloc_category_icon').find('.cmloc_category_icon_image').attr('src', obj.attr('src'));
		obj.parents('.cmloc_category_icon').find('input[name=cmloc_category_icon]').val(obj.attr('src'));
	});
	
	// Settings tabs handler
	$('.cmloc-settings-tabs a').click(function() {
		var match = this.href.match(/\#tab\-([^\#]+)$/);
		$('#settings .settings-category.current').removeClass('current');
		$('#settings .settings-category-'+ match[1]).addClass('current');
		$('.cmloc-settings-tabs a.current').removeClass('current');
		$('.cmloc-settings-tabs a[href="#tab-'+ match[1] +'"]').addClass('current');
		this.blur();
	});
	if (location.hash.length > 0) {
		$('.cmloc-settings-tabs a[href="'+ location.hash +'"]').click();
	} else {
		$('.cmloc-settings-tabs li:first-child a').click();
	}
	
	
	// Access custom cap handler
	var settingsAccessCustomCapListener = function() {
		var obj = $(this);
		var nextField = obj.parents('tr').first().next();
		if ('cmloc_capability' == obj.val()) {
			nextField.show();
		} else {
			nextField.hide();
		}
	};
	$('select[name^=cmloc_access_map_]').change(settingsAccessCustomCapListener);
	$('select[name^=cmloc_access_map_]').change();
	
	$('.cmloc-admin-notice .cmloc-dismiss').click(function(ev) {
		ev.preventDefault();
		ev.stopPropagation();
		var btn = $(this);
		var data = {action: btn.data('action'), nonce: btn.data('nonce'), id: btn.data('id')};
		$.post(btn.attr('href'), data, function(response) {
			btn.parents('.cmloc-admin-notice').fadeOut('slow');
		});
	});

	$('.cmloc-embed-shortcode textarea').click(function() {
		this.select();
	});
	
});