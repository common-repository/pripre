<?php
function pripre_publish_tool() {
    ?>
    <div class="wrap">
        <h2 style="float: left;">出版ツール</h2>
        <a href="http://zamasoft.net/2012/04/23/publish/" target="_blank"><br/>ヘルプ</a>
        <br style="clear: both;"/>
    <?php
	add_meta_box('pripre_publish_books_box', __('カテゴリと本', 'pripre_textdomain'), 'pripre_publish_books_box', 'pripre_publish', 'normal');
    if (!empty($_GET['book_id'])) {
        add_meta_box('pripre_publish_book_box', __('本', 'pripre_textdomain'), 'pripre_publish_book_box', 'pripre_publish', 'normal');
    }
    
    do_meta_boxes('pripre_publish', 'normal', '');
    ?>
    </div>
    <?php
}

function pripre_publish_books_box() {
    require_once ('utils.inc.php');

    global $wpdb;
    $sql = "SELECT b.id,b.book_date
            FROM {$wpdb->prefix}pripre_book AS b
            ORDER BY b.book_date DESC;";
    $rows = $wpdb->get_results($sql, ARRAY_A);
    $books = array();
    foreach($rows as $row) {
    	$books[] = $row['id'];
    }
    $cats = get_all_category_ids();
    $cats = array_diff($cats, $books);
    if (!empty($cats)) { ?>
<form action="<?php echo plugins_url('pripre/actions/add-book.php');?>" method="post" class="pripre_publish">
	<p>記事をカテゴリーごとに本にすることができます。</p>
    <label><span>本にしていないカテゴリー </span><select name="category">
    <?php foreach($cats as $cat) { ?>
        <option value="<?php echo $cat; ?>"><?php echo get_cat_name($cat); ?></option>
    <?php } ?>
    </select></label>
    <input type="submit" name="button" class="button-secondary" value="本にする"  />
</form>
<?php }
	if (!empty($rows)) { ?>
<form method="get" class="pripre_publish">
    <input type="hidden" name="page" value="pripre_publish_tool"/>
	<label><span>本にしたカテゴリー </span><select name="book_id" onChange="form.submit();">
	<option value="">-- 選択してください --</option>
    <?php
    foreach($rows as $row) {
    ?>
    <option value="<?php echo $row['id']; ?>"
    <?php if (isset($_GET['book_id']) && $_GET['book_id'] == $row['id']) { ?>
    selected="selected"
    <?php } ?>><?php echo get_cat_name($row['id']); ?></option>
    <?php
    }
    ?>
    </select></label>
</form>
<?php
	}
}

