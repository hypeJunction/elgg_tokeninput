require(['jquery'], function ($) {

	if ($('.elgg-input-tokeninput').length) {
		require(['tokeninput/lib'], function(tokeninput) {
			tokeninput.init();
		});
	}

	$(document).ajaxComplete(function() {
		if ($('.elgg-input-tokeninput:not(.elgg-state-ready)').length) {
			require(['tokeninput/lib'], function(tokeninput) {
				tokeninput.init();
			});
		}
	});
	
});