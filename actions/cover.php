<?php
require_once ('../../../../wp-load.php');
require_once ('../includes/utils.inc.php');

if ( !current_user_can('edit_pages') ) {
	wp_die( __('You are not allowed to edit this item.') );
}

mb_regex_encoding('UTF-8');
mb_internal_encoding("UTF-8");
$bind = $_POST['bind'];
$spine = $_POST['spine'];
$marks = !empty($_POST['marks']);
$size = $_POST['size'];
$type = $_POST['type'];
$isbn = $_POST['isbn'];
$book_code = $_POST['book_code'];
$price = (int)$_POST['price'];

$mime = "image/svg+xml";

$isbn_13 = str_replace('-', '', $isbn);
$price_5 = sprintf("%05d", $price);

header("Content-Type:  $mime");
header("Content-Disposition:  attachment; filename=cover.svg");

$copper = pripre_get_copper();
$copper->property("output.type", $mime);
$copper->property("output.image.resolution", "300");

preg_match("/^([0-9]+)x([0-9]+)$/", $size, $matches);
$w = $matches[1];
$h = $matches[2];

$pageWidth = ($w + $spine + $w);
$pageHeight = $h;
if ($marks) {
	$copper->property("output.marks.spine-width", $spine."mm");
	$copper->property("output.marks", "both");
	$offset = -3;
}
else {
	$offset = 0;
	$pageWidth += 6;
	$pageHeight += 6;
}
$copper->property("output.page-width", $pageWidth."mm");
$copper->property("output.page-height", $pageHeight."mm");

$copper->start_main(".", array('encoding' => 'UTF-8'));
?>
<html>
  <head>
    <style>
@page {
	margin: 0;
}
body {
	position: relative;
	margin: 0mm;
	left: <?php echo $offset ?>mm;
	top: <?php echo $offset ?>mm;
	width: <?php echo (3 + $w + $spine + $w + 3); ?>mm;
	height: <?php echo (3 + $h + 3); ?>mm;
}
div.left {
	position: absolute;
	background-color: Gray;
	top: 0;
	left: 0;
	width: <?php echo (3 + $w); ?>mm;
	height: <?php echo (3 + $h + 3); ?>mm;
}
div.spine {
	position: absolute;
	background-color: White;
	top: 0;
	left: <?php echo (3 + $w); ?>mm;
	width: <?php echo $spine; ?>mm;
	height: <?php echo (3 + $h + 3); ?>mm;
}
div.right {
	position: absolute;
	background-color: Gray;
	top: 0;
	left: <?php echo (3 + $w + $spine); ?>mm;
	width: <?php echo ($w + 3); ?>mm;
	height: <?php echo (3 + $h + 3); ?>mm;
}
div.barcodebox {
	position: absolute;
	top: 3mm;
	<?php
	if ($bind == 'left') {
		echo "right: ".(3 + $w + $spine + 2)."mm;";
	}
	else {
		echo "left: ".(3 + $w + $spine + 2)."mm;";
	}
	?>
}
barcode {
	display: block;
	font-family: 'ocrb10';
}
div.padding {
	height: 6.37mm;
}
div.box {
	padding: 5mm;
	background-color: White;
}
div.wrapper {
	padding: 5mm;
	<?php
	if ($bind == 'left') {
		echo "float: right;";
	}
	else {
		echo "float: left;";
	}
	?>
}
div.human-readable {
	margin-top: 10mm;
	font-size: 9pt;
	font-family: monospace;
	white-space: nowrap;
	<?php
	if ($type == 'B') {
	    echo "margin-left: 10mm; margin-right: 10mm; clear: both;";
	}
	else if ($type == 'D') {
	    echo "margin-left: 10mm; margin-right: 10mm; clear: both;";
	    if ($bind == 'left') {
	    	echo "text-align: right;";
	    }
	}
	?>
}
</style>
  </head>
  <body xmlns:bc="http://barcode4j.krysalis.org/ns">
<div class="left"></div>
<div class="spine"></div>
<div class="right"></div>

<div class="barcodebox">
<div class="wrapper">
<div class="box">
<bc:barcode message="<?php echo $isbn_13; ?>">
  <bc:isbn>
      <bc:height>14mm</bc:height>
      <bc:module-width>0.33mm</bc:module-width>
      <bc:quiet-zone enabled="false"/>
      <bc:human-readable>
        <bc:placement>bottom</bc:placement>
        <bc:font-size>3.63mm</bc:font-size>
      </bc:human-readable>
  </bc:isbn>
</bc:barcode>
<div class="padding"></div>
<bc:barcode message="192<?php echo $book_code.$price_5; ?>">
  <bc:isbn>
      <bc:height>14mm</bc:height>
      <bc:module-width>0.33mm</bc:module-width>
      <bc:quiet-zone enabled="false"/>
      <bc:checksum>add</bc:checksum>
      <bc:human-readable>
        <bc:placement>bottom</bc:placement>
        <bc:font-size>3.63mm</bc:font-size>
      </bc:human-readable>
  </bc:isbn>
</bc:barcode>
</div>
</div>
<div class="human-readable">
ISBN<?php echo $isbn; ?>
<?php if ($type == 'A' || $type == 'B') echo "<br/>"; else echo " "; ?>
C<?php echo $book_code; ?> &yen;<?php echo $price; ?>E
</div>
</div>
    </body>
</html>
<?php
$copper->end_main();

$copper->close();
?>
