define(['elgg', 'jquery', 'jquery.tokeninput'], function(elgg, $) {

	var tokeninput = {
		/**
		 * Default configuration
		 */
		config: function() {
			return {
				method: 'POST',
				queryParam: 'term',
				searchDelay: 300,
				minChars: 0,
				propertyToSearch: 'label',
				preventDuplicates: true,
				hintText: elgg.echo('tokeninput:text:hint'),
				noResultsText: elgg.echo('tokeninput:text:noresults'),
				searchText: elgg.echo('tokeninput:text:searching'),
				deleteText: elgg.echo('tokeninput:text:delete'),
				resultsLimit: 10,
				tokenLimit: null,
				resultsFormatter: tokeninput.resultsFormatter,
				tokenFormatter: tokeninput.tokenFormatter,
				tokenValue: 'value',
				escapeHTML: false
			};
		},
		/**
		 * Initialize the module
		 * @returns {void}
		 */
		init: function() {
			$(document).on('initialize', '.elgg-input-tokeninput:not(.elgg-state-ready)', tokeninput.initInput);
			$('.elgg-input-tokeninput:not(.elgg-state-ready)').trigger('initialize');
		},
		/**
		 * Initialize the input
		 * @returns {void}
		 */
		initInput: function() {
			var $input = $(this);
			var params = $.extend(true, {}, tokeninput.config());
			$.extend(params, $input.data());
			$input.tokenInput($input.data('href'), params);
			$input.addClass('elgg-state-ready');
		},
		/**
		 * Format dropdown results
		 * @param {object} item
		 * @returns {String|Bool|@var;value|Object}
		 */
		resultsFormatter: function(item) {
			var html = (item.html_result) ? '<li>' + item.html_result + '</li>' :
					'<li><div class="elgg-image-block elgg-tokeninput-suggestion">\n\
					<div class="elgg-image">' + ((item.icon) ? item.icon : '') + '</div>\n\
					<div class="elgg-body">' + ((item.label) ? item.label : '') + '<br />\n\
						<span class="elgg-subtext">' + ((item.metadata) ? item.metadata : '') + '</span>\n\
					</div>\n\
			</div></li>';
			html = elgg.trigger_hook('results:formatter', 'tokeninput', {item: item}, html);
			return html;
		},
		/**
		 * Format tokens
		 * @param {object} item
		 * @returns {String|Bool|@var;value|Object}
		 */
		tokenFormatter: function(item) {
			var html = (item.html_token) ? '<li><p>' + item.html_token + '</p></li>' :
					'<li><p><div class="elgg-image-block elgg-tokeninput-token">\n\
					<div class="elgg-image">' + ((item.icon) ? item.icon : '') + '</div>\n\
					<div class="elgg-body">' + ((item.label) ? item.label : '') + '</div>\n\
			</div></p></li>';
			html = elgg.trigger_hook('results:formatter', 'tokeninput', {item: item}, html);
			return html;
		}
	};

	return tokeninput;
});