# js-markdown-extra

ver 1.2.4
based on PHP Markdown Extra 1.2.5  
originally developed by [boumankan](http://bmky.net/product/js-markdown-extra/)

### What's this? ###

js-markdown-extra is a experimental JavaScript port of PHP Markdown Extra.

[PHP Markdown Extra demo](http://www.michelf.com/projects/php-markdown/dingus/)

I couldn't retain complete comaptibility because of difference between PHP's
regular expression and JavaScript's one, but it can convert most of simple
markdown text.
**perhaps**.

### Demo ###

You can try in your hand.

[Demo page](http://tanakahisateru.github.com/js-markdown-extra/demo.html)

### How to use ###

Load this script in HTML and call `Markdown` function.

```javascript
	//example :
	var html = Markdown( text );
```

### Notice ###

It has possibility of entering infinite loop by some user input because
I try to port PHP Markdown Extra with incompatible regular expression test.

### Known issues ###

* Emphasis or strong syntax may have a bug.
* Possible to freeze when incomplete syntax.
* Bracket nesting is unsupported.

### Copyright ###

* [Markdown](http://daringfireball.net/projects/markdown/)
* [PHP Markdown & PHP Markdown Extra](http://www.michelf.com/projects/php-markdown/)
* [PHP Markdown on GitHub](https://github.com/michelf/php-markdown)
* [js-markdown](http://rephrase.net/box/js-markdown/)

### License ###

This software is based on BSD license.

Free for modification, redistribution and embedding if copyright included.

### Agreement ###

Shall we not be liable for any damages caused by this software.