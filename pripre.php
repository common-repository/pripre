<?php

/*
  Plugin Name: PriPre
  Plugin URI: https://zamasoft.net/pripre
  Description: 印刷物・電子書籍の両方に対応した出版システムです。
  Version: 0.4.11
  Author: 宮部龍彦
  Author URI: https://copper-pdf.com/
  License: Apache License 2.0
 */
define('PRIPRE_COPPER_URI_DEFAULT', 'ctip://free.cssj.jp/');
define('PRIPRE_COPPER_USER_DEFAULT', 'user');
define('PRIPRE_COPPER_PASSWORD_DEFAULT', 'kappa');
define('PRIPRE_DEFAULT_STYLE', 'default/plain');

require_once 'includes/config.inc.php';
require_once 'includes/edit.inc.php';
require_once 'includes/publish.inc.php';
require_once 'includes/distribute.inc.php';
require_once 'includes/cover.inc.php';
require_once 'includes/styles.inc.php';
require_once 'includes/filters.inc.php';

register_activation_hook(__FILE__, 'pripre_install');
register_deactivation_hook(__FILE__, 'pripre_uninstall');

// 青空文庫風タグ
add_filter('the_content', 'pripre_text_filter');
add_action('wp_head', 'pripre_wp_head');

function pripre_wp_head() {
	add_filter('the_content', 'pripre_pdf_button');
	add_filter('the_content', 'pripre_dist_tags');
}

// スタイル保存
add_filter('pre_post_update', 'pripre_pre_post_update');
function pripre_pre_post_update($post_id) {
	if (!empty($_POST['template'])) {
		require_once ('includes/utils.inc.php');
		
		include ('includes/post_params.inc.php');
		
		$style = $_POST['template'];
		pripre_set_postparam($post_id, 'style', $style);
	}
}

// PDFボタン表示
function pripre_pdf_button($content) {
	$pdf_button = get_option("pripre_pdf_button");
	if ($pdf_button || mb_strpos($content, '<!-- PRIPRE_PDF -->') !== FALSE) {
		ob_start();
		pripre_pdf();
		$tags = ob_get_contents();
		ob_end_clean();
	}
	$content = str_replace('<!-- PRIPRE_PDF -->', $tags, $content);
	if ($pdf_button) {
		$content .= '<hr style="clear:both;"/>'.$tags;
	}
	return $content;
}

function pripre_dist_tags($content) {
	$content = str_replace('<!-- PRIPRE_ITEM -->', $tags, $content);
	return $content;
}

function pripre_pdf() {
	?>
	<form target="_blank" method="post" action="<?php echo plugins_url('pripre/actions/read-pdf.php'); ?>">
	  <fieldset style="border:2pt solid Gray;padding:3px;margin:5px;">
	  <legend>PDF</legend>
	  <input type="hidden" name="p" value="<?php the_ID(); ?>"/>
	  <label>スタイル <select name="s">
	    <option value="">サイトの設定</option>
	    <option value="yoko">横書き</option>
	    <option value="tate">縦書き</option>
	    <option value="tate2">縦２段</option>
	    </select></label>
	  <label>文字 <select name="f">
	    <option value="-2">特小</option>
	    <option value="-1">小</option>
	    <option value="" selected="selected">中</option>
	    <option value="1">大</option>
	    <option value="2">特大</option>
	  </select></label>
	  <label>周囲余白 <select name="m">
	    <option value="e">なし</option>
	    <option value="" selected="selected">小</option>
	    <option value="2">大</option>
	  </select></label>
	  <input type="submit" value="表示" style="background-color:#337fcc;color:White;border:1px solid #225588;border-radius:5px;"/>
	  </fieldset>
	</form>
	<?php
}

// SVGをアップロード許可
add_filter('ext2type', 'pripre_ext2type');
add_filter('upload_mimes', 'pripre_mime_type');

// 標準のスタイル
global $pripre_style_groups;
$pripre_style_groups = NULL;
add_filter('pripre_style_groups', 'pripre_style_groups');
global $pripre_estyles;
$pripre_estyles = NULL;
add_filter('pripre_estyles', 'pripre_estyles');

global $pripre_db_version;
$pripre_db_version = 7;

