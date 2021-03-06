<?php
/**
 * Likes plugin
 * 
 */

elgg_register_event_handler('init', 'system', 'likes_init');

function likes_init() {

	elgg_extend_view('css/elgg', 'likes/css');

	// registered with priority < 500 so other plugins can remove likes
	elgg_register_plugin_hook_handler('register', 'menu:river', 'likes_river_menu_setup', 400);
	elgg_register_plugin_hook_handler('register', 'menu:entity', 'likes_entity_menu_setup', 400);

	$actions_base = elgg_get_plugins_path() . 'likes/actions/likes';
	elgg_register_action('likes/add', "$actions_base/add.php");
	elgg_register_action('likes/delete', "$actions_base/delete.php");
}

/**
 * Add likes to entity menu at end of the menu
 */
function likes_entity_menu_setup($hook, $type, $return, $params) {
	if (elgg_in_context('widgets')) {
		return $return;
	}

	$entity = $params['entity'];

	// likes
	$options = array(
		'name' => 'likes',
		'text' => elgg_view('likes/display', array('entity' => $entity)),
		'href' => false,
		'priority' => 1000,
	);
	$return[] = ElggMenuItem::factory($options);

	return $return;
}

/**
 * Add a like button to river actions
 */
function likes_river_menu_setup($hook, $type, $return, $params) {
	if (elgg_is_logged_in()) {
		$item = $params['item'];
		$object = $item->getObjectEntity();
		if (!elgg_in_context('widgets') && $item->annotation_id == 0) {
			if ($object->canAnnotate(0, 'likes')) {
				if (!elgg_annotation_exists($object->getGUID(), 'likes')) {
					// user has not liked this yet
					$url = "action/likes/add?guid={$object->getGUID()}";
					$options = array(
						'name' => 'like',
						'href' => $url,
						'text' => elgg_view('likes/display', array('entity' => $object)),
						'is_action' => true,
						'priority' => 100,
					);
				} else {
					// user has liked this
					$likes = elgg_get_annotations(array(
						'guid' => $object->getGUID(),
						'annotation_name' => 'likes',
						'annotation_owner_guid' => elgg_get_logged_in_user_guid()
					));
					$url = elgg_get_site_url() . "action/likes/delete?annotation_id={$likes[0]->id}";
					$options = array(
						'name' => 'like',
						'href' => $url,
						'text' => elgg_view('likes/display', array('entity' => $object)),
						'is_action' => true,
						'priority' => 100,
					);
				}
				$return[] = ElggMenuItem::factory($options);
			}
		}
	}

	return $return;
}

/**
 * Count how many people have liked an entity.
 *
 * @param  ElggEntity $entity 
 *
 * @return int Number of likes
 */
function likes_count($entity) {
	$type = $entity->getType();
	$params = array('entity' => $entity);
	$number = elgg_trigger_plugin_hook('likes:count', $type, $params, false);

	if ($number) {
		return $number;
	} else {
		return $entity->countAnnotations('likes');
	}
}
