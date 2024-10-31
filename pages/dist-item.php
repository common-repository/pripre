<?php
session_start();
require_once ('../../../../wp-load.php');
require_once ('../includes/utils.inc.php');

$id = $_GET['id'];
$cart_id = session_id();

global $wpdb;
$sql = $wpdb->prepare("SELECT d.id,d.title,d.series,d.author,d.cover,d.description,d.browse,d.publish,
    d.writing_mode,d.retail,d.added,d.filename,
	(SELECT GROUP_CONCAT(t.tag SEPARATOR ' ') FROM {$wpdb->prefix}pripre_dist_tag AS t WHERE t.id=d.id) AS tags
    FROM {$wpdb->prefix}pripre_dist AS d
    WHERE d.id=%s AND d.publish<>0", $id);
$row = $wpdb->get_row($sql, ARRAY_A);

$sql = $wpdb->prepare("SELECT t.tag
		FROM {$wpdb->prefix}pripre_dist_tag AS t
		WHERE t.id=%s", $id);
$tags = $wpdb->get_results($sql, ARRAY_A);

header('Content-Type: text/html');
wp_register_style('pripre_style', plugins_url('pripre/styles/style.css'));
wp_enqueue_style('pripre_style');
get_header();
?>
<div id="primary" class="pripre-dist">
	<div id="content" role="main">
	<a href="<?php echo plugins_url('pripre/pages/dist-shop.php'); ?>">ショップ</a>
	|
	<a href="<?php echo plugins_url('pripre/pages/dist-cart.php?id='.$cart_id);?>">カート</a>
	<hr/>
	<?php if (!empty($row)) {
	$sql = $wpdb->prepare("SELECT v.version,v.added,v.page,LENGTH(v.pdf) AS size,v.caption,
    	(SELECT COUNT(*) FROM {$wpdb->prefix}pripre_dist_cart_buy AS c WHERE c.dist_id=v.id AND c.version=v.version) AS buy
        FROM {$wpdb->prefix}pripre_dist_version AS v
        WHERE v.id=%s AND v.version=%d", array($id, $row['publish']));
    $version = $wpdb->get_row($sql, ARRAY_A);
	?>
	<h2><?php echo htmlspecialchars($row['title']); ?></h2>
    <?php if ($row['cover']) { ?>
    <?php echo wp_get_attachment_image($row['cover'], 'medium', 0, array('style' => 'float:right;margin:1em;')); ?>
    <?php } ?>
    <table>
	<?php if($row['author']) { ?>
	<tr>
	<th>著者：</th>
	<td><?php echo htmlspecialchars($row['author']); ?></td>
	</tr>
	<?php } ?>
    <?php if($row['series']) { ?>
    <tr>
    <th>シリーズ：</th>
    <td>
    <a href="<?php echo plugins_url('pripre/pages/dist-shop.php?s=').htmlspecialchars($row['series']);?>"><?php echo htmlspecialchars($row['series']); ?></a>
    </td>
    </tr>
    <?php } ?>
    <tr>
    <th>タグ：</th>
    <td>
    <?php
    foreach ($tags as $tag) {
    	echo '<a href="'.plugins_url('pripre/pages/dist-shop.php?t=').htmlspecialchars($tag['tag']).'">'.htmlspecialchars($tag['tag']).'</a> ';
    }
    ?>
    </td>
    </tr>
    <tr>
    <th>書字方向：</th>
    <td><?php switch($row['writing_mode']) {
    	case 0:
    		echo '任意';
    		break;
    	case 1:
    		echo '横書きを推奨';
    		break;
    	case 2:
    		echo '縦書きを推奨';
    		break;
    	case 3:
    		echo '横書き';
    		break;
    	case 4:
    		echo '縦書き';
    		break;
    } ?></td>
    </tr>
    <tr>
    <th>ページ数：</th>
    <td>約<?php echo $version['page'] ?>ページ相当</td>
    </tr>
    <tr>
    <th>サイズ：</th>
    <td>約<?php echo (int)($version['size'] / 1024); ?>KB</td>
    </tr>
    <tr>
    <th>発行日：</th>
    <td><?php echo $row['added']; ?></td>
    </tr>
    <tr>
    <th>配信版：</th>
    <td><?php echo $version['added']; ?></td>
    </tr>
    </table>
    <p>
    <span style="color: Red;">価格：
    <?php echo $row['retail'] == 0 ? '無料' : $row['retail'].'円'; ?></td>
    </span>
    <?php
    $sql = $wpdb->prepare("SELECT COUNT(*) AS count
    		FROM {$wpdb->prefix}pripre_dist_cart_item
    		WHERE id=%s AND dist_id=%s", array($cart_id, $row['id']));
    $crow = $wpdb->get_row($sql, ARRAY_A);
    if ($crow['count'] == 0) {
    ?>
    <input onClick="window.location='<?php echo plugins_url('pripre/actions/dist-cart-add.php?id='.$id);?>'" type="button" class="button-secondary" value="カートに追加"/>
    <?php } else { ?>
    カートに追加済み
    <?php } ?>
    </p>
    
    <?php if ($row['browse'] > 0) { ?>
    <form method="post"
    onsubmit="a=document.getElementById('dl-<?php echo $row['id']; ?>');if (a.innerHTML=='0') { alert('ダウンロード回数の制限を超えました'); return false; } else --a.innerHTML;"
    action="<?php echo plugins_url("pripre/actions/dist-read-pdf.php/{$row['filename']}.pdf");?>">
    <fieldset>
      <legend>立ち読み</legend>
      <input type="hidden" name="id" value="<?php echo $row['id']; ?>"/>
      <?php switch ($row['writing_mode']) {
      	case 1:
      	?>
	  <label>書字方向 <select name="v">
	    <option value="0">横書き</option>
	    <option value="1">縦書き</option>
	  </select></label>
	  <?php break;
		case 2:
	  ?>
	  <label>書字方向 <select name="v">
	    <option value="0">横書き</option>
	    <option value="1" selected="selected">縦書き</option>
	  </select></label>
	  <?php break;
		case 3:
	  ?>
	  <label>横書き<input type="hidden" name="v" value="0"/></label>
	  <?php break;
		case 4:
	  ?>
	  <label>縦書き<input type="hidden" name="v" value="1"/></label>
	  <?php } ?>
	  
	  <label>文字<select name="f">
	    <option value="-2">特小</option>
	    <option value="-1">小</option>
	    <option value="" selected="selected">中</option>
	    <option value="1">大</option>
	    <option value="2">特大</option>
	  </select></label><br/>
      <input type="submit" value="ダウンロード"/>
    </fieldset>
    </form>
    <?php } ?>
    
    <h2>商品の説明</h2>
    <div><?php echo nl2br(htmlspecialchars($row['description'])); ?></div>
    <?php } ?>
    </div>
</div>
<?php get_footer(); ?>