<?php
require_once ('filters.inc.php');

// Copper PDFへの接続を得る
function pripre_get_copper() {
	require_once ('CTI/DriverManager.php');
	$server = get_option("pripre_copper_server");
	if ($server) {
		$uri = get_option("pripre_copper_uri");
		if (!$uri) {
			$uri = PRIPRE_COPPER_URI_DEFAULT;
		}
		$user = get_option("pripre_copper_user");
		if (!$user) {
			$user = PRIPRE_COPPER_USER_DEFAULT;
		}
		$password = get_option("pripre_copper_password");
		if (!$password) {
			$password = PRIPRE_COPPER_PASSWORD_DEFAULT;
		}
	}
	else {
		$uri = 'ctip://free.cssj.jp:8499/';
		$user = 'user';
		$password = 'kappa';
	}
	$copper = cti_get_session($uri, array('user' => $user, 'password' => $password));

	$copper->property("output.pdf.version", "1.4A-1");
	$copper->property("output.pdf.fonts.policy", "embedded");
	$copper->property("output.pdf.hyperlinks", "true");
	$copper->property("output.pdf.bookmarks", "true");
	$copper->property("output.clip", "false");
	$copper->property("input.include", site_url()."/**");
	$copper->property("input.include", "http://**");
	$copper->property("input.include", "https://**");
	$copper->property("processing.page-references", "true");

	return $copper;
}

function pripre_svg_to_png($src, $dest) {
	$copper = pripre_get_copper();
	$copper->set_output_as_file($dest);
	$copper->property('output.type', 'image/png');
	$copper->start_main('.', array('mimeType' => 'image/svg+xml'));
	readfile($src);
	$copper->end_main();
	$copper->close();
}

function pripre_get_base_dir() {
	$dir = dirname(dirname(__FILE__));
	return $dir;
}

function pripre_get_session_id() {
	global $pripre_session_id;
	if (empty($pripre_session_id)) {
		$pripre_session_id = wp_parse_auth_cookie();
		$pripre_session_id = $pripre_session_id['hmac'];
	}
	return $pripre_session_id;
}

function pripre_get_tmp_dir() {
	$id = pripre_get_session_id();
	$tmp = sys_get_temp_dir()."/wp-pripre";
	$dir = "$tmp/$id";
	if (!@mkdir($dir, 0777, true)) {
		if (!@touch($dir)) {
			$tmp = pripre_get_base_dir().'/tmp';
			$dir = "$tmp/$id";
			if (!@mkdir($dir, 0777, true)) {
				touch($dir);
			}
		}
	}

	// １日以上前の一時ディレクトリは消す
	if ($dh = opendir($tmp)) {
		while (($file = readdir($dh)) !== false) {
			if (substr($file, 0, 1) !== '.' && filemtime("$tmp/$file") < time() - 3600 * 24) {
				pripre_rmr("$tmp/$file");
			}
		}
		closedir($dh);
	}

	return $dir;
}

function pripre_tempcmp($a, $b) {
	if ($a['order'] == $b['order']) {
		return 0;
	}
	return ($a['order'] < $b['order']) ? -1 : 1;
}

function pripre_get_styles() {
	global $pripre_style_groups;
	if ($pripre_style_groups == NULL) {
		$pripre_style_groups = array();
		$pripre_style_groups = apply_filters('pripre_style_groups', $pripre_style_groups);
	}

	$styles = array();
	foreach($pripre_style_groups as $group_id => $dir) {
		$file = "$dir/info.xml";
		$doc = new DOMDocument();
		if ($doc->load($file)) {
			$xpath = new DOMXPath($doc);
			$styles[$group_id] = array(
					'id' => $group_id,
					'name' => $xpath->evaluate('string(/info/name/text())'),
					'order' => (int)$xpath->evaluate('string(/info/order/text())'),
					'styles' => array()
			);
		}
		$dh = opendir($dir);
		while (($style_id = readdir($dh)) !== FALSE) {
			if (substr($style_id, 0, 1) == '.' || $style_id == 'info.xml') {
				continue;
			}
			$style = pripre_get_style("$group_id/$style_id");
			if ($template !== FALSE) {
				$styles[$group_id]['styles'][] = $style;
			}
		}
		closedir($dh);
		uasort($styles[$group_id]['styles'], 'pripre_tempcmp');
	}
	uasort($styles, 'pripre_tempcmp');
	return $styles;
}

