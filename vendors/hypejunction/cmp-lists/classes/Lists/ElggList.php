<?php

namespace hypeJunction\Lists;

use ElggAnnotation;
use ElggBatch;
use ElggRiverItem;
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

	/**
	 * Get an array of attributes to contruct a new list
	 * @param array $vars
	 * @return array
	 */
	public function getListAttributes() {

		if (!$this->options['list_type'] == 'list') {
			$list_classes[] = 'elgg-list';
			if (isset($this->options['list_class'])) {
				$list_classes[] = $this->options['list_class'];
			}
		} else if ($this->options['list_type'] == 'gallery') {
			$list_classes[] = 'elgg-gallery';
			if (isset($this->options['gallery_class'])) {
				$list_classes[] = $this->options['gallery_class'];
			}
		}

		$attributes = array(
			'id' => (isset($this->options['id'])) ? $this->options['id'] : $this->hash,
			'class' => implode(' ', $list_classes),
			'data-list' => true,
			'data-hash' => $this->hash,
		);
		return elgg_trigger_plugin_hook('attributes:list', 'list', array(
			'list' => $this
				), $attributes);
	}

	/**
	 * Get a list of attributes to attach to the list item
	 * @param mixed $item	List item (entity, annotation or river)
	 * @return array
	 */
	public function getItemAttributes($item = null) {

		$item_classes[] = 'elgg-item';

		if (elgg_instanceof($item)) {
			$entity = $item;
			$url = $entity->getURL();
			$id = "elgg-{$item->getType()}-{$item->getGUID()}";
			$item_classes[] = "elgg-item-{$item->getType()}";
		} else if ($item instanceof ElggRiverItem) {
			$river_id = $item->id;
			$entity = $item->getObjectEntity();
			if (!$entity) {
				$item->getSubjectEntity();
			}
			$id = "item-{$item->getType()}-{$item->id}";
			$item_classes[] = "elgg-item-river";
		} else if ($item instanceof ElggAnnotation) {
			$annotation_id = $item->id;
			$entity = $item->getEntity();
			$url = $item->getURL();
			$id = "item-{$item->getType()}-{$item->id}";
			$item_classes[] = "elgg-item-annotation";
		}

		if (isset($this->options['item_class'])) {
			$item_classes[] = $this->options['item_class'];
		}

		$attributes = array(
			'id' => $id,
			'class' => implode(' ', $item_classes),
			'data-river-id' => $river_id,
			'data-annotation-id' => $annotation_id,
			'data-guid' => $entity->guid,
			'data-url' => $url,
			'data-title' => (elgg_instanceof($entity, 'object')) ? $entity->title : $entity->name,
		);

		return elgg_trigger_plugin_hook('attributes:item', 'list', array(
			'item' => $item
				), $attributes);
	}

	/**
	 * Display an html list of the entities
	 * @param string $class		CSS class to attach to the table
	 * @return string
	 */
	public function viewList() {

		$context = (isset($this->options['list_type'])) ? $this->options['list_type'] : 'list';

		elgg_push_context($context);

		$items = $this->getItems();
		$options = $this->getOptions();
		$count = $this->getCount();

		$offset = elgg_extract('offset', $options);
		$limit = elgg_extract('limit', $options);
		$base_url = elgg_extract('base_url', $options, '');
		$pagination = elgg_extract('pagination', $options, true);
		$offset_key = elgg_extract('offset_key', $options, 'offset');
		$position = elgg_extract('position', $options, 'after');

		if ($pagination && $count) {
			$nav = elgg_view('navigation/pagination', array(
				'base_url' => $base_url,
				'offset' => $offset,
				'count' => $count,
				'limit' => $limit,
				'offset_key' => $offset_key,
			));
		}

		$html .= '<div class="elgg-list-container">';

		if ($position == 'before' || $position == 'both') {
			$html .= $nav;
		}

		$list_attrs = elgg_format_attributes($this->getListAttributes());

		$html .= "<ul $list_attrs>";

		foreach ($items as $item) {

			$view = elgg_view_list_item($item, $options);

			if (!$view) {
				continue;
			}

			$has_items = true;

			$item_attrs = elgg_format_attributes($this->getItemAttributes($item));

			$html .= "<li $item_attrs>$view</li>";
		}

		if (!$has_items) {
			$html .= '<li class="elgg-list-placeholder">' . elgg_echo('list:empty') . '</li>';
		}

		$html .= '</ul>';

		if ($position == 'after' || $position == 'both') {
			$html .= $nav;
		}

		$html .= '</div>';

		elgg_pop_context();

		return $html;
	}

}
