<?php

/**
 * Get exportable entity values
 *
 * @param mixed $entity GUID or ElggEntity
 * @return array
 */
function elgg_tokeninput_export_entity($entity) {

	$entity = (elgg_instanceof($entity)) ? $entity : get_entity($entity);

	if (!elgg_instanceof($entity))
		return array();

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

	$return = array(
		'label' => $title,
		'value' => $entity->guid,
		'metadata' => ($metadata) ? implode('<br />', $metadata) : '',
		'icon' => $icon
	);

	return elgg_trigger_plugin_hook('tokeninput:entity:export', $entity->getType(), array('entity' => $entity), $return);
}

/**
 * Callback function to search for all entity types
 *
 * @param string $query Query term
 * @param array $options An array of getter options
 * @return array An array of elgg entities matching the search criteria
 */
function elgg_tokeninput_search_all($query, $options = array()) {

	$query = sanitize_string($query);

	// replace mysql vars with escaped strings
	$q = str_replace(array('_', '%'), array('\_', '\%'), $query);

	$entities = elgg_get_config('registered_entities');
	$subtypes = array(0);
	foreach ($entities['object'] as $subtype) {
		$subtype_id = get_subtype_id('object', $subtype);
		if ($subtype_id) $subtypes[] = $subtype_id;
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
 * @param string $query Query term
 * @param array $options An array of getter options
 * @return array An array of elgg entities matching the search criteria
 */
function elgg_tokeninput_search_users($query, $options = array()) {

	$query = sanitize_string($query);

	// replace mysql vars with escaped strings
	$q = str_replace(array('_', '%'), array('\_', '\%'), $query);

	$dbprefix = elgg_get_config('dbprefix');

	$options['types'] = array('user');
	$options['joins'][] = "JOIN {$dbprefix}users_entity ue ON ue.guid = e.guid";
	$options['wheres'][] = "ue.banned = 'no' AND (ue.name LIKE '%$q%' OR ue.username LIKE '%$q%')";

	return elgg_get_entities($options);
	
}

/**
 * Callback function to search groups
 *
 * @param string $query Query term
 * @param array $options An array of getter options
 * @return array An array of elgg entities matching the search criteria
 */
function elgg_tokeninput_search_groups($query, $options = array()) {

	$query = sanitize_string($query);

	// replace mysql vars with escaped strings
	$q = str_replace(array('_', '%'), array('\_', '\%'), $query);

	$dbprefix = elgg_get_config('dbprefix');

	$options['types'] = array('group');
	$options['joins'][] = "JOIN {$dbprefix}groups_entity ge ON ge.guid = e.guid";
	$options['wheres'][] = "ge.name LIKE '%$q%'";

	return elgg_get_entities($options);

}

/**
 * Callback function to search friends
 *
 * @param string $query Query term
 * @param array $options An array of getter options
 * @return array An array of elgg entities matching the search criteria
 */
function elgg_tokeninput_search_friends($query, $options = array()) {

	$query = sanitize_string($query);

	// replace mysql vars with escaped strings
	$q = str_replace(array('_', '%'), array('\_', '\%'), $query);

	$dbprefix = elgg_get_config('dbprefix');

	$options['types'] = array('user');
	$options['relationship'] = 'friend';
	$options['relationship_guid'] = elgg_get_logged_in_user_guid();
	$options['inverse_relationship'] = false;
	$options['joins'][] = "JOIN {$dbprefix}users_entity ue ON ue.guid = e.guid";
	$options['wheres'][] = "ue.banned = 'no' AND (ue.name LIKE '%$q%' OR ue.username LIKE '%$q%')";

	return elgg_get_entities_from_relationship($options);

}

/**
 * Callback function to search owned entities
 *
 * @param string $query Query term
 * @param array $options An array of getter options
 * @return array An array of elgg entities matching the search criteria
 */
function elgg_tokeninput_search_owned_entities($query, $options = array()) {

	$user = elgg_get_logged_in_user_entity();

	$query = sanitize_string($query);

	// replace mysql vars with escaped strings
	$q = str_replace(array('_', '%'), array('\_', '\%'), $query);

	$entities = elgg_get_config('registered_entities');
	$subtypes = array(0);
	foreach ($entities['object'] as $subtype) {
		$subtype_id = get_subtype_id('object', $subtype);
		if ($subtype_id) $subtypes[] = $subtype_id;
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