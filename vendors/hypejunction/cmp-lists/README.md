cmp-lists
=========

Classes for displaying and manipulating Elgg lists

Includes:
* *hypeJunction\Lists\ElggList* - wraps and serves lists as ElggBatch
* *hypeJunction\Lists\ElggTable* - renders HTML tables and CSV files from ElggList
* *hypeJunction\Lists\ElggListQuery* - wrapper for most common search operations

Use composer to include these in your project
```json
{
	"require": {
		"hypejunction/cmp-lists": "@stable"
	}
}
```

## Examples

1. Export all users in a CSV

```php
$table = new \hypeJunction\Lists\ElggTable(array('types' => 'user', 'limit' => 0));
$table->setColumnHeaders(array(
	'guid' => 'guid',
	'icon' => 'icon',
	'name' => 'name',
	'location' => 'profile:location',
	'briefdescription' => 'profile:briefdescription',
));
$table->exportCSV();
```

2. Display a table of users with pagination (50 users per page)
```php
$table = new \hypeJunction\Lists\ElggTable(array(
	'types' => 'user',
	'limit' => 50
));
$table->setColumnHeaders(array(
	'icon' => 'icon',
	'title' => 'profile:name',
	'location' => 'profile:location',
	'briefdescription' => 'profile:briefdescription',
	'friends_count' => 'user:friends_count',
));
echo $table->viewTable('elgg-table-alt');
```

3. Complex example

Display a table of all files and images that have been tagged with 'red'
ordered by the number of likes

```php
$options = array(
	'types' => 'object',
	'subtypes' => array('file', 'image'),
	'limit' => 200,
	'annotation_names' => 'likes',
);

$query = new \hypeJunction\Lists\ElggListQuery('tags', 'red');
$options = $query->sqlGetOptions($options);

$table = new \hypeJunction\Lists\ElggTable($options, 'elgg_get_entities_from_annotation_calculation');
$table->setColumnHeaders(array(
	'preview' => '',
	'title' => 'title',
	'location' => 'location',
	'description' => 'description',
	'likes_count' => 'file:likes_count',
));

elgg_register_plugin_hook_handler('export:entity', 'table', __NAMESPACE__ . '\\my_callback');

function my_callback($hook, $type, $return, $params) {

	$headers = elgg_extract('headers', $params);
	$entity = elgg_extract('entity', $params);

	if (!elgg_instanceof($entity)) {
		return $return;
	}

	foreach ($headers as $header => $label) {
		switch ($header) {
			case 'preview' :
				$value = $entity->getIconURL('medium');
				if (!elgg_in_context('plaintext')) {
					$value = elgg_view_entity_icon($entity, 'medium');
				}
				$return[$header] = $value;
				break;

			case 'likes_count' :
				$return[$header] = $entity->countAnnotations('likes');
				break;
		}
	}

	return $return;
}

echo $table->viewTable('elgg-table-alt');
```