# js-markdown-extra

ver 1.2.3
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

Load this script in HTML and call ```Markdown``` function.

```javascript
	//example :
	var html = Markdown( text );
```

### Notice ###

It has possibility of entering infinite loop by some user input because
I try to port PHP Markdown Extra with incompatible regular expression test.
Please stand by to kill your browser process. **I prefer to use it
under dual core CPU.**

### Known issues ###

* Emphasis or strong syntax may have a bug.
* Possible to freeze when incomplete syntax.
* Bracket nesting more than twice for link is unsupported. (is as standard spec)

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

### History ###

#### 1.2.3 - 2013-03-08

  * Incompleted string/itaric bug fixed. #24
  * Unexpectedly separated definition list bug fixed. #23

#### 1.2.2 - 2013-01-10

  * Performance and compatibility improved. #20 #21
  * Suppoting npm install. #22

#### 1.2.1 - 2012-12-06
  Bugfix version as 1.2.1 released.

  * README fixed. #15
  * Problem with tab characters fixed. #16
  * Redundant comma removed. #17
  * Multiple code block was concatenated unexpectedly. #18 #19

#### 1.2.0 - 2012-10-16
  Stable version as 1.2.0 released.

#### 1.2 (beta) - 2012-10-11
  Completely rewritten based on PHP-Markdown-1.2.5.

#### 1.1 - 2008-05-31
  BUGFIX: Reference syntax with empty title. (Thanks to reporter)

#### 1.0 - 2006-07-08
  1st release.