// データベース生成
function pripre_install() {
    global $wpdb;
    global $pripre_db_version;

    if (!empty($wpdb->charset))
        $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
    if (!empty($wpdb->collate))
        $charset_collate .= " COLLATE $wpdb->collate";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    // 本のマスターテーブル
    dbDelta("CREATE TABLE {$wpdb->prefix}pripre_book (
    id BIGINT(20) UNSIGNED,
    book_date DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (id) REFERENCES {$wpdb->prefix}term_taxonomy(term_taxonomy_id)
    ON DELETE CASCADE
    ) $charset_collate");

    // 本のパラメータ
    dbDelta("CREATE TABLE {$wpdb->prefix}pripre_book_param (
    book_id BIGINT(20) UNSIGNED NOT NULL,
    param_name VARCHAR(64) NOT NULL,
    param_value LONGTEXT,
    PRIMARY KEY (book_id, param_name),
    FOREIGN KEY (book_id) REFERENCES {$wpdb->prefix}pripre_book (id)
    ON DELETE CASCADE
    ) $charset_collate");

    // 記事のパラメータ
    dbDelta("CREATE TABLE {$wpdb->prefix}pripre_post_param (
    post_id BIGINT(20) UNSIGNED NOT NULL,
    param_name VARCHAR(64) NOT NULL,
    param_value LONGTEXT,
    PRIMARY KEY (post_id, param_name),
    FOREIGN KEY (post_id) REFERENCES {$wpdb->prefix}posts(id)
    ON DELETE CASCADE
    ) $charset_collate");

    // 本の記事のパラメータ
    dbDelta("CREATE TABLE {$wpdb->prefix}pripre_book_post_param (
    book_id BIGINT(20) UNSIGNED NOT NULL,
    post_id BIGINT(20) UNSIGNED NOT NULL,
    param_name VARCHAR(64) NOT NULL,
    param_value LONGTEXT,
    PRIMARY KEY (book_id, post_id, param_name),
    FOREIGN KEY (book_id) REFERENCES {$wpdb->prefix}pripre_book (id)
    ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES {$wpdb->prefix}posts(id)
    ON DELETE CASCADE
    ) $charset_collate");
    
    // ユーザースタイル
    dbDelta("CREATE TABLE {$wpdb->prefix}pripre_user_style (
    id SERIAL,
    name VARCHAR(64) NOT NULL,
    size VARCHAR(64),
    css LONGTEXT,
    PRIMARY KEY (id)
    ) $charset_collate");
    
    // 販売・頒布
    dbDelta("CREATE TABLE {$wpdb->prefix}pripre_dist (
	id VARCHAR(32) PRIMARY KEY,
	publisher VARCHAR(20) NOT NULL REFERENCES {$wpdb->prefix}pripre_dist(id),
	added TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	close DATETIME,
	title VARCHAR(200) NOT NULL,
	series VARCHAR(200),
	filename VARCHAR(31) NOT NULL,
	author TEXT,
	description TEXT,
	writing_mode INTEGER,
	retail DECIMAL NOT NULL,
	cover INTEGER,
	browse INTEGER,
	publish INTEGER
    ) $charset_collate");
    
    dbDelta("CREATE TABLE {$wpdb->prefix}pripre_dist_tag (
	id VARCHAR(32) NOT NULL REFERENCES {$wpdb->prefix}pripre_dist(id) ON DELETE CASCADE,
	tag VARCHAR(100) NOT NULL,
	PRIMARY KEY (id,tag)
    ) $charset_collate");
    
    dbDelta("CREATE TABLE {$wpdb->prefix}pripre_dist_version (
	id VARCHAR(32) NOT NULL REFERENCES {$wpdb->prefix}pripre_dist(id),
	version INTEGER NOT NULL,
	added TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	caption VARCHAR(200),
	data LONGBLOB,
	pdf LONGBLOB,
	page INTEGER,
	PRIMARY KEY(id,version)
    ) $charset_collate");
    
    dbDelta("CREATE TABLE {$wpdb->prefix}pripre_dist_cart (
	id VARCHAR(32) PRIMARY KEY,
	expire DATETIME NOT NULL,
	mail_address VARCHAR(256),
	token VARCHAR(32),
	log TEXT
    ) $charset_collate");
    
    dbDelta("CREATE TABLE {$wpdb->prefix}pripre_dist_cart_item (
	id VARCHAR(32) NOT NULL REFERENCES {$wpdb->prefix}pripre_dist_cart(id) ON DELETE CASCADE,
	dist_id VARCHAR(32) NOT NULL REFERENCES {$wpdb->prefix}pripre_dist(id),
	PRIMARY KEY(id,dist_id)
    ) $charset_collate");
    
    dbDelta("CREATE TABLE {$wpdb->prefix}pripre_dist_cart_buy (
	token VARCHAR(32) NOT NULL,
	buyed TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	dist_id VARCHAR(32) NOT NULL REFERENCES {$wpdb->prefix}pripre_dist(id),
	version INTEGER NOT NULL,
	expire DATETIME,
	tickets INTEGER,
	mail_address VARCHAR(256) NOT NULL,
	retail DECIMAL NOT NULL,
	PRIMARY KEY(token,dist_id),
	FOREIGN KEY (dist_id,version) REFERENCES {$wpdb->prefix}pripre_dist_version(id,version)
    ) $charset_collate");
    
    dbDelta("CREATE TABLE {$wpdb->prefix}pripre_dist_log_download (
	id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
	downloaded TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	token VARCHAR(32) REFERENCES {$wpdb->prefix}pripre_dist_cart_buy(token),
	dist_id VARCHAR(32) NOT NULL,
	revision INTEGER NOT NULL,
	log TEXT,
	mail_address VARCHAR(256),
	device VARCHAR(80),
	text_size INTEGER,
	columns INTEGER,
	direction INTEGER
    ) $charset_collate");
    
    add_option("pripre_db_version", $pripre_db_version);
    add_option("pripre_pdf_button", 'on');
}

