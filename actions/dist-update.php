<?php
require_once ('../../../../wp-load.php');
require_once ('../includes/utils.inc.php');

if ( !current_user_can('edit_pages') ) {
	wp_die( __('You are not allowed to edit this item.') );
}

$id = $_POST['id'];
global $wpdb;

if (!empty($_POST['delete'])) {
	// 配信を削除
	$sql = $wpdb->prepare("DELETE FROM {$wpdb->prefix}pripre_dist
			WHERE id=%s", $id);
	$wpdb->query($sql);
	wp_redirect( admin_url('admin.php?page=pripre_distribute_tool') );
	return;
}

// 更新
$title = $_POST['title'];
$series = $_POST['series'];
$filename = $_POST['filename'];
$author = $_POST['author'];
$cover = $_POST['cover'];
$description = $_POST['description'];
$writing_mode = $_POST['writing_mode'];
$tags = $_POST['tags'];
$retail = $_POST['retail'];
$browse = $_POST['browse'];
$publish = $_POST['publish'];

$wpdb->update($wpdb->prefix . "pripre_dist",
        array('title' => $title,
        	'series' => $series,
        	'filename' => $filename,
        	'author' => $author,
        	'description' => $description,
        	'cover' => $cover,
        	'writing_mode' => $writing_mode,
         	'retail' => $retail,
        	'browse' => $browse,
        	'publish' => $publish
        ),
		array('id' => $id),
        array('%s', '%s', '%s', '%s', '%s', '%d', '%d'),
		array('%s'));

// タグ
$tags = str_replace('　', ' ', $tags);
$tags = explode(' ', $tags);
$wpdb->query("DELETE FROM ".$wpdb->prefix."pripre_dist_tag
		WHERE id='".$wpdb->escape($id)."'");
foreach($tags as $tag) {
	$wpdb->insert($wpdb->prefix . "pripre_dist_tag",
			array('id' => $id, 'tag' => $tag),
			array('%s', '%s'));
}

// 版
$captions = $_POST['caption'];
$sql = $wpdb->prepare("SELECT version
		FROM {$wpdb->prefix}pripre_dist_version
		WHERE id=%s ORDER BY version", $id);
$versions = $wpdb->get_results($sql, ARRAY_A);
$i = 0;
foreach($versions as $version) {
	$wpdb->update($wpdb->prefix . "pripre_dist_version",
			array('caption' => $captions[$i]),
			array('version' => $version['version']),
			array('%s'),
			array('%s'));
	++$i;
}

// 版を削除
$deletes = $_POST['delete-version'];
foreach($deletes as $delete) {
	$sql = $wpdb->prepare("DELETE FROM {$wpdb->prefix}pripre_dist_version
			WHERE id=%s AND version=%d", array($id, $delete));
	$wpdb->query($sql);
}

// EPUB追加
if (isset($_FILES['epub_file']) && filesize($_FILES['epub_file']['tmp_name'])) {
	$epub_file = $_FILES['epub_file'];
	$epub = $epub_file['tmp_name'];
	pripre_dist_add_epub($id, $epub);
}

wp_redirect( admin_url('admin.php?page=pripre_distribute_tool&id='.$id) );
?>
