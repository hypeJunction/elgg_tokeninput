<?php

/**
 * Export an entity into a form accepted by tokeninput
 *
 * @note Use 'tokeninput:entity:export', $type to filter the exported values
 *
 * @param ElggEntity $entity Entity to export
 * @return array
 */
function elgg_tokeninput_export_entity($entity) {
	if (!$entity instanceof \ElggEntity) {
		$entity_from_guid = is_numeric($entity) ? get_entity((int) $entity) : null;
		if ($entity_from_guid) {
			$entity = $entity_from_guid;
		} else {
			return elgg_tokeninput_export_metadata($entity);
		}
	}

	$type = $entity->getType();
	$subtype = $entity->getSubtype();
	$icon = elgg_view_entity_icon($entity, 'small', ['use_hover' => false]);
	$metadata = [];
	if ($entity instanceof \ElggUser) {
		$title = "{$entity->name} ({$entity->username})";
	} else if ($entity instanceof \ElggGroup) {
		$title = $entity->name;
	} else {
		$title = $entity->getDisplayName();
		if (!$title) {
			$title = elgg_echo('untitled');
		}

		$owner = $entity->getOwnerEntity();
		if ($owner) {
			$metadata[] = elgg_echo('byline', [$owner->getDisplayName()]);
		}
	}

	if ($entity->description) {
		$metadata[] = elgg_get_excerpt(elgg_strip_tags($entity->description), 100);
	}

	if ($entity->location) {
		$metadata[] = $entity->location;
	}

	$export = ['label' => $title, 'value' => $entity->guid, 'metadata' => !empty($metadata) ? implode('<br />', $metadata) : '', 'icon' => $icon, 'type' => $type, 'subtype' => $subtype, 'html_result' => elgg_view_exists("tokeninput/{$type}/{$subtype}") ? elgg_view("tokeninput/{$type}/{$subtype}", ['entity' => $entity, 'for' => 'result']) : null, 'html_token' => elgg_view_exists("tokeninput/{$type}/{$subtype}") ? elgg_view("tokeninput/{$type}/{$subtype}", ['entity' => $entity, 'for' => 'token']) : null];
	$export = elgg_trigger_event_results('tokeninput:entity:export', $type, ['entity' => $entity], $export);
	array_walk_recursive($export, function (&$value) {
		$value = is_string($value) ? html_entity_decode($value, ENT_QUOTES, 'UTF-8') : $value;
	});
	return $export;
}

/**
 * Export metadata into a form accepted by tokeninput
 *
 * @note Use 'tokeninput:entity:export', $metadata_name to filter output
 *
 * @param ElggMetadata|string $metadata Metadata to export
 * @return array
 */
function elgg_tokeninput_export_metadata($metadata) {
	if ($metadata instanceof \ElggMetadata) {
		$type = $metadata->name;
		$subtype = null;
		$tag = $metadata->value;
		$id = $metadata->id;
	} else if (is_string($metadata)) {
		$type = 'tag';
		$subtype = null;
		$tag = $metadata;
		$id = null;
	} else {
		return [];
	}

	$export = ['label' => $tag, 'value' => $tag, 'type' => $type, 'subtype' => $subtype, 'html_result' => elgg_view_exists("tokeninput/{$type}/{$subtype}") ? elgg_view("tokeninput/{$type}/{$subtype}", ['tag' => $tag, 'metadata_id' => $id, 'for' => 'result']) : null, 'html_token' => elgg_view_exists("tokeninput/{$type}/{$subtype}") ? elgg_view("tokeninput/{$type}/{$subtype}", ['tag' => $tag, 'metadata_id' => $id, 'for' => 'token']) : null];
	$export = elgg_trigger_event_results('tokeninput:entity:export', $type, ['tag' => $tag, 'metadata_id' => $id], $export);
	array_walk_recursive($export, function (&$value) {
		$value = is_string($value) ? html_entity_decode($value, ENT_QUOTES, 'UTF-8') : $value;
	});
	return $export;
}

/**
 * Callback function to search for all entity types
 *
 * @param string $term    Query term
 * @param array  $options An array of getter options
 * @return array An array of elgg entities matching the search criteria
 */
