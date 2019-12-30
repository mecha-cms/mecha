Mecha CMS
=========

> Mecha is a flat-file content management system for minimalists.

[![Download](https://cloud.githubusercontent.com/assets/1669261/25494257/3d373b0c-2ba3-11e7-8f88-13e36d1b5bd9.png)](http://mecha-cms.com) [![Learn](https://cloud.githubusercontent.com/assets/1669261/25494261/410de820-2ba3-11e7-86e4-bc7901ed403b.png)](http://mecha-cms.com/reference)

[![Gitter](https://img.shields.io/gitter/room/nwjs/nw.js.svg)](https://gitter.im/mecha-cms?utm_source=share-link&utm_medium=link&utm_campaign=share-link)

Front-End
---------

This layout uses only Serif and Mono fonts. Different operating systems might display somewhat different results. This preview was taken through a computer with Linux operating system. Serif font that’s displayed in the preview below should be [DejaVu Serif](https://commons.wikimedia.org/wiki/File:DejaVuSerifSpecimen.svg "DejaVu Serif · Wikimedia Commons"):

![Front-End](https://user-images.githubusercontent.com/1669261/71335843-2f1d3280-2577-11ea-940e-7777eda2a5d1.png)

Back-End ([Optional](https://github.com/mecha-cms/x.panel "Panel Extension"))
-----------------------------------------------------------------------------

To be able to activate the back-end feature requires you to install our [panel](https://github.com/mecha-cms/x.panel "Panel Extension") and [user](https://github.com/mecha-cms/x.user "User Extension") extensions. This feature is forever optional. You can choose to use this feature in the local version only, and get rid of it in the public version as the most basic security measure if you want.

![Back-End](https://user-images.githubusercontent.com/1669261/71335844-2fb5c900-2577-11ea-9b39-4703813566cd.png)

Colors and font types in the control panel preview above are generated from the default layout files. Without them, the display will look like the preview below:

![Back-End](https://user-images.githubusercontent.com/1669261/71335845-2fb5c900-2577-11ea-85e8-f9b93f388202.png)

Mecha is a file-based CMS that survives on principle that a database-less site should be personal, portable, light and easy to be exported and backed up. That’s why most of the projects associated with Mecha are created with personal natures and are dedicated to be used for personal purposes such as blog, journal and diary. Mecha’s market shares are people with high creativity and individuals who want to dedicate themselves to the freedom of speech, that probably don’t have much time to learn web programming languages. By introducing Mecha as files and folders that used to be seen by people everyday in their working desktop, we hope you will soon be familiar with the way Mecha CMS works.

Mecha is as simple as files and folders. Yet, that doesn’t mean that Mecha is weak. Mecha has fairly flexible set of API that you can use without having to make it bloated, keeping you happy focused on developing your own site, according to your personality.

If you want to make something that is super huge with Mecha, that would be possible, but remember that Mecha wasn’t created to replace databases. Mecha was previously created simply to help people getting rid of various resources that are not needed from the start (such as databases). There will be a time when you need a database, and when that time comes, just use a database. Mecha is fairly open to be extended with other database-based applications.

Features
--------

 - Writing pages with ease using Markdown.
 - Unlimited page children.
 - Unlimited page fields.
 - Extensible as hell.
 - Create unique design for each blog post by adding special CSS and JavaScript files using the art extension.
 - Built-in commenting system using the comment extension.
 - Almost everything are optional.
 - RSS and Sitemap using the feed extension.
 - Easy to use and well documented API.
 - Control panel extension.

Environments
------------

 - PHP 7.1.0 and above, with enabled [`mbstring`](http://php.net/manual/en/book.mbstring.php "PHP Extension `mbstring`") and [`dom`](http://php.net/manual/en/book.dom.php "PHP Extension `dom`") extension.
 - Apache 2.4 and above, with enabled [`mod_rewrite`](http://httpd.apache.org/docs/current/mod/mod_rewrite.html "Apache Module `mod_rewrite`") module.

Preparations
------------

 1. Make sure that you already have the required components.
 2. Download the available package from the [home page](https://mecha-cms.com).
 3. Upload Mecha through your FTP/SFTP to the public folder/directory on your site, then extract it!
 4. Take a look on the available extensions and layouts that you might be interested.
 5. Upload your extension files to `.\lot\x` and your layout files to `.\lot\layout`. They’re auto-loaded.
 6. Read on how to add pages and tags. Learn on how to create pages from the author by looking at the [source code](https://github.com/mecha-cms/lot "GitHub").
 7. Install the panel extension if you are stuck by doing everything manually. You always have the full control to remove this extension without having to worry that your site will stop running after doing so.

---

Release Notes
-------------

### 2.2.0

Compatible with PHP 7.1.0 and above. Mecha uses `Closure::fromCallable()` method (which is only available in PHP version 7.1.0 and above) to convert named function into closures, so that we can pass `$this` reference from another class instance to the function body even if it’s a named function. The `??` operator becomes a must-have feature in this version as we no longer use extra `$fail` parameter on certain class methods to set default values.

 - Added more static functions: `abort`, `alert`, `anemon`, `any`, `c2f`, `cache`, `check`, `concat`, `content`, `cookie`, `eq`, `exist`, `extend`, `f2c`, `f2p`, `fetch`, `find`, `fire`, `ge`, `get`, `gt`, `has`, `hook`, `is`, `kick`, `le`, `let`, `lt`, `map`, `mecha`, `ne`, `not`, `open`, `p2f`, `page`, `pages`, `pluck`, `route`, `send`, `session`, `set`, `shake`, `state`, `step`, `stream`, `test`, `token`.
 - Added page conditional statement features.
 - Removed automatic constant creation for every folder name in the `.\lot` directory.
 - Removed classes: `Extend` `Elevator`, `Form`, `Mecha`, `Plugin`, `Shield`, `Union`.
 - Added classes: `Client`, `Files`, `Folders`, `Layout`, `Pager\Page`, `Pager\Pages`, `Pages`, `Post`, `Server`, `SGML`.
 - Renamed class `Config` to `State`.
 - Renamed class `Date` to `Time`.
 - Renamed class `Guardian` to `Guard`.
 - Renamed class `Message` to `Alert`.
 - Renamed `.\lot\extend` directory address to `.\lot\x`.
 - Renamed the `X` constant to `P`. “P” stands for “Placeholder”.
 - Moved configuration file from `.\lot\state\config.php` to `.\state.php`.
 - Moved configuration file from `.\lot\extend\:extension\state\config.php` to `.\lot\x\:extension\state.php`.
 - Moved configuration file from `.\lot\shield\:layout\state\config.php` to `.\lot\layout\state.php`.
 - Moved class `Page` and `Pager` to a separate _Page_ extension.
 - Moved YAML parser feature to a separate _YAML_ extension.
 - Moved search functionality to a separate _Search_ extension.
 - The “Set, Get and Reset” method naming standard has now been changed to “Set, Get and Let”.
 - Now you can call page properties via `$this` property inside the hook function, either as a named function or as an anonymous function.
 - Use `null` value everywhere as the default value for all inaccessible data. From now on, use the `??` operator to determine alternative value.
 - `$pages` variable is now a generator. Every page data in it will be loaded only if you iterate over the generator.
 - Removed plugin feature. There are no such thing called “plugin” in this version. They are now simply called “extension”.
 - Removed language and layout switcher features. Now we no longer have the ability to change themes through configuration files, and therefore there will only be one theme on every website built with Mecha.
 - Added ability to read special file named `task.php`.
 - Removed ability to read special file named `__index.php` and `index__.php`. Only `index.php` file that will be read automatically.

### 2.0.0

Compatible with PHP 5.3.6 and above.

 - Refactor.
