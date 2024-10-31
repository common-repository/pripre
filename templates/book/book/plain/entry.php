<?php
	$title = pripre_text_filter($title);
	$content = apply_filters('the_content', $content);
?>
<h1><?php echo $title; ?></h1>
<?php the_post_thumbnail(); ?>
<?php echo $content; ?>