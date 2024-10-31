<?php
require_once ('../../../../wp-load.php');
require_once ('../includes/utils.inc.php');

if ( !current_user_can('edit_pages') ) {
	wp_die( __('You are not allowed to edit this item.') );
}

mb_regex_encoding('UTF-8');
mb_internal_encoding("UTF-8");

$epub = pripre_generate_epub($_POST);

header("Content-Type: application/epub+zip");
header("Content-Disposition: attachment");

readfile($epub);

unlink($epub);
?>
