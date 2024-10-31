<?php

function pripre_add_link($links, $file) {
    if ($file == 'kp/kp.php') {
	array_unshift($links, '<a href="plugins.php?page=pripre_options_page">設定</a>');
    }
    return $links;
}

function pripre_options_page() {
    require_once ('utils.inc.php');
    global $pripre_default_style;
    
    $server = get_option("pripre_copper_server");
    $uri = get_option("pripre_copper_uri");
    if (!$uri) {
        $uri = PRIPRE_COPPER_URI_DEFAULT;
    }
    $user = get_option("pripre_copper_user");
    if (!$user) {
        $user = PRIPRE_COPPER_USER_DEFAULT;
    }
    $password = get_option("pripre_copper_password");
    if (!$password) {
        $password = PRIPRE_COPPER_PASSWORD_DEFAULT;
    }
    
    $properties = get_option("pripre_properties");
    $auto_preview = get_option("pripre_auto_preview");
    $pdf_button = get_option("pripre_pdf_button");
    ?>
    <div class="wrap">
        <h2 style="float: left;">PriPre 設定</h2>
        <a href="http://zamasoft.net/2012/04/23/config/" target="_blank"><br/>ヘルプ</a>
    <form method="post" action="options.php">
        <?php wp_nonce_field('update-options'); ?>
        <h3>Copper PDF 接続</h3>
        <p><label><input type="radio" name="pripre_copper_server" value="" <?php if(!$server) { ?> checked="checked"<?php } ?>/>フリーサーバー（PriPre専用）</label></p>
        <p><label><input type="radio" name="pripre_copper_server" value="1" <?php if($server) { ?> checked="checked"<?php } ?>/>他のサーバー</label></p>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Copper PDF アドレス</th>
                <td><input type="text" name="pripre_copper_uri" size="48" value="<?php echo htmlspecialchars($uri) ?>"/></td>
            </tr>
            <tr valign="top">
                <th scope="row">ユーザー</th>
                <td><input type="text" name="pripre_copper_user" size="24"  value="<?php echo htmlspecialchars($user) ?>"/></td>
            </tr>
            <tr valign="top">
                <th scope="row">パスワード</th>
                <td><input type="password" name="pripre_copper_password" size="24" value="<?php echo htmlspecialchars($password) ?>"/></td>
            </tr>
        </table>
<p>
好みのフォントをインストールするなど、自前のサーバーで自由に使いたい方は<a href="http://copper-pdf.com/">Copper PDFを購入</a>してください！
</p>
        <h3>Copper PDF 追加設定</h3>
        <p>
１行ごとに 名前=値 の組み合わせで設定を書いてください。
設定の一覧は<a href="http://dl.cssj.jp/docs/copper/3.0/html/5100_io-properties.html" target="_blank">こちら</a>です。
        </p>
        <textarea name="pripre_properties" cols="60" rows="8"><?php echo htmlspecialchars($properties) ?></textarea>
        <h3>全般</h3>
        <p>
        <label><input type="checkbox" name="pripre_auto_preview" value="on"<?php if($auto_preview) { ?> checked="checked"<?php } ?>/>
        自動プレビュー</label>
        </p>
        <p>
        <label><input type="checkbox" name="pripre_pdf_button" value="on"<?php if($pdf_button) { ?> checked="checked"<?php } ?>/>
        PDF変換ボタン</label>
        </p>
        <p>
        <label>デフォルトのスタイル
    <?php
    $name = '';
    $templates = pripre_get_styles();
    echo '<select name="pripre_default_style">';
    foreach($templates as $id => $package) {
    	foreach($package['styles'] as $style) {
        if ($name != $package['name']) {
            echo '<option value="'.$style['id'].'"';
         	$name = $package['name'];
        	echo '>'.$name.'</option>';
        }
        echo '<option value="'.$style['id'].'"';
        if ($style['id'] == $pripre_default_style) {
            echo ' selected="selected"';
        }
        echo '>&nbsp;&nbsp;'.$style['name'];
    	}
    }
    echo '</select>';
    ?>
        </label>
        </p>
        
        <input type="hidden" name="action" value="update" />
        <input type="hidden" name="page_options" value="pripre_copper_server,pripre_copper_uri,pripre_copper_user,pripre_copper_password,pripre_properties,pripre_auto_preview,pripre_pdf_button,pripre_default_style" />
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button-primary" value="更新" tabindex="1"  />
        </p>
    </form>
    <?php
    
    try {
        $copper = pripre_get_copper();
        
        $version = $copper->get_server_info("http://www.cssj.jp/ns/ctip/version");
        $doc = new DOMDocument();
        if ($doc->loadXML($version)) {
        $xpath = new DOMXPath($doc);
        $items = $xpath->evaluate("/version/*");
        echo "<h4>Copper PDF バージョン情報</h4>";
        echo '<table>';
        for($i = 0; $i < $items->length; ++$i) {
        	echo '<tr>';
        	echo "<th>".htmlspecialchars($items->item($i)->nodeName)."</th>";
        	echo "<td>".htmlspecialchars($items->item($i)->firstChild->nodeValue)."</td>";
        	echo '</tr>';
        }
        echo "</table>";
                }
        
        $fonts = $copper->get_server_info("http://www.cssj.jp/ns/ctip/fonts");
        $doc = new DOMDocument();
        if ($doc->loadXML($fonts)) {
        $xpath = new DOMXPath($doc);
        $fonts = $xpath->evaluate("/fonts/font[@direction='ltr' and @type='embedded']");
        echo "<h4>Copper PDF フォント情報</h4>";
        echo '<table><tr><th>フォント名</th><th>太さ</th><th>斜体</th></tr>';
        for($i = 0; $i < $fonts->length; ++$i) {
        	echo '<tr>';
        	echo "<td>".htmlspecialchars($fonts->item($i)->attributes->getNamedItem('name')->nodeValue)."</td>";
        	echo "<td>".htmlspecialchars($fonts->item($i)->attributes->getNamedItem('weight')->nodeValue)."</td>";
        	echo "<td>".htmlspecialchars($fonts->item($i)->attributes->getNamedItem('italic')->nodeValue)."</td>";
        	echo '</tr>';
        }
        echo "</table>";
        }
        
        $copper->close();
    } catch (Exception $e) {
        echo "<p style='color: Red;'>Copper PDF 接続に失敗しました</p>";
        echo "<pre style='background-color: Black; color: White; padding: 4px; margin: 4px; white-space: pre-wrap;'>";
        echo htmlspecialchars($e->getMessage());
        echo "</pre>";
    }
    ?>
    </div>
    <?php
}
?>