function pripre_get_style($id) {
	global $pripre_style_groups;
	if ($pripre_style_groups == NULL) {
		$pripre_style_groups = array();
		$pripre_style_groups = apply_filters('pripre_style_groups', $pripre_style_groups);
	}
	list($group_id, $style_id) = explode('/', $id);
	$dir = $pripre_style_groups[$group_id]."/$style_id";
	return pripre_get_style_from_dir($dir, $id);
}

function pripre_get_style_from_dir($dir, $id = '') {
	$file = "$dir/info.xml";
	if (file_exists($file)) {
		$doc = new DOMDocument();
		if ($doc->load($file)) {
			$xpath = new DOMXPath($doc);
			$style = array(
					'id' => $id,
					'dir' => $dir,
					'name' => $xpath->evaluate('string(/info/name/text())'),
					'page-progression' => $xpath->evaluate('string(/info/page-progression/text())'),
					'order' => (int)$xpath->evaluate('string(/info/order/text())'),
					'site' => $xpath->evaluate('string(/info/site/text())'),
			);
			return $style;
		}
	}
	return FALSE;
}

function pripre_get_estyles() {
	global $pripre_estyles;
	if ($pripre_estyles == NULL) {
		$pripre_estyles = array();
		$pripre_estyles = apply_filters('pripre_estyles', $pripre_estyles);
	}

	$styles = array();
	foreach($pripre_estyles as $id => $dir) {
		$file = "$dir/info.xml";
		$doc = new DOMDocument();
		if ($doc->load($file)) {
			$xpath = new DOMXPath($doc);
			$styles[$id] = array(
					'name' => $xpath->evaluate('string(/info/name/text())'),
					'order' => (int)$xpath->evaluate('string(/info/order/text())'),
					'id' => $id
			);
		}
	}
	uasort($styles, 'pripre_tempcmp');
	return $styles;
}

function pripre_get_estyle_dir($id) {
	global $pripre_estyles;
	if ($pripre_estyles == NULL) {
		$pripre_estyles = array();
		$pripre_estyles = apply_filters('pripre_estyles', $pripre_estyles);
	}
	return $pripre_estyles[$id];
}

function pripre_get_user_styles() {
	global $wpdb;
	$sql = "SELECT id,name
	FROM {$wpdb->prefix}pripre_user_style
	ORDER BY id;";
	$rows = $wpdb->get_results($sql, ARRAY_A);
	$styles = array();
	foreach($rows as $row) {
		$styles[$row['id']] = $row['name'];
	}
	return $styles;
}

function pripre_get_user_style($id) {
	global $wpdb;
	$rows = $wpdb->get_results($wpdb->prepare(
			"SELECT name,size,css FROM {$wpdb->prefix}pripre_user_style
			WHERE id=%d",
			$id), ARRAY_A);
	if (empty($rows)) {
		$data = array(
			'name' => '',
			'size' => '',
			'css' => ''
		);
	}
	else {
		$data = $rows[0];
	}
	$size = $data['size'];
	if (!empty($size)) {
		preg_match("/^([0-9]+)x([0-9]+)$/", $size, $matches);
		$data['width'] = $matches[1];
		$data['height'] = $matches[2];
	}
	else {
		$data['width'] = 210;
		$data['height'] = 297;
	}
	return $data;
}

