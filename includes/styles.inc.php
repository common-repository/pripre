<?php
function pripre_styles() {
	?>
    <div class="wrap">
        <h2 style="float: left;">ユーザースタイル</h2>
        <a href="http://zamasoft.net/2012/04/23/style/" target="_blank"><br/>ヘルプ</a>
        <br style="clear: both;"/>
    <?php
	add_meta_box('pripre_styles_box', __('スタイル', 'pripre_textdomain'), 'pripre_styles_box', 'pripre_styles', 'normal');
    if (!empty($_GET['style']) || !empty($_GET['new_name'])) {
        add_meta_box('pripre_styles_style_box', __('設定', 'pripre_textdomain'), 'pripre_styles_style_box', 'pripre_styles', 'normal');
    }
    do_meta_boxes('pripre_styles', 'normal', '');
    ?>
    </div>
    <?php
}

function pripre_styles_box() {
	require_once ('utils.inc.php');

    if (!empty($_GET['new_name'])) {
    	$_GET['style'] = pripre_add_user_style($_GET['new_name']);
    }
	$styles = pripre_get_user_styles();
	?>
<form method="get">
	<input type="hidden" name="page" value="pripre_styles"/>
	<p>設定するスタイルを選択してください。</p>
    <label><span>スタイル </span><select name="style" onChange="form.submit();">
        <option value="">-- 選択してください --</option>
    <?php foreach($styles as $id => $name) { ?>
        <option value="<?php echo $id; ?>"<?php if ($id == $_GET['style']) { echo ' selected="selected"'; }; ?>><?php echo htmlspecialchars($name); ?></option>
    <?php } ?>
    </select></label>
    <label>名前</label><input type="text" name="new_name" value=""/></label>
    <button type="submit">新規</button>
</form>
<?php
}

function pripre_styles_style_box() {
    require_once ('utils.inc.php');

    $style = $_GET['style'];
    
    if (isset($_POST['name'])) {
    	pripre_update_user_style($style, $_POST['name'], $_POST['size'], $_POST['css']);
    }

    $data = pripre_get_user_style($style);
    $name = $data['name'];
    $size = $data['size'];
    $css = $data['css'];
?>
<form action="?page=pripre_styles&amp;style=<?php echo $style ?>" method="post">
    <table class="form-table">
        <tr valign="top">
            <th>名前</th>
            <td><input name="name" value="<?php echo htmlspecialchars($name); ?>" /></td>
        </tr>
    	<tr valign="top">
            <th>サイズ</th>
            <td><label><select name="size">
              <option value="210x297">A4</option>
              <option value="127x188"<?php if ($size == '127x188'){ ?> selected="selected"<?php } ?>>四六判</option>
              <option value="128x182"<?php if ($size == '128x182'){ ?> selected="selected"<?php } ?>>B6判</option>
              <option value="148x210"<?php if ($size == '148x210'){ ?> selected="selected"<?php } ?>>A5判（教科書）</option>
              <option value="105x148"<?php if ($size == '105x148'){ ?> selected="selected"<?php } ?>>A6判（文庫）</option>
              <option value="103x182"<?php if ($size == '103x182'){ ?> selected="selected"<?php } ?>>新書判</option>
            </select></label></td>
        </tr>
        <tr valign="top">
            <th>追加CSS</th>
            <td><textarea name="css" cols="80" rows="12"><?php echo htmlspecialchars($css); ?></textarea></td>
        </tr>
    </table>
    <input type="submit" name="button" class="button-secondary" value="適用"  />
</form>
<?php
}
?>
