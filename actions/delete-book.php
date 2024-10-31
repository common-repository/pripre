<?php
require_once ('../../../../wp-load.php');
require_once ('../includes/utils.inc.php');

if ( !current_user_can('edit_pages') ) {
	wp_die( __('You are not allowed to edit this item.') );
}

global $wpdb;
$pripre_book = $wpdb->prefix . "pripre_book";
$wpdb->insert($pripre_book,
        array('id' => $_POST['book_id'],
           'book_date' => date('Y-m-d H:i:s')),
        array('%d', '%s'));
$wpdb->query("DELETE FROM $pripre_book WHERE id=".((int)$_POST['book_id']));

wp_redirect( admin_url('admin.php?page=pripre_publish_tool') );
?>
