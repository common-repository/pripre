<?php
$user_style = $_POST['user_style'];
$hide_title = $_POST['hide_title'];
$hide_thumbnail = $_POST['hide_thumbnail'];
$page_break = $_POST['page_break'];
$extra_css = $_POST['extra_css'];

pripre_set_postparam($post_id, 'user_style', $user_style);
pripre_set_postparam($post_id, 'hide_title', $hide_title);
pripre_set_postparam($post_id, 'hide_thumbnail', $hide_thumbnail);
pripre_set_postparam($post_id, 'page_break', $page_break);
pripre_set_postparam($post_id, 'extra_css', $extra_css);

$user_style_data = pripre_get_user_style($user_style);
?>