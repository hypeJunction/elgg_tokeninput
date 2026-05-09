import * as elgg from 'elgg';
import $ from 'jquery';
import 'jquery.tokeninput';
import { trigger } from 'elgg/hooks';
import { echo } from 'elgg/i18n';

const tokeninput = {
	/**
	 * Default configuration
	 */
	config: function () {
		return {
			method: 'POST',
			queryParam: 'term',
			searchDelay: 300,
			minChars: 0,
			propertyToSearch: 'label',
			preventDuplicates: true,
			hintText: echo('tokeninput:text:hint'),
			noResultsText: echo('tokeninput:text:noresults'),
			searchText: echo('tokeninput:text:searching'),
			deleteText: echo('tokeninput:text:delete'),
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
	init: function () {
		$(document)
				.off('initialize', '.elgg-input-tokeninput:not(.elgg-state-ready)')
				.on('initialize', '.elgg-input-tokeninput:not(.elgg-state-ready)', tokeninput.initInput);
		$('.elgg-input-tokeninput:not(.elgg-state-ready)').trigger('initialize');
	},
	/**
	 * Initialize the input
	 * @returns {void}
	 */
	initInput: function () {
		var $input = $(this);
		var params = $.extend(true, {}, tokeninput.config());
		$.extend(params, $input.data());
		$input.tokenInput($input.data('href'), params);
		$input.addClass('elgg-state-ready');

		if (params.sortable) {
			import('jquery-ui/widgets/sortable').then(function () {
				$input.parent().find('.token-input-list').sortable({
					items: '.token-input-token',
					connectWith: '.token-input-list',
					forcePlaceholderSize: true,
					placeholder: 'token-input-token-placeholder',
					opacity: 0.8,
					revert: 500
				});
			});
		}
	},
	/**
	 * Format dropdown results
	 * @param {object} item
	 * @returns {String|Bool|@var;value|Object}
	 */
	resultsFormatter: function (item) {
		var html = (item.html_result) ? '<li>' + item.html_result + '</li>' : '<li><div class="elgg-image-block elgg-tokeninput-suggestion">\n\
				<div class="elgg-image">' + ((item.icon) ? item.icon : '') + '</div>\n\
				<div class="elgg-body">' + ((item.label) ? item.label : '') + '<br />\n\
					<span class="elgg-subtext">' + ((item.metadata) ? item.metadata : '') + '</span>\n\
				</div>\n\
		</div></li>';
		html = trigger('results:formatter', 'tokeninput', {item: item}, html);
		return html;
	},
	/**
	 * Format tokens
	 * @param {object} item
	 * @returns {String|Bool|@var;value|Object}
	 */
	tokenFormatter: function (item) {
		var html = (item.html_token) ? '<li><p>' + item.html_token + '</p></li>' : '<li><p><div class="elgg-image-block elgg-tokeninput-token">\n\
				<div class="elgg-image">' + ((item.icon) ? item.icon : '') + '</div>\n\
				<div class="elgg-body">' + ((item.label) ? item.label : '') + '</div>\n\
		</div></p></li>';
		html = trigger('results:formatter', 'tokeninput', {item: item}, html);
		return html;
	}
};

export default tokeninput;

tokeninput.init();
