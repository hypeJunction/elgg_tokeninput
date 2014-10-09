<?php

namespace hypeJunction\Lists;

use Exception;
use stdClass;

/**
 * Adds additional search query options to list options
 */
class ElggListQuery {

	/**
	 * Database table previs
	 * @var string
	 */
	private $dbprefix;

	/**
	 * Search type
	 * @var string
	 */
	protected $search_type;

	/**
	 * Keyword to find in entity attributes
	 * @var string
	 */
	protected $query;

	/**
	 * Min search query length for fulltext search
	 * @var integer
	 */
	protected $ft_min_chars;

	/**
	 * Max search query length for fulltext search
	 * @var integer
	 */
	protected $ft_max_chars;

	/**
	 * An array of getter options
	 * @var array
	 */
	protected $options = array();

	/**
	 * Valid search types
	 * @var array
	 */
	public $search_types = array(
		'user',
		'object',
		'group',
		'site',
		'tags'
	);

	/**
	 * Map of table prefixes
	 * @var array
	 */
	protected $table_map = array(
		'entities' => 'e',
		'users_entity' => 'ue',
		'groups_entity' => 'ge',
		'objects_entity' => 'oe',
		'sites_entity' => 'oe',
	);

	/**
	 * Prefixes object
	 * @var stdClass
	 */
	protected $prefixes;

	/**
	 * Cache metadata name and value IDs
	 * @var array
	 */
	static $metamap;

	/**
	 * Constructs a new query object
	 * @param string $search_type	Valid search type
	 * @param mixed $query			Query options
	 * @param array $table_map		An alternative table mapping
	 */
	function __construct($search_type, $query = '', $table_map = null) {

		$this->search_type = $search_type;
		$this->query = $query;
		$this->dbprefix = elgg_get_config('dbprefix');
		if (is_array($table_map)) {
			if (count(array_diff_key($table_map, $this->table_map))) {
				throw new Exception(get_class() . ' requires a table prefix map to match the preset keys');
			}
		} else {
			$table_map = $this->table_map;
		}

		$this->prefixes = (object) $table_map;

		$search_info = elgg_get_config('search_info');
		$this->ft_min_chars = elgg_extract('min_chars', $search_info, 4);
		$this->ft_max_chars = elgg_extract('max_chars', $search_info, 90);
	}

	/**
	 * Add/replace element in an options array
	 *
	 * @param string $key	Array key, e.g. 'wheres' or 'order_by'
	 * @param mixed $value	New value
	 * @param string $name	Unique name of the clause for array keys that take multiple options
	 * @return ElggListQuery
	 */
	public function sqlAddOption($key = '', $value = '', $name = '') {

		$multi = array(
			'types' => '',
			'subtypes' => '',
			'type_subtype_pairs' => '',
			'guids' => '',
			'owner_guids' => '',
			'container_guids' => '',
			'site_guids' => '',
			'selects' => '',
			'wheres' => '',
			'joins' => '',
			'metadata_names' => '',
			'metadata_values' => '',
			'metadata_name_value_pairs' => '',
			'metadata_owner_guids' => '',
			'annotation_names' => '',
			'annotation_values' => '',
			'annotation_name_value_pairs' => '',
			'annotation_owner_guids' => '',
		);

		if (array_key_exists($key, $multi)) {
			if (!isset($this->options[$key])) {
				$this->options[$key] = array();
			}
			if ($name) {
				$this->options[$key][$name] = $value;
			} else {
				$this->options[$key][$name] = $value;
			}
		} else {
			$this->options[$key] = $value;
		}

		return $this;
	}

	/**
	 * Get an array of options suitable for ege* functions
	 * @param array $options	Current options array
	 * @return array			Filtered options array
	 */
	public function sqlGetOptions($options = array()) {

		$this->options = $options;

		$query = (is_array($query)) ? elgg_extract('string', $query, '') : sanitize_string((string) $this->query);

		if (empty($query)) {
			return $this->options;
		}

		switch ($this->search_type) {
			case 'user' :
				$this->sqlAddUserSearchQuery($query);
				break;

			case 'group' :
				$this->sqlAddGroupSearchQuery($query);
				break;

			case 'object' :
				$this->sqlAddObjectSearchQuery($query);
				break;

			case 'site' :
				$this->sqlAddSiteSearchQuery();
				break;

			case 'tags' :
				$this->sqlAddTagsSearchQuery($query);
				break;
		}

		return $this->options;
	}

	/**
	 * Add user search clauses
	 * @return ElggListQuery
	 */
	private function sqlAddUserSearchQuery($query = '') {

		$this->sqlJoinUsersTable();

		$where[] = $this->sqlGetWhereFulltextClause($this->prefixes->users_entity, array('username', 'name'), $query, false);

		$profile_fields = array_keys(elgg_get_config('profile_fields'));
		if (is_array($profile_fields) && count($profile_fields)) {

			$map = self::getMetaMap($profile_fields);

			$this->sqlJoinMetadataTable('md')
					->sqlJoinMetadataValueTable('msv', 'md');

			$where_md[] = $this->sqlGetWhereInClause('md', array('name_id'), $map);
			$where_md[] = $this->sqlGetWhereLikeClause('msv', array('string'), $query);
			$where_md[] = get_access_sql_suffix('md');

			$where[] = '(' . implode(' AND ', $where_md) . ')';
		}

		$where = '(' . implode(' OR ', $where) . ')';
		$this->sqlAddOption('wheres', $where, 'user_query');
		return $this;
	}

