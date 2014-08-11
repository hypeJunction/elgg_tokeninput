define(function(require) {

	var $ = require('jquery');

	if ($('.elgg-input-tokeninput').length) {
		require(['tokeninput/lib'], function(tokeninput) {
			tokeninput.init();
		});
	}

	$(document).ajaxSuccess(function(data) {
		if ($(data).has('.elgg-input-tokeninput')) {
			require(['tokeninput/lib'], function(tokeninput) {
				tokeninput.init();
			});
		}
	});
});