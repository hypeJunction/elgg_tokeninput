<?php if (FALSE) : ?>
	<style type="text/css">
	<?php endif; ?>


	ul.token-input-list {
		overflow: hidden;
		height: auto !important;
		height: 1%;
		width: 100% !important;
		border: 1px solid #cfcfcf;
		cursor: text;
		font-size: 0.75em;
		z-index: 999;
		margin: 0 0 10px 0;
		padding: 0 3px;
		background-color: #fff;
		list-style-type: none;
		clear: left;
		-moz-box-sizing: border-box;
		-webkit-box-sizing: border-box;
		box-sizing: border-box;
	}

	ul.token-input-list li {
		width: 100%;
		overflow: hidden;
		list-style-type: none;
	}

	ul.token-input-list li input,
	ul.token-input-list li input:focus {
		border: 1px solid transparent;
		margin: 0;
		height: auto;
		width: 100%;
		min-width: 185px;
		background-color: white;
		-webkit-appearance: caret;
		-webkit-box-shadow: none;
		-moz-box-shadow: none;
		box-shadow: none;
	}

	ul.token-input-disabled li.token-input-token {
		background-color: whitesmoke;
		color: #666666;
	}

	li.token-input-token {
		overflow: hidden;
		height: auto !important;
		height: 1%;
		margin: 3px 0;
		padding: 3px 5px;
		background-color: whitesmoke;
		color: #666666;
		font-weight: bold;
		cursor: default;
		display: block;
	}

	li.token-input-token p {
		float: left;
		padding: 0;
		margin: 0;
	}

	li.token-input-token span {
		float: right;
		color: #666666;
		cursor: pointer;
	}

	li.token-input-selected-token {
		background-color: #e8e8e8;
		color: #595959;
	}

	li.token-input-selected-token span {
		color: #fff;
	}

	div.token-input-dropdown {
		position: absolute;
		width: 100%;
		background-color: #fff;
		padding: 3px;
		overflow: hidden;
		border-left: 1px solid #cfcfcf;
		border-right: 1px solid #cfcfcf;
		border-bottom: 1px solid #cfcfcf;
		cursor: default;
		font-size: 0.75em;
		z-index: 1;
		-moz-box-sizing: border-box;
		-webkit-box-sizing: border-box;
		box-sizing: border-box;
	}

	div.token-input-dropdown p {
		margin: 0;
		padding: 5px;
		font-weight: bold;
		color: #666666;
	}

	div.token-input-dropdown ul {
		margin: 0;
		padding: 0;
	}

	div.token-input-dropdown ul li {
		background-color: #fff;
		padding: 3px;
		list-style-type: none;
	}

	div.token-input-dropdown ul li.token-input-dropdown-item {
		background-color: #f5f5f5;
	}

	div.token-input-dropdown ul li.token-input-dropdown-item2 {
		background-color: #fff;
	}

	div.token-input-dropdown ul li em {
		font-weight: bold;
		font-style: normal;
	}

	div.token-input-dropdown ul li.token-input-selected-dropdown-item {
		background-color: #e8e8e8;
		color: #595959;
	}

	.elgg-tokeninput-suggestion {
		margin: 3px;
	}

	.elgg-tokeninput-suggestion .elgg-image {
		margin-right: 10px;
	}

	.elgg-tokeninput-suggestion .elgg-image,
	.elgg-tokeninput-suggestion .elgg-image img {
		max-width: 40px;
		max-height: 40px;
		height: auto;
		overflow: hidden;
	}

	.elgg-tokeninput-token {
		float: left;
		width: 95%;
	}

	.elgg-tokeninput-token .elgg-image,
	.elgg-tokeninput-token .elgg-image img {
		max-width: 25px;
		max-height: 25px;
		height: auto;
		overflow: hidden;
	}