function pripre_publish_book_box() {
    require_once ('utils.inc.php');
    global $pripre_default_style;
    
    $book_id = (int)$_GET['book_id'];

    global $wpdb;

    $bind = pripre_get_bookparam($book_id, 'bind');
    $size = pripre_get_bookparam($book_id, 'size');
    $color = pripre_get_bookparam($book_id, 'color');
    $css = pripre_get_bookparam($book_id, 'css');
    $epubmeta = pripre_get_bookparam($book_id, 'epubmeta');
    $etamplate = pripre_get_bookparam($book_id, 'etemplate');
    
    $estyles = pripre_get_estyles();
    
    ?>
    <script type="text/javascript" src="<?php echo plugins_url('pripre/js/publish.js'); ?>"></script>
    <script type="text/javascript">var pripre_base = "<?php echo plugins_url('pripre'); ?>";</script>
    <form action="" method="post" target="_blank">
        <input id="pripre_book_id" type="hidden" name="book_id" value="<?php echo $book_id; ?>" />
        <h4>オプション</h4>
        <table class="form-table">
            <tr valign="top">
                <th>綴じ方</th>
                <td><label><input id="pripre_bind_left" type="radio" name="bind" value="left"<?php if (empty($bind) || $bind == 'left') echo ' checked="checked"';?> onChange="pripre_update();"/>左綴じ</label>
                <label><input id="pripre_bind_right" type="radio" name="bind" value="right"<?php if ($bind == 'right') echo ' checked="checked"';?> onChange="pripre_update();"/>右綴じ</label></td>
            </tr>
            <tr valign="top">
                <th>追加CSS
                <a href="http://zamasoft.net/2012/04/23/publish/" target="_blank">説明</a></th>
                <td><textarea name="css" cols="60" rows="6" onChange="pripre_update();" id="pripre_css"><?php echo htmlspecialchars($css); ?></textarea></td>
            </tr>
        </table>
        
        <h4>PDF</h4>
        <table class="form-table">
            <tr valign="top">
                <th>サイズ</th>
                <td><label><select id="pripre_size" name="size" onChange="pripre_update();">
                  <option value="210x297">A4</option>
                  <option value="127x188"<?php if ($size == '127x188'){ ?> selected="selected"<?php } ?>>四六判</option>
                  <option value="128x182"<?php if ($size == '128x182'){ ?> selected="selected"<?php } ?>>B6判</option>
                  <option value="148x210"<?php if ($size == '148x210'){ ?> selected="selected"<?php } ?>>A5判（教科書）</option>
                  <option value="105x148"<?php if ($size == '105x148'){ ?> selected="selected"<?php } ?>>A6判（文庫）</option>
                  <option value="103x182"<?php if ($size == '103x182'){ ?> selected="selected"<?php } ?>>新書判</option>
                </select></label></td>
            </tr>
            <tr valign="top">
                <th>カラー</th>
                <td>
                  <label><input type="radio" name="color" value="0" onChange="pripre_update();"<?php if (empty($color)) { ?> checked="checked"<?php } ?>/> グレースケール</label>
                  <label><input id="pripre_color" type="radio" name="color" value="1" onChange="pripre_update();"<?php if ($color == 1) { ?> checked="checked"<?php } ?>/> フルカラー</label>
                </td>
            </tr>
        </table>
        <p class="pripre-submit">
            <input type="submit" name="submit" class="button-secondary"
            onclick="form.action='<?php echo plugins_url('pripre/actions/publish-pdf.php/book.pdf'); ?>'; return 1;"
            value="PDFをダウンロード"/>
            <br/>
            <a href="http://zamasoft.net/2012/05/12/print/" target="_blank">説明</a>
            <input type="submit" name="submit" class="button-secondary"
            onclick="form.action='<?php echo plugins_url('pripre/actions/publish-print.cssj.jp.php'); ?>'; return 1;"
            value="ブログ出版局で製本"/>
        </p>
        
        <h4>EPUB</h4>
        <table class="form-table">
            <tr valign="top">
                <th>スタイル</th>
                <td>
                  <select name="etemplate" id="pripre_etemplate" onChange="pripre_update();">
                    <option value=""<?php if (empty($etamplate)) echo ' selected="selected"'; ?>>-書籍スタイル-</option>
                    <?php foreach ($estyles as $etemplate) { ?>
                    <option value="<?php echo $etemplate['id']; ?>"<?php if ($etamplate == $etemplate['id']) echo ' selected="selected"'; ?>><?php echo $etemplate['name']; ?></option>
                    <?php } ?>
                  </select>
                </td>
            </tr>
            <tr valign="top">
                <th>メタデータ
                <a href="http://zamasoft.net/2012/04/23/publish/" target="_blank">説明</a></th></th>
                <td><textarea name="epubmeta" cols="60" rows="6" onChange="pripre_update();" id="pripre_epubmeta"><?php echo htmlspecialchars($epubmeta); ?></textarea></td>
            </tr>
        </table>
        <p class="pripre-submit">
            <label>
            <input type="checkbox" name="tidyhtml" value="1" checked="checked"/>XHTML整形
            </label>
            <label>
            <input type="checkbox" name="svgtoimage" value="1" checked="checked"/>SVGを画像に変換
            </label>
            <label>
            <input type="checkbox" name="tate_filter" value="1" />縦書きフィルタ適用
            </label>
                    <input type="submit" name="submit" class="button-secondary"
            onclick="form.action='<?php echo plugins_url('pripre/actions/publish-epub.php/book.epub'); ?>'; return 1;"
            value="EPUBをダウンロード"/>
            <br/>
            <select name="dist_id">
              <option value="">--新規作成--</option>
            <?php
            $sql = "SELECT id,title
				FROM {$wpdb->prefix}pripre_dist";
			$rows = $wpdb->get_results($sql, ARRAY_A);
			foreach($rows as $row) {
            ?>
              <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['title']); ?></option>
            <?php } ?>
            </select>
            <input type="submit" name="submit" class="button-secondary"
            onclick="form.action='<?php echo plugins_url('pripre/actions/publish-dist-epub.php'); ?>'; return 1;"
            value="EPUBを販売"/>
        </p>
        
        <h4>その他</h4>
        <p class="pripre-submit">
            <a href="http://zamasoft.net/2012/05/12/toc-gen/" target="_blank">説明</a>
        	<input type="button" class="button-secondary"
            onclick="window.open('<?php echo plugins_url('pripre/actions/publish-toc.php?book_id='.$book_id); ?>');"
            value="目次生成"/>
        </p>
        
        <h4>内容</h4>
        <p>ドラッグ＆ドロップで並び替えることができます。
        <div id="pripre-book" class="pripre-list">
            <?php
    $sql = "SELECT p.post_date,p.id,p.post_title
            FROM {$wpdb->prefix}term_relationships AS t
            INNER JOIN {$wpdb->prefix}posts AS p ON p.id=t.object_id
            WHERE t.term_taxonomy_id=$book_id";
    $rows = $wpdb->get_results($sql, ARRAY_A);
    pripre_sort_bookposts($book_id, $rows);
            $templates = pripre_get_styles();
            foreach($rows as $row) {
                ?>
                <div class="pripre-post-item pripre-in-book">
                    <span class="pripre-id"><?php echo $row['id']; ?></span>
                    <span class="pripre-date"><?php echo $row['post_date']; ?></span>
                    <a href="post.php?post=<?php echo $row['id']; ?>&amp;action=edit" target="_blank" class="pripre-title"><?php echo $row['post_title']; ?></a>
    <?php echo '<select name="style" class="pripre-style" onChange="pripre_update();">';
    $post_style = pripre_get_postparam($row['id'], 'style');
    if (empty($post_style)) {
    	$post_style = $pripre_default_style;
    }
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
        if ($style['id'] == $post_style) {
            echo ' selected="selected"';
        }
        echo '>'.$style['name'].'</option>';
    	}
    }
    echo '</optgroup></select>';
    
    $user_style = pripre_get_postparam($row['id'], 'user_style');
    $user_styles = pripre_get_user_styles();
    echo '<select name="user_style" class="pripre-user-style" onChange="pripre_update();">';
    echo '<option value="">-- なし --</option>';
    foreach($user_styles as $id => $name) {
    	echo "<option value=\"$id\"";
    	if ($id == $user_style) {
    	echo ' selected="selected"';
    	}
    	echo ">".htmlspecialchars($name)."</option>";
    }
    echo '</select>';
    
    ?>
                </div>
                <?php
            }
            ?>
        </div>
        <p class="pripre-submit">
            <input type="submit" class="button-secondary"
            onclick="if (!confirm('本当に削除しますか？')) { return 0 }; form.action='<?php echo plugins_url('pripre/actions/delete-book.php'); ?>'; return 1;"
            value="この本を削除"/>
        </p>
    </form>
    <?php
}
?>
