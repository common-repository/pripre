<?php
set_include_path(get_include_path().PATH_SEPARATOR."../includes/pear");
include_once 'HTTP/Request2.php';

$_POST['size'] = '127x188';
$outfile = tmpfile();
$target = 'http://print.cssj.jp/info/if/incomming.php';

include('publish-pdf.php');

$user = wp_get_current_user();

$req = new HTTP_Request2($target, HTTP_Request2::METHOD_POST);
try {
	$req->setHeader('content-type', 'multipart/form-data');
	fseek($outfile, 0);
	$req->addUpload('data_file', $outfile);
	$req->addPostParameter('title', get_cat_name($book_id));
	$req->addPostParameter('mail_address', $user->user_email);
	$req->addPostParameter('author', $user->display_name);
	$req->addPostParameter('mode', '4');
	$req->addPostParameter('color', $color == 1 ? '2' : ' 1');
	if ($bind == 'right-side') {
		$req->addPostParameter('bind', '2');
	}
	else {
		$req->addPostParameter('bind', '1');
	}
	$req->addPostParameter('return', 'javascript:window.close()');
	$res = $req->send();
	if (200 == $res->getStatus()) {
        $doc = new DOMDocument();
        $doc->loadXML($res->getBody());
        $xpath = new DOMXPath($doc);
        $redirect = $xpath->evaluate("string(/response/redirect/text())");
		header("Location: $redirect");
	} else {
		echo 'Unexpected HTTP status: ' . $res->getStatus() . ' ' .
				$res->getReasonPhrase();
	}
} catch (HTTP_Request2_Exception $e) {
	echo 'Error: ' . $e->getMessage();
}
fclose($outfile);

?>