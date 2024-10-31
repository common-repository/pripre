<?php
require_once ('../../../../wp-load.php');
require_once ('../includes/utils.inc.php');

$id = $_GET['id'];

global $wpdb;
$sql = $wpdb->prepare("SELECT d.id,d.title,d.author,d.cover,
    d.retail
    FROM {$wpdb->prefix}pripre_dist_cart_item AS ci
    INNER JOIN {$wpdb->prefix}pripre_dist AS d ON d.id=ci.dist_id
    WHERE ci.id=%s", $id);
$rows = $wpdb->get_results($sql, ARRAY_A);

$amount = 0;
header('Content-Type: text/html');
wp_register_style('pripre_style', plugins_url('pripre/styles/style.css'));
wp_enqueue_style('pripre_style');
get_header();
?>
<div id="primary" class="pripre-dist">
	<div id="content" role="main">
	<a href="<?php echo plugins_url('pripre/pages/dist-shop.php'); ?>">ショップ</a>
	
	<?php if(empty($rows)) { ?>
	<p>カートは空です</p>
	<?php } else { ?>
	<h2>選択した商品</h2>
	<?php foreach($rows as $row) { ?>
	<hr/>
	<div>
	<?php if ($row['cover']) { ?>
    <?php echo wp_get_attachment_image($row['cover'], 'thumbnail', 0, array('style' => 'float:left;margin:1em;')); ?>
    <?php } ?>
    <table>
    <tr><th>タイトル：</th><td><?php echo htmlspecialchars($row['title']); ?></td></tr>
    <tr><th>作者：</th><td><?php echo htmlspecialchars($row['author']); ?></td></tr>
    <tr><th>価格：</th><td><?php echo $row['retail'] == 0 ? '無料' : $row['retail'].' 円'; ?></td></tr>
    </table>
    <div><input onClick="window.location='<?php echo plugins_url("pripre/actions/dist-cart-remove.php?id={$row['id']}");?>'" type="button" class="button-secondary" value="カートから除外"/></div>
    <div style="clear:both;"></div>
    </div>
    <?php
    $amount += $row['retail'];
	} ?>
    <p>
    <form action="<?php echo plugins_url('pripre/actions/dist-cart-buy.php');?>" method="post">
      <input type="hidden" name="id" value="<?php echo $id; ?>"/>
      <?php if ($amount) { ?>
      <div style="color: Red;"><strong>合計金額：</strong><?php echo $amount ?> 円</div>
      <label>メールアドレス：<input type="text" name="email" value=""/>
      <?php if (isset($message)) { ?>
      <small style="color: Red;">メールアドレスは必須です</small>
      <?php } else { ?>
      <small>（必須）</small>
      <?php } ?>
      </label><br/>
      <input type="submit" value="購入手続きへすすむ"/>
      <?php } else { ?>
      <strong>合計金額：</strong>　無料<br/>
      <label>メールアドレス：<input type="text" name="email" value=""/>
      <small>（メールでの控えが必要な方のみ）</small></label><br/>
      <input type="submit" value="選択した本を読む"/>
      <?php } ?>
    </form>
    </p>
    <?php } ?>
    
    </div>
</div>
<?php get_footer(); ?>