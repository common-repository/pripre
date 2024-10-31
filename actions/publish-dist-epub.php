<?php
require_once ('../../../../wp-load.php');
require_once ('../includes/utils.inc.php');

if ( !current_user_can('edit_pages') ) {
	wp_die( __('You are not allowed to edit this item.') );
}

mb_regex_encoding('UTF-8');
mb_internal_encoding("UTF-8");

$file = pripre_generate_epub($_POST);
$epub = pripre_epub_parse($file);
$title = $epub['title'];
$author = $epub['author'];
$description = $epub['description'];

global $wpdb;

// 追加
if (empty($_POST['dist_id'])) {
	$id = pripre_uniqid();
	$wpdb->insert($wpdb->prefix . "pripre_dist",
			array('id' => $id,
					'added' => date('Y-m-d H:i:s'),
					'title' => $title,
					'filename' => $filename,
					'author' => $author,
					'description' => $description,
					'retail' => 0,
			),
			array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d'));
}
else {
	$id = $_POST['dist_id'];
}

if ($file !== NULL) {
	// EPUB追加
	pripre_dist_add_epub($id, $file);
}

wp_redirect(admin_url('admin.php?page=pripre_distribute_tool&id='.$id));

unlink($file);
?>
