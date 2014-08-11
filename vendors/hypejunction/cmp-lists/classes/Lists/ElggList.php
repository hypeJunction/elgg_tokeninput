<?php

namespace hypeJunction\Lists;

use ElggBatch;
use Exception;

class ElggList {

	/**
	 * Getter options
	 * @var array
	 */
	protected $options;

	/**
	 * Callable function name
	 * @var string
	 */
	protected $getter;

	/**
	 * Total count of items
	 * @var integer
	 */
	protected $count;

	/**
	 * Hash identifier based on options
	 * @var string
	 */
	protected $hash;

	/**
	 * Construct a new list
	 * @param array $options	Options to pass to the getter function
	 */
	function __construct($options = array(), $getter = 'elgg_get_entities') {

		if (!is_callable($getter)) {
			throw new Exception(get_class($this) . " expects a callable getter function");
		}

		$this->getter = $getter;

		$this->setOptions($options);

		if (isset($this->options['items'])) {
			$this->entities = $this->options['items'];
			unset($this->options['items']);
		}

		$this->setHash();
	}

	/**
	 * Get the total count of items
	 * @return integer
	 */
	public function getCount() {

		$count = $this->options['count'];
		if (is_int($count)) {
			return $count;
		}
		$this->options['count'] = true;
		$new_count = call_user_func($this->getter, $this->options);
		$this->options['count'] = false;
		return $new_count;
	}

	/**
	 * Get an iterable array of items
	 * @return ElggBatch
	 */
	public function getItems() {
		if (!$this->entities) {
			return new ElggBatch($this->getter, $this->options);
		} else {
			return $this->entities;
		}
	}

	/**
	 * Set getter options
	 * @param type $options
	 * @return ElggList
	 */
	private function setOptions($options = array()) {
		// clear values that might have been passed with the view $vars
		if (isset($options['config'])) {
			unset($options['config']);
		}
		if (isset($options['url'])) {
			unset($options['url']);
		}
		if (isset($options['user'])) {
			unset($options['user']);
		}
		if (isset($options['list'])) {
			unset($options['list']);
		}
		$this->options = $options;

		return $this;
	}

	/**
	 * Get options
	 * @return array
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * Construct a new query
	 * @param array $query An array of search type and query option pairs
	 * @return ElggList
	 */
	public function setSearchQuery($query = array()) {
	
		if (!is_array($query)) {
			return $this;
		}

		foreach ($query as $type => $options) {
			try {
				$listQuery = new ElggListQuery($type, $options);
				$this->options = $listQuery->sqlGetOptions($this->options);
			} catch (Exception $e) {
				elgg_log($e->getMessage(), 'ERROR');
			}
		}

		return $this;
	}

	/**
	 * Generate a hash to identify this map
	 * @return ElggList
	 */
	private function setHash() {

		if ($this->hash) {
			return $this->hash;
		}

		if (isset($this->options['id'])) {
			$this->hash = "mapbox-{$this->options['id']}";
			return $this->hash;
		}

		$defaults = array(
			'types' => '',
			'subtypes' => '',
			'type_subtype_pairs' => '',
			'guids' => '',
			'owner_guids' => '',
			'container_guids' => '',
			'site_guids' => '',
			'modified_time_lower' => '',
			'modified_time_upper' => '',
			'created_time_lower' => '',
			'created_time_upper' => '',
			'reverse_order_by' => '',
			'order_by' => '',
			'group_by' => '',
			'selects' => '',
			'wheres' => '',
			'joins' => '',
			'callback' => '',
			'metadata_names' => '',
			'metadata_values' => '',
			'metadata_name_value_pairs' => '',
			'metadata_name_value_pairs_operator' => 'AND',
			'metadata_case_sensitive' => '',
			'order_by_metadata' => '',
			'metadata_owner_guids' => '',
			'relationship' => '',
			'relationship_guid' => '',
			'inverse_relationship' => '',
			'annotation_names' => '',
			'annotation_values' => '',
			'annotation_name_value_pairs' => '',
			'annotation_name_value_pairs_operator' => '',
			'annotation_case_sensitive' => '',
			'order_by_annotation' => '',
			'annotation_created_time_lower' => '',
			'annotation_created_time_upper' => '',
			'annotation_owner_guids' => '',
		);

		$intersection = array_intersect_key($this->options, $defaults);
		$this->hash = md5(serialize($intersection));

		return $this;
	}

	/**
	 * Get the hash
	 * @return string
	 */
	public function getHash() {
		if (!$this->hash) {
			$this->setHash();
		}
		return $this->hash;
	}

}
