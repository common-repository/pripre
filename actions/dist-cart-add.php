<?php
session_start();
require_once ('../../../../wp-load.php');
require_once ('../includes/utils.inc.php');

$id = $_GET['id'];

global $wpdb;

$cart_id = session_id();
// カート作成
$wpdb->insert($wpdb->prefix."pripre_dist_cart",
        array('id' => $cart_id,
			'expire' => date('Y-m-d H:i:s', time() + 3600 * 24),
        ),
        array('%s', '%s'));

// 古いカート削除
$sql = $wpdb->prepare("DELETE FROM ".$wpdb->prefix."pripre_dist_cart
        WHERE id=%s AND expire < NOW()", $id);
$wpdb->query($sql);

// アイテム追加
$wpdb->insert($wpdb->prefix."pripre_dist_cart_item",
		array('id' => $cart_id,
				'dist_id' => $id,
		),
		array('%s', '%s'));

wp_redirect(plugins_url("pripre/pages/dist-cart.php?id=$cart_id"));
?>