	/**
	 * Add group search clauses
	 * @return ElggListQuery
	 */
	private function sqlAddGroupSearchQuery($query = '') {

		$this->sqlJoinGroupsTable();

		$where[] = $this->sqlGetWhereFulltextClause($this->prefixes->groups_entity, array('name', 'description'), $query, false);

		$profile_fields = array_keys(elgg_get_config('group'));
		if (is_array($profile_fields) && count($profile_fields)) {

			$map = self::getMetaMap($profile_fields);

			$this->sqlJoinMetadataTable('md')
					->sqlJoinMetadataValueTable('msv', 'md');

			$where_md[] = $this->sqlGetWhereInClause('md', array('name_id'), $map);
			$where_md[] = $this->sqlGetWhereLikeClause('msv', array('string'), $query);
			$where_md[] = get_access_sql_suffix('md');

			$where[] = '(' . implode(' AND ', $where_md) . ')';
		}

		$where = '(' . implode(' OR ', $where) . ')';
		$this->sqlAddOption('wheres', $where, 'group_query');
		return $this;
	}

	/**
	 * Add object search clauses
	 * @return ElggListQuery
	 */
	private function sqlAddObjectSearchQuery($query = '') {

		$this->sqlJoinObjectsTable();
		$where = $this->sqlGetWhereFulltextClause($this->prefixes->objects_entity, array('title', 'description'), $query, false);
		$this->sqlAddOption('wheres', $where, 'object_query');
		return $this;
	}

	/**
	 * Add site search clauses
	 * @return ElggListQuery
	 */
	private function sqlAddSiteSearchQuery($query = '') {

		$this->sqlJoinSitesTable();
		$where = $this->sqlGetWhereFulltextClause($this->prefixes->sites_entity, array('name', 'description'), $query, false);
		$this->sqlAddOption('wheres', $where, 'site_query');
		return $this;
	}

	/**
	 * Add tags search clauses
	 * @return ElggListQuery
	 */
	private function sqlAddTagsSearchQuery($query = '') {

		$this->sqlJoinMetadataTable('md')
				->sqlJoinMetadataValueTable('msv', 'md');

		$tag_names = elgg_get_registered_tag_metadata_names();
		if ($tag_names) {
			$map = self::getMetaMap($tag_names);
			$where_md[] = $this->sqlGetWhereInClause('md', array('name_id'), $map);
			$where_md[] = $this->sqlGetWhereLikeClause('msv', array('string'), $query);
			$where_md[] = get_access_sql_suffix('md');
			$where = '(' . implode(' AND ', $where_md) . ')';
		}

		if ($where) {
			$this->sqlAddOption('wheres', $where, 'tags_query');
		}

		return $this;
	}

	/**
	 * Metastring ID mapping
	 * @param array $metastrings
	 * @return array
	 */
	public static function getMetaMap($metastrings = array()) {
		$map = array();
		foreach ($metastrings as $metastring) {
			if (isset(self::$matamap) && in_array(self::$metamap[$metastring])) {
				$map[$metastring] = self::$metamap[$metastring];
			} else {
				$id = add_metastring($metastring);
				self::$metamap[$metastring] = $map[$metastring] = $id;
			}
		}
		return $map;
	}

	/**
	 * Join users table
	 */
	private function sqlJoinUsersTable() {
		$e = $this->prefixes->entities;
		$ue = $this->prefixes->users_entity;
		if (!isset($this->options['joins'][$ue])) {
			$this->options['joins'][$ue] = "JOIN {$this->dbprefix}users_entity $ue ON $e.guid = $ue.guid";
		}

		return $this;
	}

	/**
	 * Join groups table
	 */
	private function sqlJoinGroupsTable() {
		$e = $this->prefixes->entities;
		$ge = $this->prefixes->groups_entity;
		if (!isset($this->options['joins'][$ge])) {
			$this->options['joins'][$ge] = "JOIN {$this->dbprefix}groups_entity $ge ON $e.guid = $ge.guid";
		}

		return $this;
	}

	/**
	 * Join groups table
	 */
	private function sqlJoinObjectsTable() {
		$e = $this->prefixes->entities;
		$oe = $this->prefixes->objects_entity;
		if (!isset($this->options['joins'][$oe])) {
			$this->options['joins'][$oe] = "JOIN {$this->dbprefix}objects_entity $oe ON $e.guid = $oe.guid";
		}

		return $this;
	}

