<!DOCTYPE html>
<?php
require_once ('../../../../wp-load.php');
require_once ('../includes/utils.inc.php');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml"  dir="ltr" lang="ja">
<style type="text/css">
@page {
	margin: 0;
}
body {
	margin: 0;
	background-color: LightGray;
	font-size: 12pt;
}
img {
	border: 1px solid Black;
}
input {
	background-color: white;
	border: 1px solid Gray;
	border-radius: 3px;
	height: 22px;
}
</style>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Preview</title>
</head>
<body>
  <div style="text-align: center;">
  <input type="button" value="&laquo;" id="pripre_go_leftend"/>
  <input type="button" value="&lsaquo;" id="pripre_go_left"/>
  <span id="pripre_page"></span>
  <input type="button" value="&rsaquo;" id="pripre_go_right"/>
  <input type="button" value="&raquo;" id="pripre_go_rightend"/>
  </div>
  <img id="pripre_image" src=""/>
</body>
</html>