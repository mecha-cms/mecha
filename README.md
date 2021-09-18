Mecha CMS
=========

> Mecha is a flat-file content management system for minimalists.

[<img src="https://user-images.githubusercontent.com/1669261/119496162-69eb5180-bd8d-11eb-830c-897168f58416.png" width="127" height="46">](https://mecha-cms.com) [<img src="https://user-images.githubusercontent.com/1669261/119496168-6b1c7e80-bd8d-11eb-8ee1-33e8eb5b90ed.png" width="87" height="46">](https://mecha-cms.com/reference) [<img src="https://user-images.githubusercontent.com/1669261/119496170-6bb51500-bd8d-11eb-9d6d-9d95c0510b67.png" width="102" height="46">](https://github.com/mecha-cms/mecha/discussions)

Front-End
---------

The default layout uses only Serif and Mono fonts. Different operating systems might display somewhat different results. This preview was taken through a computer with Linux operating system. Serif font that’s displayed in the preview below should be [DejaVu Serif](https://commons.wikimedia.org/wiki/File:DejaVuSerifSpecimen.svg "DejaVu Serif · Wikimedia Commons"):

![Front-End](https://user-images.githubusercontent.com/1669261/71335843-2f1d3280-2577-11ea-940e-7777eda2a5d1.png)

Back-End ([Optional](https://github.com/mecha-cms/x.panel "Panel Extension"))
-----------------------------------------------------------------------------

To be able to activate the back-end feature requires you to install our [panel](https://github.com/mecha-cms/x.panel "Panel Extension") and [user](https://github.com/mecha-cms/x.user "User Extension") extensions. This feature is forever optional. You can choose to use this feature in the local version only, and get rid of it in the public version as the most basic security measure if you want.

![Back-End](https://user-images.githubusercontent.com/1669261/104103529-31af0f00-52d5-11eb-8e08-fe2c4f2d3b4c.png)

Colors and font types in the control panel preview above are generated from the default layout files. Without them, the display will look like the preview below:

![Back-End](https://user-images.githubusercontent.com/1669261/104103532-3378d280-52d5-11eb-9667-0056264cbdc2.png)

Mecha survives on the principle that a database-less site should be personal, portable, light and easy to be exported and backed up. That’s why most of the projects associated with Mecha are created with personal natures and are dedicated to be used for personal purposes such as blog, journal and diary. Mecha’s market shares are people with high creativity and individuals who want to dedicate themselves to the freedom of speech, that probably don’t have much time to learn web programming languages. By introducing Mecha as files and folders that used to be seen by people everyday in their working desktop, we hope you will soon be familiar with the way Mecha CMS works.

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
 - RSS and Sitemap using the feed and sitemap extension.
 - Easy to use and well documented API.
 - Almost everything are optional.
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

Alternatives
------------

### Command Line

This assumes that your site’s public directory is in `/srv/http`.

#### Using Composer

~~~ .txt
$ cd /srv/http
$ composer create-project mecha-cms/mecha .
~~~

#### Using Git

~~~ .txt
$ cd /srv/http
$ git clone https://github.com/mecha-cms/mecha.git --depth 1 .
$ rm LICENSE README.md
$ rm -r .git
~~~

### Web Browser

Download the installer file from <https://mecha-cms.com/start> and then follow the instructions.

---

Contributors
------------

- [@taufik-nurrohman](https://github.com/taufik-nurrohman) (lead)
- [@igoynawamreh](https://github.com/igoynawamreh) (member)

Release Notes
-------------

### 2.6.3

 - Added `Path::long()` and `Path::short()` method.
 - Added `content-length` header to facilitate AJAX-based applications with progress bars.
 - Added `link` to the core extensions.
 - Added second parameter to the `content` function to allow user to use this function to create a file.
 - Fixed bug of `SGML` class when parsing attributes contain Base64 image URL.
 - Fixed bug of default date format not applied to the output (#117)
 - Improved alert message session. Now you can print the `$alert` variable multiple times and all `<alert>` elements will appear at each location.
 - Improved class auto-loader. `\` now will be converted into `/`, and `__` will be converted into `.` (#96)
 - Improved internal JSON validator.
 - Improved path and URL resolver.
 - Removed function `mecha`.
 - Renamed `$link->active` to `$link->current` in layout navigation.
 - Updated function and method parameter names. Make them to be more semantic for better support with the new named parameter feature in PHP 8.x.

### 2.5.3

 - Bug fixes.

### 2.5.2

 - Added `path` helper function.
 - Removed cache optimization stuff from `.htaccess`. The main `.htaccess` file should focus only to the rewrite module.
 - Removed all image asset methods.

### 2.5.1

 - Added `YAML\SOH`, `YAML\ETB`, and `YAML\EOT` constant in the YAML extension (#94)

### 2.5.0

 - Added `$status` parameter to `Guard::kick()` with default value set to `301`.
 - Added `?` symbol for `Route` as alias of `:key` pattern. So, `foo/bar/:baz` will be equal to `foo/bar/?`.
 - Added ability to set response status automatically based on the first numeric layout path.
 - Renamed `$route->view()` method to `$route->layout()` for consistency.

### 2.4.0

This update focuses on improving the pagination feature of page extension. `$pager->next`, `$pager->parent` and `$pager->prev` will now return a `Page` instance or `null`. This allows us to get richer data easily from the previous and next page property such as to retrieve title, description and image thumbnail to be displayed in the previous and next page navigation HTML.

 - Improved HTML output generated by `To::excerpt()` method.
 - `$pager->next`, `$pager->parent` and `$pager->prev` are now return a `Page` instance or `null`.

### 2.3.2

 - Added `drop` helper function.
 - Improved `Path` methods to allow `null` values.
 - Updated [Parsedown Extra](https://github.com/erusev/parsedown-extra) to version 0.8.0.

### 2.3.1

 - Bug fixes and improvements for the YAML extension.
 - Prefers HTTP/2 header style for both request and response (#89)

### 2.3.0

This update focuses on improving the token feature so that it is not too strict. We need to give other extension opportunities to load the current page for certain purposes without having to change the current token.

 - Added `$deep` option for `From::HTML()` with default value set to `false` to prevent double encode HTML special characters.
 - Added `X-Requested-With` header field to `fetch()` with default value set to `CURL` to let the client to know that the request is not came from a normal web browser (#86)
 - Fixed double encode on HTML attribute’s value caused by the `HTML` class (#85)
 - Fixed form extension bug that caused the comment duplicate checker to fails to work.
 - Fixed layout extension bug that does not capture the custom attributes added to the asset path that is relative to the layout folder.
 - Improved alert counter and serializer. Counting alert messages or converting them into a JSON string will not clear the alert session.
 - Improved hook remover. It is now possible to remove a hook function from closures as long as you store the function closure into a variable. You can then remove the hook function using the variable as a reference.
 - Improved HTTP response headers API. They are now case-insensitive.
 - Improved markdown extension. It is now possible to generate HTML `<figure>` element automatically from every image that appears alone in a paragraph.
 - Improved token mechanism. Added `$for` parameter for `Guard::token()` to set delay time for the token to refresh. The default value is one minute. Previously, every token will be refreshed on every page visits. This causes [several obstacles](https://github.com/mecha-cms/mecha/issues/82) if some extensions require to reload the page to build the cache (even if it is only to load pages in the background) or to prepare it to load the next page via the HTML5 prefetch feature.
 - Removed `State::over()` method.
 - Removed automatic paragraph tags in page description data for consistency with other page data such as the title data. If I had to be consistent, when the description data is required to be wrapped in paragraph tags, then the title data should also be wrapped in heading tags. But it doesn’t (#87)
 - Renamed `Cache::expire()` to `Cache::stale()` for more semantic method naming (#84)
 - Renamed `Route::over()` to `Route::hit()` to make it in-line with `Cache::hit()` (#83)

### 2.2.2

This update focuses on stabilizing the `URL` class. In this version, you can use the class to parse all types of URLs, not only internal URLs but also external URLs. Mecha has its own specifications regarding URLs, and is a bit different from the native PHP `parse_url` function. One of them is the presence of `d` and `i` properties. You can learn more about this on the [URL reference page](https://mecha-cms.com/reference/class/u-r-l).

 - Added optional `$d` and `$i` parameter to the `URL` class constructor.
 - Fixed `$lot` parameter applied to `Route::fire()` does not give any effect.

### 2.2.1

 - Added `$as` parameter to `copy` and `move` methods of `File` and `Folder` class.
 - Fixed `send` function not sending HTML email.
 - Small bug fixes for the `let` hook.

### 2.2.0

Compatible with PHP 7.1.0 and above. Mecha uses `Closure::fromCallable()` method (which is only available in PHP version 7.1.0 and above) to convert named function into closures, so that we can pass `$this` reference from another class instance to the function body even if it’s a named function. The `??` operator becomes a must-have feature in this version as we no longer use extra `$fail` parameter on certain class methods to set default values.

 - Added ability to read special file named `task.php`.
 - Added classes: `Client`, `Files`, `Folders`, `Layout`, `Pager\Page`, `Pager\Pages`, `Pages`, `Post`, `Server`, `SGML`.
 - Added more static functions: `abort`, `alert`, `anemon`, `any`, `c2f`, `cache`, `check`, `concat`, `content`, `cookie`, `eq`, `exist`, `extend`, `f2c`, `f2p`, `fetch`, `find`, `fire`, `ge`, `get`, `gt`, `has`, `hook`, `is`, `kick`, `le`, `let`, `lt`, `map`, `mecha`, `ne`, `not`, `open`, `p2f`, `page`, `pages`, `pluck`, `route`, `send`, `session`, `set`, `shake`, `state`, `step`, `stream`, `test`, `token`.
 - Added page conditional statement features.
 - Moved YAML parser feature to a separate _YAML_ extension.
 - Moved class `Page` and `Pager` to a separate _Page_ extension.
 - Moved configuration file from `.\lot\extend\:extension\state\config.php` to `.\lot\x\:extension\state.php`.
 - Moved configuration file from `.\lot\shield\:layout\state\config.php` to `.\lot\layout\state.php`.
 - Moved configuration file from `.\lot\state\config.php` to `.\state.php`.
 - Moved search functionality to a separate _Search_ extension.
 - Now you can call page properties via `$this` property inside the hook function, either as a named function or as an anonymous function.
 - Removed ability to read special file named `__index.php` and `index__.php`. Only `index.php` file that will be read automatically.
 - Removed automatic constant creation for every folder name in the `.\lot` directory.
 - Removed classes: `Extend` `Elevator`, `Form`, `Mecha`, `Plugin`, `Shield`, `Union`.
 - Removed language and layout switcher features. Now we no longer have the ability to change themes through configuration files, and therefore there will only be one theme on every website built with Mecha.
 - Removed plugin feature. There are no such thing called “plugin” in this version. They are now simply called “extension”.
 - Renamed `.\lot\extend` directory address to `.\lot\x`.
 - Renamed class `Config` to `State`.
 - Renamed class `Date` to `Time`.
 - Renamed class `Guardian` to `Guard`.
 - Renamed class `Message` to `Alert`.
 - Renamed the `X` constant to `P`. “P” stands for “Placeholder”.
 - The “Set, Get and Reset” method naming standard has now been changed to “Set, Get and Let”.
 - Use `null` value everywhere as the default value for all inaccessible data. From now on, use the `??` operator to determine alternative value.
 - `$pages` variable is now a generator. Every page data in it will be loaded only if you iterate over the generator.

### 2.0.0

Compatible with PHP 5.3.6 and above.

 - Refactor.