(function($) {

window.CMLOC = {};
window.CMLOC.Utils = {
		
	addSingleHandler: function(handlerName, selector, action, func) {
		var obj;
		if (typeof selector == 'string') obj = $(selector);
		else obj = selector;
		obj.each(function() {
			var obj = $(this);
			if (obj.data(handlerName) != '1') {
				obj.data(handlerName, '1');
				obj.on(action, func);
			}
		});
	},
	
	leftClick: function(func) {
		return function(e) {
			// Allow to use middle-button to open thread in a new tab:
			if (e.which > 1 || e.shiftKey || e.altKey || e.metaKey || e.ctrlKey) return;
			func.apply(this, [e]);
			return false;
		}
	},
	
	
	toast: function(msg, className, duration) {
		if (typeof className == 'undefined') className = 'info';
		if (typeof duration == 'undefined') duration = 5;
		var toast = $('<div/>', {"class":"cmloc-toast "+ className, "style":"display:none"});
		toast.text(msg);
		$('body').append(toast);
		toast.fadeIn(500, function() {
			setTimeout(function() {
				toast.fadeOut(500);
			}, duration*1000);
		});
	},
	
	overlay: function(content) {
		var overlay = $('<div>', {"class": 'cmloc-overlay'});
		var contentOuter = $('<div>', {"class": 'cmloc-overlay-content-outer'});
		var contentWrapper = $('<div>', {"class": 'cmloc-overlay-content'});
		var closeButton = $('<span>', {"class": 'cmloc-overlay-close'})
		closeButton.html('&times;');
		$('body').append(overlay);
		overlay.append(contentOuter);
		contentOuter.append(contentWrapper);
		if (typeof content == 'string') contentWrapper.html(content);
		else contentWrapper.append(content.clone());
		contentWrapper.append(closeButton);
		overlay.fadeIn('fast');
		var close = function() {
			overlay.fadeOut('fast', function() {
				overlay.remove();
			});
		};
		overlay.click(function(ev) {
			var target = $(ev.target);
			if (target.hasClass('cmloc-overlay')) {
				close();
			}
		});
		closeButton.click(close);
		$(window).keydown(function(ev) {
			if (ev.keyCode == 27) {
				close();
			}
		});
		return overlay;
	}
		
};


$('.cmloc-delete-confirm').click(function(ev) {
	if (!confirm(CMLOC_Utils.deleteConfirmText)) {
		ev.stopPropagation();
		ev.preventDefault();
	}
});


})(jQuery);
	


