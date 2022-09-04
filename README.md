Mecha CMS
=========

> Mecha is a flat-file content management system for minimalists.

[<img src="https://user-images.githubusercontent.com/1669261/119496162-69eb5180-bd8d-11eb-830c-897168f58416.png" width="127" height="46">](https://mecha-cms.com) [<img src="https://user-images.githubusercontent.com/1669261/119496168-6b1c7e80-bd8d-11eb-8ee1-33e8eb5b90ed.png" width="87" height="46">](https://mecha-cms.com/reference) [<img src="https://user-images.githubusercontent.com/1669261/119496170-6bb51500-bd8d-11eb-9d6d-9d95c0510b67.png" width="102" height="46">](https://github.com/mecha-cms/mecha/discussions)

![Code Size](https://img.shields.io/github/languages/code-size/mecha-cms/mecha?color=%23444&style=for-the-badge) ![License](https://img.shields.io/github/license/mecha-cms/mecha?color=%23444&style=for-the-badge)

[![Meme](https://user-images.githubusercontent.com/1669261/187597836-936abaa2-6898-4817-a968-346c4a678d93.png)](https://vincentdnl.com/drawings/developers-side-projects)

Front-End
---------

The default layout uses only Serif and Mono fonts. Different operating systems might display somewhat different results. This preview was taken through a computer with Linux operating system. Serif font that’s displayed in the preview below should be [DejaVu Serif](https://commons.wikimedia.org/wiki/File:DejaVuSerifSpecimen.svg "DejaVu Serif · Wikimedia Commons"):

![Front-End](https://user-images.githubusercontent.com/1669261/141647070-e994e220-8061-4cd6-bdf7-45ef9ad8ea56.png)

Back-End ([Optional](https://github.com/mecha-cms/x.panel "Panel Extension"))
-----------------------------------------------------------------------------

To be able to activate the back-end feature requires you to install our [alert](https://github.com/mecha-cms/x.alert "Alert Extension"), [panel](https://github.com/mecha-cms/x.panel "Panel Extension") and [user](https://github.com/mecha-cms/x.user "User Extension") extensions. This feature is forever optional. You can use this feature on the local version only, and remove it on the public version to secure your website (only if you don&rsquo;t trust this extension).

![Back-End](https://user-images.githubusercontent.com/1669261/141484343-0568ef0d-f7c5-4991-a8ee-7773379415b2.png)

Colors and font types in the control panel preview above are generated from the default layout files. Without them, the display will look like the preview below:

![Back-End](https://user-images.githubusercontent.com/1669261/141484323-d97a403f-5706-4e84-b5ab-78ebd9eb6bd9.png)

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

Requirements
------------

 - Apache 2.4 and above, with enabled [`mod_rewrite`](http://httpd.apache.org/docs/current/mod/mod_rewrite.html "Apache Module `mod_rewrite`") module.
 - PHP 7.1 and above, with enabled [`mbstring`](http://php.net/manual/en/book.mbstring.php "PHP Extension `mbstring`") and [`dom`](http://php.net/manual/en/book.dom.php "PHP Extension `dom`") extension.

Preparations
------------

 1. Make sure that you already have the required components.
 2. Download the available package from the [home page](https://mecha-cms.com).
 3. Upload Mecha through your FTP/SFTP to the public folder/directory on your site, then extract it!
 4. Take a look on the available extensions and layouts that you might be interested.
 5. Upload your extension files to `.\lot\x` and your layout files to `.\lot\y`. They’re auto-loaded.
 6. Read on how to add pages and tags. Learn on how to create pages from the author by looking at the [source code](https://github.com/mecha-cms/lot "GitHub").
 7. Install the panel extension if you are stuck by doing everything manually. You always have the full control to remove this extension without having to worry that your site will stop running after doing so.

Alternatives
------------

### Command Line

This assumes that your site’s public directory is in `/srv/http`. Make sure the folder is empty, or move the existing files to another place first. Don’t forget with that `.` at the end of the command as written in the example below, to clone the repository into the current root folder.

#### Using Composer

~~~ .sh
$ cd /srv/http
$ composer create-project mecha-cms/mecha .
~~~

You may want to install our [panel](https://github.com/mecha-cms/x.panel) extension as well:

~~~ .sh
$ composer require mecha-cms/x.panel
~~~

#### Using Git

~~~ .sh
$ cd /srv/http
$ git clone https://github.com/mecha-cms/mecha.git --depth 1 .
$ git submodule update --init --recursive
$ rm .gitmodules composer.json LICENSE README.md
$ rm -r .git
~~~

You may want to install our [panel](https://github.com/mecha-cms/x.panel) extension as well. Here, [alert](https://github.com/mecha-cms/x.alert) and [user](https://github.com/mecha-cms/x.user) extension is required to be installed:

~~~ .sh
$ cd lot/x
$ git clone https://github.com/mecha-cms/x.alert.git --depth 1 alert
$ rm alert/LICENSE alert/README.md
$ rm -r alert/.git
$ git clone https://github.com/mecha-cms/x.user.git --depth 1 user
$ rm user/LICENSE user/README.md
$ rm -r user/.git
$ git clone https://github.com/mecha-cms/x.panel.git --depth 1 panel
$ rm panel/LICENSE panel/README.md
$ rm -r panel/.git
~~~

### Web Browser

Download the installer file from <https://mecha-cms.com/start> and then follow the instructions.

---

Contributors
------------

This project exists and survives because of you. I would like to thank all those who have taken the time to contribute to this project.

[![Contributors](https://opencollective.com/mecha-cms/contributors.svg?avatarHeight=24&button=false&width=890)](https://github.com/mecha-cms/mecha/graphs/contributors)

Contribute financially to keep the project domain and website accessible to everyone. The website provides complete documentation and latest information regarding the software and future development plans. Some parts of the website also serve to provide a clean and efficient project file download feature which is obtained by managing responses from the [GitHub API](https://docs.github.com/en/rest/reference/repos).

### Backers

[![Contribute](https://opencollective.com/mecha-cms/individuals.svg?width=890)](https://opencollective.com/mecha-cms)

### Sponsors

[![0](https://opencollective.com/mecha-cms/organization/0/avatar.svg)](https://opencollective.com/mecha-cms/organization/0/website)
[![1](https://opencollective.com/mecha-cms/organization/1/avatar.svg)](https://opencollective.com/mecha-cms/organization/1/website)
[![2](https://opencollective.com/mecha-cms/organization/2/avatar.svg)](https://opencollective.com/mecha-cms/organization/2/website)
[![3](https://opencollective.com/mecha-cms/organization/3/avatar.svg)](https://opencollective.com/mecha-cms/organization/3/website)
[![4](https://opencollective.com/mecha-cms/organization/4/avatar.svg)](https://opencollective.com/mecha-cms/organization/4/website)
[![5](https://opencollective.com/mecha-cms/organization/5/avatar.svg)](https://opencollective.com/mecha-cms/organization/5/website)
[![6](https://opencollective.com/mecha-cms/organization/6/avatar.svg)](https://opencollective.com/mecha-cms/organization/6/website)
[![7](https://opencollective.com/mecha-cms/organization/7/avatar.svg)](https://opencollective.com/mecha-cms/organization/7/website)
[![8](https://opencollective.com/mecha-cms/organization/8/avatar.svg)](https://opencollective.com/mecha-cms/organization/8/website)
[![9](https://opencollective.com/mecha-cms/organization/9/avatar.svg)](https://opencollective.com/mecha-cms/organization/9/website)