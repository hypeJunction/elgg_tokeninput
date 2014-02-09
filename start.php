<?php

define('ELGG_TOKENINPUT_PAGEHANDLER', 'tokeninput');

elgg_register_event_handler('init', 'system', 'elgg_tokeninput_init');

/**
 * Initialize the plugin
 */
function elgg_tokeninput_init() {

	elgg_register_library('elgg.tokeninput', elgg_get_plugins_path() . 'elgg_tokeninput/lib/tokeninput.php');

	elgg_register_js('jquery.tokeninput.js', '/mod/elgg_tokeninput/vendors/jquery-tokeninput/src/jquery.tokeninput.js');

	elgg_register_simplecache_view('js/elgg_tokeninput/tokeninput');
	elgg_register_js('elgg.tokeninput.js', elgg_get_simplecache_url('js', 'elgg_tokeninput/tokeninput'));

	elgg_register_simplecache_view('css/elgg_tokeninput/tokeninput');
	elgg_register_css('elgg.tokeninput.css', elgg_get_simplecache_url('css', 'elgg_tokeninput/tokeninput'));

	elgg_register_plugin_hook_handler('action', 'all', 'elgg_tokeninput_explode_field_values', 1);

	elgg_register_page_handler(ELGG_TOKENINPUT_PAGEHANDLER, 'elgg_tokeninput_page_handler');
}

/**
 * Unserialize tokeninput field values before performing an action
 */
function elgg_tokeninput_explode_field_values($hook, $type, $return, $params) {

	$elgg_tokeninput_fields = get_input('elgg_tokeninput_fields');

	if ($elgg_tokeninput_fields) {
		foreach ($elgg_tokeninput_fields as $field_name) {
			$values = explode(',', get_input($field_name, ''));
			set_input($field_name, $values);
		}
		set_input($elgg_tokeninput_fields, null);
	}

	return $return;
}

/**
 * Page handler for parcing autocomplete results
 *
 * @param type $page
 */
function elgg_tokeninput_page_handler($page) {

	elgg_load_library('elgg.tokeninput');

	$user = elgg_get_logged_in_user_entity();

	$callback = urldecode(get_input('callback', 'elgg_tokeninput_search_all'));
	$q = urldecode(get_input('term', get_input('q', '')));
	$strict = (bool) get_input('strict', true);

	if (!is_callable($callback))
		exit;


	$results = array();

	$entities = call_user_func($callback, $q);

	if ($entities) {
		foreach ($entities as $entity) {
			if (elgg_instanceof($entity)) {
				$results[] = elgg_tokeninput_export_entity($entity);
			} else if ($entity instanceof ElggMetadata) {
				$results[] = elgg_tokeninput_export_metadata($entity);
			} else {
				$results[] = (array) $entity;
			}
		}
	} else if ($strict === false) {
		$suggest = array(
			'label' => $q,
			'value' => $q,
			'html_result' => '<span>' . elgg_echo('tokeninput:suggest', array($q)) . '</span>'
		);

		$results[] = $suggest;
	}

	header("Content-Type: application/json");
	echo json_encode($results);
	exit;
}