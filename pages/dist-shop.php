<?php
session_start();
require_once ('../../../../wp-load.php');
require_once ('../includes/utils.inc.php');

$cart_id = session_id();

global $wpdb;

$cols = "d.id,d.title,d.author,d.cover,
			d.retail";
if (!empty($_GET['s'])) {
	$sql = $wpdb->prepare("SELECT $cols
			FROM {$wpdb->prefix}pripre_dist AS d
			WHERE d.series=%s AND publish<>0", $_GET['s']);
}
else if (!empty($_GET['t'])) {
	$sql = $wpdb->prepare("SELECT $cols
			FROM {$wpdb->prefix}pripre_dist_tag AS t
			INNER JOIN {$wpdb->prefix}pripre_dist AS d ON d.id=t.id
			WHERE t.tag=%s AND publish<>0", $_GET['t']);
} else {
	$sql = "SELECT $cols
			FROM {$wpdb->prefix}pripre_dist AS d
			WHERE publish<>0";
}

$rows = $wpdb->get_results($sql, ARRAY_A);

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
	
	<?php foreach($rows as $row) { ?>
	<hr style="clear: both;"/>
	<a href="<?php echo plugins_url('pripre/pages/dist-item.php?id=').htmlspecialchars($row['id']); ?>">
	<h2><?php echo htmlspecialchars($row['title']); ?></h2>
    <?php if ($row['cover']) { ?>
    <?php echo wp_get_attachment_image($row['cover'], 'thumbnail', 0, array('style' => 'float:right;margin:1em;border:1px solid Gray;')); ?>
    <?php } ?>
    </a>
    <table>
	<?php if($row['author']) { ?><tr>
	<th>著者：</th>
	<td><?php echo htmlspecialchars($row['author']); ?></td>
	</tr>
	<?php } ?>
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
    <input onClick="window.location='<?php echo plugins_url('pripre/actions/dist-cart-add.php?id='.$row['id']);?>'" type="button" class="button-secondary" value="カートに追加"/>
    <?php } else { ?>
    カートに追加済み
    <?php } ?>
    </p>
        <?php } ?>
    
    </div>
</div>
<?php get_footer(); ?>