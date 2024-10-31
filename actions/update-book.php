<?php
require_once ('../../../../wp-load.php');
require_once ('../includes/utils.inc.php');

if ( !current_user_can('edit_pages') ) {
	wp_die( __('You are not allowed to edit this item.') );
}

$book_id = (int)$_POST['book_id'];
$bind = $_POST['bind'];
$size = $_POST['size'];
$color = (int)$_POST['color'];
$css = $_POST['css'];
$epubmeta = $_POST['epubmeta'];
$etemplate = $_POST['etemplate'];
$ids = explode(',', $_POST['ids']);
$styles = explode(',', $_POST['styles']);
$userstyles = explode(',', $_POST['userstyles']);

global $wpdb;

pripre_set_bookparam($book_id, 'bind', $bind);
pripre_set_bookparam($book_id, 'size', $size);
pripre_set_bookparam($book_id, 'color', $color);
pripre_set_bookparam($book_id, 'css', $css);
pripre_set_bookparam($book_id, 'epubmeta', $epubmeta);
pripre_set_bookparam($book_id, 'etemplate', $etemplate);

for ($i = 0; $i < count($ids); ++$i) {
	pripre_set_bookpostparam($book_id, $ids[$i], 'order', $i);
	pripre_set_postparam($ids[$i], 'style', $styles[$i]);
	pripre_set_postparam($ids[$i], 'user_style', $userstyles[$i]);
}
?>
