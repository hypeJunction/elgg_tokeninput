<?php

/**
 * Get exportable entity values
 *
 * @param ElggEntity $entity
 * @return array
 */
function elgg_tokeninput_export_entity($entity) {

	if (!elgg_instanceof($entity)) {
		if ($entity_from_guid = get_entity($entity)) {
			$entity = $entity_from_guid;
		} else {
			return elgg_tokeninput_export_metadata($entity);
		}
	}

	$type = $entity->getType();
	$subtype = $entity->getSubtype();

	$icon = elgg_view_entity_icon($entity, 'small', array(
		'use_hover' => false,
	));

	if (elgg_instanceof($entity, 'user')) {
		$title = "$entity->name ($entity->username)";
	} else if (elgg_instanceof($entity, 'group')) {
		$title = $entity->name;
	} else {
		$title = $entity->title;
		$metadata[] = elgg_echo('byline', array($entity->getOwnerEntity()->name));
	}

	if ($entity->description) {
		$metadata[] = elgg_get_excerpt(elgg_strip_tags($entity->description), 100);
	}

	if ($entity->location) {
		$metadata[] = $entity->location;
	}

	$export = array(
		'label' => $title,
		'value' => $entity->guid,
		'metadata' => ($metadata) ? implode('<br />', $metadata) : '',
		'icon' => $icon,
		'type' => $type,
		'subtype' => $subtype,
		'html_result' => (elgg_view_exists("tokeninput/$type/$subtype")) ? elgg_view("tokeninput/$type/$subtype", array('entity' => $entity, 'for' => 'result')) : null,
		'html_token' => (elgg_view_exists("tokeninput/$type/$subtype")) ? elgg_view("tokeninput/$type/$subtype", array('entity' => $entity, 'for' => 'token')) : null,
	);

	$export = elgg_trigger_plugin_hook('tokeninput:entity:export', $type, array('entity' => $entity), $export);
	array_walk_recursive($export, function (&$value) {
		$value = (is_string($value)) ? html_entity_decode($value, ENT_QUOTES, 'UTF-8') : $value;
	});

	return $export;
}

/**
 * Get exportable metadata values
 *
 * @param ElggMetadata $metadata
 * @return array
 */
function elgg_tokeninput_export_metadata($metadata) {

	if ($metadata instanceof ElggMetadata) {
		$type = $metadata->getType();
		$subtype = $metadata->getSubtype();
		$tag = $metadata->value;
		$id = $metadata->id;
	} else if (is_string($metadata)) {
		$type = 'tag';
		$subtype = null;
		$tag = $metadata;
		$id = null;
	} else {
		return array();
	}

	$export = array(
		'label' => $tag,
		'value' => $tag,
		'type' => $type,
		'subtype' => $subtype,
		'html_result' => (elgg_view_exists("tokeninput/$type/$subtype")) ? elgg_view("tokeninput/$type/$subtype", array('tag' => $tag, 'metadata_id' => $id, 'for' => 'result')) : null,
		'html_token' => (elgg_view_exists("tokeninput/$type/$subtype")) ? elgg_view("tokeninput/$type/$subtype", array('tag' => $tag, 'metadata_id' => $id, 'for' => 'token')) : null,
	);

	$export = elgg_trigger_plugin_hook('tokeninput:entity:export', $type, array('tag' => $tag, 'metadata_id' => $id), $export);

	array_walk_recursive($export, function (&$value) {
		$value = (is_string($value)) ? html_entity_decode($value, ENT_QUOTES, 'UTF-8') : $value;
	});

	return $export;
}

/**
 * Callback function to search for all entity types
 *
 * @param string $term Query term
 * @param array $options An array of getter options
 * @return array An array of elgg entities matching the search criteria
 */
function elgg_tokeninput_search_all($term, $options = array()) {

	$term = sanitize_string($term);

	// replace mysql vars with escaped strings
	$q = str_replace(array('_', '%'), array('\_', '\%'), $term);

	$entities = elgg_get_config('registered_entities');
	$subtypes = array(0);
	foreach ($entities['object'] as $subtype) {
		$subtype_id = get_subtype_id('object', $subtype);
		if ($subtype_id)
			$subtypes[] = $subtype_id;
	}

	$subtypes_in = implode(',', $subtypes);

	$dbprefix = elgg_get_config('dbprefix');

	$options['joins'][] = "LEFT JOIN {$dbprefix}users_entity ue ON ue.guid = e.guid AND e.type = 'user'";
	$options['joins'][] = "LEFT JOIN {$dbprefix}groups_entity ge ON ge.guid = e.guid AND e.type = 'group'";
	$options['joins'][] = "LEFT JOIN {$dbprefix}objects_entity oe ON oe.guid = e.guid AND e.type = 'object'";

	$options['wheres'][] = "(e.type = 'user' AND ue.banned = 'no' AND (ue.name LIKE '%$q%' OR ue.username LIKE '%$q%'))
			OR (e.type = 'group' AND ge.name LIKE '%$q%')
			OR (e.type = 'object' AND e.subtype IN ($subtypes_in) AND oe.title LIKE '%$q%')";

	return elgg_get_entities($options);
}

