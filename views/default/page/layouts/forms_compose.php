<?php
/**
 * Layout for main column with one sidebar
 *
 * @package Elgg
 * @subpackage Core
 *
 * @uses $vars['title']   Optional title for main content area
 * @uses $vars['content'] Content HTML for the main column
 * @uses $vars['sidebar'] Optional content that is added to the sidebar
 * @uses $vars['nav']     Optional override of the page nav (default: breadcrumbs)
 * @uses $vars['header']  Optional override for the header
 * @uses $vars['footer']  Optional footer
 * @uses $vars['class']   Additional class to apply to layout
 */

$class = 'elgg-layout elgg-layout-one-sidebar clearfix';
if (isset($vars['class'])) {
	$class = "$class {$vars['class']}";
}

?>

<div class="<?php echo $class; ?>">
	<div class="elgg-sidebar">
		<?php
			// use a custom sidebar (not using sidebar view to prevent extensions)
			echo elgg_view('form/compose/fields', $vars);
		?>
	</div>

	<div class="elgg-main elgg-body">
		<?php
			echo elgg_extract('nav', $vars, elgg_view('navigation/breadcrumbs'));

			echo elgg_view('page/layouts/elements/header', $vars);

			if (isset($vars['content'])) {
				echo $vars['content'];
			}
			
			echo elgg_view('page/layouts/elements/footer', $vars);
		?>
	</div>
</div>
