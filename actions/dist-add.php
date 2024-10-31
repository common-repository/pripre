<?php
require_once ('../../../../wp-load.php');
require_once ('../includes/utils.inc.php');

if ( !current_user_can('edit_pages') ) {
	wp_die( __('You are not allowed to edit this item.') );
}

$id = pripre_uniqid();
if (isset($_FILES['data_file']) && filesize($_FILES['data_file']['tmp_name'])) {
	$data_file = $_FILES['data_file'];
	$file = $data_file['tmp_name'];
	$filename = $data_file['name'];
	$dot = strrpos($filename, '.');
	if ($dot !== FALSE) {
	    $filename = substr($filename, 0, $dot);
	}
	$filename = mb_ereg_replace('[^A-Za-z\s_\-\.,0-9~]', '', $filename);
	
	$epub = pripre_epub_parse($file);
	$title = $epub['title'];
	$author = $epub['author'];
	$description = $epub['description'];
}
else {
	$title = 'タイトルなし';
	$filename = $id;
	$author = '';
	$description = '';
	$file = NULL;
}

global $wpdb;

// 追加
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

if ($file !== NULL) {
	// EPUB追加
	pripre_dist_add_epub($id, $file);
}

wp_redirect(admin_url('admin.php?page=pripre_distribute_tool&id='.$id));
?>
