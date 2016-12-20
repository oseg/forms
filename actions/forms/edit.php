<?php

elgg_make_sticky_form('forms/edit');

$guid = (int) get_input('guid');
$container_guid = (int) get_input('container_guid');

$title = get_input('title');
$friendly_url = get_input('friendly_url', elgg_get_friendly_title($title));
$friendly_url = elgg_get_friendly_title($friendly_url);
$description = get_input('description');
$access_id = (int) get_input('access_id');

$entity = false;
if (!empty($guid)) {
	$entity = get_entity($guid);
	if (!($entity instanceof Form) || !$entity->canEdit()) {
		return elgg_error_response(elgg_echo('noaccess'));
	}
}

if (empty($title) || empty($friendly_url) || empty($container_guid)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

if (!forms_is_valid_friendly_url($friendly_url)) {
	return elgg_error_response(elgg_echo('forms:action:edit:error:friendly_url'));
}

$container = get_entity($container_guid);
if (!($container instanceof ElggGroup)) {
	$container = elgg_get_site_entity();
}

if (empty($entity)) {
	
	if (!$container->canWriteToContainer(0, 'object', Form::SUBTYPE)) {
		return elgg_error_response(elgg_echo('actionunauthorized'));
	}
	
	$entity = new Form();
	$entity->container_guid = $container->getGUID();
	
	if (!$entity->save()) {
		return elgg_error_response(elgg_echo('save:fail'));
	}
}

$entity->title = $title;
$entity->friendly_url = $friendly_url;
$entity->description = $description;
$entity->access_id = $access_id;

if (!$entity->save()) {
	return elgg_error_response(elgg_echo('save:fail'));
}

elgg_clear_sticky_form('forms/edit');

return elgg_ok_response('', elgg_echo('save:success'), 'forms/all');
