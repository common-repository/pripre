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

header('Content-Type: text/html');
wp_register_style('pripre_style', plugins_url('pripre/styles/style.css'));
wp_enqueue_style('pripre_style');
get_header();
?>
<div id="primary" class="pripre-dist">
	<div id="content" role="main">
	<p>書籍購入のお申込みありがとうございます。
	次のステップに必要な情報をメールいたしましたので、ご確認下さい。</p>
    </div>
</div>
<?php get_footer(); ?>