function pripre_update_user_style($id, $name, $size, $css) {
	global $wpdb;
	$sql = $wpdb->prepare("UPDATE ".$wpdb->prefix."pripre_user_style
			SET name=%s,size=%s,css=%s
			WHERE id=%d", array($name, $size, $css, $id));
	$wpdb->query($sql);
}

function pripre_add_user_style($name) {
	global $wpdb;
	
	$wpdb->insert("{$wpdb->prefix}pripre_user_style",
	array('name' => $name),
			array('%s'));
	
	return $wpdb->get_var($wpdb->prepare(
			"SELECT max(id)
	FROM {$wpdb->prefix}pripre_user_style"));
}
	
function pripre_rmr($dir) {
	if (is_dir($dir)) {
		$objects = scandir($dir);
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (filetype($dir."/".$object) == "dir") pripre_rmr($dir."/".$object); else unlink($dir."/".$object);
			}
		}
		reset($objects);
		rmdir($dir);
	}
}

/**
 * 本のパラメータを設定します。
 *
 * @param integer $book_id
 * @param string $name
 * @param string $value
 */
function pripre_set_bookparam($book_id, $name, $value) {
	global $wpdb;
	$wpdb->query($wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}pripre_book_param
			WHERE book_id=%d AND param_name=%s",
			$book_id, $name));
	if (!empty($value)) {
		$wpdb->insert("{$wpdb->prefix}pripre_book_param",
		array('book_id' => $book_id,
				'param_name' => $name,
				'param_value' => $value),
				array('%d', '%s', '%s'));
	}
}

/**
 * 本のパラメータを返します。
 *
 * @param integer $book_id
 * @param string $name
 *
 * @return string
 */
function pripre_get_bookparam($book_id, $name) {
	global $wpdb;
	return $wpdb->get_var($wpdb->prepare(
			"SELECT param_value FROM {$wpdb->prefix}pripre_book_param
			WHERE book_id=%d AND param_name=%s",
			$book_id, $name));
}

/**
 * 記事のパラメータを設定します。
 *
 * @param integer $post_id
 * @param string $name
 * @param string $value
 */
function pripre_set_postparam($post_id, $name, $value) {
	global $wpdb;
	$wpdb->query($wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}pripre_post_param
			WHERE post_id=%d AND param_name=%s",
			$post_id, $name));
	if (!empty($value)) {
		$wpdb->insert("{$wpdb->prefix}pripre_post_param",
		array('post_id' => $post_id,
				'param_name' => $name,
				'param_value' => $value),
				array('%d', '%s', '%s'));
	}
}

/**
 * 記事のパラメータを返します。
 *
 * @param integer $post_id
 * @param string $name
 *
 * @return string
 */
function pripre_get_postparam($post_id, $name) {
	global $wpdb;
	return $wpdb->get_var($wpdb->prepare(
			"SELECT param_value FROM {$wpdb->prefix}pripre_post_param
			WHERE post_id=%d AND param_name=%s",
			$post_id, $name));
}

function pripre_set_bookpostparam($book_id, $post_id, $name, $value) {
	global $wpdb;
	$wpdb->query($wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}pripre_book_post_param
			WHERE book_id=%d AND post_id=%d AND param_name=%s",
			$book_id, $post_id, $name));
	if (!empty($value)) {
		$wpdb->insert("{$wpdb->prefix}pripre_book_post_param",
		array('book_id' => $book_id,
				'post_id' => $post_id,
				'param_name' => $name,
				'param_value' => $value),
				array('%d', '%d', '%s', '%s'));
	}
}

function pripre_get_bookpostparam($book_id, $post_id, $name) {
	global $wpdb;
	return $wpdb->get_var($wpdb->prepare(
			"SELECT param_value FROM {$wpdb->prefix}pripre_book_post_param
			WHERE book_id=%d AND post_id=%d AND param_name=%s",
			$book_id, $post_id, $name));
}

function pripre_sort_bookposts($book_id, &$posts) {
	$result = array();
	$imax = 0;
	foreach($posts as $k => $v) {
		$i = pripre_get_bookpostparam($book_id, $posts[$k]['id'], 'order');
		if (isset($result[$i])) {
			$i = $imax + 1;
		}
		$result[$i] = $v;
		$imax = max($imax, $i);
	}
	$posts = $result;
	ksort($posts);
	return $posts;
}

