<?php if (FALSE) : ?>
	<script type="text/javascript">
<?php endif; ?>

	elgg.provide('elgg.tokeninput');

	elgg.tokeninput.init = function() {

		elgg.tokeninput.config = {
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
			resultsFormatter: elgg.tokeninput.resultsFormatter,
			tokenFormatter: elgg.tokeninput.tokenFormatter,
			tokenValue: 'value',
			escapeHTML: false,
		}

		$('.elgg-input-tokeninput').live('initialize', elgg.tokeninput.initInput);

		$('.elgg-input-tokeninput')
				.each(function() {
			$(this).trigger('initialize');
		})

	}

	elgg.tokeninput.initInput = function(e) {

		var $input = $(this);

		var params = $.extend(true, {}, elgg.tokeninput.config);
		$.extend(params, $input.data());

		$input.tokenInput($input.data('href'), params);

	}

	elgg.tokeninput.resultsFormatter = function(item) {

		var html = (item.html_result) ? '<li>' + item.html_result + '</li>' :
				'<li><div class="elgg-image-block elgg-tokeninput-suggestion">\n\
					<div class="elgg-image">' + ((item.icon) ? item.icon : '') + '</div>\n\
					<div class="elgg-body">' + ((item.label) ? item.label : '') + '<br />\n\
						<span class="elgg-subtext">' + ((item.metadata) ? item.metadata : '') + '</span>\n\
					</div>\n\
			</div></li>';

		html = elgg.trigger_hook('results:formatter', 'tokeninput', {item: item}, html);

		return html;
	}

	elgg.tokeninput.tokenFormatter = function(item) {
		
		var html = (item.html_token) ? '<li><p>' + item.html_token + '</p></li>' :
				'<li><p><div class="elgg-image-block elgg-tokeninput-token">\n\
					<div class="elgg-image">' + ((item.icon) ? item.icon : '') + '</div>\n\
					<div class="elgg-body">' + ((item.label) ? item.label : '') + '</div>\n\
			</div></p></li>';

		html = elgg.trigger_hook('results:formatter', 'tokeninput', {item: item}, html);

		return html;
	}

	elgg.register_hook_handler('init', 'system', elgg.tokeninput.init);


<?php if (FALSE) : ?></script><?php
endif;
?>
