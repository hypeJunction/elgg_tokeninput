<?php

/**
 * Renders a table representation of a list
 * Can also be used for exporting large sets as CSV
 */

namespace hypeJunction\Lists;

class ElggTable extends ElggList {

	protected $headers = array(
		'guid' => 'table:guid',
		'icon' => 'table:icon',
		'title' => 'table:title',
		'time_created' => 'table:time_created',
		'owner_guid' => 'table:owner_guid',
		'container_guid' => 'table:container_guid'
	);

	function __construct($options = array(), $getter = 'elgg_get_entities') {
		parent::__construct($options, $getter);
	}
	/**
	 * Set a mapping of column headers
	 * @param array $headers	An array of header => label key pairs
	 */
	public function setColumnHeaders($headers = array()) {
		$this->headers = $headers;
		return $this;
	}

	/**
	 * Get a mapping of column headers
	 * @param boolean $csv
	 */
	public function getColumnHeaders() {
		return elgg_trigger_plugin_hook('export:headers', 'table', array(
			'options' => $this->options
				), $this->headers);
	}

	/**
	 * Map column headers to a proper representation in the row cell
	 * @param ElggEntity $entity
	 * @param boolean $csv
	 * @return array
	 */
	public function getRowCells($entity) {

		$row = array();

		$headers = $this->getColumnHeaders();

		foreach ($headers as $header => $label) {

			$value = '';

			switch ($header) {

				default :
					$value = $entity->$header;
					if (is_array($value)) {
						$value = implode('; ', $value);
					}
					break;

				case 'guid' :
					$value = $entity->guid;
					break;

				case 'icon' :
					$value = $entity->getIconURL();
					if (!elgg_in_context('plaintext')) {
						$value = elgg_view_entity_icon($entity, 'small');
					}
					break;

				case 'title' :
					$value = (elgg_instanceof($entity, 'object')) ? $entity->title : $entity->name;
					if (!elgg_in_context('plaintext')) {
						$value = elgg_view('output/url', array(
							'text' => $value,
							'href' => $entity->getURL()
						));
					}
					break;

				case 'time_created' :
					$value = date('M d, Y H:i', $entity->time_created);
					break;

				case 'owner_guid' :
					$value = '';
					$owner = $entity->getOwnerEntity();
					if (elgg_instanceof($owner)) {
						$value = $owner->guid;
						if (!elgg_in_context('plaintext')) {
							$value = elgg_view('output/url', array(
								'text' => (elgg_instanceof($owner, 'object')) ? $owner->title : $owner->name,
								'href' => $owner->getURL()
							));
						}
					}
					break;

				case 'container_guid' :
					$value = '';
					$container = $entity->getContainerEntity();
					if (elgg_instanceof($container)) {
						$value = $container->guid;
						if (!elgg_in_context('plaintext')) {
							$value = elgg_view('output/url', array(
								'text' => (elgg_instanceof($container, 'object')) ? $container->title : $container->name,
								'href' => $container->getURL()
							));
						}
					}
					break;
			}

			$row[$header] = $value;
		}

		return elgg_trigger_plugin_hook('export:entity', 'table', array(
			'headers' => $this->getColumnHeaders(),
			'entity' => $entity,
				), $row);
	}

	/**
	 * Display an html table of the entities
	 * @param string $class		CSS class to attach to the table
	 * @return string
	 */
	public function viewTable($class = '') {

		$headers = $this->getColumnHeaders();
		$count = $this->getCount();
		if ($count) {
			$items = $this->getItems();
		}
		$cols = sizeof($headers);

		$pager = elgg_view('navigation/pagination', array(
			'count' => $this->getCount(),
			'limit' => $this->limit,
			'offset' => $this->offset,
		));

		$head = '<tr>';
		foreach ($headers as $header => $label) {
			$label = elgg_echo($label);
			$head .= "<th class=\"$header\">$label</th>";
		}
		$head .= '</tr>';

		if ($count) {
			foreach ($items as $item) {
				$cells = $this->getRowCells($item, false);
				$body .= '<tr>';
				foreach ($cells as $colname => $value) {
					$body .= "<td class=\"$colname\">$value</td>";
				}
				$body .= '</tr>';
			}
			$body .= "<tr><td colspan=\"$cols\">$pager</td></tr>";
		} else {
			$placeholder = elgg_echo('table:empty');
			$body .= "<tr><td colspan=\"$cols\">$placeholder</td></tr>";
		}

		return "<table class=\"$class\"><thead>$head</thead><tbody>$body</tbody></table>";
	}

	/**
	 * Display an html table of the report
	 * @param string $filename		Name of the file to output
	 * @return string
	 */
	public function exportCSV($filename = 'export.csv') {

		set_time_limit(0);

		elgg_set_context('plaintext');
		
		header('Content-Description: File Transfer');
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename={$filename}");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");

		$headers = $this->getColumnHeaders();
		$items = $this->getItems();
		$count = $this->getCount();
		$i = 0;

		$fh = @fopen('php://output', 'w');
		fputcsv($fh, array_values($headers)); // Write header labels
		foreach ($items as $item) {
			$i++;
			$name = (elgg_instanceof($item, 'object')) ? $item->title : $item->name;
			elgg_log("CSV Exporting $i of $count: $name ($item->guid)");
			// Put the data into the stream
			fputcsv($fh, $this->getRowCells($item));
		}
		fclose($fh);
		exit;
	}

}