function elgg_tokeninput_search_all($term, $options = []) {
	$results = [];

	// Elgg 3.0 rewrote search to be query-based: the legacy search hook/event no
	// longer returns an entities array, so the 2.x pattern returned nothing
	// (autocomplete silently empty). elgg_search() runs the query and returns
	// the matching entities.

	// Search users
	$user_options = $options;
	$user_options['query'] = $term;
	$user_options['type'] = 'user';
	$user_entities = elgg_search($user_options);
	if (is_array($user_entities)) {
		$results = array_merge($results, $user_entities);
	}

	// Search groups
	$group_options = $options;
	$group_options['query'] = $term;
	$group_options['type'] = 'group';
	$group_entities = elgg_search($group_options);
	if (is_array($group_entities)) {
		$results = array_merge($results, $group_entities);
	}

	// Search objects
	$object_options = $options;
	$object_options['query'] = $term;
	$object_options['type'] = 'object';
	$entity_types = elgg_entity_types_with_capability('searchable');
	$object_subtypes = elgg_extract('object', $entity_types, []);
	if ($object_subtypes) {
		$object_options['subtypes'] = $object_subtypes;
	}

	$object_entities = elgg_search($object_options);
	if (is_array($object_entities)) {
		$results = array_merge($results, $object_entities);
	}

	return $results;
}

/**
 * Callback function to search users
 *
 * @param string $term    Query term
 * @param array  $options An array of getter options
 * @return array An array of elgg entities matching the search criteria
 */
function elgg_tokeninput_search_users($term, $options = []) {
	$options['query'] = $term;
	$options['type'] = 'user';
	return elgg_search($options) ?: [];
}

/**
 * Callback function to search groups
 *
 * @param string $term    Query term
 * @param array  $options An array of getter options
 * @return array An array of elgg entities matching the search criteria
 */
function elgg_tokeninput_search_groups($term, $options = []) {
	$options['query'] = $term;
	$options['type'] = 'group';
	return elgg_search($options) ?: [];
}

/**
 * Callback function to search objects
 *
 * @param string $term    Query term
 * @param array  $options An array of getter options
 * @return array An array of elgg entities matching the search criteria
 */
function elgg_tokeninput_search_objects($term, $options = []) {
	$options['query'] = $term;
	$options['types'] = 'object';
	if (!isset($options['subtype']) && !isset($options['subtypes'])) {
		$entity_types = elgg_entity_types_with_capability('searchable');
		$object_subtypes = elgg_extract('object', $entity_types, []);
		$options['subtypes'] = $object_subtypes;
	}

	return elgg_search($options) ?: [];
}

/**
 * Callback function to search friends
 *
 * @param string $term    Query term
 * @param array  $options An array of getter options
 * @return array An array of elgg entities matching the search criteria
 */
function elgg_tokeninput_search_friends($term, $options = []) {
	$user_guid = elgg_get_logged_in_user_guid();
	if (!$user_guid) {
		return [];
	}

	$friend_guids = [];
	$friends = elgg_get_entities([
		'relationship' => 'friend',
		'relationship_guid' => $user_guid,
		'inverse_relationship' => false,
		'type' => 'user',
		'limit' => 0,
		'callback' => false,
	]);
	foreach ($friends as $friend) {
		$friend_guids[] = $friend->guid;
	}

	if (empty($friend_guids)) {
		return [];
	}

	$options['query'] = $term;
	$options['guids'] = $friend_guids;
	$options['type'] = 'user';
	return elgg_search($options) ?: [];
}

/**
 * Callback function to search owned entities
 *
 * @param string $term    Query term
 * @param array  $options An array of getter options
 * @return array An array of elgg entities matching the search criteria
 */
function elgg_tokeninput_search_owned_entities($term, $options = []) {
	$user = elgg_get_logged_in_user_entity();
	if (!$user) {
		return [];
	}

	$results = [];

	// Search owned groups
	$group_options = $options;
	$group_options['query'] = $term;
	$group_options['owner_guid'] = $user->guid;
	$group_options['type'] = 'group';
	$group_entities = elgg_search($group_options) ?: [];
	if (is_array($group_entities)) {
		$results = array_merge($results, $group_entities);
	}

	// Search owned objects
	$object_options = $options;
	$object_options['query'] = $term;
	$object_options['types'] = 'object';
	$object_options['owner_guid'] = $user->guid;
	$entity_types = elgg_entity_types_with_capability('searchable');
	$object_subtypes = elgg_extract('object', $entity_types, []);
	if ($object_subtypes) {
		$object_options['subtypes'] = $object_subtypes;
	}

	$object_options['type'] = 'object';
	$object_entities = elgg_search($object_options) ?: [];
	if (is_array($object_entities)) {
		$results = array_merge($results, $object_entities);
	}

	return $results;
}

