<?php
require_once ('../../../../wp-load.php');
require_once ('../includes/utils.inc.php');

$token = $_GET['token'];

global $wpdb;
$sql = $wpdb->prepare("SELECT d.id,d.title,d.author,d.cover,d.writing_mode,
    d.retail,d.filename,cb.expire,cb.tickets
    FROM {$wpdb->prefix}pripre_dist_cart_buy AS cb
    INNER JOIN {$wpdb->prefix}pripre_dist AS d ON d.id=cb.dist_id
    WHERE cb.token=%s", $token);
$rows = $wpdb->get_results($sql, ARRAY_A);

header('Content-Type: text/html');
wp_register_style('pripre_style', plugins_url('pripre/styles/style.css'));
wp_enqueue_style('pripre_style');
get_header();
?>
<div id="primary" class="pripre-dist">
	<div id="content" role="main">
	<a href="<?php echo plugins_url('pripre/pages/dist-shop.php'); ?>">ショップ</a>
	
	<h2>お買い上げ書籍一覧</h2>
	<?php foreach($rows as $row) { ?>
	<hr/>
	<div>
	<?php if ($row['cover']) { ?>
    <?php echo wp_get_attachment_image($row['cover'], 'thumbnail', 0, array('style' => 'float:right;margin:1em;border:1px solid Gray;')); ?>
    <?php } ?>
    <table>
    <tr><th>タイトル：</th><td><?php echo htmlspecialchars($row['title']); ?></td></tr>
    <?php if ($row['author']) { ?>
    <tr><th>作者：</th><td><?php echo htmlspecialchars($row['author']); ?></td></tr>
    <?php } ?>
    <tr><th>期限：</th><td> <?php echo $row['expire']; ?></td></tr>
    </table>
    <div>
    <?php if ($row['tickets'] > 0) { ?>
    あと<span id="dl-<?php echo $row['id']; ?>"><?php echo $row['tickets']; ?></span>回ダウンロードできます
    <?php } else { ?>
ダウンロード回数の制限を超えました
    <?php } ?>
    </div>
    <?php if ($row['tickets'] > 0) { ?>
    <form method="post"
    onsubmit="a=document.getElementById('dl-<?php echo $row['id']; ?>');if (a.innerHTML=='0') { alert('ダウンロード回数の制限を超えました'); return false; } else --a.innerHTML;"
    action="<?php echo plugins_url("pripre/actions/dist-read-pdf.php/".htmlspecialchars($row['filename']).".pdf");?>">
    <fieldset>
      <input type="hidden" name="id" value="<?php echo $row['id']; ?>"/>
      <input type="hidden" name="token" value="<?php echo $token; ?>"/>
      <?php switch ($row['writing_mode']) {
      	case 1:
      	?>
	  <label>書字方向 <select name="v">
	    <option value="0">横書き</option>
	    <option value="1">縦書き</option>
	    <option value="2">縦２段</option>
	  </select></label>
	  <?php break;
		case 2:
	  ?>
	  <label>書字方向 <select name="v">
	    <option value="0">横書き</option>
	    <option value="1" selected="selected">縦書き</option>
	    <option value="2">縦２段</option>
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
      <div style="clear:both;"></div>
    </div>
    <?php
	} ?>
    </div>
</div>
<?php get_footer(); ?>