/**
 * EPUBファイルを解析します。
 *
 * @param string $file
 */
function pripre_epub_parse($file) {
	$base = 'zip://'.$file;
	$doc = new DOMDocument();
	if (@$doc->load("$base#META-INF/container.xml") === FALSE) {
		return 'EPUB形式のファイルではありません';
	}
	$rootfiles = $doc->getElementsByTagName('rootfile');
	$cnt = $rootfiles->length;

	$root = NULL;
	for ($i = 0; $i < $cnt; $i++) {
		$item = $rootfiles->item($i);
		$type = $item->getAttribute('media-type');
		if ($type == 'application/oebps-package+xml') {
			$fill_path = $item->getAttribute('full-path');
			$root = $fill_path;
			break;
		}
	}
	if ($root === FALSE) {
		return 'ルートファイルがありません';
	}
	$doc = new DOMDocument();
	if (@$doc->load("$base#$root") === FALSE) {
		return htmlspecialchars($root).' を読み込めません';
	}
	$xpath = new DOMXpath($doc);
	$xpath->registerNamespace('opf', 'http://www.idpf.org/2007/opf');
	$xpath->registerNamespace('dc', 'http://purl.org/dc/elements/1.1/');

	$bookid = $xpath->evaluate("string(/opf:package/opf:metadata/dc:identifier)");
	$title = $xpath->evaluate("string(/opf:package/opf:metadata/dc:title)");
	$author = $xpath->evaluate("string(/opf:package/opf:metadata/dc:creator[@opf:role='aut'])");

	$description = $xpath->evaluate("string(/opf:package/opf:metadata/dc:description)");

	$toc = $xpath->evaluate("string(/opf:package/opf:manifest/opf:item[@id=/opf:package/opf:spine/@toc]/@href)");
	if ($toc) {
		if ($description) {
			$description .= "\n";
		}
		$root = dirname($root);
		if ($root != '.') {
			$toc = "$root/$toc";
		}
		$doc = new DOMDocument();
		if (@$doc->load("$base#$toc") === FALSE) {
			return htmlspecialchars($toc).' を読み込めません';
		}
		$xpath = new DOMXpath($doc);
		$xpath->registerNamespace('ncx', 'http://www.daisy.org/z3986/2005/ncx/');
		$navPoints = $xpath->evaluate("/ncx:ncx/ncx:navMap/ncx:navPoint/ncx:navLabel/ncx:text");
		$cnt = $navPoints->length;
		for ($i = 0; $i < $cnt; $i++) {
			$navPoint = $navPoints->item($i);
			$description .= "●".$navPoint->nodeValue."\n";
		}
	}
	return array(
			'bookid' => $bookid,
			'title' => $title,
			'author' => $author,
			'description' => $description
	);
}

/**
 * UUIDを生成
 *
 * @author     Anis uddin Ahmad <admin@ajaxray.com>
 * @param      string  an optional prefix
 * @return     string  the formatted uuid
 */
function pripre_uuid($prefix = '') {
	$chars = md5(uniqid(mt_rand(), true));
	$uuid = substr($chars, 0, 8) . '-';
	$uuid .= substr($chars, 8, 4) . '-';
	$uuid .= substr($chars, 12, 4) . '-';
	$uuid .= substr($chars, 16, 4) . '-';
	$uuid .= substr($chars, 20, 12);
	return $prefix . $uuid;
}

/**
 * ユニークIDを生成
 */
function pripre_uniqid() {
	return substr(uniqid(mt_rand(), true), 0, 16);
}

