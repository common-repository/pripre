<?php
function pripre_cover_tool() {
	?>
    <div class="wrap">
        <h2 style="float: left;">表紙テンプレート</h2>
        <a href="http://zamasoft.net/2012/05/03/cover/" target="_blank"><br/>ヘルプ</a>
        <br style="clear: both;"/>
        <p>
書籍JANコードを埋め込んだ表紙テンプレートを生成するツールです。
生成したファイル(SVG)はAdobe Illustrator等のSVG対応ドローソフトで読み込んで利用してください。
        </p>
        <p>
<strong>書籍として頒布するためには
<a href="http://www.isbn-center.jp/regist/index.html" target="_blank">日本図書コード管理センター</a>
にISBNコードと書籍JANコードの利用申請をする必要があります。</strong>
        </p>
    <?php
	add_meta_box('pripre_cover_box', __('表紙テンプレート', 'pripre_textdomain'), 'pripre_cover_box', 'pripre_cover', 'normal');
    do_meta_boxes('pripre_cover', 'normal', '');
    ?>
    </div>
    <?php
}

function pripre_cover_box() {
    require_once ('utils.inc.php');
?>
<form method="post" class="pripre_publish"
	action="<?php echo plugins_url('pripre/actions/cover.php'); ?>">
    <table class="form-table">
    <tr valign="top">
        <th>綴じ方</th>
        <td><label><input type="radio" name="bind" value="left" checked="checked"/>左綴じ</label>
        <label><input type="radio" name="bind" value="right"/>右綴じ</label></td>
    </tr>
    <tr valign="top">
      <th>サイズ</th>
      <td><select name="size">
        <option value="210x297">A4</option>
        <option value="127x188">四六判</option>
        <option value="128x182">B6判</option>
        <option value="148x210">A5判（教科書）</option>
        <option value="105x148">A6判（文庫）</option>
        <option value="103x182">新書判</option>
      </select></td>
    </tr>
    <tr valign="top">
      <th>背表紙幅</th>
      <td><input type="text" name="spine" value="8"/>mm
      <label><input type="checkbox" name="marks" value="1" checked="checked"/>
      トンボあり</label>
      </td>
    </tr>
    <tr valign="top">
      <th>配置</th>
      <td><select name="type">
        <option value="A">A</option>
        <option value="B">B</option>
        <option value="C">C</option>
        <option value="D">D</option>
      </select></td>
    </tr>
    <tr valign="top">
      <th>ISBN コード</th>
      <td><input type="text" name="isbn" value="9780000000002"/></td>
    </tr>
    <tr valign="top">
      <th>図書分類</th>
      <td><input type="text" name="book_code" value="0000"/></td>
    </tr>
    <tr valign="top">
      <th>税抜本体価格</th>
      <td><input type="text" name="price" value="10000"/></td>
    </tr>
    </table>
    <input type="submit" name="button" class="button-secondary" value="作成"  />
    </form>
<?php
}
?>
