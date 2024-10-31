<?php
require_once ('../../../../wp-load.php');
require_once ('../includes/utils.inc.php');

if ( !current_user_can('edit_pages') ) {
	wp_die( __('You are not allowed to edit this item.') );
}

mb_regex_encoding('UTF-8');
mb_internal_encoding("UTF-8");
$book_id = (int)$_POST['book_id'];
$bind = $_POST['bind'];
$size = $_POST['size'];
$color = $_POST['color'];
$css = $_POST['css'];
$title = get_cat_name($book_id);

if ($bind == 'right') {
	$bind = 'right-side';
}
else {
	$bind = 'left-side';
}
$dir = pripre_get_base_dir();

$copper = pripre_get_copper();
$copper->property("output.print-mode", $bind);
if ($color == 0) {
	$copper->property("output.color", 'gray');
}
$copper->set_continuous(TRUE);

if (empty($outfile)) {
	header("Content-Type: application/pdf");
	header("Content-Disposition:  attachment");
}
else {
	$copper->set_output_as_resource($outfile);
}

include ("../includes/copper-common.inc.php");
preg_match("/^([0-9]+)x([0-9]+)$/", $size, $matches);
$width = $matches[1];
$height = $matches[2];
$copper->property("output.page-width", $width."mm");
$copper->property("output.page-height", $height."mm");

global $wpdb;
$sql = "SELECT p.post_date,p.id,p.post_title,p.post_content
        FROM {$wpdb->prefix}term_relationships AS t
        INNER JOIN {$wpdb->prefix}posts AS p ON p.id=t.object_id
        WHERE t.term_taxonomy_id=$book_id";
$entries = $wpdb->get_results($sql, ARRAY_A);
pripre_sort_bookposts($book_id, $entries);

for ($i = 0; $i < 2; ++$i) {
	$copper->property("processing.middle-pass", $i == 0 ? "true" : "false");
	foreach ($entries as $entry) {
	    $post_id = $entry['id'];
	    $title = $entry['post_title'];
	    $content = $entry['post_content'];
	    
	    $user_style = pripre_get_postparam($post_id, 'user_style');
	    $hide_title = pripre_get_postparam($post_id, 'hide_title');
	    $hide_thumbnail = pripre_get_postparam($post_id, 'hide_thumbnail');
	    $page_break = pripre_get_postparam($post_id, 'page_break');
	    $user_style_data = pripre_get_user_style($user_style);
	    $extra_css = pripre_get_postparam($post_id, 'extra_css');
	     
	    $style = pripre_get_postparam($post_id, 'style');
	    if (empty($style)) {
	    	$style = $pripre_default_style;
	    }

	    $template = pripre_get_style($style);
	    $base = $template['dir'];
	     
	    $copper->start_resource(site_url()."/style.css", array('encoding' => 'UTF-8'));
	    include ("$base/style.css");
		echo $user_style_data['css'];
	    $copper->end_resource();
	     
	    $contents_template = "$base/entry.php";
		$copper->start_main(site_url().'/?p='.$post_id, array('encoding' => 'UTF-8'));
	    include("$dir/templates/html.php");
		$copper->end_main();
	}
}

$copper->join();
$copper->close();
?>
