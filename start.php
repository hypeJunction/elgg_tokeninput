<?php

require_once __DIR__ . '/autoloader.php';
require_once __DIR__ . '/lib/tokeninput.php';

elgg_register_event_handler('init', 'system', 'elgg_tokeninput_init');

/**
 * Initialize
 * @return void
 */
function elgg_tokeninput_init() {

	elgg_register_simplecache_view('tokeninput/lib.js');

	elgg_extend_view('elgg.css', 'tokeninput/stylesheet.css');
	elgg_extend_view('admin.css', 'tokeninput/stylesheet.css');

	elgg_register_plugin_hook_handler('action', 'all', 'elgg_tokeninput_explode_field_values', 1);

	elgg_register_page_handler('tokeninput', 'elgg_tokeninput_page_handler');

	elgg_extend_view('theme_sandbox/forms', 'theme_sandbox/forms/elgg_tokeninput');

	elgg_extend_view('input/tokeninput', 'tokeninput/require');
}

/**
 * Unserialize tokeninput field values before performing an action
 *
 * @note Default behavior of the JS plugin is to implode user input into a
 * comma-separated string. PHP plugin hook for 'action', 'all' will attempt
 * to explode these values and feed them back into an action for further
 * processing. This however, will only work with basic form input names,
 * e.g. ```name="field_name"``` If you are working with more complex forms,
 * where e.g. ```name="field_name[element_name]"```, you will need to add some
 * custom logic to your action.
 *
 * @return void
 */
function elgg_tokeninput_explode_field_values() {

	$elgg_tokeninput_fields = (array) get_input('elgg_tokeninput_fields', array());
	$elgg_tokneinput_autocomplete = (array) get_input('elgg_tokeninput_autocomplete', array());

	if (!empty($elgg_tokeninput_fields)) {
		foreach ($elgg_tokeninput_fields as $field_name) {
			$values = explode(',', get_input($field_name, ''));
			if (in_array($field_name, $elgg_tokneinput_autocomplete)) {
				foreach ($values as $key => $value) {
					$user = get_entity($value);
					if ($user instanceof ElggUser) {
						$values[$key] = $user->username;
					}
				}
				if (sizeof($values) === 1) {
					$values = array_values($values)[0];
				}
			}
			set_input($field_name, $values);
		}
	}

	set_input('elgg_tokeninput_fields', null);
	set_input('elgg_tokeninput_autocomplete', null);
}

/**
 * Page handler for serving autocomplete results
 *
 * @param array $page URL segments
 * @return void
 */
function elgg_tokeninput_page_handler($page) {

	$user = elgg_get_logged_in_user_entity();

	$callback = urldecode(get_input('callback'));
	if ($callback) {
		$hmac = get_input('hmac');
		$ts = get_input('ts');
		if (hash_hmac('sha256', $ts . $callback, elgg_tokeninput_get_secret()) !== $hmac) {
			header('HTTP/1.1 403 Forbidden');
			exit;
		}
	} else {
		$callback = 'elgg_tokeninput_search_all';
	}

	$q = urldecode(get_input('term', get_input('q', '')));
	$strict = (bool) get_input('strict', true);

	if (!is_callable($callback)) {
		header('HTTP/1.1 400 Bad Request');
		exit;
	}

	$results = array();

	$options = get_input('options', array());

	$entities = call_user_func($callback, $q, $options);

	if (is_array($entities) && count($entities)) {
		foreach ($entities as $entity) {
			if (elgg_instanceof($entity)) {
				$results[] = elgg_tokeninput_export_entity($entity);
			} else if ($entity instanceof ElggMetadata || is_string($entity)) {
				$results[] = elgg_tokeninput_export_metadata($entity);
			} else {
				$results[] = (array) $entity;
			}
		}
	}

	if (!count($results) && $strict === false) {
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
