<?php
set_include_path(get_include_path().PATH_SEPARATOR."../includes/pear");
include_once 'HTTP/Request2.php';

function &pripre_paypal_get_request($method) {	
	$server = get_option("pripre_paypal_server");
	if ($server == '1') {
		$server = 'https://api-3t.paypal.com/nvp';
		$user = get_option("pripre_paypal_user");
		$password = get_option("pripre_paypal_password");
		$signature = get_option("pripre_paypal_signature");
	}
	else {
		$server = 'https://api-3t.sandbox.paypal.com/nvp';
		$user = get_option("pripre_sandbox_paypal_user");
		$password = get_option("pripre_sandbox_paypal_password");
		$signature = get_option("pripre_sandbox_paypal_signature");
	}
	
	$req = new HTTP_Request2($server, HTTP_Request2::METHOD_POST);
	$req->setConfig('ssl_verify_peer', false);
	$req->addPostParameter('USER', $user);
	$req->addPostParameter('PWD', $password);
	$req->addPostParameter('SIGNATURE', $signature);
	$req->addPostParameter('VERSION', '91.0');
	$req->addPostParameter('METHOD', $method);
	return $req;
}

function &pripre_paypal_get_response(&$req) {
	$res = $req->send();
	$res = $res->getBody();
	parse_str($res, $res);
	return $res;
}

function pripre_paypal_get_checkout_url($token) {
	$server = get_option("pripre_paypal_server");
	if ($server == '1') {
		$server = 'https://www.paypal.com/cgi-bin/webscr';
	}
	else {
		$server = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
	}
	return "$server?cmd=_express-checkout&token=$token";
}

?>
