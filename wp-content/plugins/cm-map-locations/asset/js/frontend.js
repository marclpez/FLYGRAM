jQuery(function($) {
	
	$('.cmloc-embed-btn a').click(function(ev) {
		ev.preventDefault();
		var wrapper = $(this).parents('.cmloc-toolbar').parent();
		var overlay = CMLOC.Utils.overlay(wrapper.find('.cmloc-route-embed'));
		overlay.find('.cmloc-route-embed').show();
		overlay.find('.cmloc-route-embed textarea').click(function() {
			this.select();
		});
		$(".cmloc-route-embed-copy-btn", overlay).click(function(e) {
			e.preventDefault();
			var wrapper = $(this).parents('.cmloc-route-embed');
		    wrapper.find("textarea").select();
		    document.execCommand('copy');
		});
	});
	
	
});
