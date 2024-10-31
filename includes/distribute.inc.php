<?php
function pripre_distribute_tool() {
    ?>
    <div class="wrap">
        <h2 style="float: left;">販売・頒布</h2>
        <a href="http://zamasoft.net/2012/06/19/distribute/" target="_blank"><br/>ヘルプ</a>
        <br style="clear: both;"/>
    </div>
    <?php
    if (empty($_GET['id'])) {
    	add_meta_box('pripre_distribute_list_box', __('一覧', 'pripre_textdomain'), 'pripre_distribute_list_box', 'pripre_distribute', 'normal');
    	add_meta_box('pripre_distribute_add_box', __('新規', 'pripre_textdomain'), 'pripre_distribute_add_box', 'pripre_distribute', 'normal');
    	add_meta_box('pripre_distribute_buy_box', __('販売実績', 'pripre_textdomain'), 'pripre_distribute_buy_box', 'pripre_distribute', 'normal');
    	add_meta_box('pripre_distribute_paypal_box', __('設定', 'pripre_textdomain'), 'pripre_distribute_paypal_box', 'pripre_distribute', 'normal');
    }
    else {
    	wp_enqueue_script('media-upload');
    	wp_enqueue_style('thickbox');
    	wp_enqueue_script('jquery');
    	add_meta_box('pripre_distribute_update_box', __('更新', 'pripre_textdomain'), 'pripre_distribute_update_box', 'pripre_distribute', 'normal');
    }
    do_meta_boxes('pripre_distribute', 'normal', '');
}

function pripre_distribute_list_box() {
	global $wpdb;
	$sql = "SELECT id,title,author,retail,publish
			FROM {$wpdb->prefix}pripre_dist";
	$rows = $wpdb->get_results($sql, ARRAY_A);
	?><table class="wp-list-table widefat fixed posts" cellspacing="0">
	<thead>
	<tr><th>題名</th><th>作者</th><th>価格</th><th>配信</th></tr>
	</thead>
	<tbody>
	<?php
	foreach($rows as $row) {
		?>
		<tr>
		<td>
		<a href="?page=pripre_distribute_tool&amp;id=<?php echo htmlspecialchars($row['id']); ?>">
		<?php echo htmlspecialchars($row['title']); ?>
		</a></td>
		<td><?php echo htmlspecialchars($row['author']); ?></td>
		<td><?php echo htmlspecialchars($row['retail']); ?> 円</td>
		<td><?php if ($row['publish']) { ?><a href="<?php  echo plugins_url('pripre/pages/dist-item.php?id='.$row['id']); ?>" target="_blank">配信中</a><?php } ?></td>
		</tr>
		<?php
	}
	?>
	</tbody>
	</table><?php
}

function pripre_distribute_add_box() {
	?>
	<form action="<?php echo plugins_url('pripre/actions/dist-add.php');?>"
	      method="post"
	      enctype="multipart/form-data">
	<table>
	  <tr>
	    <td align="right">EPUBファイル</td>
	    <td><input type="file" name="data_file" value="" /></td>
	  </tr>
	</table>
	<p class="pripre-submit">
	<input type="submit" class="button-secondary" value="追加"/>
	</p>
	</form>
	<?php
}

