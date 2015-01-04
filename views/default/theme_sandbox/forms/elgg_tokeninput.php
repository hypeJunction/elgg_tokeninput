<form action="#">
	<fieldset>
		<legend>Tokeninput Fields</legend>
		<div>
			<label for="tk1">Users tokeninput:</label>
			<?php
			echo elgg_view('input/tokeninput/users', array(
				'name' => 'tk1',
				'id' => 'tk1',
			));
			?>
		</div>
		<div>
			<label for="tk2">Friends tokeninput:</label>
			<?php
			echo elgg_view('input/tokeninput/friends', array(
				'name' => 'tk2',
				'id' => 'tk2',
			));
			?>
		</div>
		<div>
			<label for="tk3">Groups tokeninput:</label>
			<?php
			echo elgg_view('input/tokeninput/groups', array(
				'name' => 'tk3',
				'id' => 'tk3',
			));
			?>
		</div>
		<div>
			<label for="tk4">Objects tokeninput:</label>
			<?php
			echo elgg_view('input/tokeninput/objects', array(
				'name' => 'tk4',
				'id' => 'tk4',
				'subtype' => array('blog', 'bookmarks', 'file'),
			));
			?>
		</div>
	</fieldset>
</form>