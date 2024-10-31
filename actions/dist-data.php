<?php
require_once ('../../../../wp-load.php');
require_once ('../includes/utils.inc.php');

if ( !current_user_can('edit_pages') ) {
	wp_die( __('You are not allowed to edit this item.') );
}

$id = $_GET['id'];
$version = $_GET['version'];
$mode = $_GET['mode'];

if ($mode == 'pdf') {
	$mode = 'pdf';
	header('Content-Type: application/pdf');
}
else {
	$mode = 'data';
	header('Content-Type: application/epub+zip');
}

global $wpdb;

$sql = $wpdb->prepare("SELECT dv.$mode AS data
		FROM {$wpdb->prefix}pripre_dist_version AS dv
		WHERE dv.id=%s AND dv.version=%d", array($id, $version));
$row = $wpdb->get_row($sql, ARRAY_A);

header('Content-Disposition: attachment');
echo $row['data'];
?>