/**
 * Callback function to search users
 *
 * @param string $term Query term
 * @param array $options An array of getter options
 * @return array An array of elgg entities matching the search criteria
 */
function elgg_tokeninput_search_users($term, $options = array()) {

	$term = sanitize_string($term);

	// replace mysql vars with escaped strings
	$q = str_replace(array('_', '%'), array('\_', '\%'), $term);

	$list = new hypeJunction\Lists\ElggList($options);
	$list->setSearchQuery(array('user' => $q));
	return $list->getItems();
}

/**
 * Callback function to search groups
 *
 * @param string $term Query term
 * @param array $options An array of getter options
 * @return array An array of elgg entities matching the search criteria
 */
function elgg_tokeninput_search_groups($term, $options = array()) {

	$term = sanitize_string($term);

	// replace mysql vars with escaped strings
	$q = str_replace(array('_', '%'), array('\_', '\%'), $term);

	$list = new hypeJunction\Lists\ElggList($options);
	$list->setSearchQuery(array('group' => $q));
	return $list->getItems();
}

/**
 * Callback function to search friends
 *
 * @param string $term Query term
 * @param array $options An array of getter options
 * @return array An array of elgg entities matching the search criteria
 */
function elgg_tokeninput_search_friends($term, $options = array()) {

	$term = sanitize_string($term);

	// replace mysql vars with escaped strings
	$q = str_replace(array('_', '%'), array('\_', '\%'), $term);

	$options['relationship'] = 'friend';
	$options['relationship_guid'] = elgg_get_logged_in_user_guid();
	$options['inverse_relationship'] = false;

	$list = new hypeJunction\Lists\ElggList($options, 'elgg_get_entities_from_relationship');
	$list->setSearchQuery(array('user' => $q));
	return $list->getItems();
}

/**
 * Callback function to search owned entities
 *
 * @param string $term Query term
 * @param array $options An array of getter options
 * @return array An array of elgg entities matching the search criteria
 */
function elgg_tokeninput_search_owned_entities($term, $options = array()) {

	$user = elgg_get_logged_in_user_entity();

	$term = sanitize_string($term);

	// replace mysql vars with escaped strings
	$q = str_replace(array('_', '%'), array('\_', '\%'), $term);

	$entities = elgg_get_config('registered_entities');
	$subtypes = array(0);
	foreach ($entities['object'] as $subtype) {
		$subtype_id = get_subtype_id('object', $subtype);
		if ($subtype_id) {
			$subtypes[] = $subtype_id;
		}
	}

	$subtypes_in = implode(',', $subtypes);

	$dbprefix = elgg_get_config('dbprefix');

	$options['types'] = array('object', 'group');

	$options['joins'][] = "LEFT JOIN {$dbprefix}groups_entity ge ON ge.guid = e.guid AND e.type = 'group'";
	$options['joins'][] = "LEFT JOIN {$dbprefix}objects_entity oe ON oe.guid = e.guid AND e.type = 'object'";

	$options['wheres'][] = "(e.type = 'group' AND ge.name LIKE '%$q%')
			OR (e.type = 'object' AND e.subtype IN ($subtypes_in) AND oe.title LIKE '%$q%')";

	$options['wheres'][] = "e.owner_guid = $user->guid";

	return elgg_get_entities($options);
}

/**
 * Callback function to search valid tags
 *
 * @param string $term Query term
 * @param array $options An array of getter options
 * @return array An array of metadata matching the search criteria
 */
function elgg_tokeninput_search_tags($term, $options = array()) {

	$term = sanitize_string($term);

	// replace mysql vars with escaped strings
	$q = str_replace(array('_', '%'), array('\_', '\%'), $term);

	$valid_tag_names = elgg_get_registered_tag_metadata_names();
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
	$options['group_by'] = "v.string";
	$options['wheres'] = array("v.string LIKE '%$q%'");

	return elgg_get_metadata($options);
}
