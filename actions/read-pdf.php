<?php
require_once ('../../../../wp-load.php');
require_once ('../includes/utils.inc.php');

$post_id = $_POST['p'];
$post = get_post( $post_id );
if (get_post_status($post_id) != 'publish') {
	$post_type = $post->post_type;
	$post_type_object = get_post_type_object( $post_type );
	if ( !current_user_can($post_type_object->cap->read_post, $post_id) ) {
		wp_die( __('You are not allowed to edit this item.') );
	}
}

global $base;

$style = $_POST['s'];

global $pripre_ebook_font;
$pripre_ebook_font = $_POST['f'];
$pripre_ebook_margin = $_POST['m'];

if(empty($style)) {
	$style = pripre_get_postparam($post_id, 'style');
	if (empty($style)) {
		$style = $pripre_default_style;
	}
	$template = pripre_get_style($style);
	$user_style = pripre_get_postparam($post_id, 'user_style');
	$user_style_data = pripre_get_user_style($user_style);
	$width = $user_style_data['width'];
	$height = $user_style_data['height'];
}
else {
	$style = pripre_get_estyle_dir($style);
	$template = pripre_get_style_from_dir($style);
	$width = 132;
	$height = 182;
}

$base = $template['dir'];

$site = home_url();
$copper = pripre_get_copper();

header("Content-Type: application/pdf");
//header("Content-Type: text/plain");

include ("../includes/copper-common.inc.php");
$template = pripre_get_style($style);

$copper->property("output.page-width", $width."mm");
$copper->property("output.page-height", $height."mm");

$hide_title = pripre_get_postparam($post_id, 'hide_title');
$hide_thumbnail = pripre_get_postparam($post_id, 'hide_thumbnail');
$extra_css = pripre_get_postparam($post_id, 'extra_css');

switch ($pripre_ebook_font) {
	case -2:
		$copper->property("output.text-size", "0.5");
		break;
	case -1:
		$copper->property("output.text-size", "0.75");
		break;
	case 1:
		$copper->property("output.text-size", "1.5");
		break;
	case 2:
		$copper->property("output.text-size", "2");
		break;
}

$copper->start_resource(site_url()."/style.css", array('encoding' => 'UTF-8'));
include ("$base/style.css");
echo $user_style_data['css'];
$copper->end_resource();

$copper->start_main(site_url().'/', array('encoding' => 'UTF-8'));
$title =  str_replace('\\"', '"', $post->post_title);
$content =  str_replace('\\"', '"', $post->post_content);
$contents_template = "$base/entry.php";
$dir = pripre_get_base_dir();
include ("$dir/templates/html.php");
$copper->end_main();
$copper->close();
?>
