<?php

/**
 * Export an entity into a form accepted by tokeninput
 *
 * @note Use 'tokeninput:entity:export', $type to filter the exported values
 * 
 * @param ElggEntity $entity Entity to export
 * @return array
 */
function elgg_tokeninput_export_entity($entity)
{
    if (!$entity instanceof \ElggEntity) {
        if ($entity_from_guid = get_entity($entity)) {
            $entity = $entity_from_guid;
        } else {
            return elgg_tokeninput_export_metadata($entity);
        }
    }
    $type = $entity->getType();
    $subtype = $entity->getSubtype();
    $icon = elgg_view_entity_icon($entity, 'small', array('use_hover' => false));
    if ($entity instanceof \ElggUser) {
        $title = "{$entity->name} ({$entity->username})";
    } else if ($entity instanceof \ElggGroup) {
        $title = $entity->name;
    } else {
        $title = $entity->getDisplayName();
        if (!$title) {
            $title = elgg_echo('untitled');
        }
        $metadata[] = elgg_echo('byline', array($entity->getOwnerEntity()->name));
    }
    if ($entity->description) {
        $metadata[] = elgg_get_excerpt(elgg_strip_tags($entity->description), 100);
    }
    if ($entity->location) {
        $metadata[] = $entity->location;
    }
    $export = array('label' => $title, 'value' => $entity->guid, 'metadata' => $metadata ? implode('<br />', $metadata) : '', 'icon' => $icon, 'type' => $type, 'subtype' => $subtype, 'html_result' => elgg_view_exists("tokeninput/{$type}/{$subtype}") ? elgg_view("tokeninput/{$type}/{$subtype}", array('entity' => $entity, 'for' => 'result')) : null, 'html_token' => elgg_view_exists("tokeninput/{$type}/{$subtype}") ? elgg_view("tokeninput/{$type}/{$subtype}", array('entity' => $entity, 'for' => 'token')) : null);
    $export = elgg_trigger_plugin_hook('tokeninput:entity:export', $type, array('entity' => $entity), $export);
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
 * @param ElggMetadata $metadata Metadata to export
 * @return array
 */
function elgg_tokeninput_export_metadata($metadata)
{
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
    $export = array('label' => $tag, 'value' => $tag, 'type' => $type, 'subtype' => $subtype, 'html_result' => elgg_view_exists("tokeninput/{$type}/{$subtype}") ? elgg_view("tokeninput/{$type}/{$subtype}", array('tag' => $tag, 'metadata_id' => $id, 'for' => 'result')) : null, 'html_token' => elgg_view_exists("tokeninput/{$type}/{$subtype}") ? elgg_view("tokeninput/{$type}/{$subtype}", array('tag' => $tag, 'metadata_id' => $id, 'for' => 'token')) : null);
    $export = elgg_trigger_plugin_hook('tokeninput:entity:export', $type, array('tag' => $tag, 'metadata_id' => $id), $export);
    array_walk_recursive($export, function (&$value) {
        $value = is_string($value) ? html_entity_decode($value, ENT_QUOTES, 'UTF-8') : $value;
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
function elgg_tokeninput_search_all($term, $options = array())
{
    $results = [];

    // Search users
    $user_options = $options;
    $user_options['query'] = $term;
    $user_results = elgg_trigger_plugin_hook('search', 'user', $user_options, []);
    $user_entities = elgg_extract('entities', $user_results, []);
    if (is_array($user_entities)) {
        $results = array_merge($results, $user_entities);
    }

    // Search groups
    $group_options = $options;
    $group_options['query'] = $term;
    $group_results = elgg_trigger_plugin_hook('search', 'group', $group_options, []);
    $group_entities = elgg_extract('entities', $group_results, []);
    if (is_array($group_entities)) {
        $results = array_merge($results, $group_entities);
    }

    // Search objects
    $object_options = $options;
    $object_options['query'] = $term;
    $object_options['types'] = 'object';
    $entity_types = elgg_get_config('registered_entities');
    $object_subtypes = elgg_extract('object', $entity_types, []);
    if ($object_subtypes) {
        $object_options['subtypes'] = $object_subtypes;
    }
    $object_results = elgg_trigger_plugin_hook('search', 'object', $object_options, []);
    $object_entities = elgg_extract('entities', $object_results, []);
    if (is_array($object_entities)) {
        $results = array_merge($results, $object_entities);
    }

    return $results;
}
/**
 * Callback function to search users
 *
 * @param string $term Query term
 * @param array $options An array of getter options
 * @return array An array of elgg entities matching the search criteria
 */
function elgg_tokeninput_search_users($term, $options = array())
{
    $options['query'] = $term;
    $results = elgg_trigger_plugin_hook('search', 'user', $options, array());
    return elgg_extract('entities', $results, array());
}
/**
 * Callback function to search groups
 *
 * @param string $term Query term
 * @param array $options An array of getter options
 * @return array An array of elgg entities matching the search criteria
 */
function elgg_tokeninput_search_groups($term, $options = array())
{
    $options['query'] = $term;
    $results = elgg_trigger_plugin_hook('search', 'group', $options, array());
    return elgg_extract('entities', $results, array());
}
/**
 * Callback function to search objects
 *
 * @param string $term Query term
 * @param array $options An array of getter options
 * @return array An array of elgg entities matching the search criteria
 */
function elgg_tokeninput_search_objects($term, $options = array())
{
    $options['query'] = $term;
    $options['types'] = 'object';
    if (!isset($options['subtype']) && !isset($options['subtypes'])) {
        $entity_types = elgg_get_config('registered_entities');
        $object_subtypes = elgg_extract('object', $entity_types, array());
        $options['subtypes'] = $object_subtypes;
    }
    $results = elgg_trigger_plugin_hook('search', 'object', $options, array());
    return elgg_extract('entities', $results, array());
}
/**
 * Callback function to search friends
 *
 * @param string $term Query term
 * @param array $options An array of getter options
 * @return array An array of elgg entities matching the search criteria
 */
function elgg_tokeninput_search_friends($term, $options = array())
{
    $options['query'] = $term;
    $options['guids'] = array(ELGG_ENTITIES_NO_VALUE);
    $friends = new ElggBatch('elgg_get_entities', array('relationship' => 'friend', 'relationship_guid' => elgg_get_logged_in_user_guid(), 'inverse_relationship' => false, 'limit' => 0, 'callback' => false), null, 100);
    foreach ($friends as $friend) {
        $options['guids'][] = $friend->guid;
    }
    $results = elgg_trigger_plugin_hook('search', 'user', $options, array());
    return elgg_extract('entities', $results, array());
}
/**
 * Callback function to search owned entities
 *
 * @param string $term Query term
 * @param array $options An array of getter options
 * @return array An array of elgg entities matching the search criteria
 */
function elgg_tokeninput_search_owned_entities($term, $options = array())
{
    $user = elgg_get_logged_in_user_entity();
    if (!$user) {
        return [];
    }

    $results = [];

    // Search owned groups
    $group_options = $options;
    $group_options['query'] = $term;
    $group_options['owner_guid'] = $user->guid;
    $group_results = elgg_trigger_plugin_hook('search', 'group', $group_options, []);
    $group_entities = elgg_extract('entities', $group_results, []);
    if (is_array($group_entities)) {
        $results = array_merge($results, $group_entities);
    }

    // Search owned objects
    $object_options = $options;
    $object_options['query'] = $term;
    $object_options['types'] = 'object';
    $object_options['owner_guid'] = $user->guid;
    $entity_types = elgg_get_config('registered_entities');
    $object_subtypes = elgg_extract('object', $entity_types, []);
    if ($object_subtypes) {
        $object_options['subtypes'] = $object_subtypes;
    }
    $object_results = elgg_trigger_plugin_hook('search', 'object', $object_options, []);
    $object_entities = elgg_extract('entities', $object_results, []);
    if (is_array($object_entities)) {
        $results = array_merge($results, $object_entities);
    }

    return $results;
}
/**
 * Callback function to search valid tags
 *
 * @param string $term Query term
 * @param array $options An array of getter options
 * @return array An array of metadata matching the search criteria
 */
function elgg_tokeninput_search_tags($term, $options = array())
{
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
    $options['search_name_value_pairs'] = [
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
function elgg_tokeninput_get_secret()
{
    $secret = elgg_get_plugin_setting('__secret', 'elgg_tokeninput');
    if (!$secret) {
        $secret = elgg_generate_password();
        elgg_set_plugin_setting('__secret', $secret, 'elgg_tokeninput');
    }
    return $secret;
}