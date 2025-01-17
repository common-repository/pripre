=== PriPre ===
Contributors: miyabe
Donate link: https://zamasoft.net/pripre
Tags: pdf, epub, publish, print
Requires at least: 6.0.0
Tested up to: 6.6.2
Stable tag: 0.4.11

印刷物(PDF)・電子書籍(EPUB)の両方に対応した出版・電子書籍販売システムです。

== Description ==

PriPreは、書籍として出版できるレベルの印刷向けPDFと、電子出版のためのEPUBを作成し、また電子書籍の販売をするためのプラグインです。

次の機能を備えています。

* ブログの読者が自由に使うことができるPDF変換機能を各記事に付ける。
* ルビ、圏点などのために青空文庫風のタグを使用できる。
* メディアライブラリにSVGをアップロードできる。
* 各記事を印刷向けレイアウトの画像、PDF、HTMLでプレビューする。
* 複数の記事をまとめて出版用のPDFを生成する。
* 複数の記事をまとめて電子出版用のEPUBを生成する。
* ISBN, 書籍JANバーコードを表示した表紙テンプレートを生成する。
* PayPal決済で電子書籍を販売する。

PDF変換のためにCopper PDF http://copper-pdf.com/ を使用しています。

詳細なマニュアルはこちらをご覧ください。

https://zamasoft.net/pripre

== Installation ==

1. pripreディレクトリを/wp-content/plugins/ディレクトリに配置してください。

プラグインのインストールに成功すると、管理画面左下に「PriPre」という項目が現れます。
また、各記事の編集画面で「PriPre」というボックスが表示されます。

== Frequently Asked Questions ==

ご質問は twitter @miyabet まで！

== Screenshots ==

1. 記事を編集中に、即座に印刷レイアウトを確認することができます。
2. 同じカテゴリーの複数の記事を並べ替えて本にすることができます。
3. スマートフォンやタブレットに合わせたPDFを読者がその場で作ることができます。

== Changelog ==
= 0.4.11 =
Block Editor に対応しました。

= 0.4.10 =
PHP8で警告が出ないようにしました。

= 0.4.9 =
HTMLを整形した際に環境によって文字化けする問題に対処しました。

= 0.4.6 =
記事に追加のCSSを設定した時にEPUBが正常に生成されないバグを修正。
圧縮SVG(.svgz)が画像に変換されるようにしました。

= 0.4.2rc =
販売機能で、購入後ダウンロード画面で飛べないバグを修正。
EPUB生成で追加CSSが後に適用されるようにしました。
EPUB生成で最初の画像にcover-imageプロパティを付けるようにしました。

= 0.4.1rc =
Internet Explorerでプレビューのポップアップに画像が表示されないバグを修正。

= 0.4.0rc =
Internet Explorerで出版ツールが正常に動作しない不具合に対応しました。

販売機能を入れました。

= 0.3.2rc =
PriPre用のフリーサーバーを利用できるようにしました。

EPUB出力時にタグの不整合を整形できるようにしました。

= 0.3.1rc =
EPUB出力時に縦書き用文字の変換を行うかどうかを選べるようにしました。

EPUB出力時にSVGをPDFに変換する機能を追加しました。

多くのEPUBリーダーとの互換性のために、CSSを物理方向指定にしました。

= 0.3.0rc =
無変換・図・説明タグを追加しました。

記事のタイトルの表示・非表示を切り替えられるようにしました。

記事の開始ページを指定できるようにしました。

ユーザーのスタイル（CSS・ページサイズ）を準備・記事ごとに設定できるようにしました。

書籍・記事でのスタイルの指定を廃止しました。

EPUB出力時のスタイルを改良しました。

= 0.2.5a =
EPUBのタイトルにタグが残る問題に対処しました。

= 0.2.4a =
Googleツールバーを入れたIEでブログ出版局申込時にポップアップとしてブロックされる問題に対応しました。

= 0.2.3a =
データベースが正しく生成されないため、本の記事順が反映されないバグを修正しました。

= 0.2.2a =
Perlが不要になりました。

ロリポップ！サーバーで動作するようになりました。

= 0.2.1a =
PEAR::HTTP_Request2のインストールを不要にしました。

目次生成ツールを追加しました。

= 0.2.0a =
オンデマンド印刷サービス（ブログ出版局）に直接印刷発注する機能を追加。

単行本スタイルを調整しました。

スタイルをプラグインとして追加する機能を追加。

グレイスケールでPDFを出力できるようにしました。

ハイパーリンク、ブックマークがPDFに適用されるようにしました。

= 0.1.7d =
EPUBのspine要素にtoc="ncx"属性が抜けているバグを修正。

投稿の更新ボタンでスタイルが更新されないバグを修正。

= 0.1.6d =
bodyタグのclassにスタイル名を入れるようにしました（スタイルごとのカスタマイズをしやすくするため）

= 0.1.5d =
改ページ、改カラムのためのタグを加えました。

キャプション付きの画像に対応しました。

単行本のデフォルトの大きさを四六版(127mm x 188mm)にしました。

投稿中に &lt;!-- PRIPRE_PDF --&gt;と入れると、そこにPDF変換ボタンが出るようにしました。

スタイルごとにカスタマイズできるようにしました。

= 0.1.4d =
表紙や漫画などに使えるフルサイズレイアウト「全面」を加えました。

= 0.1.3d =
電子書籍端末向けPDFを読みやすいように行間を空け、両合わせをした。

オフィス向けに書類スタイルを追加。

EPUB 2.0リーダでも読めるようにEPUBにNCXを追加。

サーチエンジンにPDFを捕捉されないように読者用のPDF生成ボタンをGETからPOSTメソッドに変更。

= 0.1.2d =
「設定」メニューが消えてしまうバグを修正。

スタイルのセレクトボックスの内容の順番が安定するようにした。

横書き目次スタイルを追加。

一時ファイルの生成に /tmp ディレクトリを使うようにした。

各スタイルの解説ページを追加。

= 0.1.1d =
WordPressをサイトのルートパス以外に配備した場合にPDFのスタイルが適用されないバグを修正。

EPUBの書籍スタイルを適用しないスタイルを追加。

横書き本文のスタイルを追加。

= 0.1d =
最初のリリース。

== Upgrade Notice ==

= 0.1d =
最初のリリース。
