<?php
require_once ('../../../../wp-load.php');
require_once ('../includes/utils.inc.php');

$post_id = $_POST['post_id'];
$style = $_POST['template'];

$post = get_post( $post_id );
$post_type = $post->post_type;
$post_type_object = get_post_type_object( $post_type );
if ( !current_user_can($post_type_object->cap->edit_post, $post_id) ) {
	wp_die( __('You are not allowed to edit this item.') );
}

header("Content-Type: text/xml");

global $base;

pripre_set_postparam($post_id, 'style', $style);
$template = pripre_get_style($style);

include ('../includes/post_params.inc.php');

$base = $template['dir'];
$title =  str_replace('\\"', '"', $_POST['title']);
$content =  str_replace('\\"', '"', $_POST['content']);
$contents_template = "$base/entry.php";
$dir = pripre_get_base_dir();
include ("$dir/templates/xml.php");

?>
