<?php
require_once ('../../../../wp-load.php');
require_once ('../includes/utils.inc.php');

if ( !current_user_can('edit_pages') ) {
	wp_die( __('You are not allowed to edit this item.') );
}

global $wpdb;
$pripre_book = $wpdb->prefix . "pripre_book";
$wpdb->insert($pripre_book,
        array('id' => $_POST['category'],
           'book_date' => date('Y-m-d H:i:s')),
        array('%d', '%s'));


wp_redirect( admin_url('admin.php?page=pripre_publish_tool&book_id='.((int)$_POST['category'])) );
?>
