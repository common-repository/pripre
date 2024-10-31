<?php
require_once ('../../../../wp-load.php');
require_once ('../includes/utils.inc.php');

if ( !current_user_can('edit_pages') ) {
	wp_die( __('You are not allowed to edit this item.') );
}

mb_regex_encoding('UTF-8');
mb_internal_encoding("UTF-8");
$book_id = (int)$_GET['book_id'];

global $wpdb;
$sql = "SELECT p.post_date,p.id,p.post_title,p.post_content
        FROM {$wpdb->prefix}term_relationships AS t
        INNER JOIN {$wpdb->prefix}posts AS p ON p.id=t.object_id
        WHERE t.term_taxonomy_id=$book_id";
$entries = $wpdb->get_results($sql, ARRAY_A);
pripre_sort_bookposts($book_id, $entries);

header('Content-Type: text/plain');
foreach ($entries as $entry) {
    $id = $entry['id'];
    $title = $entry['post_title'];
    $content = $entry['post_content'];
    $old = $content;
    
    //ID生成
    $seq = 1;
    $textarr = explode("\n", $content);
    foreach($textarr as &$content) {
    	if (preg_match("|<h[1-6](.*)>(.*)</h[1-6]>|iU", $content, $matches)) {
	    	if (preg_match("|id=\"(.*)\"|iU", $matches[1], $idmatches)) {
	    		if (preg_match("|h$id-(.+)|iU", $idmatches[1], $seqmatches)) {
	    			$seq = max($seq, (int)$seqmatches[1] + 1);
	    		}
	    	}
	    	else {
	    		$content = preg_replace("|(<h[1-6])(.*)(>.*</h[1-6]>)|iU", "\${1}\${2} id=\"h$id-$seq\"\${3}", $content);
	    		++$seq;
			}
    	}
    }
    $content = implode("\n", $textarr);
    if ($content != $old) {
	    $wpdb->query($wpdb->prepare(
	    		"UPDATE {$wpdb->prefix}posts
	    		SET post_content=%s
	    		WHERE id=%d",
	    		$content, $id));
    }
    
    // 目次生成
echo <<< EOD
<div class="section">
<div class="label"><a href="?p=$id#h$id"><span class="text">$title</span></a></div>
EOD;

	preg_match_all("|<h[1-6](.*)>(.*)</h[1-6]>|iU", $content, $matches, PREG_SET_ORDER);
	foreach($matches as $match) {
	$title = $match[2];
	preg_match("|id=\"(.+)\"|iU", $match[1], $idmatches);
	$hid = $idmatches[1];

echo <<< EOD

<div class="item"><a href="?p=$id#$hid">$title</a></div>
EOD;
}

echo <<< EOD

</div>


EOD;
}
?>
