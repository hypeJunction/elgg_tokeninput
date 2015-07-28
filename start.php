<?php

require_once dirname(dirname(dirname(__FILE__))) . '/vendor/autoload.php';

define('ELGG_TOKENINPUT_PAGEHANDLER', 'tokeninput');

elgg_register_event_handler('init', 'system', 'elgg_tokeninput_init');

/**
 * Initialize the plugin
 */
function elgg_tokeninput_init() {

	elgg_register_library('elgg.tokeninput', elgg_get_plugins_path() . 'elgg_tokeninput/lib/tokeninput.php');

	elgg_define_js('jquery.tokeninput', array(
		'src' => 'mod/elgg_tokeninput/vendors/jquery-tokeninput/build/jquery.tokeninput.min.js',
		'deps' => array('jquery'),
	));

	elgg_require_js('tokeninput/init');

	elgg_extend_view('css/elgg', 'css/tokeninput/stylesheet.css');
	elgg_extend_view('css/admin', 'css/tokeninput/stylesheet.css');

	elgg_register_plugin_hook_handler('action', 'all', 'elgg_tokeninput_explode_field_values', 1);

	elgg_register_page_handler(ELGG_TOKENINPUT_PAGEHANDLER, 'elgg_tokeninput_page_handler');

	elgg_extend_view('theme_sandbox/forms', 'theme_sandbox/forms/elgg_tokeninput');
}

/**
 * Unserialize tokeninput field values before performing an action
 */
function elgg_tokeninput_explode_field_values($hook, $type, $return, $params) {

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
			} else if ($entity instanceof ElggMetadata) {
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
