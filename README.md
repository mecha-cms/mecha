Mecha CMS
=========

Mecha is a flat-file content management system for minimalists.

[<img src="https://user-images.githubusercontent.com/1669261/119496162-69eb5180-bd8d-11eb-830c-897168f58416.png" width="127" height="46">](https://mecha-cms.com) [<img src="https://user-images.githubusercontent.com/1669261/119496168-6b1c7e80-bd8d-11eb-8ee1-33e8eb5b90ed.png" width="87" height="46">](https://mecha-cms.com/reference) [<img src="https://user-images.githubusercontent.com/1669261/119496170-6bb51500-bd8d-11eb-9d6d-9d95c0510b67.png" width="102" height="46">](https://github.com/mecha-cms/mecha/discussions)

![Code Size](https://img.shields.io/github/languages/code-size/mecha-cms/mecha?color=%23444&style=for-the-badge) ![License](https://img.shields.io/github/license/mecha-cms/mecha?color=%23444&style=for-the-badge)

[![Meme](https://user-images.githubusercontent.com/1669261/187597836-936abaa2-6898-4817-a968-346c4a678d93.png)](https://vincentdnl.com/drawings/developers-side-projects)

Front-End
---------

The [default layout][mecha-cms/y.log] uses only Serif and Mono fonts. Different operating systems may produce slightly
different results. This preview was taken from a computer running [Arch Linux][taufik-nurrohman/arch] operating system.
The Serif font shown in the preview should be [DejaVu Serif][dejavu-serif]:

![Front-End](https://user-images.githubusercontent.com/1669261/190838629-860bfd9c-0444-4426-990f-71a604b95c32.png)

Back-End ([Optional][mecha-cms/x.panel])
-----------------------------------------------------------------------------

To be able to activate the back-end feature requires you to install our [Alert][mecha-cms/x.alert],
[Panel][mecha-cms/x.panel], and [User][mecha-cms/x.user] extensions. This feature is forever optional. You can use this
feature on the local version only, and remove it on the public version to secure your website (only if you don’t trust
this extension).

![Back-End](https://user-images.githubusercontent.com/1669261/193995098-3d4ff7c3-6d49-4d77-86e6-ca3ca0039d3f.png)

Colors and font types in the control panel preview above are generated from the
[default skin][mecha-cms/x.panel.skin.default]. Without them, the display will look like the preview below:

![Back-End](https://user-images.githubusercontent.com/1669261/193995030-9538357e-a5c4-4292-8ad2-a1e657f40acc.png)

Features
--------

 - Writing pages with ease using [Markdown][mecha-cms/x.markdown] extension.
 - Unlimited page children.
 - Unlimited page fields.
 - Extensible as hell.
 - Create unique design for each blog post by adding special CSS and JavaScript files using
   [Art][mecha-cms/x.art] extension.
 - Built-in commenting system using [Comment][mecha-cms/x.comment] extension.
 - RSS and Sitemap using the [Feed][mecha-cms/x.feed] and [Sitemap][mecha-cms/x.sitemap] extension.
 - Easy to use and well documented API.
 - Almost everything are optional.
 - Control panel using [Panel][mecha-cms/x.panel] extension.

Requirements
------------

 - Apache 2.4 and above, with enabled [`mod_rewrite`][apache/mod_rewrite] module.
 - PHP 7.3 and above.

Preparations
------------

 1. Make sure that you already have the required components.
 2. Download the available package from the [home page][home].
 3. Upload Mecha through your FTP/SFTP to the public folder/directory on your site, then extract it!
 4. Take a look on the available extensions and layouts that you might be interested.
 5. Upload your extension files to `.\lot\x` and your layout files to `.\lot\y`. They’re auto-loaded.
 6. Read on how to add pages and tags. Learn on how to create pages from the author by looking at the
    [source code][mecha-cms/site].
 7. Install [Panel][mecha-cms/x.panel] extension if you are stuck by doing everything manually. You always have the full
    control to remove this extension without having to worry that your site will stop running after doing so.

Alternatives
------------

### Command Line

This assumes that your site’s public directory is in `/srv/http`. Make sure the folder is empty, or move the existing
files to another place first. Don’t forget with that `.` at the end of the command as written in the example below, to
clone the repository into the current root folder.

#### Using Composer

~~~ .sh
cd /srv/http
composer create-project mecha-cms/mecha .
~~~

You may want to install our [Panel][mecha-cms/x.panel] extension as well:

~~~ .sh
composer require mecha-cms/x.panel
~~~

#### Using Git

~~~ .sh
cd /srv/http
git clone https://github.com/mecha-cms/mecha.git --depth 1 .
git submodule update --init --recursive
~~~

You may want to install our [Panel][mecha-cms/x.panel] extension as well. Here, [Alert][mecha-cms/x.alert], and
[User][mecha-cms/x.user] extension is required to be installed:

~~~ .sh
git submodule add https://github.com/mecha-cms/x.alert.git --depth 1 lot/x/alert
git submodule add https://github.com/mecha-cms/x.user.git --depth 1 lot/x/user
git submodule add https://github.com/mecha-cms/x.panel.git --depth 1 lot/x/panel
~~~

### Web Browser

Download the installer file from <https://github.com/mecha-cms/start> and then follow the instructions.

---

Contributors
------------

This project exists and survives because of you. I would like to thank all those who have taken the time to contribute
to this project.

[![Contributors](https://opencollective.com/mecha-cms/contributors.svg?avatarHeight=24&button=false&width=890)](https://github.com/mecha-cms/mecha/graphs/contributors)

Contribute financially to keep the project domain and website accessible to everyone. The website provides complete
documentation and latest information regarding the software and future development plans. Some parts of the website also
serve to provide a clean and efficient project file download feature which is obtained by managing responses from the
[GitHub API](https://docs.github.com/en/rest/reference/repos).

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

 [apache/mod_rewrite]: http://httpd.apache.org/docs/current/mod/mod_rewrite.html 'Apache Module `mod_rewrite`'
 [dejavu-serif]: https://commons.wikimedia.org/wiki/File:DejaVuSerifSpecimen.svg 'DejaVu Serif · Wikimedia Commons'
 [home]: https://mecha-cms.com 'Mecha CMS'
 [mecha-cms/site]: https://github.com/mecha-cms/site 'GitHub'
 [mecha-cms/x.alert]: https://github.com/mecha-cms/x.alert 'Alert Extension'
 [mecha-cms/x.art]: https://github.com/mecha-cms/x.art 'Art Extension'
 [mecha-cms/x.comment]: https://github.com/mecha-cms/x.comment 'Comment Extension'
 [mecha-cms/x.feed]: https://github.com/mecha-cms/x.feed 'Feed Extension'
 [mecha-cms/x.markdown]: https://github.com/mecha-cms/x.markdown 'Markdown Extension'
 [mecha-cms/x.panel.skin.default]: https://github.com/mecha-cms/x.panel.skin.default 'Default Skin for Mecha’s Panel'
 [mecha-cms/x.panel]: https://github.com/mecha-cms/x.panel 'Panel Extension'
 [mecha-cms/x.sitemap]: https://github.com/mecha-cms/x.sitemap 'Sitemap Extension'
 [mecha-cms/x.user]: https://github.com/mecha-cms/x.user 'User Extension'
 [mecha-cms/y.log]: https://github.com/mecha-cms/y.log 'Log Layout'
 [php/dom]: http://php.net/manual/en/book.dom.php 'PHP Extension `dom`'
 [php/json]: http://php.net/manual/en/book.json.php 'PHP Extension `json`'
 [php/mbstring]: http://php.net/manual/en/book.mbstring.php 'PHP Extension `mbstring`'
 [taufik-nurrohman/arch]: https://github.com/taufik-nurrohman/arch 'My Minimalist Desk Setup'