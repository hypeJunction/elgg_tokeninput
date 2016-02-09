Tokenizing Autocomplete for Elgg
================================
![Elgg 2.0](https://img.shields.io/badge/Elgg-2.0.x-orange.svg?style=flat-square)

Replacement for Elgg's core autocomplete and userpicker

## Screenshots ##
![alt text](https://raw.github.com/hypeJunction/elgg_tokeninput/master/screenshots/tokeninput.png "Tokeninput")

## Features ##

* User-friendly autocomplete and userpicker inputs
* Works with entities and meta tags
* Developer-friendly
* Single and multiple selection
* Free tagging
* Drag&Drop sorting of tokens

## Notes ##

### I/O ###
Default behavior of the JS plugin is to implode user input into a comma-separated string.
PHP plugin hook for 'action', 'all' will attempt to explode these values
and feed them back into an action for further processing. This however,
will only work with basic form input names, e.g. ```name="field_name"```
If you are working with more complex forms, where e.g. ```name="field_name[element_name]"```,
you will need to add some custom logic to your action.

### AJAX requests ###
To initialize a tokeninput upon successfull AJAX request, use
``` $('.elgg-input-tokeninput').trigger('initialize'); ```

### Results and Token Format ###

#### Server Side ####
Use ```'tokeninput:entity:export', $entity_type``` plugin hook to modify json output.
You can add 'html_result' and 'html_token' parameters to customize the output.

#### Client Side ####
Use ```'results:formatter', 'tokeninput'``` and ```'results:formatter', 'tokeninput'```
hooks.

## Examples ###

### Example 1 ###

*Create an input that would allow users to search and select multiple files to be attached to an entity:*

Add an input to the form:

```php
echo elgg_view('input/tokeninput', array(
		'value' => $current_attachment_guids, // An array of values (guids or entities) to pre-populate the input with
		'name' => 'attachment_guids',
		'callback' => 'my_search_files_callback',
		'query' => array('simpletype' => 'image'),
		'multiple' => true
	));
```

Add a callback function:

```php
function my_search_files_callback($query, $options = array()) {

	$user = elgg_get_logged_in_user_entity();
	$simpletype = get_input('simpletype');

	$query = sanitize_string($query);

	// replace mysql vars with escaped strings
	$q = str_replace(array('_', '%'), array('\_', '\%'), $query);

	$dbprefix = elgg_get_config('dbprefix');

	$options['types'] = array('object');
	$options['subtypes'] = array('file');
	$options['joins'][] = "JOIN {$dbprefix}objects_entity oe ON oe.guid = e.guid";
	$options['wheres'][] = "oe.title LIKE '%$q%'";
	$options['wheres'][] = "e.owner_guid = $user->guid";

	if ($simpletype) {
		$options['metadata_name_value_pairs'] = array(
			'name' => 'simpletype', 'value' => $simpletype
		);
	}

	return elgg_get_entities_from_metadata($options);

}
```

In your action file:

```php
$attachment_guids = get_input('attachment_guids');
if (is_string($attachment_guids)) {
	$attachment_guids = explode(',', $attachment_guids);
}
if (is_array($attachment_guids)) {
	foreach ($attachment_guids as $attachment_guid) {
		make_attachment($entity->guid, $attachment_guid);
	}
}
```


## Upgrades

## To 4.x

* `elgg.tokeninput` library has been removed. `elgg_load_library('elgg.tokeninput')` calls will WSOD,
just remove them

* `tokeninput/init` AMD module has been removed. Tokeninput is bootstrapped as needed on page load and on ajax requests.

* `define('ELGG_TOKENINPUT_PAGEHANDLER', 'tokeninput');` has been removed. If you need to modify
page handler identifier, unregister the page handler and register a new one


## Attributions / Credits ##

* jquery.tokeninput http://loopj.com/jquery-tokeninput/