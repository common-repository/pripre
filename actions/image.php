<?php
require_once ('../../../../wp-load.php');
require_once ('../includes/utils.inc.php');

$page = $_GET['page'];

$tmp = pripre_get_tmp_dir();

header('Content-Type: image/png');
readfile("$tmp/$page.png");
?>