function pripre_distribute_buy_box() {
	global $wpdb;
	
	$sql = "SELECT DISTINCT YEAR(buyed) AS y,MONTH(buyed) AS m
		FROM {$wpdb->prefix}pripre_dist_cart_buy ORDER BY y DESC,m DESC";
	$rows = $wpdb->get_results($sql, ARRAY_A);
	?>
	<form>
	<input type="hidden" name="page" value="pripre_distribute_tool"/>
	<select name="month" onChange="form.submit()">
	<?php
	foreach($rows as $row) {
		$val = "{$row['y']}-{$row['m']}";
	?>
	<option <?php if (!empty($_GET['month']) && $_GET['month'] == $val) echo "selected='selected'"; ?>><?php echo $val; ?></option>
	<?php
	}
	?>
	</select>
	</form>
	<?php
	if (!empty($_GET['month'])) {
		$date = strtotime($_GET['month'].'-1');
	}
	else if (empty($rows)) {
		$date = time();
	}
	else {
		$row = $rows[0];
		$date = strtotime("{$row['y']}-{$row['m']}-1");
	}
	$year = date('Y', $date);
	$month = date('n', $date);
	
	$sql = $wpdb->prepare("SELECT d.id,b.buyed,d.title,b.version,b.retail,b.mail_address
	FROM {$wpdb->prefix}pripre_dist_cart_buy AS b
	INNER JOIN {$wpdb->prefix}pripre_dist AS d ON d.id=b.dist_id
	WHERE b.buyed >= %s AND b.buyed < %s
	ORDER BY b.buyed DESC", array(
			date('Y-n-j', mktime(0, 0, 0, $month, 1, $year)),
			date('Y-n-j', mktime(0, 0, 0, $month + 1, 1, $year))
			));
	$rows = $wpdb->get_results($sql, ARRAY_A);
	?><table class="wp-list-table widefat fixed posts" cellspacing="0">
	<thead>
	<tr><th>購入日</th><th>タイトル</th><th>版番号</th><th>売価</th><th>メールアドレス</th></tr>
	</thead>
	<tbody>
	<?php
	foreach($rows as $row) {
		?>
		<tr>
		<td><?php echo htmlspecialchars($row['buyed']); ?></td>
		<td>
		<a href="?page=pripre_distribute_tool&amp;id=<?php echo htmlspecialchars($row['d.id']); ?>">
		<?php echo htmlspecialchars($row['title']); ?>
		</a></td>
		<td><?php echo htmlspecialchars($row['version']); ?></td>
		<td style="text-align:right;"><?php echo htmlspecialchars($row['retail']); ?> 円</td>
		<td><?php echo htmlspecialchars($row['mail_address']); ?></td>
		</tr>
		<?php
	}
	?>
	</tbody>
	</table><?php
}

function pripre_distribute_paypal_box() {
	$server = get_option("pripre_paypal_server");
	$sandbox_user = get_option("pripre_sandbox_paypal_user");
	$sandbox_password = get_option("pripre_sandbox_paypal_password");
	$sandbox_signature = get_option("pripre_sandbox_paypal_signature");
	$user = get_option("pripre_paypal_user");
	$password = get_option("pripre_paypal_password");
	$signature = get_option("pripre_paypal_signature");
	$contact = get_option("pripre_contact");
	?>
	<form action="options.php"
	      method="post">
	<?php wp_nonce_field('update-options'); ?>
	<fieldset class="pripre">
	<legend>PayPal API</legend>
	<p><strong>販売機能の使用にあたっては、テストを行って機能を理解した上で、購入者から連絡が取れる状態にした上でご使用ください。</strong></p>
	<p><a href="https://www.paypal.jp/" target="_blank">PayPalアカウント</a>が必要になります。Classic APIの情報を設定してください。</p>
	<table>
	  <tr>
	    <th>サーバー</th>
	    <td>
	    <select name="pripre_paypal_server">
	      <option value="0" <?php if ($server == '0') echo 'selected="selected"'; ?>>テスト</option>
	      <option value="1" <?php if ($server == '1') echo 'selected="selected"'; ?>>本番</option>
	    </select>
	    </td>
	  </tr>
	</table>
	<fieldset class="pripre"><legend>テスト</legend>
	<table>
	  <tr>
	    <th>APIユーザー名</th><td><input type="text" name="pripre_sandbox_paypal_user" size="40" value="<?php echo htmlspecialchars($sandbox_user); ?>"/></td>
	  </tr>
	  <tr>
	    <th>APIパスワード</th><td><input type="text" name="pripre_sandbox_paypal_password" size="40" value="<?php echo htmlspecialchars($sandbox_password); ?>"/></td>
	  </tr>
	  <tr>
	    <th>署名</th><td><input type="text" name="pripre_sandbox_paypal_signature" size="80" value="<?php echo htmlspecialchars($sandbox_signature); ?>"/></td>
	  </tr>
	</table>
	</fieldset>
	<fieldset class="pripre"><legend>本番</legend>
	<table>
	  <tr>
	    <th>APIユーザー名</th><td><input type="text" name="pripre_paypal_user" size="40" value="<?php echo htmlspecialchars($user); ?>"/></td>
	  </tr>
	  <tr>
	    <th>APIパスワード</th><td><input type="text" name="pripre_paypal_password" size="40" value="<?php echo htmlspecialchars($password); ?>"/></td>
	  </tr>
	  <tr>
	    <th>署名</th><td><input type="text" name="pripre_paypal_signature" size="80" value="<?php echo htmlspecialchars($signature); ?>"/></td>
	  </tr>
	</table>
	</fieldset>
	</fieldset>
	<fieldset class="pripre">
	<legend>販売責任者連絡先</legend>
	<p>購入者が問い合わせできるように情報を設定してください。</p>
	<p><textarea name="pripre_contact" cols="80" rows="8"><?php echo htmlspecialchars($contact); ?></textarea></p>
    <input type="hidden" name="action" value="update" />
    <input type="hidden" name="page_options" value="pripre_paypal_server,pripre_sandbox_paypal_user,pripre_sandbox_paypal_password,pripre_sandbox_paypal_signature,pripre_paypal_user,pripre_paypal_password,pripre_paypal_signature,pripre_contact" />
	<p class="submit">
        <input type="submit" name="submit" id="submit" class="button-primary" value="更新" tabindex="1"  />
    </p>
    <br/>
	</fieldset>
    </form>
	<?php
}

