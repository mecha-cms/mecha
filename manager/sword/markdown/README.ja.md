# js-markdown-extra

ver 1.2.4
based on PHP Markdown Extra 1.2.5
originally developed by [暴満館](http://bmky.net/product/js-markdown-extra/)

### これは何？ ###

js-markdown-extraはPHP Markdown ExtraをJavaScript上で再現しようとしたものです。

[PHP Markdown Extraのデモページ](http://www.michelf.com/projects/php-markdown/dingus/)

正規表現に互換性が無い為に完全に再現できませんでしたが、
単純なmarkdown文法ならば、問題無く変換してくれます。
**たぶん**。

### デモ ###

実際に試すことができます。

[デモページ](http://tanakahisateru.github.com/js-markdown-extra/demo.html)

### 使い方 ###

htmlのhead内でスクリプトを読み込ませた後、任意の箇所で `Markdown` 関数を呼び出してください。

```javascript
	//例 :
	var html = Markdown( text );
```

### 使用上の注意 ###

PHP Markdown Extraを互換性の無い正規表現で不完全に再現しようとしているので、
入力したテキスト次第では正規表現で無限にループしてしまう可能性があります。

### 既知の不具合 ###

* 強調の処理が怪しい。
* 不完全な構文を処理させようとすると固まる可能性有り。
* ブラケットのネストに未対応。

### コピーライト ###

* [Markdown](http://daringfireball.net/projects/markdown/)
* [PHP Markdown & PHP Markdown Extra](http://www.michelf.com/projects/php-markdown/)
* [PHP Markdown on GitHub](https://github.com/michelf/php-markdown)
* [js-markdown](http://rephrase.net/box/js-markdown/)

### ライセンス ###

BSDに基づくオープンソースウェアです。

著作権表示を怠らなければ自由に改変・配布・組み込み等を行うことができます。

### 免責 ###

このスクリプトを利用して発生した障害・損失に関して当方は一切責任を負いません。