// データベース削除
function pripre_uninstall() {
	global $wpdb;
	
	$wpdb->query("DROP TABLE {$wpdb->prefix}pripre_dist_log_download");
	$wpdb->query("DROP TABLE {$wpdb->prefix}pripre_dist_cart_buy");
	$wpdb->query("DROP TABLE {$wpdb->prefix}pripre_dist_cart_item");
	$wpdb->query("DROP TABLE {$wpdb->prefix}pripre_dist_cart");
	$wpdb->query("DROP TABLE {$wpdb->prefix}pripre_dist_version");
	$wpdb->query("DROP TABLE {$wpdb->prefix}pripre_dist_tag");
	$wpdb->query("DROP TABLE {$wpdb->prefix}pripre_dist");
	
	$wpdb->query("DROP TABLE {$wpdb->prefix}pripre_book_post");
	$wpdb->query("DROP TABLE {$wpdb->prefix}pripre_book_param");
	$wpdb->query("DROP TABLE {$wpdb->prefix}pripre_book_post_param");
	$wpdb->query("DROP TABLE {$wpdb->prefix}pripre_user_style");
}

add_action('admin_menu', 'pripre_admin_menu');
add_filter('plugin_action_links', 'pripre_add_link', 10, 2);

function pripre_admin_menu() {
	global $pripre_db_version;
	
	$current_db_version = get_option("pripre_db_version");
	if ($current_db_version != $pripre_db_version) {
		delete_option("pripre_db_version");
		pripre_install();
	}
	
	wp_enqueue_script('jquery-ui-draggable');
	wp_enqueue_script('jquery-ui-droppable');
	wp_enqueue_script('jquery-ui-sortable');
	
	wp_register_style('pripre_style', plugins_url('pripre/styles/style.css'));
	wp_enqueue_style('pripre_style');
	
	// サイドメニュー
    // 設定
	add_menu_page('PriPre 設定', 'PriPre', 'administrator', 'pripre.php', 'pripre_options_page', plugins_url('pripre/images/icon.png')); 
	// スタイル
	add_submenu_page('pripre.php', 'ユーザースタイル', 'ユーザースタイル', 'publish_pages', 'pripre_styles', 'pripre_styles');
	// 出版
	add_submenu_page('pripre.php', '出版ツール', '出版ツール', 'publish_pages', 'pripre_publish_tool', 'pripre_publish_tool');
	// 販売・頒布
	add_submenu_page('pripre.php', '販売・頒布', '販売・頒布', 'publish_pages', 'pripre_distribute_tool', 'pripre_distribute_tool');
	// 表紙作成
	add_submenu_page('pripre.php', '表紙テンプレート', '表紙テンプレート', 'publish_pages', 'pripre_cover_tool', 'pripre_cover_tool');
	
    // 投稿画面の印刷プレビュー
    add_meta_box('pripre_post_style_box', __('PriPre', 'pripre_textdomain'), 'pripre_post_style_box', 'post', 'side');
    add_meta_box('pripre_post_style_box', __('PriPre', 'pripre_textdomain'), 'pripre_post_style_box', 'page', 'side');
}

global $pripre_default_style;
$pripre_default_style = get_option("pripre_default_style");
if (empty($pripre_default_style)) {
	$pripre_default_style = PRIPRE_DEFAULT_STYLE;
}
?>