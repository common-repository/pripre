<?php
	$title = pripre_text_filter($title);
    $content = apply_filters('the_content', $content);
?>
<span id="left-nombre"><span class="page"></span><span class="heading"></span></span>
<span id="right-nombre"><span class="heading"></span><span class="page"></span></span>
<h1><?php echo $title; ?></h1>
<?php the_post_thumbnail(); ?>
<?php echo $content; ?>