<?php

/*
  Plugin Name: PriPre My Style
  Plugin URI: http://zamasoft.net/pripre
  Description: My description
  Version: 1.0.0
  Author: My Name
  Author URI: my URI
  License: Apache License 2.0
 */
function my_style_groups($dirs) {
	$dirs['my_style_group_id'] = dirname(__FILE__).'/book';
	return $dirs;
}
add_filter('pripre_style_groups', 'my_style_groups');

function my_estyles($dirs) {
	$dirs['my_estyle_id'] = dirname(__FILE__).'/ebook';
	return $dirs;
}
add_filter('pripre_estyles', 'my_estyles');

?>