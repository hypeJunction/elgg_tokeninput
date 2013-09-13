<?php if (FALSE) : ?>
	<script type="text/javascript">
<?php endif; ?>

	elgg.provide('elgg.tokeninput');

	elgg.tokeninput.init = function() {

		elgg.tokeninput.config = {
			method: 'POST',
			queryParam: 'term',
			searchDelay: 300,
			minChars: 1,
			propertyToSearch: 'label',
			preventDuplicates: true,
			hintText: elgg.echo('tokeninput:text:hint'),
			noResultsText: elgg.echo('tokeninput:text:noresults'),
			searchText: elgg.echo('tokeninput:text:searching'),
			deleteText: elgg.echo('tokeninput:text:delete'),
			resultsLimit: 10,
			tokenLimit: null,
			resultsFormatter: elgg.tokeninput.resultsFormatter,
//		tokenFormatter: elgg.tokeninput.tokenFormatter,
			tokenValue: 'value'
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
	
		$input.tokenInput($input.attr('href'), params);
	}

	elgg.tokeninput.resultsFormatter = function(item) {
		var html = '<li><div class="elgg-image-block elgg-tokeninput-suggestion"><div class="elgg-image">' + item.icon + '</div><div class="elgg-body">' + item.label + '<br /><span class="elgg-subtext">' + ((item.metadata) ? item.metadata : '') + '</span></div></div></li>';
		return html;
	}

	elgg.register_hook_handler('init', 'system', elgg.tokeninput.init);

<?php if (FALSE) : ?></script><?php
endif;
?>
