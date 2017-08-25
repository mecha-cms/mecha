Mecha CMS
=========

> Mecha is a flat-file content management system with no dependencies.

[![Download](https://cloud.githubusercontent.com/assets/1669261/25494257/3d373b0c-2ba3-11e7-8f88-13e36d1b5bd9.png)](http://mecha-cms.com) [![Learn](https://cloud.githubusercontent.com/assets/1669261/25494261/410de820-2ba3-11e7-86e4-bc7901ed403b.png)](http://mecha-cms.com/reference)

[![Gitter](https://img.shields.io/gitter/room/nwjs/nw.js.svg)](https://gitter.im/mecha-cms?utm_source=share-link&utm_medium=link&utm_campaign=share-link)

### At a Glance

#### Control Panel

Requires [panel](https://github.com/mecha-cms/extend.panel) extension.

![1](https://cloud.githubusercontent.com/assets/1669261/25493598/f831dd3e-2ba0-11e7-8733-c0bd36c68653.png)

#### Site

The default _shield_ for Mecha version `2.x.x`.

![2](https://cloud.githubusercontent.com/assets/1669261/25493599/f87525a8-2ba0-11e7-9df0-523858a32c55.png)

### Objective

Mecha is a file-based CMS that survives on principle that a database-less site should be personal, portable, light and easy to be exported and backed up. That’s why most of the projects associated with Mecha are created with personal natures and are dedicated to be used for personal purposes such as blog, journal and diary. Mecha’s market shares are people with high creativity and individuals who want to dedicate themselves to the freedom of speech, that probably don’t have much time to learn web programming languages. By introducing Mecha as files and folders that used to be seen by people everyday in their working desktop, we hope you will soon be familiar with the way Mecha CMS works.

Mecha is as simple as files and folders. Yet, that doesn’t mean that Mecha is weak. Mecha has fairly flexible set of API that you can use without having to make it bloated, keeping you happy focused on developing your own site, according to your personality.

If you want to make something that is super huge with Mecha, that would be possible, but remember that Mecha wasn’t created to replace databases. Mecha was previously created simply to help people getting rid of various resources that are not needed from the start (such as databases). There will be a time when you need a database, and when that time comes, just use a database. Mecha is fairly open to be extended with other database-based applications.

### Features

 - Writing pages with ease using Markdown.
 - Unlimited page children.
 - Unlimited page fields.
 - Extensible as hell.
 - Create unique design for each blog post by adding special CSS and JavaScript files using the art plugin.
 - Built-in commenting system using the comment extension.
 - Almost everything are optional.
 - RSS and Sitemap using the feed extension.
 - Easy to use and well documented API.
 - Control panel extension.

### Dependencies

 - PHP 5.6 and above, with enabled [`curl`](http://php.net/manual/en/book.curl.php "PHP Extension `curl`") and [`mbstring`](http://php.net/manual/en/book.mbstring.php "PHP Extension `mbstring`") extension.
 - Apache 2.4 and above, with enabled [`mod_rewrite`](http://httpd.apache.org/docs/current/mod/mod_rewrite.html "Apache Module `mod_rewrite`") module.

### Preparations

 1. Make sure that you already have the required components.
 2. Download the available package from the [home page](http://mecha-cms.com).
 3. Upload Mecha through your FTP/SFTP to the public folder/directory on your site, then extract it!
 4. Take a look on the available extensions and plugins that you might be interested.
 5. Upload your extension folders to `lot\extend` and your plugin folders to `lot\extend\plugin\lot\worker`. They’re auto-loaded.
 6. Read on how to add pages and tags. Learn on how to create pages from the author by looking at the [source code](https://github.com/mecha-cms/lot "GitHub").
 7. Install the panel extension if you are stuck by doing everything manually. You always have the full control to remove this extension without having to worry that your site will stop running after doing so.