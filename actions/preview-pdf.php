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

global $base;

pripre_set_postparam($post_id, 'style', $style);
$template = pripre_get_style($style);

include ('../includes/post_params.inc.php');

$base = $template['dir'];
$copper = pripre_get_copper();

header("Content-Type: application/pdf");
//header("Content-Type: text/plain");
//$time_start = microtime(true);

include ("../includes/copper-common.inc.php");
$width = $user_style_data['width'];
$height = $user_style_data['height'];
$copper->property("output.page-width", $width."mm");
$copper->property("output.page-height", $height."mm");

$copper->start_resource(site_url()."/style.css", array('encoding' => 'UTF-8'));
include ("$base/style.css");
echo $user_style_data['css'];
$copper->end_resource();

$copper->start_main(site_url().'/', array('encoding' => 'UTF-8'));
$title =  str_replace('\\"', '"', $_POST['title']);
$content =  str_replace('\\"', '"', $_POST['content']);
$contents_template = "$base/entry.php";
$dir = pripre_get_base_dir();
include ("$dir/templates/html.php");
$copper->end_main();
$copper->close();

//$time_end = microtime(true);
//$time = $time_end - $time_start;
//echo "$time\n";
?>
