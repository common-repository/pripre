<?php
session_start();
require_once ('../../../../wp-load.php');
require_once ('../includes/utils.inc.php');

$id = $_GET['id'];

global $wpdb;

$cart_id = session_id();
// カート作成
$sql = $wpdb->prepare("DELETE FROM ".$wpdb->prefix."pripre_dist_cart_item
        WHERE id=%s AND dist_id=%s",
		array($cart_id, $id));
$wpdb->query($sql);

wp_redirect(plugins_url("pripre/pages/dist-cart.php?id=$cart_id"));
?>