function pripre_generate_epub($params) {
	global $pripre_tate_filter_disable;

	$book_id = (int)$params['book_id'];
	$bind = $params['bind'];
	$extcss = $params['css'];
	$epubmeta = $params['epubmeta'];
	$etemplate = $params['etemplate'];
	$tidyhtml = $params['tidyhtml'];
	$svgtoimage = $params['svgtoimage'];
	$tate_filter = $params['tate_filter'];
	$title = get_cat_name($book_id);

	if (!$tate_filter) {
		$pripre_tate_filter_disable = TRUE;
	}
	if ($bind == 'right') {
		$bind = 'rtl';
	}
	else {
		$bind = 'ltr';
	}

	global $wpdb;
	$sql = "SELECT p.id,p.post_title,p.post_content
	FROM {$wpdb->prefix}term_relationships AS t
	INNER JOIN {$wpdb->prefix}posts AS p ON p.id=t.object_id
	WHERE t.term_taxonomy_id=$book_id";
	$entries = $wpdb->get_results($sql, ARRAY_A);
	$rows = $wpdb->get_results($sql, ARRAY_A);
	pripre_sort_bookposts($book_id, $rows);

	$tmpdir = pripre_get_tmp_dir();
	$dir = tempnam($tmpdir, "dir");
	unlink($dir);
	mkdir($dir);
	$epub = tempnam($tmpdir, "file");
	unlink($epub);

	// container.xml
	$data = <<< EOD
<?xml version="1.0" encoding="utf-8"?>
<container xmlns="urn:oasis:names:tc:opendocument:xmlns:container" version="1.0">
   <rootfiles>
      <rootfile full-path="item/standard.opf" media-type="application/oebps-package+xml"/>
   </rootfiles>
</container>
EOD;
	mkdir("$dir/META-INF");
	file_put_contents("$dir/META-INF/container.xml", $data);

	$uuid = "urn:uuid:".pripre_uuid();
	$modified = date('Y-m-d\Th:i:s\Z');

	global $more;
	$more = 1;

	$rsrcs = array();

	// content.opf
	mkdir("$dir/item");
	mkdir("$dir/item/xhtml");
	mkdir("$dir/item/style");
	mkdir("$dir/item/image");
	$fp = fopen("$dir/item/standard.opf", "w");
	if (empty($epubmeta)) {
		$epubmeta = <<< EOD
   <metadata xmlns:dc="http://purl.org/dc/elements/1.1/">
      <dc:language>ja</dc:language>
      <dc:identifier id="BookID">$uuid</dc:identifier>
      <dc:title>$title</dc:title>
      <meta property="dcterms:modified">$modified</meta>
   </metadata>

EOD;
	}
	else {
		$epubmeta = str_replace('@TITLE@', $title, $epubmeta);
		$epubmeta = str_replace('@UUID@', $uuid, $epubmeta);
		$epubmeta = str_replace('@MODIFIED@', $modified, $epubmeta);
	}
	$data = <<< EOD
<?xml version="1.0" encoding="utf-8"?>
<package xmlns="http://www.idpf.org/2007/opf" version="3.0" unique-identifier="BookID">
   $epubmeta
   <manifest>
      <item id="nav" href="navigation-documents.xhtml" properties="nav" media-type="application/xhtml+xml"/>
      <item id="ncx" href="navigation-documents.ncx" media-type="application/x-dtbncx+xml"/>
      <item id="common-css" href="style/common.css" media-type="text/css"/>

EOD;
	fwrite($fp, $data);

	if (!empty($etemplate)) {
		$data = <<< EOD
      <item id="style-css" href="style/style.css" media-type="text/css"/>

EOD;
		fwrite($fp, $data);
	}

	if (!empty($extcss)) {
		$data = <<< EOD
      <item id="ext-css" href="style/ext.css" media-type="text/css"/>

EOD;
		fwrite($fp, $data);
	}

	function _endsWith($haystack, $needle) {
		$start = - strlen($needle);
		return (substr($haystack, $start) === $needle);
	}

	$first_image = TRUE;
	foreach ($rows as $key => $row) {
		$rows[$key]['post_title'] = pripre_text_filter($row['post_title']);
		$id = $row['id'];
		if (!$svgtoimage && preg_match('/src=\\"([^\\"]*)\\"/', $row['post_content'])) {
			$props = ' properties="svg"';
		}
		else {
			$props = '';
		}
		$data = <<< EOD
      <item id="t$id" href="xhtml/$id.xhtml" media-type="application/xhtml+xml"$props/>

EOD;
		fwrite($fp, $data);

		$content = $row['post_content'];
		$regs = array();
		preg_match_all ('/src=\\"([^\\"]*)\\"/', $content, $regs, PREG_PATTERN_ORDER);
		foreach ($regs[1] as $src) {
			$slash = mb_strrpos($src, '/');
			if ($slash !== FALSE) {
				$name = mb_substr($src, $slash + 1);
			}
			else {
				$name = $src;
			}
			$mime = "image/jpeg";
			if (_endsWith($name, ".svg") || _endsWith($name, ".svgz")) {
				if ($svgtoimage) {
					$mime = "image/png";
					$name = substr($name, 0, strlen($name) - 4).'.png';
				}
				else {
					$mime = "image/svg+xml";
				}
			}
			else if (_endsWith($name, ".png")) {
				$mime = "image/png";
			}
			$rsrcs[$src] = $name;
			if ($first_image) {
				$props = ' properties="cover-image"';
				$first_image = FALSE;
			}
			else {
				$props = '';
			}
			$data = <<< EOD
      <item id="r-$name" href="image/$name" media-type="$mime"$props/>

EOD;
			fwrite($fp, $data);
		}
		$row['post_content'] = preg_replace('/(src=\\".*)(\\.svg)(\\")/',
				'$1.png$3',
				$row['post_content']);
	}

	$data = <<< EOD
   </manifest>
   <spine toc="ncx" page-progression-direction="$bind">

EOD;
	fwrite($fp, $data);

	foreach ($rows as $row) {
		$id = $row['id'];
		$data = <<< EOD
      <itemref idref="t$id"/>

EOD;
		fwrite($fp, $data);
	}

	$data = <<< EOD
   </spine>
</package>
EOD;
	fwrite($fp, $data);
	fclose($fp);

	// EPUB3 TOC
	$fp = fopen("$dir/item/navigation-documents.xhtml", "w");
	$data = <<< EOD
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html>
<html lang="ja"
      xmlns="http://www.w3.org/1999/xhtml"
      xmlns:epub="http://www.idpf.org/2007/ops">
  <head>
    <meta charset="UTF-8" />
    <meta name="generator" content="PriPre" />
    <title>$title</title>
  </head>
  <body>
    <nav epub:type="toc" id="toc">
      <ol>

EOD;
	fwrite($fp, $data);

	$i = 0;
	foreach ($rows as $row) {
		$id = $row['id'];
		$post_title = $row['post_title'];
		++$i;
		$data = <<< EOD
        <li><a href="xhtml/$id.xhtml">$post_title</a></li>

EOD;
		fwrite($fp, $data);
	}

	$data = <<< EOD
      </ol>
    </nav>
  </body>
</html>
EOD;
	fwrite($fp, $data);
	fclose($fp);

	// EPUB2 NCX
	$fp = fopen("$dir/item/navigation-documents.ncx", "w");
	$data = <<< EOD
<?xml version="1.0" encoding="utf-8"?>
<ncx xmlns="http://www.daisy.org/z3986/2005/ncx/" version="2005-1">
  <head>
    <meta name="dtb:uid" content="$uuid"/>
    <meta name="dtb:depth" content="1"/>
    <meta name="dtb:totalPageCount" content="0"/>
    <meta name="dtb:maxPageNumber" content="0"/>
  </head>
  <docTitle>
    <text>$title</text>
  </docTitle>
  <navMap>

EOD;
	fwrite($fp, $data);

	$i = 0;
	foreach ($rows as $row) {
		$id = $row['id'];
		$post_title = $row['post_title'];
		$post_title = mb_ereg_replace('<[^>]+>', '', $post_title);
		++$i;
		$data = <<< EOD
	<navPoint id="$id" playOrder="$i">
	  <navLabel>
        <text>$post_title</text>
      </navLabel>
      <content src="xhtml/$id.xhtml"/>
    </navPoint>

EOD;
		fwrite($fp, $data);
	}

	$data = <<< EOD
 </navMap>
</ncx>
EOD;
	fwrite($fp, $data);
	fclose($fp);

	// 共通CSS
	$basedir = pripre_get_base_dir();
	copy("$basedir/templates/common.css", "$dir/item/style/common.css");
	if (!empty($etemplate)) {
		copy(pripre_get_estyle_dir($etemplate)."/style.css", "$dir/item/style/style.css");
	}
	if (!empty($extcss)) {
		file_put_contents("$dir/item/style/ext.css", $extcss);
	}

	// リソース
	foreach ($rsrcs as $src => $name) {
		$file = "$dir/item/image/$name";
		@mkdir(dirname($file), 0777, true);
		if (substr($src, 0, 1) == '/') {
			$src = get_site_url().$src;
		}
		if ($svgtoimage && (_endsWith($src, ".svg") || _endsWith($src, ".svgz"))) {
			pripre_svg_to_png($src, $file);
		}
		else {
			copy($src, $file);
		}
	}

	// 本文
	$html_head = <<< EOD
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml"
      lang="ja">
  <head>
    <meta charset="UTF-8" />
    <meta name="generator" content="PriPre" />
    <link rel="stylesheet" href="../style/common.css" type="text/css" />

EOD;

	if (!empty($etemplate)) {
		$html_head .= <<< EOD
    <link rel="stylesheet" href="../style/style.css" type="text/css" />

EOD;
	}

	foreach ($rows as $row) {
		$id = $row['id'];
		$title = $row['post_title'];
		$content = $row['post_content'];
		
		// 画像のアドレスを変換
		foreach ($rsrcs as $src => $name) {
			$content = str_replace(" src=\"$src\"", " src=\"../image/$name\"", $content);
		}
		
		// リンクのアドレスを変換
		foreach ($rows as $row2) {
			$id2 = $row2['id'];
			$content = str_replace(" href=\"?p={$id2}#", " href=\"$id2.xhtml#", $content);
		}
		
		$user_style = pripre_get_postparam($id, 'user_style');
		$user_style_data = pripre_get_user_style($user_style);
		if (empty($etemplate)) {
			$template = pripre_get_bookpostparam($book_id, $id, 'style');
			if (empty($template)) {
				$template = pripre_get_postparam($id, 'style');
				if (empty($template)) {
					$template = $pripre_default_style;
				}
			}
			$style = str_replace('/', '-', $template);
			$template = "book/$template";
			ob_start();
			include ("$basedir/templates/$template/style.css");
			echo $user_style_data['css'];
			$css = ob_get_contents();
			ob_end_clean();
		}
		else {
			$css = $user_style_data['css'];
			$style = $etemplate;
			$template = "ebook/$etemplate";
		}

		$hide_title = pripre_get_postparam($id, 'hide_title');
		if ($hide_title == '1') {
			$css .= "\nh1 { visibility: hidden; height: 0; width: 0; margin: 0; white-space: nowrap;}";
		}

		$hide_thumbnail = pripre_get_postparam($id, 'hide_thumbnail');
		if ($hide_thumbnail == '1') {
			$css .= "\n.attachment-post-thumbnail { display: none;}";
		}
		
		$extra_css = pripre_get_postparam($id, 'extra_css');
		if (!empty($extra_css)) {
			$css .= $extra_css;
		}
		
		ob_start();
		include ("$basedir/templates/$template/entry.php");
		$entry = ob_get_contents();
		ob_end_clean();

		$data = $html_head;
		if (!empty($css)) {
			$data .= <<< EOD
    <style type="text/css">
$css
	</style>

EOD;
		}

		if (!empty($extcss)) {
			$data .= <<< EOD
    <link rel="stylesheet" href="../style/ext.css" type="text/css" />
		
EOD;
		}
		
		$doc_title = mb_ereg_replace('<[^>]+>', '', $title);
		$data .= <<< EOD
    <title>$doc_title</title>
  </head>
  <body id="h$id" class="$style">
$entry
  </body>
</html>
EOD;
		if ($tidyhtml) {
			$domDocument = new DOMDocument();
			$domDocument->loadHTML(mb_convert_encoding($data, 'HTML-ENTITIES', 'UTF-8'));
			$data = $domDocument->saveXML();
			$data = str_replace('<?xml version="1.0" encoding="UTF-8"??>', '', $data);
			$rev = function ($matches) {
				return mb_convert_encoding($matches[0], 'UTF-8', 'HTML-ENTITIES');
			};
			$data = preg_replace_callback('/(?:&#\d++;)++/', $rev, $data);
		}
		file_put_contents("$dir/item/xhtml/$id.xhtml", $data);
	}

	// mimetype application/epub+zip を含むスタブ
	file_put_contents($epub, base64_decode("UEsDBBQACAAAAHCorEAAAAAAFAAAABQAAAAIAAAAbWltZXR5cGVhcHBsaWNhdGlvbi9lcHViK3ppcFBLBwhvYassFAAAABQAAABQSwECFAMUAAgAAABwqKxAb2GrLBQAAAAUAAAACAAAAAAAAAABAAAA5IEAAAAAbWltZXR5cGVQSwUGAAAAAAEAAQA2AAAASgAAAAAA"));
	$zip = new ZipArchive();
	$zip->open($epub);
	function addDirectory($prefix, $zip, $dir) {
		foreach(glob("$dir/*") as $file) {
			if(is_dir($file))
				addDirectory($prefix, $zip, $file);
			else
				$zip->addFile($file, substr($file, $prefix));
		}
	}
	addDirectory(strlen($dir) + 1, $zip, $dir);
	$zip->close();
	pripre_rmr($dir);
	return $epub;
}

