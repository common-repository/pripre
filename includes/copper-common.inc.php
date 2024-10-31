<?php
$properties = get_option("pripre_properties");
$properties = explode("\n", $properties);
foreach($properties as $prop) {
	if (!empty($prop)) {
		$equal = mb_strpos($prop, '=');
		if ($equal != FALSE) {
			$name = trim(mb_substr($prop, 0, $equal));
			$value = trim(mb_substr($prop, $equal + 1));
			$copper->property($name, $value);
		}
	}
}

$copper->start_resource(site_url()."/common.css", array('encoding' => 'UTF-8'));
include (pripre_get_base_dir()."/templates/common.css");
$copper->end_resource();

if (!empty($css)) {	
	$copper->start_resource(site_url()."/ext.css", array('encoding' => 'UTF-8'));
	echo $css;
	$copper->end_resource();
}
?>
