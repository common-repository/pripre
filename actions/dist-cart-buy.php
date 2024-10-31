<?php
require_once ('../../../../wp-load.php');
require_once ('../includes/utils.inc.php');

$id = $_POST['id'];
$email = $_POST['email'];

global $wpdb;
$sql = $wpdb->prepare("SELECT d.id,d.title,d.retail
		FROM {$wpdb->prefix}pripre_dist_cart_item AS ci
		INNER JOIN {$wpdb->prefix}pripre_dist AS d ON d.id=ci.dist_id
		WHERE ci.id=%s", $id);
$rows = $wpdb->get_results($sql, ARRAY_A);

$amount = 0;
foreach($rows as $row) {
	$amount += $row['retail'];
}

$contact = get_option("pripre_contact");
$token = pripre_uniqid();
if ($amount) {
	// 課金
	if (empty($email)) {
		$_GET['id'] = $id;
		$message = 'メールアドレスが必要です';
		include ('../pages/dist-cart.php');
		exit;
	}
	
	$wpdb->update($wpdb->prefix . "pripre_dist_cart",
	        array('token' => $token, 'mail_address' => $email),
			array('id' => $id),
	        array('%s', '%s'),
			array('%s'));
	
	require_once ('../includes/paypal.inc.php');
	$req = pripre_paypal_get_request('SetExpressCheckout');
	$req->addPostParameter('SOLUTIONTYPE', 'Mark');
	$i = 0;
	foreach($rows as $row) {
		if ($row['retail']) {
			$req->addPostParameter('L_PAYMENTREQUEST_0_ITEMCATEGORY'.$i, 'Digital');
			$req->addPostParameter('L_PAYMENTREQUEST_0_NAME'.$i, '電子書籍');
			$req->addPostParameter('L_PAYMENTREQUEST_0_QTY'.$i, '1');
			$req->addPostParameter('L_PAYMENTREQUEST_0_AMT'.$i, $row['retail']);
			$req->addPostParameter('L_PAYMENTREQUEST_0_DESC'.$i, $row['title']);
			++$i;
		}
	}
	$req->addPostParameter('PAYMENTREQUEST_0_ITEMAMT', $amount);
	$req->addPostParameter('PAYMENTREQUEST_0_AMT', $amount);
	$req->addPostParameter('PAYMENTREQUEST_0_CURRENCYCODE', 'JPY');
	$req->addPostParameter('PAYMENTREQUEST_0_PAYMENTACTION', 'Sale');
	
	
	$req->addPostParameter('RETURNURL', plugins_url("pripre/actions/dist-buy.php?pripre_id=$id&pripre_token=$token"));
	$req->addPostParameter('CANCELURL', plugins_url("pripre/pages/dist-cart.php?id=$id"));
	$req->addPostParameter('LOCALECODE', 'jp_JP');
	$req->addPostParameter('EMAIL', $email);
	$req->addPostParameter('NOSHIPPING', '1');
	$res = pripre_paypal_get_response($req);
	
	$url = pripre_paypal_get_checkout_url($res['TOKEN']);
	
	$subject = "[".get_option('blogname')."] 電子書籍の購入";
	$message = "この度は電子書籍をご注文いただき、ありがとうございます。
ウェブブラウザで以下のアドレスにアクセスし、決済手続きを継続して下さい。

代金のお支払いには、PayPal決済サービスへのご登録またはクレジットカードが必要です。

$url
	
--
$contact";
	$redirect = plugins_url('pripre/pages/dist-emailed.php');
}
else {
	// 無料本	
	$sql = $wpdb->prepare("INSERT INTO {$wpdb->prefix}pripre_dist_cart_buy
		(token,buyed,dist_id,publish,expire,tickets,mail_address,retail)
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
	$subject = "[".get_option('blogname')."] 電子書籍のダウンロードアドレス";
	$message = "この度は電子書籍をご注文いただき、ありがとうございます。
全て無料本ですので、お代は不要です。以下のアドレスにアクセスしてダウンロードしてください、
	
$url
	
--
$contact";
	$redirect = $url;
}

if (!empty($email)) {
	wp_mail($email, $subject, $message);
}

wp_redirect($redirect);
?>