/**
 * 頒布EPUBを追加
 *
 * @param string $id
 * @param string $file
 */
function pripre_dist_add_epub($id, $file) {
	global $wpdb;

	$sql = $wpdb->prepare("SELECT d.writing_mode
			FROM {$wpdb->prefix}pripre_dist AS d
			WHERE d.id=%s", $id);
	$row = $wpdb->get_row($sql, ARRAY_A);

	$copper = pripre_get_copper();
	$copper->set_output_as_variable($pdf);
	$copper->property('output.page-width', '148mm');
	$copper->property('output.page-height', '210mm');
	$copper->property('input.default-stylesheet', 'file:///default.css');

	global $page;
	function message ($code, $message, $args){
		global $page;
		if ($code == 0x1801) {
			$page = $args[0];
		}
	}
	$copper->set_message_func('message');

	switch($row['writing_mode']) {
		case 1:
		case 3:
			$writing_mode = '-epub-writing-mode: horizontal-tb;';
			break;

		case 2:
		case 4:
			$writing_mode = '-epub-writing-mode: vertical-rl;';
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
	readfile($file);
	$copper->end_main();
	$copper->close();

	$sql = $wpdb->prepare("SELECT MAX(version) AS version
			FROM {$wpdb->prefix}pripre_dist_version
			WHERE id=%s", $id);
	$version = $wpdb->get_row($sql, ARRAY_A);
	$version = $version['version'] + 1;

	$wpdb->insert($wpdb->prefix . "pripre_dist_version",
			array('id' => $id, 'version' => $version),
			array('%s', '%d'));
	$epub = file_get_contents($file);

	$sql = $wpdb->prepare("UPDATE ".$wpdb->prefix."pripre_dist_version
			SET data=%s,pdf=%s,page=%d
			WHERE id=%s AND version=%d", array($epub, $pdf, $page, $id, $version));
	$wpdb->query($sql);
}

?>
