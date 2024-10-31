<?php
// 投稿画面の印刷プレビュー
function pripre_post_style_box($post, $box) {
    require_once ('utils.inc.php');
    global $pripre_default_style;

    $entry_style = pripre_get_postparam($post->ID, 'style');
    if (empty($entry_style)) {
        $entry_style = $pripre_default_style;
    }
    
    $name = '';
    $templates = pripre_get_styles();
    echo '<label>スタイル<select name="template" id="pripre_template">';
    foreach($templates as $id => $package) {
    	foreach($package['styles'] as $style) {
        if ($name != $package['name']) {
        	if (!empty($name)) {
        		echo '</optgroup>';
        	}
         	$name = $package['name'];
        	echo '<optgroup label="'.$name.'">';
        }
        echo '<option value="'.$style['id'].'"';
        if ($style['id'] == $entry_style) {
            echo ' selected="selected"';
        }
        echo '>'.$style['name'].'</option>';
    	}
    }
    echo '</optgroup></select></label>';
    echo '<a href="" target="_blank" id="pripre_site"></a>';
    
    $user_style = pripre_get_postparam($post->ID, 'user_style');
    $user_style_data = pripre_get_user_style($user_style);
    $user_styles = pripre_get_user_styles();
    echo '<div><label><span>ユーザースタイル </span><select name="user_style" id="pripre_user_style">';
    echo '<option value="">-- なし --</option>';
    foreach($user_styles as $id => $name) {
    	echo "<option value=\"$id\"";
    	if ($id == $user_style) {
    		echo ' selected="selected"';
    	}
    	echo ">".htmlspecialchars($name)."</option>";
    }
    echo '</select></label></div>';

    $entry_hide_title = pripre_get_postparam($post->ID, 'hide_title');
    echo '<div><label><input type="checkbox" name="hide_title" value="1" id="pripre_hide_title"';
    if ($entry_hide_title) {
    	echo ' checked="checked"';
    }
    echo '/>タイトル非表示</label></div>';

    $entry_hide_thumbnail = pripre_get_postparam($post->ID, 'hide_thumbnail');
    echo '<div><label><input type="checkbox" name="hide_thumbnail" value="1" id="pripre_hide_thumbnail"';
    if ($entry_hide_thumbnail) {
    	echo ' checked="checked"';
    }
    echo '/>アイキャッチ画像非表示</label></div>';
    
    $entry_page_break = pripre_get_postparam($post->ID, 'page_break');
    echo '<div><label>開始ページ</label><select name="page_break" id="pripre_page_break">
      <option value="">指定なし</option>
      <option value="right"';
    if ($entry_page_break == 'right') {
    	echo ' selected="selected"';
    }
    echo'>右</option>
      <option value="left"';
    if ($entry_page_break == 'left') {
    	echo ' selected="selected"';
    }
    echo '>左</option>
    </select></div>';
    
    $entry_extra_css = pripre_get_postparam($post->ID, 'extra_css');
    echo '<div>
   	<label>追加CSS<br/>
   	<textarea name="extra_css" id="pripre_extra_css">'.htmlspecialchars($entry_extra_css).'</textarea></label>
    </div>';
    
    echo '<div id="pripre_preview_pdf"></div>';
    echo '<input type="button" value="プレビュー" onClick="pripre_preview_images();"/>';
    echo '<input type="button" value="PDF" onClick="pripre_preview_pdf();"/>';
    echo '<input type="button" value="HTML" onClick="pripre_preview_html();"/>';
    echo '<input type="button" value="XML" onClick="pripre_preview_xml();"/>';
    
    echo '<script type="text/javascript">';
    echo 'var pripre_base = "'.plugins_url('pripre').'";
    var pripre_auto_preview = "'.get_option("pripre_auto_preview").'";
    var pripre_post_id = "'.$post->ID.'";
    var pripre_template_params = {';
    foreach($templates as $id => $package) {
    	foreach($package['styles'] as $style) {
	    	echo '"'.$style['id'].'":{';
	    	echo '"page-progression":"'.$style['page-progression'].'",';
	    	echo '"width":"'.$user_style_data['width'].'",';
	    	echo '"height":"'.$user_style_data['height'].'",';
	    	echo '"site":"'.$style['site'].'",';
	    	echo '},';
    	}
    }
    echo '};';
    echo '</script>';
    echo '<script type="text/javascript" src="' . plugins_url('pripre/js/edit.js') . '"></script>';

    $site = parse_url(get_option('home'));
    echo '<div id="pripre_preview"><input type="button" value="&laquo;" id="pripre_go_leftend"/>';
    echo '<input type="button" value="&lsaquo;" id="pripre_go_left"/>';
    echo '<span id="pripre_page"></span> ';
    echo '<input type="button" value="&rsaquo;" id="pripre_go_right"/>';
    echo '<input type="button" value="&raquo;" id="pripre_go_rightend"/></div>';
    echo '<div id="pripre_image_wrapper">';
    echo '<img src="" id="pripre_image"/>';
    echo '</div>';
}
?>
