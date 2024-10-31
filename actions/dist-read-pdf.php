<?php
require_once ('../../../../wp-load.php');
require_once ('../includes/utils.inc.php');

$id = $_POST['id'];

$vertical = $_POST['v'];
$font = $_POST['f'];

global $wpdb;
if (!empty($_POST['token'])) {
	// 購読
	$token = $_POST['token'];
	$sql = $wpdb->prepare("SELECT d.writing_mode,dv.data
			FROM {$wpdb->prefix}pripre_dist_cart_buy AS cb
			INNER JOIN {$wpdb->prefix}pripre_dist AS d ON d.id=cb.dist_id
			INNER JOIN {$wpdb->prefix}pripre_dist_version AS dv ON dv.id=d.id AND dv.version=d.publish
			WHERE cb.token=%s AND cb.dist_id=%s AND cb.tickets>0", array($token, $id));
}
else {
	// 立ち読み版
	$sql = $wpdb->prepare("SELECT d.writing_mode,dv.data
			FROM {$wpdb->prefix}pripre_dist AS d
			INNER JOIN {$wpdb->prefix}pripre_dist_version AS dv ON dv.version=d.browse
			WHERE d.id=%s", $id);
}
$row = $wpdb->get_row($sql, ARRAY_A);

if (!$row) {
	exit;
}
$sql = $wpdb->prepare("UPDATE {$wpdb->prefix}pripre_dist_cart_buy
		SET tickets=tickets-1
		WHERE token=%s AND dist_id=%s", array($token, $id));
$wpdb->query($sql);

header('Content-Type: application/pdf');
header('Content-Disposition: attachment');
$copper = pripre_get_copper();

$template = pripre_get_style($style);
$copper->property("output.page-width", "132mm");
$copper->property("output.page-height", "182mm");

switch ($font) {
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

$copper->property('input.default-stylesheet', 'file:///default.css');

switch($vertical) {
	case 0:
		// 横書き
		$writing_mode = '-epub-writing-mode: horizontal-tb ! important;';
		break;
	
	case 1:
		// 縦書き
		$writing_mode = '-epub-writing-mode: vertical-rl ! important;';
		$copper->property("output.print-mode", "right-side");
		$copper->property("x.jp.cssj.plugins.epub.replace-numbers", "true");
		break;
		
	case 2:
		// 縦２段
		$writing_mode = '-epub-writing-mode: vertical-rl ! important;
-epub-column-count: 2 ! important;';
		$copper->property("output.print-mode", "right-side");
		$copper->property("x.jp.cssj.plugins.epub.replace-numbers", "true");
		break;
		
	default:
		$writing_mode = '';
		break;
}
$copper->start_resource('file:///default.css', array('mimeType' => 'text/css'));
echo <<<EOD
body {
	$writing_mode
}
img {
	max-width: 100%;
	max-height: 100%;
}
EOD;
$copper->end_resource();

$copper->start_main('.', array('mimeType' => 'application/epub+zip'));
echo $row['data'];
$copper->end_main();
$copper->close();

?>