function pripre_distribute_update_box() {
	$id = $_GET['id'];
	
    global $wpdb;
    $sql = $wpdb->prepare("SELECT d.id,d.title,d.series,d.filename,d.author,d.cover,d.description,
    		d.writing_mode,d.retail,d.browse,d.publish,
    		(SELECT GROUP_CONCAT(t.tag SEPARATOR ' ') FROM {$wpdb->prefix}pripre_dist_tag AS t WHERE t.id=d.id) AS tags
            FROM {$wpdb->prefix}pripre_dist AS d
            WHERE d.id=%s", $id);
    $row = $wpdb->get_row($sql, ARRAY_A);
	?>
<form action="<?php echo plugins_url('pripre/actions/dist-update.php');?>"
      method="post"
      enctype="multipart/form-data">
<table>
  <tr>
    <td align="right">ID：</td>
    <td><?php echo htmlspecialchars($row['id']); ?>
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>" /></td>
  </tr>
  <tr>
    <td align="right">題名：</td>
    <td><input type="text" size="60" name="title" value="<?php echo htmlspecialchars($row['title']); ?>" /></td>
  </tr>
  
  <tr>
    <td align="right">シリーズ：</td>
    <td><input type="text" size="60" name="series" value="<?php echo htmlspecialchars($row['series']); ?>" /></td>
  </tr>

  <tr>
    <td align="right">ファイル名：</td>
    <td><input type="text" size="30" name="filename" value="<?php echo htmlspecialchars($row['filename']); ?>" /></td>
  </tr>

  <tr>
    <td align="right">著者：</td>
    <td><input type="text" size="60" name="author" value="<?php echo htmlspecialchars($row['author']); ?>" /></td>
  </tr>

  <script type="text/javascript">
jQuery('document').ready(function(){
jQuery('#upload_image_button').click(function() {
  formfield = jQuery('#upload_image').attr('name');
  tb_show(null, 'media-upload.php?type=image&amp;TB_iframe=true');
  return false;
});

jQuery('#upload_image_delete').click(function() {
  jQuery('#upload_image_prev').toggle();
  jQuery('#upload_image').val('');
  jQuery('#upload_image_delete').attr("disabled", "disabled");
  return false;
});

window.send_to_editor = function(html) {
  imgurl = jQuery('img', html).attr('src');
  jQuery('#upload_image_prev').attr('src', imgurl);
  jQuery('#upload_image_prev').show();

  imgid = jQuery('img', html).attr('class');
  imgid.match(/wp-image-/g);
  imgid = RegExp.rightContext;
  jQuery('#upload_image').val(imgid);
  jQuery('#upload_image_delete').removeAttr("disabled");
  tb_remove();
}
});
  </script>
  <tr>
    <td align="right">画像：</td>
    <td>
    <?php if ($row['cover']) { ?>
    <?php echo wp_get_attachment_image($row['cover'], 'thumbnail', 0, array('id' => 'upload_image_prev')); ?>
    <?php } else { ?>
    <img id="upload_image_prev" src="" width="200" style="display:none;"/>
    <?php } ?>
    <br/>
    <input id="upload_image_button" type="button" value="画像を選択"/>
    <input id="upload_image_delete" type="button" value="画像を削除"<?php if (!$row['cover']) echo ' disabled="disabled"'; ?>/>
    <input id="upload_image" type="hidden" name="cover" value="<?php echo $row['cover']; ?>"/>
    </td>
  </tr>

  <tr>
    <td align="right">説明：</td>
    <td><textarea rows="8" cols="60" name="description"><?php echo htmlspecialchars($row['description']); ?></textarea></td>
  </tr>

  <tr>
    <td align="right">書字方向：</td>
    <td><select name="writing_mode">
<option value="1"<?php if($row['writing_mode'] == 1) echo ' selected="selected"';?>>横書きを推奨</option>
<option value="2"<?php if($row['writing_mode'] == 2) echo ' selected="selected"';?>>縦書きを推奨</option>
<option value="3"<?php if($row['writing_mode'] == 3) echo ' selected="selected"';?>>横書き</option>
<option value="4"<?php if($row['writing_mode'] == 4) echo ' selected="selected"';?>>縦書き</option>
</select></td>
  </tr>

  <tr>
    <td align="right">タグ：</td>
    <td><input type="text" size="60" name="tags" value="<?php echo htmlspecialchars($row['tags']); ?>" /></td>
  </tr>

  <tr>
    <td align="right">販売価格：</td>
    <td><input type="text" size="16" name="retail" value="<?php echo htmlspecialchars($row['retail']); ?>" />円</td>
  </tr>
</table>
<label><input type="radio" name="browse" value=""<?php if(!$row['"browse"']) echo ' checked="checked"';?>/>立ち読みなし</label>
<label><input type="radio" name="publish" value=""<?php if(!$row['publish']) echo ' checked="checked"';?>/>配信停止</label>
<?php
    if ($row['publish']) {
?>
      <a href="<?php  echo plugins_url('pripre/pages/dist-item.php?id='.$id); ?>" target="_blank">配信中</a>
<p><strong>配信にあたっては、各版のPDFをダウンロードして、間違いなく読めることを確認しておいてください。</strong></p>
<?php
    }
    
    $sql = $wpdb->prepare("SELECT v.version,v.added,v.page,LENGTH(v.pdf) AS size,v.caption,
    		(SELECT COUNT(*) FROM {$wpdb->prefix}pripre_dist_cart_buy AS c WHERE c.dist_id=v.id AND c.version=v.version) AS buy
            FROM {$wpdb->prefix}pripre_dist_version AS v
            WHERE v.id=%s ORDER BY v.version", $id);
    $versions = $wpdb->get_results($sql, ARRAY_A);
    foreach($versions as $version) {
?>
<hr/>
<table>
  <tr>
    <td align="right">番号：</td>
    <td><?php echo $version['version']; ?></td>
  </tr>
  <tr>
    <td align="right">登録日時：</td>
    <td><?php echo $version['added']; ?></td>
  </tr>
  <tr>
    <td align="right">ページ数：</td>
    <td><?php echo $version['page']; ?></td>
  </tr>
  <tr>
    <td align="right">サイズ：</td>
    <td><?php echo (int)($version['size'] / 1024); ?>KB</td>
  </tr>
  <tr>
    <td align="right">購読数：</td>
    <td><?php echo (int)($version['buy']); ?></td>
  </tr>
  <tr>
    <td align="right">版名：</td>
    <td>
      <input type="text" name="caption[]" value="<?php echo htmlspecialchars($version['caption']); ?>"/>
    </td>
  </tr>
  <tr>
    <td align="right">配信：</td>
    <td>
      <label><input type="radio" name="browse" value="<?php echo $version['version']; ?>"<?php if($row['browse'] == $version['version']) echo ' checked="checked"';?>/>立ち読み</label>
      <label><input type="radio" name="publish" value="<?php echo $version['version']; ?>"<?php if($row['publish'] == $version['version']) echo ' checked="checked"';?>/>配信</label>
      <?php if ($version['buy'] == 0) { ?><label><input type="checkbox" name="delete-version[]" value="<?php echo $version['version']; ?>"/>削除</label><?php } else { ?>
      <div>読者が存在するため削除できません</div>
      <?php } ?>
    </td>
  </tr>
</table>
<a href="<?php echo plugins_url('pripre/actions/dist-data.php/book.epub');?>?id=<?php echo $id; ?>&amp;version=<?php echo $version['version']; ?>&amp;mode=epub">EPUB</a>
<a href="<?php echo plugins_url('pripre/actions/dist-data.php/book.pdf');?>?id=<?php echo $id; ?>&amp;version=<?php echo $version['version']; ?>&amp;mode=pdf">PDF</a>
<?php
    }
?>
<hr/>
<p>
<label>EPUB追加： <input type="file" size="30" name="epub_file" /></label>
</p>
<p class="pripre-submit">
  <input type="submit" class="button-secondary" name="delete" value="削除"/>
  <input type="submit" class="button-secondary" name="update" value="更新"/>
</p>
</form>
<?php
}
?>