/**
 * Callback function to search valid tags
 *
 * @param string $term    Query term
 * @param array  $options An array of getter options
 * @return array An array of metadata matching the search criteria
 */
function elgg_tokeninput_search_tags($term, $options = []) {
	$valid_tag_names = elgg_get_config('registered_tag_metadata_names') ?? ['tags'];
	$tag_names = urldecode(get_input('tag_names', ''));
	if ($tag_names) {
		if (is_array($tag_names)) {
			$search_tag_names = $tag_names;
		} else {
			$search_tag_names = explode(',', $tag_names);
		}

		foreach ($search_tag_names as $i => $tag_name) {
			if (!in_array($tag_name, $valid_tag_names)) {
				unset($search_tag_names[$i]);
			}
		}
	} else {
		$search_tag_names = $valid_tag_names;
	}

	if (!$search_tag_names) {
		return false;
	}

	$options['metadata_names'] = $search_tag_names;
	$options['metadata_name_value_pairs'] = [
		[
			'name' => $search_tag_names,
			'value' => "%{$term}%",
			'operand' => 'LIKE',
			'case_sensitive' => false,
		],
	];
	return elgg_get_metadata($options);
}

/**
 * Returns a secret key to sign ajax requests
 * @return string
 */
function elgg_tokeninput_get_secret() {
	$plugin = elgg_get_plugin_from_id('elgg_tokeninput');
	$secret = $plugin ? $plugin->getSetting('__secret') : null;
	if (!$secret) {
		$secret = elgg_generate_password();
		if ($plugin) {
			$plugin->setSetting('__secret', $secret);
		}
	}

	return $secret;
}

/**
 * Page handler for tokeninput AJAX search endpoint
 *
 * @param array $page Page segments
 * @return void
 */
function elgg_tokeninput_page_handler($page) {
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

	$results = [];
	$options = get_input('options', []);
	$entities = call_user_func($callback, $q, $options);
	if (is_array($entities) && count($entities)) {
		foreach ($entities as $entity) {
			if ($entity instanceof \ElggEntity) {
				$results[] = elgg_tokeninput_export_entity($entity);
			} else if ($entity instanceof \ElggMetadata) {
				$results[] = elgg_tokeninput_export_metadata($entity);
			} else {
				$results[] = (array) $entity;
			}
		}
	}

	if (!count($results) && $strict === false) {
		$suggest = ['label' => $q, 'value' => $q, 'html_result' => '<span>' . elgg_echo('tokeninput:suggest', [$q]) . '</span>'];
		$results[] = $suggest;
	}

	header('Content-Type: application/json');
	echo json_encode($results);
	exit;
}

/**
 * Explodes comma-separated tokeninput field values into arrays before action execution
 *
 * @param \Elgg\Event $event Action event
 * @return void
 */
function elgg_tokeninput_explode_field_values(\Elgg\Event $event) {
	$elgg_tokeninput_fields = (array) get_input('elgg_tokeninput_fields', []);
	$elgg_tokeninput_autocomplete = (array) get_input('elgg_tokeninput_autocomplete', []);
	if (!empty($elgg_tokeninput_fields)) {
		foreach ($elgg_tokeninput_fields as $field_name) {
			$values = explode(',', get_input($field_name, ''));
			if (in_array($field_name, $elgg_tokeninput_autocomplete)) {
				foreach ($values as $key => $value) {
					$user = is_numeric($value) ? get_entity((int) $value) : null;
					if ($user instanceof \ElggUser) {
						$values[$key] = $user->username;
					}
				}

				if (count($values) === 1) {
					$values = array_values($values)[0];
				}
			}

			set_input($field_name, $values);
		}
	}

	set_input('elgg_tokeninput_fields', null);
	set_input('elgg_tokeninput_autocomplete', null);
}
