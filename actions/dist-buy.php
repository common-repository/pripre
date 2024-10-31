<?php
require_once ('../../../../wp-load.php');
require_once ('../includes/utils.inc.php');

$id = $_GET['pripre_id']; // カートID
$token = $_GET['pripre_token']; // トークン

$sql = $wpdb->prepare("SELECT COUNT(*) AS c FROM {$wpdb->prefix}pripre_dist_cart
	WHERE id=%s AND token=%s", array($id, $token));
$row = $wpdb->get_row($sql, ARRAY_A);
if ($row[c] != 1) {
	echo "認証エラー";
	exit;
}

$sql = $wpdb->prepare("INSERT INTO {$wpdb->prefix}pripre_dist_cart_buy
	(token,buyed,dist_id,version,expire,tickets,mail_address,retail)
	SELECT %s,NOW(),d.id,d.publish,%s,20,c.mail_address,d.retail
	FROM {$wpdb->prefix}pripre_dist_cart_item AS ci
	INNER JOIN {$wpdb->prefix}pripre_dist AS d ON d.id=ci.dist_id
	INNER JOIN {$wpdb->prefix}pripre_dist_cart AS c ON c.id=ci.id
		WHERE ci.id=%s", array($token, date('Y-m-d H:i:s', time() + 3600 * 24 * 7), $id));
$wpdb->query($sql);
$sql = $wpdb->prepare("DELETE FROM {$wpdb->prefix}pripre_dist_cart_item
	WHERE id=%s", $id);
$wpdb->query($sql);
$sql = $wpdb->prepare("DELETE FROM {$wpdb->prefix}pripre_dist_cart
WHERE id=%s", $id);
$wpdb->query($sql);

$url = plugins_url('pripre/pages/dist-read.php?token='.$token);

$sql = $wpdb->prepare("SELECT mail_address
		FROM {$wpdb->prefix}pripre_dist_cart_buy
		WHERE token=%s LIMIT 1", $token);
$row = $wpdb->get_row($sql, ARRAY_A);

$contact = get_option("pripre_contact");
$email = $row['mail_address'];
$subject = "[".get_option('blogname')."] 電子書籍のご購入ありがとうございます";
$message = "この度は電子書籍をご購入いただき、ありがとうございます。
決済が完了いたしました。書籍のダウンロードアドレスはこちらです。

$url

--
$contact";
wp_mail($email, $subject, $message);

wp_redirect($url);
?>
