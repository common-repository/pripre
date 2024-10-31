<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<html>
<head>
<meta charset="UTF-8"/>
<title><?php echo $title; ?></title>
<style type="text/css">
    @import 'common.css';
    @import 'style.css';
    @import 'ext.css';
</style>
<?php
global $pripre_ebook_margin;

if (!empty($pripre_ebook_margin)) {
?>
<style type="text/css">
@page {
  margin:
  <?php
    switch($pripre_ebook_margin) {
    	case 'e':
    		echo '0';
    		break;
    	case 2:
    		echo '8mm';
    		break;
    }
  ?>;
  }
  </style>
<?php } ?>
</head>
<body id="h<?php echo $post_id; ?>" class="<?php echo str_replace('/', '-', $style); ?>">
<?php include($contents_template); ?>
</body>
</html>