	/**
	 * Join sites table
	 */
	private function sqlJoinSitesTable() {
		$e = $this->prefixes->entities;
		$se = $this->prefixes->sites_entity;
		if (!isset($this->options['joins'][$se])) {
			$this->options['joins'][$se] = "JOIN {$this->dbprefix}sites_entity $se ON $e.guid = $se.guid";
		}
		return $this;
		return $this;
	}

	/**
	 * Join metadata table
	 * @param string $md	prefix_metadata table prefix
	 * @param string $e		prefix_entities table prefix
	 */
	private function sqlJoinMetadataTable($md = 'md', $e = null) {
		if (!$e) {
			$e = $this->prefixes->entities;
		}
		if (!isset($this->options['joins'][$md])) {
			$this->options['joins'][$md] = "JOIN {$this->dbprefix}metadata $md ON $e.guid = $md.entity_guid";
		}

		return $this;
	}

	/**
	 * Join metastrings table on metadata name
	 * @param string $msn		prefix_metastrings table prefix
	 * @param string $md		prefix_metadata table prefix
	 */
	private function sqlJoinMetadataNameTable($msn = 'msn', $md = 'md') {

		if (!isset($this->options['joins'][$msn])) {
			$this->options['joins'][$msn] = "JOIN {$this->dbprefix}metastrings $msn ON $msn.id = $md.name_id";
		}

		return $this;
	}

	/**
	 * Join metastrings table on metadata value
	 * @param string $msv		prefix_metastrings table prefix
	 * @param string $md		prefix_metadata table prefix
	 */
	private function sqlJoinMetadataValueTable($msv = 'msv', $md = 'md') {

		if (!isset($this->options['joins'][$msv])) {
			$this->options['joins'][$msv] = "JOIN {$this->dbprefix}metastrings $msv ON $msv.id = $md.value_id";
		}

		return $this;
	}

	/**
	 * Returns a where clause for a search query.
	 *
	 * @param string $table		Prefix for table to search on
	 * @param array $fields		Fields to match against
	 * @return string
	 */
	function sqlGetWhereFulltextClause($table, $fields, $query, $use_fulltext = TRUE) {

		$query = str_replace(array('_', '%'), array('\_', '\%'), sanitize_string($query));

		// add the table prefix to the fields
		foreach ($fields as $i => $field) {
			if ($table) {
				$fields[$i] = "$table.$field";
			}
		}

		$where = '';

		// if query is shorter than the min for fts words
		// it's likely a single acronym or similar
		// switch to literal mode
		if (elgg_strlen($this->query) < $this->ft_min_chars) {
			$likes = array();
			foreach ($fields as $field) {
				$likes[] = "$field LIKE '%$query%'";
			}
			$likes_str = implode(' OR ', $likes);
			$where = "($likes_str)";
		} else {
			// if we're not using full text, rewrite the query for bool mode.
			// exploiting a feature(ish) of bool mode where +-word is the same as -word
			if (!$use_fulltext) {
				$query = '+' . str_replace(' ', ' +', $query);
			}

			// if using advanced, boolean operators, or paired "s, switch into boolean mode
			$booleans_used = preg_match("/([\-\+~])([\w]+)/i", $query);
			$quotes_used = (elgg_substr_count($query, '"') >= 2);

			if (!$use_fulltext || $booleans_used || $quotes_used) {
				$options = 'IN BOOLEAN MODE';
			} else {
				$options = '';
			}

			$query = sanitise_string($query);

			$fields_str = implode(',', $fields);
			$where = "(MATCH ($fields_str) AGAINST ('$query' $options))";
		}

		return $where;
	}

	/**
	 * Get LIKE clauses
	 * @param string $table
	 * @param array $fields
	 * @param string $query
	 * @param string $glue
	 * @return string
	 */
	private function sqlGetWhereLikeClause($table, $fields, $query = '', $glue = ' OR ') {

		$query = str_replace(array('_', '%'), array('\_', '\%'), sanitize_string($query));

		foreach ($fields as $i => $field) {
			if ($table) {
				$fields[$i] = "$table.$field";
			}
		}

		$likes = array();
		foreach ($fields as $field) {
			$likes[] = "$field LIKE '%{$query}%'";
		}
		$likes_str = implode($glue, $likes);
		return "($likes_str)";
	}

	/**
	 * Get IN clauses
	 * @param string $table
	 * @param array $fields
	 * @param array $values
	 * @param boolean $as_text
	 * @param string $glue
	 * @return string
	 */
	private function sqlGetWhereInClause($table, $fields, $values = array(), $as_text = false, $glue = ' AND ') {

		if (!is_array($values) || !count($values)) {
			return '';
		}

		foreach ($fields as $i => $field) {
			if ($table) {
				$fields[$i] = "$table.$field";
			}
		}

		$in = array();
		foreach ($values as $val) {
			$val = ($as_text) ? "'$val'" : $val;
			$in[] = sanitize_string($val);
		}

		$in = implode(',', $in);

		$ins = array();

		foreach ($fields as $field) {
			$ins[] = "$field IN ($in)";
		}
		$ins_str = implode($glue, $ins);

		return "($ins_str)";
	}

}
