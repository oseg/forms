<?php

require_once(dirname(__FILE__) . '/lib/functions.php');

// register default elgg events
elgg_register_event_handler('init', 'system', 'forms_init');
elgg_register_plugin_hook_handler('route:rewrite', 'forms', '\ColdTrick\Forms\PageHandler::routeRewrite');

/**
 * Init function for this plugin
 *
 * @return void
 */
function forms_init() {
	
	// register page handler
	elgg_register_page_handler('forms', '\ColdTrick\Forms\PageHandler::forms');
	
	// ajax views
	elgg_register_ajax_view('form/friendly_title');
	elgg_register_ajax_view('forms/forms/validation_rules/edit');
	elgg_register_ajax_view('forms/forms/definition/import');
	
	elgg_extend_view('css/elgg', 'css/forms.css');
	
	// register plugin hooks
	elgg_register_plugin_hook_handler('access:collections:write', 'user', '\ColdTrick\Forms\Access::formWriteAccess');
	elgg_register_plugin_hook_handler('register', 'menu:entity', '\ColdTrick\Forms\Menus\Entity::registerForm');
	elgg_register_plugin_hook_handler('register', 'menu:page', '\ColdTrick\Forms\Menus\Page::registerValidationRules');
	elgg_register_plugin_hook_handler('register', 'menu:validation_rule', '\ColdTrick\Forms\Menus\ValidationRule::registerEdit');
	
	// register actions
	elgg_register_action('forms/edit', dirname(__FILE__) . '/actions/forms/edit.php', 'admin');
	elgg_register_action('forms/delete', dirname(__FILE__) . '/actions/forms/delete.php', 'admin');
	elgg_register_action('forms/copy', dirname(__FILE__) . '/actions/forms/copy.php', 'admin');
	elgg_register_action('forms/compose', dirname(__FILE__) . '/actions/forms/compose.php', 'admin');
	
	elgg_register_action('forms/submit', dirname(__FILE__) . '/actions/forms/submit.php', 'public');
	
	elgg_register_action('forms/validation_rules/edit', dirname(__FILE__) . '/actions/forms/validation_rules/edit.php', 'admin');
	elgg_register_action('forms/validation_rules/delete', dirname(__FILE__) . '/actions/forms/validation_rules/delete.php', 'admin');
	
	elgg_register_action('forms/definition/import', dirname(__FILE__) . '/actions/forms/definition/import.php', 'admin');
	elgg_register_action('forms/definition/export', dirname(__FILE__) . '/actions/forms/definition/export.php', 'admin');
}
