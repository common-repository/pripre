<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"/>
<title><?php echo $title; ?></title>
<style type="text/css">
    @import 'common.css';
    @import 'style.css';
    @import 'ext.css';
    <?php
    // タイトルの消去
    if ($hide_title == '1') { ?>
    h1 { visibility: hidden; height: 0; width: 0; margin: 0; white-space: nowrap; }
    <?php }
    // アイキャッチ画像の消去
    if ($hide_thumbnail == '1') { ?>
    .attachment-post-thumbnail { display: none; }
    <?php }
    // 改ページ
    if ($page_break == 'left') { ?>
    body { page-break-before: if-right; }
    <?php }
    if ($page_break == 'right') { ?>
    body { page-break-before: if-left; }
    <?php }
    if (!empty($extra_css)) {
    	echo $extra_css;
     } ?>
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